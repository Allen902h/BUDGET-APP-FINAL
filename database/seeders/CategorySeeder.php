<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\DefaultCategoryService;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function __construct(private DefaultCategoryService $defaultCategoryService)
    {
    }

    public function run(): void
    {
        $user = User::first();

        if (! $user) {
            return;
        }

        $this->defaultCategoryService->seedFor($user);
    }
}
