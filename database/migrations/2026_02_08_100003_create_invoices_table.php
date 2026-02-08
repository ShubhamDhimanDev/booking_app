<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');

            // Invoice Details
            $table->string('invoice_number')->unique(); // INV-2026-0001
            $table->date('invoice_date');
            $table->date('due_date')->nullable();

            // Line Items (store as JSON)
            $table->json('line_items'); // [{description, quantity, unit_price, total}]

            // Amounts
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0); // GST 18%
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('INR');

            // Payment Status
            $table->enum('status', ['draft', 'pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->timestamp('paid_at')->nullable();

            // Payment Gateway Details (Gateway-agnostic)
            $table->string('gateway')->default('razorpay'); // razorpay, stripe, paypal, etc.
            $table->string('gateway_payment_id')->nullable();
            $table->string('gateway_order_id')->nullable();
            $table->string('gateway_invoice_id')->nullable();
            $table->json('payment_metadata')->nullable();

            // File Storage
            $table->string('pdf_path')->nullable(); // storage path for PDF invoice

            // Billing Address (snapshot)
            $table->text('billing_address')->nullable();
            $table->string('billing_email')->nullable();
            $table->string('gstin')->nullable(); // GST number

            $table->timestamps();

            // Indexes
            $table->index(['organization_id', 'status', 'invoice_date']);
            $table->index('invoice_number');
            $table->index(['gateway_payment_id', 'status']);
            $table->index(['status', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};
