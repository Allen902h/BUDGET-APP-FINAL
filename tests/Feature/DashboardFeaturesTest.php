<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\IncomeCycle;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_new_budgeting_sections(): void
    {
        $user = User::factory()->create([
            'currency_pref' => 'USD',
            'savings_goal_percentage' => 20,
            'monthly_budget_limit' => 3000,
        ]);

        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Bills',
            'is_fixed' => true,
            'budget_limit' => 900,
            'due_day' => now()->addDays(2)->day,
        ]);

        Category::create([
            'user_id' => $user->id,
            'name' => 'Rent',
            'is_fixed' => true,
            'budget_limit' => 1200,
            'due_day' => now()->addDays(1)->day,
        ]);

        $cycle = IncomeCycle::create([
            'user_id' => $user->id,
            'amount' => 4000,
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
        ]);

        Transaction::create([
            'cycle_id' => $cycle->id,
            'category_id' => $category->id,
            'transaction_type' => 'expense',
            'amount' => 350,
            'timestamp' => now(),
            'note' => 'Electric bill',
        ]);

        SavingsGoal::create([
            'user_id' => $user->id,
            'name' => 'Emergency fund',
            'target_amount' => 5000,
            'current_amount' => 1500,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Add income or expense');
        $response->assertSee('Savings progress');
        $response->assertSee('Transaction history');
        $response->assertSee('Download Excel Report');
        $response->assertSee('Rent is due in');
    }

    public function test_user_can_store_income_transaction_and_download_excel_report(): void
    {
        $user = User::factory()->create([
            'currency_pref' => 'USD',
            'savings_goal_percentage' => 15,
            'monthly_budget_limit' => 2500,
        ]);

        $cycle = IncomeCycle::create([
            'user_id' => $user->id,
            'amount' => 3000,
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
        ]);

        $this->actingAs($user)->post(route('transactions.store'), [
            'cycle_id' => $cycle->id,
            'transaction_type' => 'income',
            'amount' => 250,
            'timestamp' => now()->toDateTimeString(),
            'note' => 'Freelance work',
        ])->assertRedirect();

        $this->assertDatabaseHas('transactions', [
            'cycle_id' => $cycle->id,
            'transaction_type' => 'income',
            'amount' => 250,
        ]);

        $backupResponse = $this->actingAs($user)->get(route('dashboard.report.excel'));

        $backupResponse->assertOk();
        $backupResponse->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8');
    }

    public function test_user_can_update_budget_settings_create_category_and_manage_savings_goal(): void
    {
        $user = User::factory()->create([
            'currency_pref' => 'USD',
            'savings_goal_percentage' => 15,
            'monthly_budget_limit' => 2500,
        ]);

        $this->actingAs($user)->post(route('dashboard.settings.update'), [
            'currency_pref' => 'PHP',
            'savings_goal_percentage' => 25,
            'monthly_budget_limit' => 4200,
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'currency_pref' => 'PHP',
            'savings_goal_percentage' => 25,
            'monthly_budget_limit' => 4200,
        ]);

        $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'Utilities',
            'budget_limit' => 900,
            'due_day' => 18,
            'is_fixed' => '1',
        ])->assertRedirect();

        $category = Category::where('user_id', $user->id)->where('name', 'Utilities')->firstOrFail();

        $this->actingAs($user)->post(route('savings-goals.store'), [
            'name' => 'School fees',
            'target_amount' => 10000,
            'current_amount' => 2500,
            'target_date' => now()->addMonths(6)->toDateString(),
            'notes' => 'Semester reserve',
        ])->assertRedirect();

        $goal = SavingsGoal::where('user_id', $user->id)->where('name', 'School fees')->firstOrFail();

        $this->actingAs($user)->put(route('savings-goals.update', $goal), [
            'name' => 'School fees',
            'target_amount' => 10000,
            'current_amount' => 4000,
            'target_date' => now()->addMonths(6)->toDateString(),
            'notes' => 'Updated reserve',
        ])->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'due_day' => 18,
            'is_fixed' => 1,
        ]);

        $this->assertDatabaseHas('savings_goals', [
            'id' => $goal->id,
            'current_amount' => 4000,
            'notes' => 'Updated reserve',
        ]);
    }

    public function test_income_transaction_rejects_foreign_category_assignment(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $cycle = IncomeCycle::create([
            'user_id' => $user->id,
            'amount' => 5000,
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
        ]);

        $foreignCategory = Category::create([
            'user_id' => $otherUser->id,
            'name' => 'Private',
            'is_fixed' => false,
            'budget_limit' => 1000,
        ]);

        $this->actingAs($user)->post(route('transactions.store'), [
            'cycle_id' => $cycle->id,
            'transaction_type' => 'income',
            'category_id' => $foreignCategory->id,
            'amount' => 1500.00,
            'timestamp' => now()->toDateTimeString(),
            'note' => 'Invalid category attempt',
        ])->assertSessionHasErrors('category_id');

        $this->assertDatabaseMissing('transactions', [
            'cycle_id' => $cycle->id,
            'amount' => 1500.00,
        ]);
    }

    public function test_dashboard_can_filter_savings_goals_without_affecting_total_counts(): void
    {
        $user = User::factory()->create();

        $cycle = IncomeCycle::create([
            'user_id' => $user->id,
            'amount' => 5000,
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
        ]);

        SavingsGoal::create([
            'user_id' => $user->id,
            'name' => 'Emergency Fund',
            'target_amount' => 10000,
            'current_amount' => 2500,
        ]);

        SavingsGoal::create([
            'user_id' => $user->id,
            'name' => 'Travel Plan',
            'target_amount' => 8000,
            'current_amount' => 1000,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard', [
            'cycle' => $cycle->id,
            'goals_search' => 'Travel',
        ]));

        $response->assertOk();
        $response->assertSee('1 goal shown out of 2 total.');
        $response->assertSee('Travel Plan');
        $response->assertDontSee('Emergency Fund');
    }

    public function test_dashboard_can_filter_income_cycles_without_affecting_cycle_switcher(): void
    {
        $user = User::factory()->create();

        $currentCycle = IncomeCycle::create([
            'user_id' => $user->id,
            'amount' => 5000,
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
        ]);

        $olderCycle = IncomeCycle::create([
            'user_id' => $user->id,
            'amount' => 3200,
            'start_date' => now()->subMonth()->startOfMonth()->toDateString(),
            'end_date' => now()->subMonth()->endOfMonth()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard', [
            'cycle' => $currentCycle->id,
            'cycles_search' => '3200',
        ]));

        $response->assertOk();
        $response->assertSee('1 cycle shown out of 2 total.');
        $response->assertSee((string) $olderCycle->amount);
        $response->assertSee((string) $currentCycle->amount);
    }

    public function test_dashboard_can_filter_categories_without_affecting_category_dropdowns(): void
    {
        $user = User::factory()->create();

        $cycle = IncomeCycle::create([
            'user_id' => $user->id,
            'amount' => 5000,
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
        ]);

        Category::create([
            'user_id' => $user->id,
            'name' => 'Rent',
            'is_fixed' => true,
            'budget_limit' => 1200,
            'due_day' => 5,
        ]);

        Category::create([
            'user_id' => $user->id,
            'name' => 'Snacks',
            'is_fixed' => false,
            'budget_limit' => 300,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard', [
            'cycle' => $cycle->id,
            'categories_type' => 'fixed',
        ]));

        $response->assertOk();
        $response->assertSee('1 category shown out of 2 total.', false);
        $response->assertSee('Rent');
        $response->assertSee('Snacks');
    }

    public function test_user_can_update_profile_information_and_password(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'password' => Hash::make('Oldpass123'),
        ]);

        $this->actingAs($user)->post(route('profile.update'), [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'Oldpass123',
            'password' => 'Betterpass456',
            'password_confirmation' => 'Betterpass456',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('Betterpass456', $user->fresh()->password));
    }

    public function test_category_name_must_be_unique_per_user(): void
    {
        $user = User::factory()->create();

        Category::create([
            'user_id' => $user->id,
            'name' => 'Utilities',
            'is_fixed' => true,
            'budget_limit' => 500,
        ]);

        $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'Utilities',
            'budget_limit' => 200,
            'return_to' => route('dashboard').'#planner',
        ])->assertSessionHasErrors('name');
    }

    public function test_dashboard_actions_can_redirect_back_to_the_requested_panel(): void
    {
        $user = User::factory()->create([
            'currency_pref' => 'USD',
            'savings_goal_percentage' => 15,
            'monthly_budget_limit' => 2500,
        ]);

        $this->actingAs($user)->post(route('dashboard.settings.update'), [
            'currency_pref' => 'PHP',
            'savings_goal_percentage' => 20,
            'monthly_budget_limit' => 3000,
            'return_to' => route('dashboard').'#settings',
        ])->assertRedirect(route('dashboard').'#settings');
    }

    public function test_user_can_upload_and_remove_profile_photo(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent(
            'avatar.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9sX6lz4AAAAASUVORK5CYII=')
        );

        $this->actingAs($user)->post(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => $file,
        ])->assertRedirect();

        $updatedUser = $user->fresh();

        $this->assertNotNull($updatedUser->profile_photo_path);
        $this->assertFileExists(public_path($updatedUser->profile_photo_path));

        $this->actingAs($updatedUser)->delete(route('profile.photo.destroy'))->assertRedirect();

        $this->assertNull($updatedUser->fresh()->profile_photo_path);
    }

    public function test_user_can_update_and_delete_income_cycle(): void
    {
        $user = User::factory()->create();

        $cycle = IncomeCycle::create([
            'user_id' => $user->id,
            'amount' => 5000,
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
        ]);

        $this->actingAs($user)->put(route('income-cycles.update', $cycle), [
            'amount' => 6500,
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
        ])->assertRedirect(route('dashboard', ['cycle' => $cycle->id]).'#planner');

        $this->assertDatabaseHas('income_cycles', [
            'id' => $cycle->id,
            'amount' => 6500,
        ]);

        $this->actingAs($user)->delete(route('income-cycles.destroy', $cycle))
            ->assertRedirect(route('dashboard').'#planner');

        $this->assertDatabaseMissing('income_cycles', [
            'id' => $cycle->id,
        ]);
    }
}
