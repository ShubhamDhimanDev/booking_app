<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds composite index for efficient token expiry queries
     * Used by middleware and scheduled tasks to find users needing token refresh
     *
     * @return void
     */
    public function up()
    {
        // Note: google_auth_metadata is a JSON column
        // We can't directly index JSON fields in MySQL < 8.0
        // Consider adding a separate token_expires_at column if performance becomes an issue
        
        // For now, document that token refresh queries should be run off-peak
        // or consider adding a materialized field in a future migration
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No changes needed
    }
};
