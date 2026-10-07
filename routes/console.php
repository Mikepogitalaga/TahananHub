<?php

use App\Models\RentBilling;
use App\Models\RentalContract;
use App\Notifications\BillingOverdue;
use App\Services\BillingService;
use App\Services\PrivacyAccessService;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// -----------------------------------------------------------------------------
// Automatic monthly rent billing (idempotent — never duplicates a period)
// -----------------------------------------------------------------------------

Artisan::command('billing:generate-monthly {--month= : Period in YYYY-MM form (defaults to current month)}', function () {
    $billingService = app(BillingService::class);

    $month = $this->option('month')
        ? Carbon::parse($this->option('month'))->startOfMonth()
        : Carbon::now()->startOfMonth();

    $contracts = RentalContract::query()
        ->where('status', 'active')
        ->whereDate('start_date', '<=', $month->copy()->endOfMonth())
        ->where(function ($q) use ($month) {
            $q->whereNull('end_date')
              ->orWhereDate('end_date', '>=', $month->copy()->startOfMonth());
        })
        ->get();

    $generated = 0;
    $skipped = 0;

    foreach ($contracts as $contract) {
        if ($billingService->generateMonthlyBilling($contract, $month) !== null) {
            $generated++;
        } else {
            $skipped++;
        }
    }

    $this->info(sprintf(
        'Billing for %s: %d generated, %d skipped (already exist or ineligible).',
        $month->format('F Y'),
        $generated,
        $skipped
    ));
})->purpose('Generate monthly rent billing for all active contracts without duplicates');

// -----------------------------------------------------------------------------
// Mark overdue billings + notify tenants
// -----------------------------------------------------------------------------

Artisan::command('billing:mark-overdue', function () {
    $billingService = app(BillingService::class);

    // Capture the set that is about to transition so each tenant is notified once.
    $due = RentBilling::whereIn('status', ['unpaid', 'partial'])
        ->whereDate('due_date', '<', Carbon::now()->toDateString())
        ->get();

    $count = $billingService->markOverdueBillings();

    foreach ($due as $billing) {
        $billing->tenant?->user?->notify(new BillingOverdue($billing));
    }

    $this->info("Marked {$count} billing(s) overdue; " . $due->count() . " tenant notification(s) queued.");
})->purpose('Mark unpaid rent billings past their due date as overdue');

// -----------------------------------------------------------------------------
// Expire temporary admin privacy-access sessions
// -----------------------------------------------------------------------------

Artisan::command('privacy:expire-sessions', function () {
    $count = app(PrivacyAccessService::class)->expireOldSessions();
    $this->info("Expired {$count} temporary admin access session(s).");
})->purpose('Automatically expire approved Super Admin privacy access sessions');

// -----------------------------------------------------------------------------
// Contract expiry & expiring-soon reminders
// -----------------------------------------------------------------------------

Artisan::command('contracts:manage-expiring {--soon-days=30 : Days before expiry to notify owners}', function () {
    $soonDays = (int) $this->option('soon-days');
    $today = Carbon::today();

    // 1. Mark active contracts past their end date as expired (notify once on transition).
    $expired = RentalContract::query()
        ->where('status', 'active')
        ->whereDate('end_date', '<', $today)
        ->get();

    foreach ($expired as $contract) {
        $contract->update(['status' => 'expired']);
        $contract->property?->houseOwner?->user?->notify(new \App\Notifications\ContractExpired($contract));
    }

    // 2. Notify owners about contracts expiring within the window (deduplicated).
    $expiring = RentalContract::query()
        ->where('status', 'active')
        ->whereNotNull('end_date')
        ->whereBetween('end_date', [$today, $today->copy()->addDays($soonDays)])
        ->get();

    $reminders = 0;
    foreach ($expiring as $contract) {
        $ownerUser = $contract->property?->houseOwner?->user;
        if (! $ownerUser) {
            continue;
        }

        $alreadyNotified = $ownerUser->notifications()
            ->where('type', \App\Notifications\ContractExpiringSoon::class)
            ->where('data', 'like', '%"contract_id":' . $contract->id . ',%')
            ->exists();

        if ($alreadyNotified) {
            continue;
        }

        $daysRemaining = $today->diffInDays(Carbon::parse($contract->end_date));
        $ownerUser->notify(new \App\Notifications\ContractExpiringSoon($contract, (int) $daysRemaining));
        $reminders++;
    }

    $this->info(sprintf(
        'Contracts: %d marked expired, %d expiring-soon reminder(s) sent.',
        $expired->count(),
        $reminders
    ));
})->purpose('Expire past-due rental contracts and notify owners about upcoming expirations');

// -----------------------------------------------------------------------------
// Schedule (run on Windows via: php artisan schedule:work  — or Task Scheduler)
// -----------------------------------------------------------------------------

Schedule::command('billing:generate-monthly')->dailyAt('00:10')->withoutOverlapping();
Schedule::command('billing:mark-overdue')->dailyAt('00:30')->withoutOverlapping();
Schedule::command('privacy:expire-sessions')->everyFiveMinutes();
Schedule::command('contracts:manage-expiring')->dailyAt('07:00')->withoutOverlapping();

