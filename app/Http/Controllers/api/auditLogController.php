<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Department;
use App\Models\ModelJob;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OwenIt\Auditing\Models\Audit;

class auditLogController extends Controller
{
    //


// public function audits(Request $request, $page)
// {
//                 $perPage = $request->input('per_page', 10);


//             //    return  $request->input('filters.search.column');
//             //     $request->input('filters.search.value');
//                 // Base query with user join
//                 $query = Audit::join('users', 'users.id', '=', 'audits.user_id')
//                     ->select("audits.id as ID", "audits.user_id", "users.full_name as UserName", "event as Action", "auditable_type", "auditable_id", "new_values", "old_values", "audits.created_at");

//                 // Date filtering
//                 $startDate = $request->input('filters.date.startDate');
//                 $endDate = $request->input('filters.date.endDate');

//                 if ($startDate || $endDate) {
//                     if ($startDate && $endDate) {
//                         $query->whereBetween('audits.created_at', [$startDate, $endDate]);
//                     } elseif ($startDate) {
//                         $query->where('audits.created_at', '>=', $startDate);
//                     } elseif ($endDate) {
//                         $query->where('audits.created_at', '<=', $endDate);
//                     }
//                 }

//                 // Search filter for specific fields
//                 $searchColumn = $request->input('filters.search.column');
//                 $searchValue = $request->input('filters.search.value');

//                 if ($searchColumn && $searchValue) {
//                     if (in_array($searchColumn, ['event', 'auditable_type', 'user_name'])) {
//                         if ($searchColumn === 'user_name') {
//                             $query->where('users.full_name', 'like', '%' . $searchValue . '%');
//                         } else {
//                             $query->where($searchColumn, 'like', '%' . $searchValue . '%');
//                         }
//                     }
//                 }

//                 // Sorting
//                 $sort = $request->input('filters.sort', 'created_at');
//                 $order = $request->input('filters.order', 'Descending') === 'Descending' ? 'desc' : 'asc';

//                 if (in_array($sort, ['event', 'auditable_type', 'created_at', 'user_name'])) {
//                     $query->orderBy($sort, $order);
//                 } else {
//                     $query->orderBy('created_at', 'desc');
//                 }

//                 // Pagination
//                 $audits = $query->paginate($perPage, ['*'], 'page', $page);

//                 // Transform response
//                 $audits->getCollection()->transform(function ($audit) {
//                     return [
//                         "id" => $audit->id,
//                         "user_id" => $audit->user_id,
//                         "user_name" => $audit->user_name,
//                         "event" => $audit->event,
//                         "auditableType" => $audit->auditable_type,
//                         "auditableId" => $audit->auditable_id,
//                         "newValues" => $audit->new_values,
//                         "oldValues" => $audit->old_values,
//                         "createdAt" => $audit->created_at,
//                     ];
//                 });

//                 return response()->json([
//                     "audits" => $audits
//                 ]);
// }

public function audits(Request $request, $page)
{
    $perPage = $request->input('per_page', 10);

    $userlist  = User::has('audit')  // Only users that have related audits
    ->with(['audit' => function ($query) {
        $query->select('user_id');  // Only select the 'user_id' from the 'audits' table
    }])
    ->select('id AS User ID', 'full_name AS User Name')  // Only select the 'id' and 'full_name' from the 'users' table
    ->get();


    // Base query using the Audit model to filter audits first
    $query = Audit::with(['user' => function ($query) {
        // Select only necessary user fields (e.g., user id, full_name)
        $query->select('id', 'full_name');
    }]);

    // Date filtering on audits
    $startDate = $request->input('filters.date.startDate');
    $endDate = $request->input('filters.date.endDate');
    if ($startDate && $endDate) {
        $query->whereBetween('created_at', [$startDate, $endDate]);
    } elseif ($startDate) {
        $query->where('created_at', '>=', $startDate);
    } elseif ($endDate) {
        $query->where('created_at', '<=', $endDate);
    }

    // Search filtering on specific fields (audit event or auditable type)
    $searchColumn = $request->input('filters.search.column');
    $searchValue = $request->input('filters.search.value');
    if ($searchColumn && $searchValue && in_array($searchColumn, ['event', 'auditable_type'])) {
        $query->where($searchColumn, 'like', '%' . $searchValue . '%');
    }

    // Search by user name if needed
    if ($request->input('filters.search.column') === 'user_name') {
        if ($searchValue) {
            $query->whereHas('user', function ($q) use ($searchValue) {
                $q->where('full_name', 'like', '%' . $searchValue . '%');
            });
        }
    }

    // Sorting based on audit fields, default is `created_at`
    $sort = $request->input('filters.sort', 'created_at');
    $order = $request->input('filters.order', 'Descending') === 'Descending' ? 'desc' : 'asc';
    $query->orderBy($sort, $order);

    // Pagination for audits
    $audits = $query->paginate($perPage, ['*'], 'page', $page);

    // Process and transform results to include user info and audit details
     $audits->getCollection()->transform(function ($audit) {
        return [
            "Audit Id" => $audit->id,
            "Action" => $audit->event,
            "Audit Model" => $audit->auditable_type,
            "Auditable Id" => $audit->auditable_id,
            "New Value" => $audit->new_values,
            "Old Value" => $audit->old_values,
            "Created At" => $audit->created_at,
            "User Id" => $audit->user->id, // User data via eager-loaded relationship
            "User Name" => $audit->user->full_name, // User name via eager-loaded relationship
        ];
    });

    // Return the audits with user info
    return response()->json([
        "audits" => $audits,
        "users"  => $userlist
     ]);
}




public function audit($id, Request $request)
{
    try {
        // 1. Retrieve the audit record by ID, including the user who performed the action
        $audit = Audit::with(['user', 'auditable'])
            ->select(
                "id",
                "user_id",
                "event",
                "auditable_type",
                "auditable_id",
                "new_values",
                "old_values",
                "created_at"
            )->find($id);



        if ($audit) {



            // 
            // 
        
            // Retrieve the authenticated user and their permissions
            $authUser = $request->user()->load('permissions');
            
            // 2. Retrieve permissions of both the audit actor and the authenticated user
            $combinedPermissions = $audit->user->permissions->concat($authUser->permissions)->unique('permission');
            $permissionData = [];
            foreach ($combinedPermissions as $permission) {
                $actualUser = $permission->user_id == $audit->user_id;
                array_push($permissionData, [
                    'permission' => $permission->permission,
                    'view' => $actualUser ? $permission->view : false,
                    'add' => $actualUser ? $permission->add : false,
                    'delete' => $actualUser ? $permission->delete : false,
                    'edit' => $actualUser ? $permission->edit : false,
                    'id' => $permission->id,
                ]);
            }

            return response()->json([
                "audit" => [
                    "id" => $audit->id,
                    "user" => [
                        "id" => $audit->user->id,
                        "fullName" => $audit->user->full_name,
                        "username" => $audit->user->username,
                    ],
                    "event" => $audit->event,
                    "auditableType" => $audit->auditable_type,
                    "auditableId" => $audit->auditable_id,
                    "newValues" => $audit->new_values,
                    "oldValues" => $audit->old_values,
                    "createdAt" => $audit->created_at,
                ],
                "permission" => $permissionData
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json([
                'message' => __('app_translation.not_found'),
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
    } catch (Exception $err) {
        Log::error('Audit retrieval error => ' . $err->getMessage());
        return response()->json([
            'message' => __('app_translation.server_error')
        ], 500, [], JSON_UNESCAPED_UNICODE);
    }
}



private function translations($locale)
    {
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
    }











}
