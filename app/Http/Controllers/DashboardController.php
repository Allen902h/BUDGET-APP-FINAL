<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateBudgetSettingsRequest;
use App\Models\IncomeCycle;
use App\Services\BudgetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(private BudgetService $budgetService)
    {
    }

    public function index(Request $request)
    {
        $user = Auth::user()->loadMissing('categories');

        $cycle = IncomeCycle::with(['user.categories', 'transactions.category'])
            ->where('user_id', $user->id)
            ->when($request->cycle, fn ($query, $cycleId) => $query->where('id', $cycleId))
            ->when(! $request->cycle, function ($query) {
                $query->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now());
            })
            ->orderByDesc('start_date')
            ->first();

        if (! $cycle) {
            $cycle = IncomeCycle::with(['user.categories', 'transactions.category'])
                ->where('user_id', $user->id)
                ->orderByDesc('start_date')
                ->first();
        }

        $summary = null;
        $budgetAlerts = collect();
        $dueBillAlerts = collect();
        $filteredTransactions = collect();

        if ($cycle) {
            $summary = $this->budgetService->summary($cycle);
            $budgetAlerts = collect($summary['budgetAlerts']);
            $dueBillAlerts = collect($summary['dueBillAlerts']);
            $filteredTransactions = $this->filterTransactions($cycle, $request);
        }

        $allCycles = $user->incomeCycles()->withCount('transactions')->orderByDesc('start_date')->get();
        $allCategories = $user->categories()->orderBy('name')->get();
        $allSavingsGoals = $user->savingsGoals()->orderBy('target_date')->orderBy('name')->get();

        $cycles = $this->filterCycles($user, $request);
        $categories = $this->filterCategories($user, $request);
        $savingsGoals = $this->filterSavingsGoals($user, $request);

        return view('dashboard', compact(
            'cycle',
            'summary',
            'allCycles',
            'allCategories',
            'allSavingsGoals',
            'cycles',
            'categories',
            'budgetAlerts',
            'dueBillAlerts',
            'filteredTransactions',
            'savingsGoals'
        ));
    }

    public function updateSettings(UpdateBudgetSettingsRequest $request)
    {
        $request->user()->update([
            'currency_pref' => strtoupper($request->currency_pref),
            'savings_goal_percentage' => $request->savings_goal_percentage,
            'monthly_budget_limit' => $request->monthly_budget_limit,
        ]);

        return $this->dashboardRedirect($request->input('return_to'), route('dashboard').'#settings')
            ->with('success', 'Budget settings updated successfully.');
    }

    public function exportExcelReport(Request $request)
    {
        $user = $request->user()->load([
            'categories',
            'incomeCycles.transactions.category',
            'savingsGoals',
        ]);

        $reportHtml = view('reports.budget-excel', [
            'user' => $user,
            'generatedAt' => now(),
        ])->render();

        return response()->streamDownload(function () use ($reportHtml) {
            echo $reportHtml;
        }, 'budget-report-'.now()->format('Y-m-d-His').'.xls', [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    protected function filterTransactions(IncomeCycle $cycle, Request $request)
    {
        $transactions = $cycle->transactions()
            ->with('category')
            ->when($request->filled('transaction_type'), fn ($query) => $query->where('transaction_type', $request->transaction_type))
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', (int) $request->category_id))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('timestamp', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('timestamp', '<=', $request->date_to))
            ->when($request->filled('amount_min'), fn ($query) => $query->where('amount', '>=', (float) $request->amount_min))
            ->when($request->filled('amount_max'), fn ($query) => $query->where('amount', '<=', (float) $request->amount_max))
            ->orderByDesc('timestamp')
            ->get();

        return $transactions
            ->when($request->filled('search'), function ($collection) use ($request) {
                $needle = mb_strtolower($request->search);

                return $collection->filter(function ($transaction) use ($needle) {
                    $category = mb_strtolower($transaction->category?->name ?? '');
                    $note = mb_strtolower((string) $transaction->note);

                    return str_contains($category, $needle) || str_contains($note, $needle);
                });
            })
            ->values();
    }

    protected function filterCycles($user, Request $request)
    {
        return $user->incomeCycles()
            ->withCount('transactions')
            ->when($request->filled('cycles_search'), function ($query) use ($request) {
                $needle = trim((string) $request->cycles_search);

                $query->where(function ($inner) use ($needle) {
                    $inner->whereRaw("DATE_FORMAT(start_date, '%Y-%m-%d') like ?", ["%{$needle}%"])
                        ->orWhereRaw("DATE_FORMAT(end_date, '%Y-%m-%d') like ?", ["%{$needle}%"])
                        ->orWhere('amount', 'like', "%{$needle}%");
                });
            })
            ->when($request->filled('cycles_date_from'), fn ($query) => $query->whereDate('start_date', '>=', $request->cycles_date_from))
            ->when($request->filled('cycles_date_to'), fn ($query) => $query->whereDate('end_date', '<=', $request->cycles_date_to))
            ->orderByDesc('start_date')
            ->get();
    }

    protected function filterCategories($user, Request $request)
    {
        return $user->categories()
            ->when($request->filled('categories_search'), fn ($query) => $query->where('name', 'like', '%'.trim((string) $request->categories_search).'%'))
            ->when($request->filled('categories_type'), function ($query) use ($request) {
                $query->where('is_fixed', $request->categories_type === 'fixed');
            })
            ->orderBy('name')
            ->get();
    }

    protected function filterSavingsGoals($user, Request $request)
    {
        return $user->savingsGoals()
            ->when($request->filled('goals_search'), function ($query) use ($request) {
                $needle = trim((string) $request->goals_search);

                $query->where(function ($inner) use ($needle) {
                    $inner->where('name', 'like', "%{$needle}%")
                        ->orWhere('notes', 'like', "%{$needle}%");
                });
            })
            ->when($request->filled('goals_status'), function ($query) use ($request) {
                if ($request->goals_status === 'completed') {
                    $query->where('is_completed', true);
                }

                if ($request->goals_status === 'active') {
                    $query->where('is_completed', false);
                }
            })
            ->orderBy('target_date')
            ->orderBy('name')
            ->get();
    }
}
