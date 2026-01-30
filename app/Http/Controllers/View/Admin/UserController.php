<?php

namespace App\Http\Controllers\View\Admin;

use App\Models\Role;
use App\Models\User;
use App\Models\UserPhone;
use App\Models\Department;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\TelegramAuthJob;
use App\Jobs\TelegramVerifyJob;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Jobs\VerifyPhoneWithUserJob;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Application\Services\LimitService;
use Illuminate\Validation\ValidationException;
use App\Application\Services\TelegramAuthService;

class UserController extends Controller
{
    public function __construct(protected LimitService $limit, protected TelegramAuthService $authService) {}
    public function show(Request $request, $id)
    {
        $user = User::with([
            'avatar',
            'phones.messageGroups.messages',
            'ban',
            'role',
            'department',
        ])->findOrFail($id);
        
        if ($user->department_id !== $request->user()->department_id ) {
             abort(403, __('messages.users.access_denied'));   
        }
        $department = $user->department;

        $operationsCount = $user->phones
            ->pluck('messageGroups')
            ->flatten()
            ->count();

        $messagesCount = $user->phones
            ->pluck('messageGroups')
            ->flatten()
            ->pluck('messages')
            ->flatten()
            ->count();
       $auth = $request->user();

$canBan = false;
$canEditRole = false;
$canEdit = false;

if (($auth->role->name ?? null) === 'admin') {

    if ((int)$auth->id === (int)$user->id) {
        $canEdit = true;
        $canBan = false;        
        $canEditRole = false;   
    } else {
        $canEdit = true;        

        if (($user->role->name ?? null) === 'admin') {
            if ((int)$user->created_by === (int)$auth->id) {
                $canEditRole = true;
                $canBan = true;
            } else {
                $canEditRole = false;
                $canBan = false;
                $canEdit = false;

            }
        } else {
            $canEditRole = true;
            $canBan = true;
        }
    }
}

        
        $roles = Role::whereNotIn('name', ['superadmin'])->get();

        if($user->role->name === 'superadmin'){
            return view('admin.users.superadmin', compact(
            'user',
            'department',
            'operationsCount',
            'messagesCount',
            'canBan',
            'canEditRole',
            'canEdit',
            'roles'
        ));
        }
        return view('admin.users.show', compact(
            'user',
            'department',
            'operationsCount',
            'messagesCount',
            'canBan',
            'canEditRole',
            'canEdit',
            'roles'
        ));
    }


    public function update(Request $request, $id)
    {
        $user = User::with('avatar')->findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'max:255', Rule::unique('users')->ignore($user->id)],
            'telegram_id' => ['nullable', 'string', 'max:255'],
            // password no confirm now:
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'password' => ['nullable', 'min:6'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
            'active_phone_id' => ['nullable', 'integer', 'exists:user_phones,id'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'] ?? $user->email;
        $user->telegram_id = $data['telegram_id'] ?? $user->telegram_id;

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        // avatar upload
        if ($request->hasFile('avatar')) {
            $f = $request->file('avatar');
            $path = $f->store('avatars', 'public');

            try {
                $old = $user->avatar;
                if ($old && $old->path) Storage::disk('public')->delete($old->path);
            } catch (\Throwable $e) {
            }

            $user->avatar()->updateOrCreate([], ['path' => $path]);
        } elseif ($request->boolean('remove_avatar')) {
            try {
                $old = $user->avatar;
                if ($old && $old->path) Storage::disk('public')->delete($old->path);
                $user->avatar()->delete();
            } catch (\Throwable $e) {
            }
        }
        if (isset($data['role_id'])) {
            $user->role_id = $data['role_id'];
        }
        $user->save();

        // set active phone if requested
        if (!empty($data['active_phone_id'])) {
            DB::transaction(function () use ($user, $data) {
                DB::table('user_phones')->where('user_id', $user->id)->update(['is_active' => 0]);
                DB::table('user_phones')->where('id', $data['active_phone_id'])->update(['is_active' => 1]);
            });
        }

        return redirect()->route('admin.users.show', $user->id)
            ->with('success', __('messages.users.user_updated') ?? 'User updated');
    }

    // add new phone
    public function addPhone(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'phone' => ['required', 'string', 'max:50'],
        ]);

        $phone = new UserPhone();
        $phone->user_id = $user->id;
        $phone->phone = $data['phone'];
        $phone->is_active = 0;
        $phone->save();

