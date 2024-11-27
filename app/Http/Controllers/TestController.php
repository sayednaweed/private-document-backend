<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Enums\StatusEnum;
use App\Models\Destination;
use App\Models\Document;
use App\Models\DocumentsEnView;
use App\Models\DocumentsFaView;
use App\Models\DocumentsPsView;
use App\Models\RolePermission;
use App\Models\Scan;
use App\Models\User;
use App\Models\UsersEnView;
use App\Models\UsersFaView;
use App\Models\UsersPsView;
use App\Models\UsersView;
use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use function Laravel\Prompts\select;

class TestController extends Controller
{
    public function index(Request $request)
    {
        // return User::with([
        //     'contact:id,value',
        //     'email:id,value',
        //     'job:id,name',
        //     'destination:id,name'  // Eager load destination relationship
        // ])
        //     ->select("id", "username", "profile", "status", "job_id", "destination_id", 'email_id', 'contact_id', "created_at as createdAt")
        //     ->get();


        $doc = Document::find(1);
        $scan = Scan::find($doc->scan_id);
        $initailScan = storage_path('app/' . "{$scan->initail_scan}");
        return file_exists($initailScan);


        // Path to the user_error.log file
        $logFilePath = storage_path('logs/user_error.log');

        // Check if the file exists
        if (!File::exists($logFilePath)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Log file not found.'
            ], 404);
        }

        // Get the contents of the user_error.log file
        $logContents = File::get($logFilePath);

        // Split the log contents into individual lines (assuming each log entry is on a separate line)
        $logLines = explode("\n", $logContents);

        $logs = [];

        // Iterate through each log line and parse it as JSON
        foreach ($logLines as $line) {
            // Skip empty lines
            if (empty($line)) continue;

            // Decode the JSON log entry
            $logData = json_decode($line, true);

            // Check if decoding was successful
            if ($logData) {
                $context = $logData['context'] ?? null;

                if ($context) {
                    // Add additional information to each log entry
                    $logs[] = [
                        'timestamp' => $context['timestamp'] ?? now()->toDateTimeString(),
                        'error_message' => $context['error_message'] ?? 'No message',
                        'user_id' => $context['user_id'] ?? 'N/A',
                        'username' => $context['username'] ?? 'N/A',
                        'ip_address' => $context['ip_address'] ?? 'N/A',
                        'method' => $context['method'] ?? 'N/A',
                        'uri' => $context['uri'] ?? 'N/A',
                    ];
                }
            }
        }

        // Return the formatted logs as JSON
        return response()->json([
            'status' => 'success',
            'logs' => $logs
        ]);

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
