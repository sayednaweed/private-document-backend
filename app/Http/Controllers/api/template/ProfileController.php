<?php

namespace App\Http\Controllers\api\template;

use App\Http\Controllers\Controller;
use App\Http\Requests\template\user\ProfileUpdateRequest;
use App\Http\Requests\template\user\UpdateProfilePasswordRequest;
use App\Models\Email;
use App\Models\User;
use Illuminate\Http\Request;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function update(ProfileUpdateRequest $request)
    {
        $request->validated();
        try {
            $authUser = $request->user();
            // 1. Validate contact
            //    Check contact
            if (!$this->addOrRemoveContact($authUser, $request)) {
                return response()->json([
                    'message' => __('app_translation.contact_exist'),
                ], 400, [], JSON_UNESCAPED_UNICODE);
            }
            // 2. Validate email 
            $email = Email::where("value", '=', $request->email)->first();
            if (!$email) {
                // 2. Remove old email
                $oldContact = Email::find($authUser->email_id);
                $oldContact->delete();
                // 1. Add new email
                $newEmail = Email::create([
                    "value" => $email
                ]);
                // 3. Update new email
                $authUser->email_id = $newEmail->id;
            } else if ($email->id != $authUser->email_id) {
                return response()->json([
                    'message' => __('app_translation.email_exist'),
                ], 400, [], JSON_UNESCAPED_UNICODE);
            }
            $authUser->full_name = $request->name;
            $authUser->username = $request->username;
            $authUser->save();
            return response()->json([
                'message' => __('app_translation.profile_changed'),
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('Profile update error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error'),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function updatePicture(Request $request)
    {
        $request->validate([
            'profile' => 'nullable|mimes:jpeg,png,jpg|max:2048',
        ]);
        try {
            $authUser = $request->user();
            $path = $this->storeProfile($request);
            if ($path != null) {
                // 1. delete old profile
                $deletePath = storage_path('app/' . "{$authUser->profile}");
                if (file_exists($deletePath) && $authUser->profile != null) {
                    unlink($deletePath);
                }
                // 2. Update the profile
                $authUser->profile = $path;
            }
            $authUser->save();
            return response()->json([
                'message' => __('app_translation.profile_changed'),
                "profile" => $authUser->profile
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('Profile update error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error'),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function deletePicture(Request $request)
    {
        try {
            $authUser = $request->user();
            $deletePath = storage_path('app/' . "{$authUser->profile}");
            if (file_exists($deletePath) && $authUser->profile != null) {
                unlink($deletePath);
            }
            // 2. Update the profile
            $authUser->profile = null;
            $authUser->save();
            return response()->json([
                'message' => __('app_translation.profile_changed')
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('Profile update error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error'),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function changePassword(UpdateProfilePasswordRequest $request)
    {
        $payload = $request->validated();

        try {
            $authUser = User::find(Auth::user()->id);
            if (!Hash::check($payload['oldPassword'], $authUser->password)) {
                return response()->json([
                    'message' => __('app_translation.incorrect_password')
                ], 422, [], JSON_UNESCAPED_UNICODE);
            } else {
                // Old password matched
                $authUser->password = Hash::make($payload["newPassword"]);
                $authUser->save();
                // Delete token for relogin
                $request->user()->currentAccessToken()->delete();
            }
            return response()->json([
                'message' => __('app_translation.password_changed')
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('User change password error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error'),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
