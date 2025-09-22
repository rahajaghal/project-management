<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationlResource;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationsController extends Controller
{
    public function showUnreadNotifications()
    {

//        return Auth::User()->unreadNotifications->count();
        $notifications=Auth::User()->unreadNotifications;

        return ApiResponse::sendResponse(200,'Notifications Retrieved Successfully',
            NotificationlResource::collection($notifications));
//            return ApiResponse::sendResponse(200,'There Is no New Notifications',[]);
    }

    public function showNotification($id)
    {
//        $podcast=Podcast::findorFail($podcast_id);
//        $getID =DB::table('notifications')->where('id',$id)->pluck('id');
//        $getID=$getID[0];
        DB::table('notifications')->where('id',$id)->update(['read_at'=>now()]);
        $notification = auth()->user()->notifications()->findOrFail($id);

        return ApiResponse::sendResponse(200,'Notification Retrieved Successfully',new NotificationlResource($notification));
    }
}
