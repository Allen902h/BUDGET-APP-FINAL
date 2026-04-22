@extends('layouts.app')

@section('title', 'Edit Transaction | '.config('app.name', 'Budget App'))

@section('content')
<section class="auth-wrap">
    <div class="auth-card wide-card">
        <span class="eyebrow">Edit Transaction</span>
        <h1>Refine the record without losing cycle context.</h1>
        <p class="auth-copy">Update the category, amount, time, or note, then return to the dashboard with a clean audit trail.</p>

        <form method="POST" action="{{ route('transactions.update', $transaction) }}" class="stack-form">
            @csrf
            @method('PUT')
            <input type="hidden" name="return_to" value="{{ route('dashboard', ['cycle' => $transaction->cycle_id]) }}#history">

            <div class="two-field-grid">
                <label class="field">
                    <span>Entry type</span>
                    <select name="transaction_type" class="transaction-type-select" required>
                        <option value="expense" {{ $transaction->transaction_type === 'expense' ? 'selected' : '' }}>Expense</option>
                        <option value="income" {{ $transaction->transaction_type === 'income' ? 'selected' : '' }}>Income</option>
                    </select>
                </label>
                <label class="field transaction-category-field">
                    <span>Category</span>
                    <select name="category_id">
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $transaction->category_id == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="two-field-grid">
                <label class="field">
                    <span>Amount</span>
                    <input type="number" step="0.01" name="amount" value="{{ $transaction->amount }}" required>
                </label>
                <label class="field">
                    <span>Timestamp</span>
                    <input type="datetime-local" name="timestamp" value="{{ $transaction->timestamp?->format('Y-m-d\TH:i') }}">
                </label>
            </div>

            <label class="field">
                <span>Note</span>
                <textarea name="note">{{ $transaction->note }}</textarea>
            </label>

            <div class="hero-actions">
                <button type="submit" class="primary-button">Update Transaction</button>
                <a href="{{ route('dashboard', ['cycle' => $transaction->cycle_id]) }}#history" class="secondary-button">Back to Dashboard</a>
            </div>
        </form>
    </div>
</section>
@endsection
