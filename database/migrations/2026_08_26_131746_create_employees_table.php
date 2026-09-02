<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignUlid('manager_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('employee_code')->unique();
            $table->string('job_title');
            $table->decimal('basic_salary', 12, 2);
            $table->date('joined_at');
            $table->timestamps();

            $table->index('department_id');
            $table->index('manager_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};