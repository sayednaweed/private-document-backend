<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Models\Department;
use App\Models\Translate;
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