        return redirect()->route('admin.users.show', $user->id)->with('success', __('messages.users.phone_added') ?? 'Phone added');
    }

    public function deletePhone(Request $request, $id, $phoneId)
    {
        $user = User::findOrFail($id);
        $phone = UserPhone::where('user_id', $user->id)->where('id', $phoneId)->firstOrFail();

        // if it's active, try to unset or set another phone active
        if ($phone->is_active) {
            // set another phone active (if exists)
            $other = UserPhone::where('user_id', $user->id)->where('id', '<>', $phone->id)->first();
            if ($other) {
                $other->is_active = 1;
                $other->save();
            }
        }

        $phone->delete();

        return redirect()->route('admin.users.show', $user->id)->with('success', __('messages.users.phone_deleted') ?? 'Phone deleted');
    }
        public function canUsePhone(string $phone): bool
{
    return !UserPhone::where('phone', $phone)
        ->where(function ($q) {
            $q->where('is_active', true)
              ->orWhereIn('telegram_user_id', function ($sub) {
                  $sub->select('telegram_id')
                      ->from('users')
                      ->whereNotNull('telegram_id');
              });
        })
        ->exists();
}


    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);

        try {
            $old = $user->avatar;
            if ($old && $old->path) Storage::disk('public')->delete($old->path);
            $user->avatar()->delete();
        } catch (\Throwable $e) {
        }

        $departmentId = $user->department_id;
        $user->delete();

        return redirect()->back()->with('success', __('messages.users.user_deleted') ?? 'User deleted');
    }

    public function sendPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'regex:/^\+\d{6,16}$/'],
            'name' => 'required|string|max:255',
            'login' => 'required|string|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role_id' => 'nullable|integer|exists:roles,id',
        ],
        [
            'phone.regex' => __('messages.telegram.phone_invalid'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('messages.validation_failed') ?? 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }


        try {
            $phone = preg_replace('/[^0-9+]/', '', $request->input('phone', ''));
            if (!str_starts_with($phone, '+')) {
                $phone = '+' . $phone;
            }

            $user = $this->resolveUserFromRequest($request);

            if (!$this->canUsePhone($phone)) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('messages.telegram.user_exists')
                ], 403);
            }

            if (!$this->limit->canCreateUser($user)) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('messages.telegram.limit')
                ], 403);
            }
            if($request->user()->role->name==='superadmin'){
                $department_id=$request->department;
            }
            else{
                $department_id=$user->department->id;
            }

            $newUser = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('login'),
                'password' => Hash::make($request->input('password')),
                'role_id' => $request->input('role_id'),
                'created_by' => $user->id,
                'department_id' => $department_id,
            ]);
            $lockKey = "telegram_verify_lock_{$phone}_{$newUser->id}";
            $lockTtlSeconds = 60 * 10;

            $started = false;
            if (Cache::add($lockKey, true, $lockTtlSeconds)) {
                TelegramAuthJob::dispatch($phone, $newUser->id,)->onQueue('telegram');
                $started = true;
            }

            return response()->json([
                'status' => $started ? 'sms_sent' : 'locked',
                'message' => $started
                    ? __('messages.telegram.sms_sent')
                    : (__('messages.telegram.already_in_progress') ?? 'Verification already in progress'),
                'user_id' => $user->id,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    public function storeUserWithTelegram(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string',
            'login' => 'required|string|max:255|exists:users,email',
            'department_id' => 'nullable|integer|exists:departments,id',
            'user_id' => 'nullable|integer|exists:users,id'
        ]);
        $user = $request->user();
        $user = User::where('email', $data['login'])->first();
        if (!$user) {
            return redirect()->back()->with('error', __('messages.telegram.user_not_found'));
        }
        $departmentId = $data['department_id'] ?? optional($user)->department_id ?? null;


        TelegramVerifyJob::dispatch($data['phone'], $user->id, $data['code'], $departmentId, null)
            ->onQueue('telegram');
        $token = (string) Str::uuid();
        Cache::put("notif:{$token}", [
            'message' => __('messages.telegram.started'),
            'type'    => 'success',
        ], now()->addMinutes(10));
        if ($request->user()->role->name === 'superadmin') {
            return redirect()->route('superadmin.departments.users', $departmentId)->with('success', __('messages.telegram.started'));
        }
        return redirect()->route('departments.users', $departmentId)->with('success', __('messages.telegram.started'));
    }
    public function newTelegramUsers(Request $request)
{
    $user = $request->user();
    $roles = Role::whereNotIn('name', ['superadmin'])->get();

    if ($request->has('department') && $user->role->name === 'superadmin') {
        $department = Department::find($request->get('department'));

        if (!$department) {
            return redirect()->back()
                ->withErrors(['department' => __('messages.admin.not_found')]);
        }

        return view('superadmin.telegram.telegram-login', compact('department', 'roles'));
    }

    $department = $user->department;

    return view('admin.telegram.telegram-login', compact('department', 'roles'));
}


































    protected function resolveUserFromRequest(Request $request): User
    {
        $userId = $request->input('user_id') ?? $request->query('user_id');
        if ($userId) {
            $user = User::find($userId);
            if (! $user) {
                abort(404, 'User topilmadi');
            }
            return $user;
        }

        $user = $request->user();
        if (! $user) {
            abort(401, 'Login talab qilinadi');
        }
        return $user;
    }
}
