<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->latest()->paginate(15);
        auth()->user()->unreadNotifications->markAsRead();

        return view('publisher.notifications', compact('notifications'));
    }
}
