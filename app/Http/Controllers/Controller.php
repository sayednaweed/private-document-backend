<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Models\Contact;
use App\Models\Translate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\App;

abstract class Controller
{
    public function storeProfile(Request $request)
    {
        try {
            // 1. If storage not exist create it.
            $path = storage_path() . "/app/private/user-profile/";
            // Checks directory exist if not will be created.
            !is_dir($path) &&
                mkdir($path, 0777, true);

            // 2. Store image in filesystem
            $fileName = null;
            if ($request->hasFile('profile')) {
                $file = $request->file('profile');
                if ($file != null) {
                    $fileName = Str::uuid() . '.' . $file->extension();
                    $file->move($path, $fileName);

                    return "private/user-profile/" . $fileName;
                }
            }
        } catch (Exception $err) {
        }
        return null;
    }
    public function isAdminOrSuper($user)
    {
        try {
            // 1. If storage not exist create it.
            return  $user->role_id === RoleEnum::admin->value || $user->role_id === RoleEnum::super->value;
        } catch (Exception $err) {
            return  -1;
        }
    }
    public function addOrRemoveContact(User $user, Request $request)
    {
        if ($request->contact === null || $request->contact === "null") {
            if ($user->contact_id !== null) {
                $contact = Contact::find($user->contact_id);
                if ($contact) {
                    $contact->delete();
                }
            }
        } else {
            $contact = Contact::where("value", '=', $request->contact)->first();
            if (!$contact) {
                // 2. Remove old contact
                if ($user->contact_id !== null) {
                    $oldContact = Contact::find($user->contact_id);
                    if ($oldContact) {
                        $oldContact->delete();
                    }
                }
                // 1. Add new contact
                $newContact = Contact::create([
                    "value" => $request->contact
                ]);
                // 3. Update new contact
                $user->contact_id = $newContact->id;
            } else {
                if ($contact->id === $user->contact_id) {
                    // 2. Remove old contact
                    $contact->delete();
                    // 1. Add new contact
                    $newContact = Contact::create([
                        "value" => $request->contact
                    ]);
                    // 3. Update new contact
                    $user->contact_id = $newContact->id;
                } else {
                    return response()->json([
                        'message' => __('app_translation.contact_exist'),
                    ], 400, [], JSON_UNESCAPED_UNICODE);
                }
            }
        }
    }
    public function getTableTranslations($className, $locale, $order)
    {
        return Translate::where('translable_type', '=', $className)
            ->where('language_name', '=', $locale)
            ->select('value as name', 'translable_id as id', 'created_at as createdAt')
            ->orderBy('id', $order)
            ->get();
    }
    public function getTranslationWithNameColumn($model, $className)
    {
        $department = null;
        $locale = App::getLocale();
        if ($model->name) {
            if ($locale === "en")
                $department =  $model->name;
            else {
                $data = Translate::where('translable_id', '=', $model->id)
                    ->where('translable_type', '=', $className)
                    ->where('language_name', '=', $locale)
                    ->select('value')
                    ->first();
                if ($data)
                    $department = $data->value;
            }
        }
        return $department;
    }
    public function TranslateFarsi($value, $translable_id, $translable_type): void
    {
        Translate::factory()->create([
            "value" => $value,
            "language_name" => "fa",
            "translable_type" => $translable_type,
            "translable_id" => $translable_id,
        ]);
    }
    public function TranslatePashto($value, $translable_id, $translable_type): void
    {
        Translate::factory()->create([
            "value" => $value,
            "language_name" => "ps",
            "translable_type" => $translable_type,
            "translable_id" => $translable_id,
        ]);
    }
}
