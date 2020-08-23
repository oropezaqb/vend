<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications;
        auth()->user()->unreadNotifications->markAsRead();
        return view('notifications', compact('notifications'));
    }
    public function destroy($id)
    {
        auth()->user()->notifications->where('id', $id)->first()->delete();
        return redirect(route('notifications.index'));
    }
}
