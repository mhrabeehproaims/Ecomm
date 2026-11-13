<?php

namespace App\Http\View\Composers;

use App\Models\Notification;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

class NotificationComposer
{
    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view): void
    {
        // Cache the notification count for 1 minute to reduce database queries
        $notificationCount = Cache::remember('notification_count', 60, function () {
            return Notification::where('is_read', 0)->count();
        });

        $unread_nofications =  Cache::remember('unread_notification', 60, function () {
            return Notification::where('is_read', 0)->latest()->take(5)->get();
        });

        $view->with([
            'notificationCount' => $notificationCount,
            'unread_nofications' => $unread_nofications, 
        ]);
    }
}
