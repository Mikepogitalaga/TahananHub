<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Property;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OwnerExpenseController extends Controller
{
    public function __construct(
        private ActivityLogService $activityLog
    ) {}

    public function index(Request $request): View
    {
        $owner = auth()->user()->houseOwner;

        $query = Expense::where('house_owner_id', $owner->id)
            ->with('property')
            ->latest('expense_date');

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('month')) {
            $query->where('expense_date', 'like', $request->month . '%');
        }

        $expenses = $query->paginate(15)->withQueryString();
        $properties = $owner->properties;
        $totalExpensesAmount = (clone $query)->sum('amount');

        return view('owner.expenses.index', compact('expenses', 'properties', 'totalExpensesAmount'));
    }

    public function create(): View
    {
        $owner = auth()->user()->houseOwner;
        $properties = $owner->properties;

        return view('owner.expenses.create', compact('properties'));
    }

    public function store(Request $request): RedirectResponse
    {
        $owner = auth()->user()->houseOwner;

        $validated = $request->validate([
            'property_id'    => ['required', 'exists:properties,id'],
            'category'       => ['required', 'in:electricity,water,internet,maintenance,repairs,cleaning,supplies,furniture,other'],
            'description'    => ['required', 'string', 'max:255'],
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'expense_date'   => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,gcash,bank_transfer,other'],
            'receipt'        => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        $property = Property::where('house_owner_id', $owner->id)->findOrFail($validated['property_id']);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store("expenses/{$owner->id}", 'local');
        }

        $count = Expense::where('house_owner_id', $owner->id)->count() + 1;
        $expenseNumber = 'EXP-' . date('Ym') . '-' . str_pad((string)$count, 4, '0', STR_PAD_LEFT);

        $expense = Expense::create([
            'expense_number' => $expenseNumber,
            'house_owner_id' => $owner->id,
            'property_id'    => $property->id,
            'category'       => $validated['category'],
            'description'    => $validated['description'],
            'amount'         => $validated['amount'],
            'expense_date'   => $validated['expense_date'],
            'payment_method' => $validated['payment_method'],
            'receipt_path'   => $receiptPath,
            'notes'          => $validated['notes'] ?? null,
        ]);

        $this->activityLog->log('create_expense', 'expense', $expense->id, $expense->expense_number, [
            'amount'   => $expense->amount,
            'category' => $expense->category,
        ]);

        return redirect()->route('owner.expenses.index')->with('success', "Expense {$expense->expense_number} recorded.");
    }

    public function edit(Expense $expense): View
    {
        $this->authorizeOwner($expense);
        $owner = auth()->user()->houseOwner;
        $properties = $owner->properties;

        return view('owner.expenses.edit', compact('expense', 'properties'));
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorizeOwner($expense);
        $owner = auth()->user()->houseOwner;

        $validated = $request->validate([
            'property_id'    => ['required', 'exists:properties,id'],
            'category'       => ['required', 'in:electricity,water,internet,maintenance,repairs,cleaning,supplies,furniture,other'],
            'description'    => ['required', 'string', 'max:255'],
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'expense_date'   => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,gcash,bank_transfer,other'],
            'receipt'        => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        Property::where('house_owner_id', $owner->id)->findOrFail($validated['property_id']);

        if ($request->hasFile('receipt')) {
            if ($expense->receipt_path && Storage::disk('local')->exists($expense->receipt_path)) {
                Storage::disk('local')->delete($expense->receipt_path);
            }
            $validated['receipt_path'] = $request->file('receipt')->store("expenses/{$owner->id}", 'local');
        }

        $expense->update($validated);

        $this->activityLog->log('update_expense', 'expense', $expense->id, $expense->expense_number);

        return redirect()->route('owner.expenses.index')->with('success', "Expense updated successfully.");
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $this->authorizeOwner($expense);

        if ($expense->receipt_path && Storage::disk('local')->exists($expense->receipt_path)) {
            Storage::disk('local')->delete($expense->receipt_path);
        }

        $num = $expense->expense_number;
        $expense->delete();

        $this->activityLog->log('delete_expense', 'expense', null, $num);

        return redirect()->route('owner.expenses.index')->with('success', "Expense {$num} deleted.");
    }

    private function authorizeOwner(Expense $expense): void
    {
        if ($expense->house_owner_id !== auth()->user()->houseOwner?->id) {
            abort(403, 'Unauthorized.');
        }
    }
}

