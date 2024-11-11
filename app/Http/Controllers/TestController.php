<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\User;
use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

use function Laravel\Prompts\select;

class TestController extends Controller
{
    public function index(Request $request)
    {
        // $path = storage_path('app/' . "images/a7453951-0348-48b7-a7ab-e75916e20a63.jpg");

        // if (!Storage::disk('local')->exists("images/a7453951-0348-48b7-a7ab-e75916e20a63.jpg")) {
        //     return response()->json("Not found");
        // }

        // Session::put('locale', "fa");
        // $sessionLocale = Session::get('locale');

        $locale = App::getLocale();

        // Fetch destinations with their translations and the related destination type translations
        $query = Destination::whereHas('translations', function ($query) use ($locale) {
            // Filter the translations for each destination by locale
            $query->where('language_name', '=', $locale);
        })
            ->with([
                'translations' => function ($query) use ($locale) {
                    // Eager load only the 'value' column for translations filtered by locale
                    $query->select('id', 'value', 'created_at', 'translable_id')
                        // Eager load the translations for Destination filtered by locale
                        ->where('language_name', '=', $locale);
                },
                'type.translations' => function ($query) use ($locale) {
                    // Eager load only the 'value' column for translations filtered by locale
                    $query->select('id', 'value', 'created_at', 'translable_id')
                        // Eager load the translations for DestinationType filtered by locale
                        ->where('language_name', '=', $locale);
                }
            ])
            ->select('id', 'color', 'destination_type_id', 'created_at')
            ->get();

        // Process results and include the translations of DestinationType within each Destination
        // Transform the collection
        $query->each(function ($destination) {
            // Get the translated values for the destination
            $destinationTranslation = $destination->translations->first();

            // Set the transformed values for the destination
            $destination->id = $destination->id;
            $destination->name = $destinationTranslation->value;  // Translated name
            $destination->color = $destination->color;  // Translated color
            $destination->createdAt = $destination->created_at;

            // Get the translated values for the related DestinationType
            $destinationTypeTranslation = $destination->type->translations->first();

            // Add the related DestinationType translation
            $type = [
                "id" => $destination->destination_type_id,
                "name" => $destinationTypeTranslation->value,  // Translated name of the type
                "createdAt" => $destinationTypeTranslation->created_at
            ];
            unset($destination->type);  // Remove destinationType relation
            $destination->type = $type;

            // Remove unnecessary data from the destination object
            unset($destination->translations);  // Remove translations relation
            unset($destination->created_at);  // Remove translations relation
            unset($destination->destination_type_id);  // Remove translations relation
        });

        return $query;

        // ->join('destinations', function ($join) use ($locale) {
        //     // Join based on translable_id and translable_type
        //     $join->on('translates.translable_id', '=', 'destinations.id')
        //         ->on('translates.translable_type', '=', DB::raw("'App\Models\Destination'"))
        //         ->where('translates.language_name', '=', $locale);  // Filter by language name
        // });
        // ->get();
        return $query->get();
        dd($query->toSql(), $query->getBindings());
        // return $sessionLocale;
        $userCount = User::count();
        $todayCount = User::whereDate('created_at', Carbon::today())->count();
        $activeUserCount = User::where('status', true)->count();
        $inActiveUserCount = User::where('status', false)->count();
        return response()->json([
            'counts' => [
                "active" => $userCount,
                "inActive" => $todayCount,
                "total" => $activeUserCount,
                "todayTotal" => $inActiveUserCount
            ],
        ], 200, [], JSON_UNESCAPED_UNICODE);


        // $foundUser = User::with(['permissions', 'contact', 'email', 'userRole', 'userJob', 'userDepartment'])
        //     ->select(
        //         "id",
        //         "full_name as fullName",
        //         "username",
        //         "profile",
        //         "status",
        //         "grant_permission as grantPermission",
        //         "email_id",
        //         "role",
        //         "contact_id",
        //         "job_id",
        //         "department_id",
        //         "created_at as createdAt",
        //     )->find("11");

        // $authUser = User::with(['permissions'])->find("1");;
        // // Combine permissions of user1 and user2
        // $combinedPermissions = $foundUser->permissions->concat($authUser->permissions)->unique('permission');
        // return $combinedPermissions;

        // $user = User::find(10);
        // $userId = $user->id;
        // $userPermissions = DB::table('user_permissions')
        //     ->join('permissions', function ($join) use ($userId) {
        //         $join->on('user_permissions.permission', '=', 'permissions.name')
        //             ->where('user_permissions.user_id', '=', $userId);
        //     })
        //     ->select(
        //         "permissions.name as permission",
        //         "permissions.icon as icon",
        //         "permissions.priority as priority",
        //         "user_permissions.view",
        //         "user_permissions.add",
        //         "user_permissions.delete",
        //         "user_permissions.edit",
        //         "user_permissions.id",
        //     )
        //     ->orderBy("priority")
        //     ->get();
        // return ["user" => $user->toArray(), "permissions" => $userPermissions];
    }
}
