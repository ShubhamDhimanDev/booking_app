<?php

namespace Tests\Unit;

use App\Services\PayUService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayUServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_initiate_payment_sends_transaction_currency_not_currency()
    {
        $response = (new PayUService())->initiatePayment([
            'amount' => 1,
            'currency' => 'USD',
            'product_info' => 'Test Event',
            'first_name' => 'Jane',
            'email' => 'jane@example.com',
        ]);

        $this->assertSame('USD', $response['transactionCurrency']);
        // PayU's hosted checkout does not read a field literally named
        // "currency" -- sending one is silently ignored and the transaction
        // is processed in INR regardless. Guard against regressing back to it.
        $this->assertArrayNotHasKey('currency', $response);
    }

    public function test_initiate_payment_defaults_transaction_currency_to_inr()
    {
        $response = (new PayUService())->initiatePayment([
            'amount' => 500,
            'product_info' => 'Test Event',
            'first_name' => 'Jane',
            'email' => 'jane@example.com',
        ]);

        $this->assertSame('INR', $response['transactionCurrency']);
    }
}
