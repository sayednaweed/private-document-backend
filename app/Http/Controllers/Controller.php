<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Translate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

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
    public function storeDocument(Request $request, $folder)
    {
        try {
            // 1. If storage not exist create it.
            $path = storage_path() . "/app/private/documents/{$folder}/";
            // Checks directory exist if not will be created.
            !is_dir($path) &&
                mkdir($path, 0777, true);

            // 2. Store image in filesystem
            $fileName = null;
            if ($request->hasFile('document')) {
                $file = $request->file('document');
                if ($file != null) {
                    $fileName = Str::uuid() . '.' . $file->extension();
                    $file->move($path, $fileName);

                    return "private/documents/{$folder}/" . $fileName;
                }
            }
        } catch (Exception $err) {
            Log::info('storeDocument error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
        return null;
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
                if ($contact->id !== $user->contact_id) {
                    return false;
                }
            }
        }

        return true;
    }
    public function getTableTranslations($className, $locale, $order, $columns = [
        'value as name',
        'translable_id as id',
        'created_at as createdAt'
    ])
    {
        return Translate::where('translable_type', '=', $className)
            ->where('language_name', '=', $locale)
            ->select($columns)
            ->orderBy('id', $order)
            ->get();
    }
    public function getTableTranslationsWithJoin($className, $locale, $order, $columns = [
        'value as name',
        'translable_id as id',
        'created_at as createdAt'
    ])
    {
        // Dynamically get the related model's table (e.g., 'destinations' for Destination model)
        $relatedTable = (new $className)->getTable();

        // Perform the query to join the Translate table with the related model table
        return Translate::where('translable_type', '=', $className)
            ->where('language_name', '=', $locale)
            ->join($relatedTable, function ($join) use ($relatedTable) {
                // Join Translate table with the related model (e.g., 'destinations') based on translable_id
                $join->on('translates.translable_id', '=', "{$relatedTable}.id");
            })
            ->select($columns)
            ->orderBy('translates.id', $order)
            ->get();
    }
    public function getTranslationWithNameColumn($model, $className)
    {
        $item = null;
        $locale = App::getLocale();
        if ($model->name) {
            if ($locale === "en")
                $item =  $model->name;
            else {
                $data = Translate::where('translable_id', '=', $model->id)
                    ->where('translable_type', '=', $className)
                    ->where('language_name', '=', $locale)
                    ->select('value')
                    ->first();
                if ($data)
                    $item = $data->value;
            }
        }
        return $item;
    }
    public function TranslateFarsi($value, $translable_id, $translable_type): void
    {
        Translate::create([
            "value" => $value,
            "language_name" => "fa",
            "translable_type" => $translable_type,
            "translable_id" => $translable_id,
        ]);
    }
    public function TranslatePashto($value, $translable_id, $translable_type): void
    {
        Translate::create([
            "value" => $value,
            "language_name" => "ps",
            "translable_type" => $translable_type,
            "translable_id" => $translable_id,
        ]);
    }
}
