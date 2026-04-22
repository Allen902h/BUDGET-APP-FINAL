<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Budget Report</title>
    <style>
        table {
            border-collapse: collapse;
            margin-bottom: 18px;
            width: 100%;
        }

        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #e2e8f0;
        }

        h1, h2 {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <h1>Budget Report</h1>
    <p>Generated at: {{ $generatedAt->format('Y-m-d H:i:s') }}</p>

    <h2>Account Summary</h2>
    <table>
        <tr><th>Name</th><td>{{ $user->name }}</td></tr>
        <tr><th>Email</th><td>{{ $user->email }}</td></tr>
        <tr><th>Currency</th><td>{{ $user->currency_pref }}</td></tr>
        <tr><th>Savings Goal %</th><td>{{ $user->savings_goal_percentage }}</td></tr>
        <tr><th>Monthly Budget Limit</th><td>{{ $user->monthly_budget_limit }}</td></tr>
    </table>

    <h2>Categories</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Budget Limit</th>
                <th>Due Day</th>
            </tr>
        </thead>
        <tbody>
            @forelse($user->categories as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->is_fixed ? 'Fixed' : 'Variable' }}</td>
                    <td>{{ $category->budget_limit }}</td>
                    <td>{{ $category->due_day }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No categories available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Income Cycles</h2>
    <table>
        <thead>
            <tr>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Base Amount</th>
                <th>Transaction Count</th>
            </tr>
        </thead>
        <tbody>
            @forelse($user->incomeCycles as $cycle)
                <tr>
                    <td>{{ optional($cycle->start_date)->toDateString() }}</td>
                    <td>{{ optional($cycle->end_date)->toDateString() }}</td>
                    <td>{{ $cycle->amount }}</td>
                    <td>{{ $cycle->transactions->count() }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No income cycles available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Transactions</h2>
    <table>
        <thead>
            <tr>
                <th>Cycle</th>
                <th>Type</th>
                <th>Category</th>
                <th>Amount</th>
                <th>Timestamp</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            @php
                $transactions = $user->incomeCycles
                    ->flatMap(fn ($cycle) => $cycle->transactions->map(fn ($transaction) => [$cycle, $transaction]))
                    ->sortByDesc(fn ($item) => optional($item[1]->timestamp)?->getTimestamp() ?? 0);
            @endphp
            @forelse($transactions as [$cycle, $transaction])
                <tr>
                    <td>{{ optional($cycle->start_date)->toDateString() }} to {{ optional($cycle->end_date)->toDateString() }}</td>
                    <td>{{ ucfirst($transaction->transaction_type) }}</td>
                    <td>{{ $transaction->category?->name ?? 'N/A' }}</td>
                    <td>{{ $transaction->amount }}</td>
                    <td>{{ optional($transaction->timestamp)->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $transaction->note }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No transactions available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Savings Goals</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Target Amount</th>
                <th>Current Amount</th>
                <th>Target Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($user->savingsGoals as $goal)
                <tr>
                    <td>{{ $goal->name }}</td>
                    <td>{{ $goal->target_amount }}</td>
                    <td>{{ $goal->current_amount }}</td>
                    <td>{{ optional($goal->target_date)->toDateString() }}</td>
                    <td>{{ $goal->is_completed ? 'Completed' : 'In Progress' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No savings goals available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
