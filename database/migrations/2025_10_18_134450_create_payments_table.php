<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('external_id')->unique();
            $table->string('xendit_invoice_id')->nullable()->unique();
            $table->string('invoice_url'); // Checkout link/ Nullable karena dipilih nanti di checkout page
            $table->decimal('amount', 12, 2);
            $table->string('status');
            $table->json('xendit_response')->nullable();
            $table->json('callback_data')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('order_id');
            $table->index('status');
            $table->index('external_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
