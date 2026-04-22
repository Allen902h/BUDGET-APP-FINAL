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
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['currency_pref', 'savings_goal_percentage'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
