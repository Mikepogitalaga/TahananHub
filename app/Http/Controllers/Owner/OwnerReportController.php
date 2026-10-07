<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Expense;
use App\Models\RentBilling;
use App\Models\RentPayment;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerReportController extends Controller
{
    public function index(Request $request): View
    {
        $owner = auth()->user()->houseOwner;
        $propertyIds = $owner->properties()->pluck('id');

        $year = (int) $request->get('year', date('Y'));
        $month = $request->get('month', date('m'));
        $selectedPeriod = "{$year}-{$month}";

        // 1. Monthly Financials
        $monthlyCollections = RentPayment::whereHas('billing.contract', function ($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            })
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->sum('amount');

        $monthlyExpenses = Expense::where('house_owner_id', $owner->id)
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->sum('amount');

        $netProfit = $monthlyCollections - $monthlyExpenses;

        // 2. Annual Overview by Month (12 months)
        $monthlyBreakdown = [];
        for ($m = 1; $m <= 12; $m++) {
            $col = RentPayment::whereHas('billing.contract', function ($q) use ($propertyIds) {
                    $q->whereIn('property_id', $propertyIds);
                })
                ->whereYear('payment_date', $year)
                ->whereMonth('payment_date', $m)
                ->sum('amount');

            $exp = Expense::where('house_owner_id', $owner->id)
                ->whereYear('expense_date', $year)
                ->whereMonth('expense_date', $m)
                ->sum('amount');

            $monthlyBreakdown[] = [
                'month_name'  => Carbon::create($year, $m, 1)->format('M'),
                'month_num'   => $m,
                'collections' => (float)$col,
                'expenses'    => (float)$exp,
                'net'         => (float)($col - $exp),
            ];
        }

        // 3. Occupancy Statistics
        $totalBeds = Bed::whereHas('room', function ($q) use ($propertyIds) {
            $q->whereIn('property_id', $propertyIds);
        })->count();

        $occupiedBeds = Bed::whereHas('room', function ($q) use ($propertyIds) {
            $q->whereIn('property_id', $propertyIds);
        })->where('status', 'occupied')->count();

        $occupancyRate = $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100, 1) : 0;

        // 4. Outstanding Balances / Delinquent list
        $unpaidBillings = RentBilling::whereHas('contract', function ($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            })
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->with(['tenant', 'contract.property'])
            ->orderBy('due_date', 'asc')
            ->get();

        $totalUnpaidBalance = $unpaidBillings->sum('balance');

        return view('owner.reports.index', compact(
            'year',
            'month',
            'selectedPeriod',
            'monthlyCollections',
            'monthlyExpenses',
            'netProfit',
            'monthlyBreakdown',
            'totalBeds',
            'occupiedBeds',
            'occupancyRate',
            'unpaidBillings',
            'totalUnpaidBalance'
        ));
    }
}

