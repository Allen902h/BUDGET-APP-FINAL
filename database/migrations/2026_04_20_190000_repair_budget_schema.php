<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'currency_pref')) {
                $table->string('currency_pref', 10)->default('USD')->after('password');
            }

            if (! Schema::hasColumn('users', 'savings_goal_percentage')) {
                $table->decimal('savings_goal_percentage', 5, 2)->default(20)->after('currency_pref');
            }
        });

        Schema::table('income_cycles', function (Blueprint $table) {
            if (! Schema::hasColumn('income_cycles', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            }

            if (! Schema::hasColumn('income_cycles', 'amount')) {
                $table->decimal('amount', 12, 2)->default(0)->after('user_id');
            }

            if (! Schema::hasColumn('income_cycles', 'start_date')) {
                $table->date('start_date')->nullable()->after('amount');
            }

            if (! Schema::hasColumn('income_cycles', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            }

            if (! Schema::hasColumn('categories', 'name')) {
                $table->string('name')->after('user_id');
            }

            if (! Schema::hasColumn('categories', 'is_fixed')) {
                $table->boolean('is_fixed')->default(false)->after('name');
            }

            if (! Schema::hasColumn('categories', 'budget_limit')) {
                $table->decimal('budget_limit', 12, 2)->nullable()->after('is_fixed');
            }

            if (! Schema::hasColumn('categories', 'last_alert_sent_at')) {
                $table->timestamp('last_alert_sent_at')->nullable()->after('budget_limit');
            }
        });

        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'cycle_id')) {
                $table->foreignId('cycle_id')->nullable()->after('id')->constrained('income_cycles')->cascadeOnDelete();
            }

            if (! Schema::hasColumn('transactions', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('cycle_id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('transactions', 'amount')) {
                $table->decimal('amount', 12, 2)->default(0)->after('category_id');
            }

            if (! Schema::hasColumn('transactions', 'timestamp')) {
                $table->dateTime('timestamp')->nullable()->after('amount');
            }

            if (! Schema::hasColumn('transactions', 'note')) {
                $table->text('note')->nullable()->after('timestamp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'category_id')) {
                $table->dropConstrainedForeignId('category_id');
            }

            if (Schema::hasColumn('transactions', 'cycle_id')) {
                $table->dropConstrainedForeignId('cycle_id');
            }

            foreach (['amount', 'timestamp', 'note'] as $column) {
                if (Schema::hasColumn('transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }

            foreach (['name', 'is_fixed', 'budget_limit', 'last_alert_sent_at'] as $column) {
                if (Schema::hasColumn('categories', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('income_cycles', function (Blueprint $table) {
            if (Schema::hasColumn('income_cycles', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }

            foreach (['amount', 'start_date', 'end_date'] as $column) {
                if (Schema::hasColumn('income_cycles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            foreach (['currency_pref', 'savings_goal_percentage'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
