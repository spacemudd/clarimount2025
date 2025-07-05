<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Print station channel - public for all authenticated users
Broadcast::channel('print-station', function ($user) {
    return true; // Allow all authenticated users to listen
});

// Company-specific print jobs - private channel
Broadcast::channel('company.{companyId}', function ($user, $companyId) {
    // Check if user has access to this company
    return $user->ownedCompanies()->where('id', $companyId)->exists();
}); 