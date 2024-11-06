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


public function audits(Request $request, $page)
{
                $perPage = $request->input('per_page', 10);


            //    return  $request->input('filters.search.column');
            //     $request->input('filters.search.value');
                // Base query with user join
                $query = Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->select("audits.id", "audits.user_id", "users.full_name as user_name", "event", "auditable_type", "auditable_id", "new_values", "old_values", "audits.created_at");

                // Date filtering
                $startDate = $request->input('filters.date.startDate');
                $endDate = $request->input('filters.date.endDate');

                if ($startDate || $endDate) {
                    if ($startDate && $endDate) {
                        $query->whereBetween('audits.created_at', [$startDate, $endDate]);
                    } elseif ($startDate) {
                        $query->where('audits.created_at', '>=', $startDate);
                    } elseif ($endDate) {
                        $query->where('audits.created_at', '<=', $endDate);
                    }
                }

                // Search filter for specific fields
                $searchColumn = $request->input('filters.search.column');
                $searchValue = $request->input('filters.search.value');

                if ($searchColumn && $searchValue) {
                    if (in_array($searchColumn, ['event', 'auditable_type', 'user_name'])) {
                        if ($searchColumn === 'user_name') {
                            $query->where('users.full_name', 'like', '%' . $searchValue . '%');
                        } else {
                            $query->where($searchColumn, 'like', '%' . $searchValue . '%');
                        }
                    }
                }

                // Sorting
                $sort = $request->input('filters.sort', 'created_at');
                $order = $request->input('filters.order', 'Descending') === 'Descending' ? 'desc' : 'asc';

                if (in_array($sort, ['event', 'auditable_type', 'created_at', 'user_name'])) {
                    $query->orderBy($sort, $order);
                } else {
                    $query->orderBy('created_at', 'desc');
                }

                // Pagination
                $audits = $query->paginate($perPage, ['*'], 'page', $page);

                // Transform response
                $audits->getCollection()->transform(function ($audit) {
                    return [
                        "id" => $audit->id,
                        "user_id" => $audit->user_id,
                        "user_name" => $audit->user_name,
                        "event" => $audit->event,
                        "auditableType" => $audit->auditable_type,
                        "auditableId" => $audit->auditable_id,
                        "newValues" => $audit->new_values,
                        "oldValues" => $audit->old_values,
                        "createdAt" => $audit->created_at,
                    ];
                });

                return response()->json([
                    "audits" => $audits
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











}
