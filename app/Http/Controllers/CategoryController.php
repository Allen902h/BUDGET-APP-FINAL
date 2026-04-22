<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    public function store(StoreCategoryRequest $request)
    {
        Category::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'is_fixed' => $request->boolean('is_fixed'),
            'budget_limit' => $request->budget_limit,
            'due_day' => $request->due_day,
        ]);

        return $this->dashboardRedirect($request->input('return_to'), route('dashboard').'#planner')
            ->with('success', 'Category created successfully.');
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        abort_unless($category->user_id === auth()->id(), 403);

        $category->update([
            'name' => $request->name,
            'is_fixed' => $request->boolean('is_fixed'),
            'budget_limit' => $request->budget_limit,
            'due_day' => $request->due_day,
        ]);

        return $this->dashboardRedirect($request->input('return_to'), route('dashboard').'#settings')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        abort_unless($category->user_id === auth()->id(), 403);

        if ($category->transactions()->exists()) {
            return $this->dashboardRedirect(request()->input('return_to'), route('dashboard').'#settings')
                ->withErrors(['category' => 'Cannot delete category with transactions.']);
        }

        $category->delete();

        return $this->dashboardRedirect(request()->input('return_to'), route('dashboard').'#settings')
            ->with('success', 'Category deleted successfully.');
    }
}
