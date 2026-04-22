<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSavingsGoalRequest;
use App\Http\Requests\UpdateSavingsGoalRequest;
use App\Models\SavingsGoal;

class SavingsGoalController extends Controller
{
    public function store(StoreSavingsGoalRequest $request)
    {
        $request->user()->savingsGoals()->create([
            'name' => $request->name,
            'target_amount' => $request->target_amount,
            'current_amount' => $request->current_amount ?? 0,
            'target_date' => $request->target_date,
            'notes' => $request->notes,
            'is_completed' => ($request->current_amount ?? 0) >= $request->target_amount,
        ]);

        return $this->dashboardRedirect($request->input('return_to'), route('dashboard').'#goals')
            ->with('success', 'Savings goal created successfully.');
    }

    public function update(UpdateSavingsGoalRequest $request, SavingsGoal $savingsGoal)
    {
        abort_unless($savingsGoal->user_id === auth()->id(), 403);

        $currentAmount = (float) ($request->current_amount ?? 0);
        $targetAmount = (float) $request->target_amount;

        $savingsGoal->update([
            'name' => $request->name,
            'target_amount' => $targetAmount,
            'current_amount' => $currentAmount,
            'target_date' => $request->target_date,
            'notes' => $request->notes,
            'is_completed' => $currentAmount >= $targetAmount,
        ]);

        return $this->dashboardRedirect($request->input('return_to'), route('dashboard').'#goals')
            ->with('success', 'Savings goal updated successfully.');
    }

    public function destroy(SavingsGoal $savingsGoal)
    {
        abort_unless($savingsGoal->user_id === auth()->id(), 403);

        $savingsGoal->delete();

        return $this->dashboardRedirect(request()->input('return_to'), route('dashboard').'#goals')
            ->with('success', 'Savings goal deleted successfully.');
    }
}
