<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table): void {
            $table->id();
            $table->string('category', 100)->comment('lookups.code where type=expense_category');
            $table->decimal('amount', 10, 2);
            $table->text('note')->nullable();
            $table->date('expense_date');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('expense_date');
            $table->index('category');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
