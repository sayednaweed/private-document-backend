<?php

namespace App\Http\Controllers\api;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Email;
use App\Models\ModelJob;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\user\UpdateUserPasswordRequest;
use App\Http\Requests\user\UpdateUserRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Models\Contact;
use App\Models\UserPermission;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function users(Request $request, $page)
    {
        $users = [];
        $perPage = $request->input('per_page', 10); // Number of records per page

        $query = User::with(['contact', 'email', 'job', 'department']) // Eager load relationships
            ->select("id", "username", "profile", "status", "job_id", "department_id", 'email_id', 'contact_id', "created_at");

        // Apply date filtering conditionally
        $startDate = $request->input('filters.date.startDate');
        $endDate = $request->input('filters.date.endDate');

        if ($startDate || $endDate) {
            // If both dates are present, use whereBetween
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            } elseif ($startDate) {
                // If only startDate is present
                $query->where('created_at', '>=', $startDate);
            } elseif ($endDate) {
                // If only endDate is present
                $query->where('created_at', '<=', $endDate);
            }
        }

        // Apply search filter if present
        $searchColumn = $request->input('filters.search.column');
        $searchValue = $request->input('filters.search.value');
        if ($searchColumn && $searchValue) {
            // Validate that the column is allowed
            if (in_array($searchColumn, ['username', 'contact', 'email'])) {
                if ($searchColumn === 'email') {
                    $query->whereHas('email', function ($q) use ($searchValue) {
                        $q->where('value', 'like', '%' . $searchValue . '%'); // Replace 'value' with actual field name in emails table
                    });
                } elseif ($searchColumn === "contact") {
                    $query->whereHas('contact', function ($q) use ($searchValue) {
                        $q->where('value', 'like', '%' . $searchValue . '%'); // Replace 'value' with actual field name in emails table
                    });
                } else
                    $query->where($searchColumn, 'like', '%' . $searchValue . '%');
            }
        }

        // Apply sorting if present
        $sort = $request->input('filters.sort'); // e.g., 'username' or 'created_at'
        $order = $request->input('filters.order', 'Ascending') === 'Ascending' ? 'asc' : 'desc';

        if ($sort && in_array($sort, ['username', 'created_at', 'status', 'job', 'department'])) {
            $query->orderBy($sort, $order); // Apply sorting based on the sort field and order
        } else {
            // Default sorting
            $query->orderBy("created_at", "desc"); // Fallback sorting
        }

        // Apply pagination
        $users = $query->paginate($perPage, ['*'], 'page', $page);
        // 

        // Include the email address in the response
        $users->getCollection()->transform(function ($user) {
            return [
                "id" => $user->id,
                "username" => $user->username,
                "profile" => $user->profile,
                "status" => $user->status,
                "job" => $this->getTranslationWithNameColumn($user->job, ModelJob::class),
                "department" => $this->getTranslationWithNameColumn($user->department, Department::class),
                "createdAt" => $user->created_at,
                "email" => $user->email->value,
                "contact" => $user->contact ? $user->contact->value : null
            ];
        });
        return response()->json(
            [
                "users" => $users,
                "sort" => $query
            ]
        );
    }
    public function user($id, Request $request)
    {
        try {
            // 1. Retrive current user all permissions
            $foundUser = User::with(['permissions', 'contact', 'email', 'role', 'job', 'department'])
                ->select(
                    "id",
                    "full_name as fullName",
                    "username",
                    "profile",
                    "status",
                    "grant_permission",
                    "email_id",
                    "role_id",
                    "contact_id",
                    "job_id",
                    "department_id",
                    "created_at as createdAt",
                )->find($id);


            if ($foundUser) {
                $authUser = $request->user()->load('permissions');
                // 2. Combine permissions of user1 and user2
                $combinedPermissions = $foundUser->permissions->concat($authUser->permissions)->unique('permission');
                return response()->json([
                    "user" => [
                        "id" => $foundUser->id,
                        "fullName" => $foundUser->fullName,
                        "username" => $foundUser->username,
                        'email' => $foundUser->email ? $foundUser->email->value : null,
                        "profile" => $foundUser->profile,
                        "status" => $foundUser->status,
                        "grantPermission" => $foundUser->grant_permission,
                        "role" => $foundUser->role,
                        'contact' => $foundUser->contact ? $foundUser->contact->value : null,
                        "department" => [
                            'id' => $foundUser->department_id,
                            'name' => $this->getTranslationWithNameColumn($foundUser->department, Department::class)
                        ],
                        "job" => [
                            'id' => $foundUser->job_id,
                            'name' => $this->getTranslationWithNameColumn($foundUser->job, ModelJob::class),
                        ],
                        "createdAt" => $foundUser->created_at,
                    ],
                    "permission" => $combinedPermissions
                ], 200, [], JSON_UNESCAPED_UNICODE);
            } else
                return response()->json([
                    'message' => __('app_translation.not_found'),
                ], 404, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('User login error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function emailExist(Request $request)
    {
        $request->validate(
            ["email" => "required"]
        );
        try {
            $email = Email::where("value", '=', $request->email)->first();
            if ($email)
                return response()->json([
                    'message' => true,
                ], 200, [], JSON_UNESCAPED_UNICODE);
            else
                return response()->json([
                    'message' => false,
                ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('emailExist error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function store(UserRegisterRequest $request)
    {
        $request->validated();
        try {
            // 1. Check email
            $email = Email::where('value', '=', $request->email)->first();
            if ($email) {
                return response()->json([
                    'message' => __('app_translation.email_exist'),
                ], 400, [], JSON_UNESCAPED_UNICODE);
            }
            // 2. Check contact
            $contact = null;
            if ($request->contact) {
                $contact = Contact::where('value', '=', $request->contact)->first();
                if ($contact) {
                    return response()->json([
                        'message' => __('app_translation.contact_exist'),
                    ], 400, [], JSON_UNESCAPED_UNICODE);
                }
            }
            // Add email and contact
            $email = Email::create([
                "value" => $request->email
            ]);
            $contact = null;
            if ($request->contact) {
                $contact = Contact::create([
                    "value" => $request->contact
                ]);
            }
            // 3. Create User
            $newUser = User::create([
                "full_name" => $request->fullName,
                "username" => $request->username,
                "email_id" => $email->id,
                "password" => Hash::make($request->password),
                "role_id" => $request->role,
                "job_id" => $request->job,
                "department_id" => $request->department,
                "contact_id" => $contact ? $contact->id : $contact,
                "profile" => null,
                "status" => $request->status === "true" ? true : false,
                "grant_permission" => $request->grant === "true" ? true : false,
            ]);

            // 4. Add user permissions
            if ($request->Permission) {
                $data = json_decode($request->Permission, true);

                foreach ($data as $category  => $permissions) {
                    $userPermissions = new UserPermission();
                    $userPermissions->permission = $category;
                    $userPermissions->user_id = $newUser->id;
                    // If no access is givin to secction no need to add record
                    $addModel = false;
                    foreach ($permissions as $action => $allowed) {
                        // Check if the value is true or false
                        if ($allowed)
                            $addModel = true;
                        if ($action == "Add")
                            $userPermissions->add = $allowed;
                        else if ($action == "Edit")
                            $userPermissions->edit = $allowed;
                        else if ($action == "Delete")
                            $userPermissions->delete = $allowed;
                        else
                            $userPermissions->view = $allowed;
                    }
                    if ($addModel)
                        $userPermissions->save();
                }
            }
            $newUser->load('job', 'department',); // Adjust according to your relationships
            return response()->json([
                'user' => [
                    "id" => $newUser->id,
                    "username" => $newUser->username,
                    'email' => $request->email,
                    "profile" => $newUser->profile,
                    "status" => $newUser->status,
                    "department" => $this->getTranslationWithNameColumn($newUser->department, Department::class),
                    "job" => $this->getTranslationWithNameColumn($newUser->job, ModelJob::class),
                    "createdAt" => $newUser->created_at,
                ],
                'message' => __('app_translation.success'),
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('User login error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function update(UpdateUserRequest $request)
    {
        $request->validated();
        try {
            // 1. User is passed from middleware
            $user = $request->get('validatedUser');
            if ($user) {
                // 2. Check email
                $email = Email::find($user->email_id);
                if ($email && $email->value !== $request->email) {
                    // 2.1 Email is changed
                    // Delete old email
                    $email->delete();
                    // Add new email
                    $newEmail = Email::create([
                        "value" => $request->email
                    ]);
                    $user->email_id = $newEmail->id;
                }
                // 3. Check contact
                $this->addOrRemoveContact($user, $request);

                // 4. Update User other attributes
                $user->full_name = $request->fullName;
                $user->username = $request->username;
                $user->role_id = $request->role;
                $user->job_id = $request->job;
                $user->department_id = $request->department;
                $user->status = $request->status === "true" ? true : false;
                $user->grant_permission = $request->grant === "true" ? true : false;
                $user->save();

                return response()->json([
                    'message' => __('app_translation.success'),
                ], 200, [], JSON_UNESCAPED_UNICODE);
            }
            return response()->json([
                'message' => __('app_translation.not_found'),
            ], 404, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('User login error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function destroy($id)
    {
        try {
            $user = User::find($id);
            if ($user) {
                // 1. Delete user email
                Email::where('id', '=', $user->email_id)->delete();
                // 2. Delete user contact
                Contact::where('id', '=', $user->contact_id)->delete();
                $user->delete();
                return response()->json([
                    'message' => __('app_translation.success'),
                ], 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json([
                    'message' => __('app_translation.failed'),
                ], 400, [], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $err) {
            Log::info('emailExist error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function updateProfile(Request $request)
    {
        $request->validate([
            'profile' => 'nullable|mimes:jpeg,png,jpg|max:2048',
            'id' => 'required',
        ]);
        try {
            $user = User::find($request->id);
            if ($user) {
                $path = $this->storeProfile($request);
                if ($path != null) {
                    // 1. delete old profile
                    $deletePath = storage_path('app/' . "{$user->profile}");
                    if (file_exists($deletePath) && $user->profile != null) {
                        unlink($deletePath);
                    }
                    // 2. Update the profile
                    $user->profile = $path;
                }
                $user->save();
                return response()->json([
                    'message' => __('app_translation.profile_changed'),
                    "profile" => $user->profile
                ], 200, [], JSON_UNESCAPED_UNICODE);
            } else
                return response()->json([
                    'message' => __('app_translation.not_found'),
                ], 404, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('Profile update error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error'),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function changePassword(UpdateUserPasswordRequest $request)
    {
        $payload = $request->validated();
        try {
            $user = $request->get('validatedUser');
            if ($user) {

                return response()->json([
                    'message' => __('app_translation.success'),
                ], 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json([
                    'message' => __('app_translation.failed'),
                ], 400, [], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $err) {
            Log::info('emailExist error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function deleteProfile($id)
    {
        try {
            $user = User::find($id);
            if ($user) {
                $deletePath = storage_path('app/' . "{$user->profile}");
                if (file_exists($deletePath) && $user->profile != null) {
                    unlink($deletePath);
                }
                // 2. Update the profile
                $user->profile = null;
                $user->save();
                return response()->json([
                    'message' => __('app_translation.profile_changed')
                ], 200, [], JSON_UNESCAPED_UNICODE);
            } else
                return response()->json([
                    'message' => __('app_translation.not_found'),
                ], 404, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('Profile update error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error'),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
