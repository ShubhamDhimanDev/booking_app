<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ResendUserCredentials extends Command
{
    protected $signature = 'resend:user-credentials {user : id or email}';

    protected $description = 'Regenerate and resend credentials to a user by id or email';

    public function handle()
    {
        $identifier = $this->argument('user');

        $user = is_numeric($identifier) ? User::find($identifier) : User::where('email', $identifier)->first();

        if (! $user) {
            $this->error('User not found');
            return 1;
        }

        $password = \Str::random(12);
        $user->password = bcrypt($password);
        $user->save();

        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->queue(new \App\Mail\NewUserCredentials($user, $password));
            $this->info('Credentials queued for sending to ' . $user->email);
        } catch (\Exception $e) {
            $this->error('Failed to queue mail: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
