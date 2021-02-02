<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load(['notifications' => function($query) {
            return $query->where('read_at', null);
        }]);
        $notifications = [];
        foreach($user->notifications as $notify)
        {
            array_push($notifications, $notify->data);
        }
   
        return response()->json([
            'data' =>  $notifications
        ]);
    }

    public function apiGetUser()
    {
        return response()->json([
            'user_id' => Auth::id(),
        ]);
    }
}
