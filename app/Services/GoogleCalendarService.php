<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Google\Client;
use Google\Service\Calendar;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Centralized service for Google Calendar operations
 * Handles token refresh automatically and provides a consistent interface
 */
class GoogleCalendarService
{
    /**
     * Get an authenticated Google Client for the given user
     * Automatically refreshes expired tokens
     *
     * @param User $user
     * @return Client
     * @throws Exception
     */
    public function getAuthenticatedClient(User $user): Client
    {
        if (!$user->google_auth_metadata) {
            throw new Exception('User not linked with Google Calendar');
        }

        $client = new Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));

        // Check if token is expired or will expire in the next 5 minutes
        $tokenExpiry = Carbon::parse($user->google_auth_metadata['token_expiry']);
        $needsRefresh = Carbon::now()->addMinutes(5)->greaterThan($tokenExpiry);

        if ($needsRefresh) {
            $this->refreshAccessToken($user, $client);
        } else {
            $client->setAccessToken($user->google_auth_metadata['token']);
        }

        return $client;
    }

    /**
     * Refresh the access token using the refresh token
     *
     * @param User $user
     * @param Client $client
     * @return void
     * @throws Exception
     */
    protected function refreshAccessToken(User $user, Client $client): void
    {
        if (!isset($user->google_auth_metadata['refresh_token'])) {
            throw new Exception('No refresh token available. User needs to re-authenticate with Google.');
        }

        try {
            $newToken = $client->fetchAccessTokenWithRefreshToken($user->google_auth_metadata['refresh_token']);

            if (isset($newToken['error'])) {
                Log::error('Google token refresh failed', [
                    'user_id' => $user->id,
                    'error' => $newToken['error_description'] ?? $newToken['error']
                ]);
                throw new Exception('Failed to refresh Google token: ' . ($newToken['error_description'] ?? $newToken['error']));
            }

            // Update user with new token
            $user->setGoogleAuthMetadata(
                null,
                $newToken['access_token'],
                $newToken['refresh_token'] ?? $user->google_auth_metadata['refresh_token'], // Keep old refresh token if not provided
                $newToken['expires_in']
            );

            // Set the fresh token on the client
            $client->setAccessToken($newToken['access_token']);

            Log::info('Google token refreshed successfully', ['user_id' => $user->id]);
        } catch (Exception $e) {
            Log::error('Exception during token refresh', [
                'user_id' => $user->id,
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get Calendar service instance
     *
     * @param User $user
     * @return Calendar
     * @throws Exception
     */
    public function getCalendarService(User $user): Calendar
    {
        $client = $this->getAuthenticatedClient($user);
        return new Calendar($client);
    }

    /**
     * Check if user has valid Google authentication
     *
     * @param User $user
     * @return bool
     */
    public function hasValidAuth(User $user): bool
    {
        if (!$user->google_auth_metadata) {
            return false;
        }

        // Check if we have essential data
        return isset($user->google_auth_metadata['token']) &&
               isset($user->google_auth_metadata['refresh_token']) &&
               isset($user->google_auth_metadata['token_expiry']);
    }

    /**
     * Create a Google Calendar event
     *
     * @param User $organizer
     * @param string $title
     * @param Carbon $startDateTime
     * @param Carbon $endDateTime
     * @param string $attendeeEmail
     * @param string|null $description
     * @return array
     * @throws Exception
     */
    public function createEvent(
        User $organizer,
        string $title,
        Carbon $startDateTime,
        Carbon $endDateTime,
        string $attendeeEmail,
        ?string $description = null
    ): array {
        $service = $this->getCalendarService($organizer);

        $event = new \Google\Service\Calendar\Event([
            'summary' => $title,
            'description' => $description,
            'location' => 'Google Meet',
            'visibility' => 'private',
            'guestsCanInviteOthers' => false,
            'guestsCanSeeOtherGuests' => false,
            'guestsCanModify' => false,
            'start' => [
                'dateTime' => $startDateTime->toRfc3339String(),
                'timeZone' => config('app.timezone'),
            ],
            'end' => [
                'dateTime' => $endDateTime->toRfc3339String(),
                'timeZone' => config('app.timezone'),
            ],
            'attendees' => [
                ['email' => $attendeeEmail],
            ],
            'conferenceData' => [
                'createRequest' => [
                    'requestId' => uniqid(),
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                ],
            ],
            'reminders' => [
                'useDefault' => false,
                'overrides' => [
                    ['method' => 'email', 'minutes' => 24 * 60],
                    ['method' => 'popup', 'minutes' => 10],
                ],
            ],
        ]);

        $createdEvent = $service->events->insert('primary', $event, ['conferenceDataVersion' => 1]);

        return [
            'calendar_id' => $createdEvent->getId(),
            'calendar_link' => $createdEvent->getHtmlLink(),
            'meet_link' => $createdEvent->getHangoutLink(),
        ];
    }

    /**
     * Delete a Google Calendar event
     *
     * @param User $organizer
     * @param string $eventId
     * @return bool
     * @throws Exception
     */
    public function deleteEvent(User $organizer, string $eventId): bool
    {
        $service = $this->getCalendarService($organizer);
        
        try {
            $service->events->delete('primary', $eventId);
            return true;
        } catch (Exception $e) {
            Log::warning('Failed to delete Google Calendar event', [
                'user_id' => $organizer->id,
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Update a Google Calendar event
     *
     * @param User $organizer
     * @param string $eventId
     * @param Carbon $newStartDateTime
     * @param Carbon $newEndDateTime
     * @return bool
     * @throws Exception
     */
    public function updateEvent(
        User $organizer,
        string $eventId,
        Carbon $newStartDateTime,
        Carbon $newEndDateTime
    ): bool {
        $service = $this->getCalendarService($organizer);

        try {
            $event = $service->events->get('primary', $eventId);
            $event->setStart(new \Google\Service\Calendar\EventDateTime([
                'dateTime' => $newStartDateTime->toRfc3339String(),
                'timeZone' => config('app.timezone'),
            ]));
            $event->setEnd(new \Google\Service\Calendar\EventDateTime([
                'dateTime' => $newEndDateTime->toRfc3339String(),
                'timeZone' => config('app.timezone'),
            ]));

            $service->events->update('primary', $eventId, $event);
            return true;
        } catch (Exception $e) {
            Log::warning('Failed to update Google Calendar event', [
                'user_id' => $organizer->id,
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
