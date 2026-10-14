<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('payment_method', 30)->default('cod')->after('status')
                ->comment('cod | sslcommerz');
            $table->string('transaction_id', 100)->nullable()->after('payment_method')
                ->comment('SSLCommerz transaction ID');
            $table->string('payment_status', 30)->nullable()->after('transaction_id')
                ->comment('VALID | FAILED | CANCELLED | UNATTEMPTED');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['payment_method', 'transaction_id', 'payment_status']);
        });
    }
};
