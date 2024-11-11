<?php

namespace App\Http\Controllers\api\template;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function notifications()
    {
        $notifications = Notification::where("user_id", '=', Auth::user()->id)
            ->select("id", "read_status", "message", "created_at", "type")
            ->get();
        $unreadCount = $notifications->where('read_status', 0)->count();
        return response()->json(["notifications" => $notifications, "unread_count" => $unreadCount]);
    }
    public function update(Request $request)
    {
        try {
            // Decode the JSON string into an array
            $ids = json_decode($request->ids, true);
            if (is_array($ids))
                Notification::whereIn('id', $ids)->update(['read_status' => 1]);
        } catch (Exception $err) {
            Log::info('Notification update error =>' . $err->getMessage());
            return response()->json(['message' => "Something went wrong please try again later!"], 500);
        }
    }
    public function delete($id)
    {
        try {
            Notification::find($id)->delete();
        } catch (Exception $err) {
            Log::info('Notification delete error =>' . $err->getMessage());
            return response()->json(['message' => "Something went wrong please try again later!"], 500);
        }
    }
}
