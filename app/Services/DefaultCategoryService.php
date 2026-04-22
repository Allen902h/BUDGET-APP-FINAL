<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;

class DefaultCategoryService
{
    public function seedFor(User $user): void
    {
        $categories = [
            ['name' => 'Food', 'is_fixed' => false, 'budget_limit' => 2000, 'due_day' => null],
            ['name' => 'Rent', 'is_fixed' => true, 'budget_limit' => 1500, 'due_day' => 1],
            ['name' => 'Transport', 'is_fixed' => false, 'budget_limit' => 800, 'due_day' => null],
            ['name' => 'Bills', 'is_fixed' => true, 'budget_limit' => 700, 'due_day' => 10],
            ['name' => 'Savings', 'is_fixed' => false, 'budget_limit' => 1200, 'due_day' => null],
            ['name' => 'Entertainment', 'is_fixed' => false, 'budget_limit' => 600, 'due_day' => null],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $category['name'],
                ],
                [
                    'is_fixed' => $category['is_fixed'],
                    'budget_limit' => $category['budget_limit'],
                    'due_day' => $category['due_day'],
                ]
            );
        }
    }
}
