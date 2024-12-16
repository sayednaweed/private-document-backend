<?php

namespace App\Http\Controllers\api\template;

use App\Enums\LanguageEnum;
use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\template\user\UpdateUserPasswordRequest;
use App\Http\Requests\template\user\UpdateUserRequest;
use App\Http\Requests\template\user\UserRegisterRequest;
use App\Models\Contact;
use App\Models\Destination;
use App\Models\Email;
use App\Models\ModelJob;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\UsersEnView;
use App\Models\UsersFaView;
use App\Models\UsersPsView;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;

class UserController extends Controller
{
    public function users(Request $request, $page)
    {
        $locale = App::getLocale();
        $tr = [];
        $perPage = $request->input('per_page', 10); // Number of records per page
        $page = $request->input('page', 1); // Current page

        // Start building the query
        $query = [];
        if ($locale === LanguageEnum::default->value) {
            $query = UsersEnView::query();
        } else if ($locale === LanguageEnum::farsi->value) {
            $query = UsersFaView::query();
        } else {
            $query = UsersPsView::query();
        }
        // Apply date filtering conditionally if provided
        $startDate = $request->input('filters.date.startDate');
        $endDate = $request->input('filters.date.endDate');

        if ($startDate || $endDate) {
            // Apply date range filtering
            if ($startDate && $endDate) {
                $query->whereBetween('createdAt', [$startDate, $endDate]);
            } elseif ($startDate) {
                $query->where('createdAt', '>=', $startDate);
            } elseif ($endDate) {
                $query->where('createdAt', '<=', $endDate);
            }
        }

        // Apply search filter if present
        $searchColumn = $request->input('filters.search.column');
        $searchValue = $request->input('filters.search.value');

        if ($searchColumn && $searchValue) {
            $allowedColumns = ['username', 'contact', 'email'];

            // Ensure that the search column is allowed
            if (in_array($searchColumn, $allowedColumns)) {
                $query->where($searchColumn, 'like', '%' . $searchValue . '%');
            }
        }

        // Apply sorting if present
        $sort = $request->input('filters.sort'); // Sorting column
        $order = $request->input('filters.order', 'asc'); // Sorting order (default is 'asc')

        // Apply sorting by provided column or default to 'created_at'
        if ($sort && in_array($sort, ['username', 'createdAt', 'status', 'job', 'destination'])) {
            $query->orderBy($sort, $order);
        } else {
            // Default sorting if no sort is provided
            $query->orderBy("createdAt", $order);
        }

        // Apply pagination (ensure you're paginating after sorting and filtering)
        $tr = $query->paginate($perPage, ['*'], 'page', $page);
        return response()->json(
            [
                "users" => $tr,
            ],
            200,
            [],
            JSON_UNESCAPED_UNICODE
        );
    }
    public function user($id, Request $request)
    {
        // 1. Retrive current user all permissions
        $foundUser = User::with(['userPermissions', 'contact', 'email', 'role', 'job', 'destination'])
            ->find($id);


        if ($foundUser) {
            $rolePermissions = RolePermission::where('role', '=', $foundUser->role_id)
                ->select('permission')
                ->get();

            $authUser = $request->user()->load('userPermissions');
            // 2. Combine permissions of user1 and user2
            $combinedPermissions = $foundUser->userPermissions->concat($authUser->userPermissions)->unique('permission');
            $concateArr = [];
            foreach ($combinedPermissions as $permission) {
                for ($index = 0; $index < count($rolePermissions); $index++) {
                    $item = $rolePermissions[$index]['permission'];
                    if ($item == $permission->permission) {
                        $actualUser = $permission->user_id == $foundUser->id;

                        array_push($concateArr, [
                            'permission' => $permission->permission,
                            'view' => $actualUser ? $permission->view : false,
                            'add' => $actualUser ? $permission->add : false,
                            'delete' => $actualUser ? $permission->delete : false,
                            'edit' => $actualUser ? $permission->edit : false,
                            'id' => $permission->id,
                        ]);

                        break;
                    }
                }
            }

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
                    "destination" => [
                        'id' => $foundUser->destination_id,
                        'name' => $this->getTranslationWithNameColumn($foundUser->destination, Destination::class)
                    ],
                    "job" => [
                        'id' => $foundUser->job_id,
                        'name' => $this->getTranslationWithNameColumn($foundUser->job, ModelJob::class),
                    ],
                    "createdAt" => $foundUser->created_at,
                ],
                "permission" => $concateArr
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } else
            return response()->json([
                'message' => __('app_translation.not_found'),
            ], 404, [], JSON_UNESCAPED_UNICODE);
    }
    public function validateEmailContact(Request $request)
    {
        $request->validate(
            [
                "email" => "required",
                "contact" => "required",
            ]
        );
        $email = Email::where("value", '=', $request->email)->first();
        $contact = Contact::where("value", '=', $request->contact)->first();
        // Check if both models are found
        $emailExists = $email !== null;
        $contactExists = $contact !== null;

        return response()->json([
            'email_found' => $emailExists,
            'contact_found' => $contactExists,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function store(UserRegisterRequest $request)
    {
        $request->validated();
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
            "destination_id" => $request->destination,
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
        $newUser->load('job', 'destination',); // Adjust according to your relationships
        return response()->json([
            'user' => [
                "id" => $newUser->id,
                "username" => $newUser->username,
                'email' => $request->email,
                "profile" => $newUser->profile,
                "status" => $newUser->status,
                "destination" => $this->getTranslationWithNameColumn($newUser->destination, Destination::class),
                "job" => $this->getTranslationWithNameColumn($newUser->job, ModelJob::class),
                "createdAt" => $newUser->created_at,
            ],
            'message' => __('app_translation.success'),
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function update(UpdateUserRequest $request)
    {
        $request->validated();
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
            if (!$this->addOrRemoveContact($user, $request)) {
                return response()->json([
                    'message' => __('app_translation.contact_exist'),
                ], 400, [], JSON_UNESCAPED_UNICODE);
            }

            // 4. Update User other attributes
            $user->full_name = $request->fullName;
            $user->username = $request->username;
            $user->role_id = $request->role;
            $user->job_id = $request->job;
            $user->destination_id = $request->destination;
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
    }
    public function destroy($id)
    {
        $user = User::find($id);
        if ($user->role_id == RoleEnum::super->value) {
            return response()->json([
                'message' => __('app_translation.unauthorized'),
            ], 403, [], JSON_UNESCAPED_UNICODE);
        }
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
    }
    public function updateProfile(Request $request)
    {
        $request->validate([
            'profile' => 'nullable|mimes:jpeg,png,jpg|max:2048',
            'id' => 'required',
        ]);
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
    }
    public function changePassword(UpdateUserPasswordRequest $request)
    {
        $request->validated();
        $user = $request->get('validatedUser');
        $authUser = $request->user();
        if ($authUser->role_id == RoleEnum::super->value) {
            $user->password = Hash::make($request->newPassword);
            $user->save();
        } else {
            $request->validate([
                "oldPassword" => ["required", "min:8", "max:45"],
            ]);
            if (!Hash::check($request->oldPassword, $user->password)) {
                return response()->json([
                    'message' => __('app_translation.incorrect_password'),
                ], 422, [], JSON_UNESCAPED_UNICODE);
            } else {
                $user->password = Hash::make($request->newPassword);
                $user->save();
            }
        }
        return response()->json([
            'message' => __('app_translation.success'),
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function deleteProfile($id)
    {
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
    }
    public function userCount()
    {
        return response()->json([
            'counts' => [
                "userCount" => User::count(),
                "todayCount" => User::whereDate('created_at', Carbon::today())->count(),
                "activeUserCount" => User::where('status', true)->count(),
                "inActiveUserCount" =>  User::where('status', false)->count()
            ],
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function updatePermission(Request $request)
    {
        $request->validate([
            "user_id" => "required"
        ]);

        if ($request->Permission != "undefined") {
            $user = User::find($request->user_id);
            // 1. Check if it is super user ID 1 do not allow to change permissions
            if ($user === null || $user->id == "1")
                return response()->json(['message' => "You are not authorized!"], 403);

            // 2. Delete all permissions
            UserPermission::where("user_id", "=", $request->user_id)->delete();
            // 3. Add permissions again
            $data = json_decode($request->permission, true);
            foreach ($data as $category  => $permissions) {
                $userPermissions = new UserPermission;
                $userPermissions->permission = $category;
                $userPermissions->user_id = $request->user_id;
                // If no access is givin to secction no need to add record
                $addModel = false;
                foreach ($permissions as $action => $allowed) {
                    // Check if the value is true or false
                    if ($allowed == "true")
                        $addModel = true;
                    if ($action == "add")
                        $userPermissions->Add = $allowed;
                    else if ($action == "edit")
                        $userPermissions->edit = $allowed;
                    else if ($action == "delete")
                        $userPermissions->delete = $allowed;
                    else if ($action == "view")
                        $userPermissions->view = $allowed;
                }
                if ($addModel)
                    $userPermissions->save();
            }
        }
        return response()->json([
            'message' => __('app_translation.success'),
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
