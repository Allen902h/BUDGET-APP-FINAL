<?php

namespace App\Services;

use App\Models\IncomeCycle;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BudgetService
{
    public function summary(IncomeCycle $cycle): array
    {
        $cycle->loadMissing(['user', 'transactions.category']);

        $transactions = $cycle->transactions->sortByDesc('timestamp')->values();
        $incomeTransactions = $transactions->where('transaction_type', 'income')->values();
        $expenseTransactions = $transactions->where('transaction_type', 'expense')->values();

        $fixedExpenses = $expenseTransactions
            ->filter(fn ($transaction) => $transaction->category?->is_fixed)
            ->sum('amount');

        $variableExpenses = $expenseTransactions
            ->filter(fn ($transaction) => ! $transaction->category?->is_fixed)
            ->sum('amount');

        $baseIncome = (float) $cycle->amount;
        $extraIncome = (float) $incomeTransactions->sum('amount');
        $totalIncome = $baseIncome + $extraIncome;
        $totalExpenses = (float) $expenseTransactions->sum('amount');
        $remainingBalance = $totalIncome - $totalExpenses;
        $projectedSavings = $remainingBalance;
        $remainingBudget = max(0, $remainingBalance);

        $startDate = Carbon::parse($cycle->start_date)->startOfDay();
        $endDate = Carbon::parse($cycle->end_date)->endOfDay();
        $today = now();
        $effectiveEnd = $today->lessThan($endDate) ? $today->copy() : $endDate->copy();

        $daysPassed = max(1, $startDate->diffInDays($effectiveEnd) + 1);
        $cycleDays = max(1, $startDate->diffInDays($endDate) + 1);
        $daysRemaining = max(0, $today->copy()->startOfDay()->diffInDays($endDate, false));
        $burnRate = $totalExpenses / $daysPassed;
        $idealDailySpend = $totalIncome > 0 ? $totalIncome / $cycleDays : 0;
        $timeProgress = min(100, round(($daysPassed / $cycleDays) * 100, 2));
        $spendProgress = $totalIncome > 0 ? min(100, round(($totalExpenses / $totalIncome) * 100, 2)) : 0;
        $projectedEndSpend = round($burnRate * $cycleDays, 2);
        $projectedOverspend = max(0, $projectedEndSpend - $totalIncome);

        $warning = null;
        if ($burnRate > ($idealDailySpend * 1.35) || ($daysPassed <= 3 && $totalExpenses >= ($totalIncome * 0.5))) {
            $warning = 'Warning: High burn rate detected. Spending is outpacing the current budget plan.';
        }

        $expenseCategoryTotals = $expenseTransactions
            ->groupBy(fn ($transaction) => $transaction->category?->name ?? 'Uncategorized')
            ->map(fn ($items) => round($items->sum('amount'), 2));

        $incomeVsExpense = [
            'Income' => round($totalIncome, 2),
            'Expenses' => round($totalExpenses, 2),
            'Savings' => round(max(0, $remainingBalance), 2),
        ];

        $categoryBreakdown = $this->categoryBreakdown($expenseTransactions);
        $budgetAlerts = $categoryBreakdown
            ->filter(fn (array $category) => $category['limit'] && $category['used_percentage'] >= 90)
            ->values();

        $monthlyLimit = $cycle->user?->monthly_budget_limit ? (float) $cycle->user->monthly_budget_limit : null;
        $monthlyBudgetUsed = $monthlyLimit && $monthlyLimit > 0 ? round(($totalExpenses / $monthlyLimit) * 100, 2) : null;

        $savingsTargetAmount = round($totalIncome * (((float) ($cycle->user?->savings_goal_percentage ?? 0)) / 100), 2);
        $savingsProgress = $savingsTargetAmount > 0
            ? round((max(0, $remainingBalance) / $savingsTargetAmount) * 100, 2)
            : null;

        $dueBillAlerts = $this->dueBillAlerts($cycle, $expenseTransactions);

        return [
            'baseIncome' => round($baseIncome, 2),
            'extraIncome' => round($extraIncome, 2),
            'totalIncome' => round($totalIncome, 2),
            'fixedExpenses' => round($fixedExpenses, 2),
            'variableExpenses' => round($variableExpenses, 2),
            'totalExpenses' => round($totalExpenses, 2),
            'remainingBalance' => round($remainingBalance, 2),
            'projectedSavings' => round($projectedSavings, 2),
            'remainingBudget' => round($remainingBudget, 2),
            'burnRate' => round($burnRate, 2),
            'idealDailySpend' => round($idealDailySpend, 2),
            'daysPassed' => $daysPassed,
            'cycleDays' => $cycleDays,
            'daysRemaining' => $daysRemaining,
            'timeProgress' => $timeProgress,
            'spendProgress' => $spendProgress,
            'projectedEndSpend' => $projectedEndSpend,
            'projectedOverspend' => round($projectedOverspend, 2),
            'warning' => $warning,
            'categoryTotals' => $expenseCategoryTotals,
            'categoryBreakdown' => $categoryBreakdown,
            'budgetAlerts' => $budgetAlerts,
            'dueBillAlerts' => $dueBillAlerts,
            'recentTransactions' => $transactions->take(8),
            'incomeTransactions' => $incomeTransactions,
            'expenseTransactions' => $expenseTransactions,
            'incomeVsExpense' => $incomeVsExpense,
            'monthlyLimit' => $monthlyLimit ? round($monthlyLimit, 2) : null,
            'monthlyBudgetUsed' => $monthlyBudgetUsed,
            'savingsTargetAmount' => $savingsTargetAmount,
            'savingsProgress' => $savingsProgress,
            'budgetVsActualLabels' => $categoryBreakdown->pluck('name')->values(),
            'budgetVsActualBudget' => $categoryBreakdown->map(fn (array $item) => round((float) ($item['limit'] ?? 0), 2))->values(),
            'budgetVsActualSpent' => $categoryBreakdown->map(fn (array $item) => round((float) $item['spent'], 2))->values(),
            'trendLabels' => $this->trendLabels($cycleDays),
            'trendExpenses' => $this->trendValues($expenseTransactions, $startDate, $cycleDays),
            'trendIncome' => $this->trendValues($incomeTransactions, $startDate, $cycleDays),
        ];
    }

    protected function categoryBreakdown(Collection $transactions): Collection
    {
        return $transactions
            ->groupBy(fn ($transaction) => $transaction->category?->id ?? 'uncategorized')
            ->map(function (Collection $items) {
                $first = $items->first();
                $category = $first?->category;
                $spent = round($items->sum('amount'), 2);
                $limit = $category?->budget_limit ? (float) $category->budget_limit : null;
                $usedPercentage = $limit && $limit > 0 ? round(($spent / $limit) * 100, 2) : null;

                return [
                    'name' => $category?->name ?? 'Uncategorized',
                    'is_fixed' => (bool) $category?->is_fixed,
                    'spent' => $spent,
                    'limit' => $limit,
                    'remaining' => $limit ? round($limit - $spent, 2) : null,
                    'used_percentage' => $usedPercentage,
                    'due_day' => $category?->due_day,
                ];
            })
            ->sortByDesc('spent')
            ->values();
    }

    protected function dueBillAlerts(IncomeCycle $cycle, Collection $expenseTransactions): Collection
    {
        $today = now();

        return $cycle->user
            ? $cycle->user->categories
                ->where('is_fixed', true)
                ->filter(fn ($category) => ! is_null($category->due_day))
                ->map(function ($category) use ($expenseTransactions, $today) {
                    $dueDate = $today->copy()->day(min((int) $category->due_day, $today->daysInMonth));
                    $daysUntilDue = (int) $today->copy()->startOfDay()->diffInDays($dueDate->copy()->startOfDay(), false);

                    $paidThisMonth = $expenseTransactions
                        ->where('category_id', $category->id)
                        ->contains(fn ($transaction) => $transaction->timestamp && $transaction->timestamp->isSameMonth($today));

                    if ($paidThisMonth || $daysUntilDue < 0 || $daysUntilDue > 5) {
                        return null;
                    }

                    return [
                        'name' => $category->name,
                        'days_until_due' => $daysUntilDue,
                        'due_day' => (int) $category->due_day,
                    ];
                })
                ->filter()
                ->values()
            : collect();
    }

    protected function trendLabels(int $cycleDays): array
    {
        return collect(range(1, $cycleDays))
            ->map(fn (int $day) => 'Day '.$day)
            ->all();
    }

    protected function trendValues(Collection $transactions, Carbon $startDate, int $cycleDays): array
    {
        return collect(range(0, $cycleDays - 1))
            ->map(function (int $offset) use ($transactions, $startDate) {
                $date = $startDate->copy()->addDays($offset);

                return round((float) $transactions
                    ->filter(fn ($transaction) => $transaction->timestamp && $transaction->timestamp->isSameDay($date))
                    ->sum('amount'), 2);
            })
            ->all();
    }
}
