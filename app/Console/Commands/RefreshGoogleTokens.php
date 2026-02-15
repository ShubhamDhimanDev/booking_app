<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Google\Client;
use Illuminate\Console\Command;

class RefreshGoogleTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:refresh-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh Google OAuth tokens for users with owner role';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting Google token refresh for owner users...');

        // Get all users with 'owner' role who have Google auth metadata
        $users = User::role('owner')
            ->whereNotNull('google_auth_metadata')
            ->get();

        if ($users->isEmpty()) {
            $this->warn('No owner users found with Google authentication.');
            return Command::SUCCESS;
        }

        $successCount = 0;
        $failureCount = 0;

        foreach ($users as $user) {
            try {
                // Check if user has refresh token
                if (!isset($user->google_auth_metadata['refresh_token'])) {
                    $this->warn("User {$user->email} (ID: {$user->id}) has no refresh token. Skipping...");
                    $failureCount++;
                    continue;
                }

                // Check if token needs refresh (if it expires within next 1 hour)
                $tokenExpiry = Carbon::parse($user->google_auth_metadata['token_expiry']);
                if (Carbon::now()->addHour()->greaterThan($tokenExpiry)) {
                    $this->info("Refreshing token for user: {$user->email} (ID: {$user->id})");

                    // Initialize Google Client
                    $client = new Client();
                    $client->setClientId(config('services.google.client_id'));
                    $client->setClientSecret(config('services.google.client_secret'));

                    // Fetch new access token using refresh token
                    $newToken = $client->fetchAccessTokenWithRefreshToken(
                        $user->google_auth_metadata['refresh_token']
                    );

                    // Check if token refresh was successful
                    if (isset($newToken['access_token'])) {
                        // Update user's Google auth metadata
                        $user->setGoogleAuthMetadata(
                            $user->google_auth_metadata['google_uid'] ?? null,
                            $newToken['access_token'],
                            $newToken['refresh_token'] ?? $user->google_auth_metadata['refresh_token'],
                            $newToken['expires_in']
                        );

                        $this->info("✓ Token refreshed successfully for {$user->email}");
                        $successCount++;
                    } else {
                        $this->error("✗ Failed to refresh token for {$user->email}. Response: " . json_encode($newToken));
                        $failureCount++;
                    }
                } else {
                    $this->info("Token for user {$user->email} is still valid. Expires at: {$tokenExpiry->format('Y-m-d H:i:s')}");
                    $successCount++;
                }
            } catch (\Exception $e) {
                $this->error("✗ Error refreshing token for {$user->email}: " . $e->getMessage());
                $failureCount++;
            }
        }

        $this->newLine();
        $this->info("Token refresh completed!");
        $this->info("Success: {$successCount}");
        $this->info("Failures: {$failureCount}");

        return Command::SUCCESS;
    }
}
