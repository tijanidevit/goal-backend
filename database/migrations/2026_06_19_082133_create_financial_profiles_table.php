<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('total_monthly_income', 15, 2)->default(0);
            $table->decimal('total_monthly_expenses', 15, 2)->default(0);
            $table->decimal('total_monthly_debt_repayment', 15, 2)->default(0);
            $table->decimal('available_monthly_savings', 15, 2)->default(0);
            $table->integer('salary_day_of_month')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_profiles');
    }
};
