<?php

namespace App\Jobs;

use App\Models\User;
use App\Mail\NewUserCredentials;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendNewUserCredentials implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public User $user;
    public string $password;
    public int $tries = 3;

    public function __construct(User $user, string $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    public function handle(): void
    {
        try {
            Mail::to($this->user->email)
                ->queue(new NewUserCredentials($this->user, $this->password));

            Log::info('New user credentials email queued', [
                'user_id' => $this->user->id,
                'email' => $this->user->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue new user credentials', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
