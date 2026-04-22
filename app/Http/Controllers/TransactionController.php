<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Category;
use App\Models\IncomeCycle;
use App\Models\Transaction;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function store(StoreTransactionRequest $request)
    {
        $cycle = IncomeCycle::where('id', $request->cycle_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $category = null;
        if ($request->filled('category_id')) {
            $category = Category::where('id', $request->category_id)
                ->where('user_id', auth()->id())
                ->firstOrFail();
        }

        Transaction::create([
            'cycle_id' => $cycle->id,
            'category_id' => $category?->id,
            'transaction_type' => $request->transaction_type,
            'amount' => $request->amount,
            'timestamp' => $request->timestamp ?? now(),
            'note' => $request->note,
        ]);

        return $this->dashboardRedirect(
            $request->input('return_to'),
            route('dashboard', ['cycle' => $cycle->id]).'#history'
        )->with('success', ucfirst($request->transaction_type).' added successfully.');
    }

    public function sync()
    {
        $payload = request()->validate([
            'transactions' => ['required', 'array', 'min:1'],
            'transactions.*.cycle_id' => [
                'required',
                'integer',
                Rule::exists('income_cycles', 'id')->where(fn ($query) => $query->where('user_id', auth()->id())),
            ],
            'transactions.*.category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', auth()->id())),
            ],
            'transactions.*.transaction_type' => ['required', 'in:income,expense'],
            'transactions.*.amount' => ['required', 'numeric', 'min:0.01'],
            'transactions.*.timestamp' => ['nullable', 'date'],
            'transactions.*.note' => ['nullable', 'string', 'max:500'],
        ]);

        $created = 0;

        foreach ($payload['transactions'] as $item) {
            $cycle = IncomeCycle::where('id', $item['cycle_id'])
                ->where('user_id', auth()->id())
                ->first();

            $category = null;
            if (! empty($item['category_id'])) {
                $category = Category::where('id', $item['category_id'])
                    ->where('user_id', auth()->id())
                    ->first();
            }

            if (! $cycle || (! $category && $item['transaction_type'] === 'expense')) {
                continue;
            }

            Transaction::create([
                'cycle_id' => $cycle->id,
                'category_id' => $category?->id,
                'transaction_type' => $item['transaction_type'],
                'amount' => $item['amount'],
                'timestamp' => $item['timestamp'] ?? now(),
                'note' => $item['note'] ?? null,
            ]);

            $created++;
        }

        return response()->json([
            'message' => 'Offline transactions synced.',
            'created' => $created,
        ]);
    }

    public function edit(Transaction $transaction)
    {
        abort_unless($transaction->cycle && $transaction->cycle->user_id === auth()->id(), 403);

        $categories = auth()->user()->categories()->orderBy('name')->get();

        return view('transactions.edit', compact('transaction', 'categories'));
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        abort_unless($transaction->cycle && $transaction->cycle->user_id === auth()->id(), 403);

        $category = null;
        if ($request->filled('category_id')) {
            $category = Category::where('id', $request->category_id)
                ->where('user_id', auth()->id())
                ->firstOrFail();
        }

        $transaction->update([
            'category_id' => $category?->id,
            'transaction_type' => $request->transaction_type,
            'amount' => $request->amount,
            'timestamp' => $request->timestamp ?? $transaction->timestamp,
            'note' => $request->note,
        ]);

        return $this->dashboardRedirect(
            $request->input('return_to'),
            route('dashboard', ['cycle' => $transaction->cycle_id]).'#history'
        )->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Transaction $transaction)
    {
        abort_unless($transaction->cycle && $transaction->cycle->user_id === auth()->id(), 403);

        $transaction->delete();

        return $this->dashboardRedirect(
            request()->input('return_to'),
            route('dashboard', ['cycle' => $transaction->cycle_id]).'#history'
        )->with('success', 'Transaction deleted successfully.');
    }
}
