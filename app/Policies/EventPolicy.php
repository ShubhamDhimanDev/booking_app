<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
  use HandlesAuthorization;

  /**
   * Determine whether the user can view any models.
   *
   * @param  \App\Models\User  $user
   * @return \Illuminate\Auth\Access\Response|bool
   */
  public function viewAny(User $user)
  {
    return $user->organization_id !== null;
  }

  /**
   * Determine whether the user can view the model.
   *
   * @param  \App\Models\User  $user
   * @param  \App\Models\Event  $event
   * @return \Illuminate\Auth\Access\Response|bool
   */
  public function view(User $user, Event $event)
  {
    // User can view events that belong to their organization
    return $user->organization_id === $event->organization_id;
  }

  /**
   * Determine whether the user can create models.
   *
   * @param  \App\Models\User  $user
   * @return \Illuminate\Auth\Access\Response|bool
   */
  public function create(User $user)
  {
    // User must belong to an organization
    if (!$user->organization_id || !$user->organization) {
      return false;
    }

    $organization = $user->organization;

    // Organization must be active or on trial
    if (!$organization->isActive() && !$organization->isOnTrial()) {
      return false;
    }

    // Check if organization has an active subscription
    $subscription = $organization->subscription;

    if (!$subscription) {
      // Allow if organization is on trial
      return $organization->isOnTrial();
    }

    // Subscription must be active or trialing
    if (!$subscription->isActive() && !$subscription->isTrialing()) {
      return false;
    }


    // Check if within events limit (if applicable)
    if ($subscription->plan && !$subscription->withinEventsLimit()) {
      return false;
    }

    return true;
  }

  /**
   * Determine whether the user can update the model.
   *
   * @param  \App\Models\User  $user
   * @param  \App\Models\Event  $event
   * @return \Illuminate\Auth\Access\Response|bool
   */
  public function update(User $user, Event $event)
  {
    // User can update events that belong to their organization
    return $user->organization_id === $event->organization_id;
  }

  /**
   * Determine whether the user can delete the model.
   *
   * @param  \App\Models\User  $user
   * @param  \App\Models\Event  $event
   * @return \Illuminate\Auth\Access\Response|bool
   */
  public function delete(User $user, Event $event)
  {
    // User can delete events that belong to their organization
    return $user->organization_id === $event->organization_id;
  }

  /**
   * Determine whether the user can restore the model.
   *
   * @param  \App\Models\User  $user
   * @param  \App\Models\Event  $event
   * @return \Illuminate\Auth\Access\Response|bool
   */
  public function restore(User $user, Event $event)
  {
    // User can restore events that belong to their organization
    return $user->organization_id === $event->organization_id;
  }

  /**
   * Determine whether the user can permanently delete the model.
   *
   * @param  \App\Models\User  $user
   * @param  \App\Models\Event  $event
   * @return \Illuminate\Auth\Access\Response|bool
   */
  public function forceDelete(User $user, Event $event)
  {
    // User can force delete events that belong to their organization
    return $user->organization_id === $event->organization_id;
  }
}
