<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'monthly_budget_limit')) {
                $table->decimal('monthly_budget_limit', 12, 2)->nullable()->after('savings_goal_percentage');
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'due_day')) {
                $table->unsignedTinyInteger('due_day')->nullable()->after('budget_limit');
            }
        });

        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'transaction_type')) {
                $table->string('transaction_type', 20)->default('expense')->after('category_id');
            }
        });

        if (! Schema::hasTable('savings_goals')) {
            Schema::create('savings_goals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->decimal('target_amount', 12, 2);
                $table->decimal('current_amount', 12, 2)->default(0);
                $table->date('target_date')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_completed')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('savings_goals')) {
            Schema::drop('savings_goals');
        }

        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'transaction_type')) {
                $table->dropColumn('transaction_type');
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'due_day')) {
                $table->dropColumn('due_day');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'monthly_budget_limit')) {
                $table->dropColumn('monthly_budget_limit');
            }
        });
    }
};
