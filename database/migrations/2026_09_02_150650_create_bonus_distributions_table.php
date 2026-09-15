<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bonus_distributions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('bonus_id')->constrained('bonuses')->cascadeOnDelete();
            $table->foreignUlid('source_employee_id')->constrained('employees');
            $table->foreignUlid('beneficiary_employee_id')->constrained('employees');
            $table->integer('upline_level');
            $table->decimal('percentage', 5, 2);
            $table->decimal('amount', 12, 2);
            $table->string('status', 20)->default('pending');
            $table->timestamps();

            $table->unique(['bonus_id', 'beneficiary_employee_id', 'upline_level'], 'unique_upline_dist');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bonus_distributions');
    }
};