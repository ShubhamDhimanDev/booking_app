<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\GoogleCalendarService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class RefreshGoogleTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:refresh-tokens 
                            {--user= : Specific user ID to refresh}
                            {--check : Only check token status without refreshing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and refresh Google OAuth tokens for users with calendar integration';

    protected GoogleCalendarService $googleCalendar;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->googleCalendar = app(GoogleCalendarService::class);
        
        $this->info('🔍 Checking Google OAuth tokens...');

        // Get users with Google auth
        $query = User::whereNotNull('google_auth_metadata');
        
        if ($userId = $this->option('user')) {
            $query->where('id', $userId);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->warn('No users with Google authentication found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$users->count()} user(s) with Google authentication.\n");

        $statusTable = [];
        $refreshed = 0;
        $errors = 0;

        foreach ($users as $user) {
            $status = $this->checkUserToken($user);
            $statusTable[] = $status;

            if ($status['refreshed']) {
                $refreshed++;
            }
            if ($status['error']) {
                $errors++;
            }
        }

        // Display results
        $this->table(
            ['User ID', 'Email', 'Token Status', 'Expires At', 'Action'],
            array_map(function ($row) {
                return [
                    $row['id'],
                    $row['email'],
                    $row['status'],
                    $row['expires_at'],
                    $row['action'],
                ];
            }, $statusTable)
        );

        // Summary
        $this->newLine();
        if ($this->option('check')) {
            $this->info('✅ Token check complete.');
        } else {
            $this->info("✅ Token refresh complete: {$refreshed} refreshed, {$errors} errors.");
        }

        return Command::SUCCESS;
    }

    /**
     * Check and optionally refresh a user's token
     *
     * @param User $user
     * @return array
     */
    protected function checkUserToken(User $user): array
    {
        $result = [
            'id' => $user->id,
            'email' => $user->email,
            'status' => 'Unknown',
            'expires_at' => 'N/A',
            'action' => 'None',
            'refreshed' => false,
            'error' => false,
        ];

        if (!$user->hasGoogleAuth()) {
            $result['status'] = '❌ Invalid';
            $result['action'] = 'Re-auth needed';
            $result['error'] = true;
            return $result;
        }

        // Get token expiry
        $tokenExpiry = Carbon::parse($user->google_auth_metadata['token_expiry']);
        $result['expires_at'] = $tokenExpiry->format('Y-m-d H:i:s');

        // Check if expired
        if (Carbon::now()->greaterThan($tokenExpiry)) {
            $result['status'] = '⚠️  Expired';
        } elseif (Carbon::now()->addHours(24)->greaterThan($tokenExpiry)) {
            $result['status'] = '⚡ Expiring Soon';
        } else {
            $result['status'] = '✅ Valid';
        }

        // Refresh if needed and not in check-only mode
        if (!$this->option('check') && $user->isGoogleTokenExpired(60)) {
            try {
                $this->googleCalendar->getAuthenticatedClient($user);
                $result['action'] = '🔄 Refreshed';
                $result['refreshed'] = true;
                
                // Update expiry after refresh
                $user->refresh();
                $newExpiry = Carbon::parse($user->google_auth_metadata['token_expiry']);
                $result['expires_at'] = $newExpiry->format('Y-m-d H:i:s');
                $result['status'] = '✅ Valid';
            } catch (\Exception $e) {
                $result['action'] = '❌ Failed: ' . substr($e->getMessage(), 0, 30);
                $result['error'] = true;
            }
        }

        return $result;
    }
}
