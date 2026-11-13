<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('my-channel', fn ($user) => $user instanceof \App\Models\User, ['guards' => ['web']]);
Broadcast::channel('sales-return', fn ($user) => $user instanceof \App\Models\User, ['guards' => ['web']]);
