<?php

namespace App\Http\Controllers\View\Admin;

use App\Models\User;
use App\Models\UserPhone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function show(Request $request, $id)
{
    $user = User::with([
        'avatar',
        'phones.messageGroups.messages',
        'ban',
        'role',
        'department',
    ])->findOrFail($id);

    $department = $user->department;

    // ✅ OPERATIONS = messageGroups
    $operationsCount = $user->phones
        ->pluck('messageGroups')
        ->flatten()
        ->count();

    // ✅ MESSAGES
    $messagesCount = $user->phones
        ->pluck('messageGroups')
        ->flatten()
        ->pluck('messages')
        ->flatten()
        ->count();

    return view('admin.users.show', compact(
        'user',
        'department',
        'operationsCount',
        'messagesCount'
    ));
}


    public function update(Request $request, $id)
    {
        $user = User::with('avatar')->findOrFail($id);

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['nullable','email','max:255', Rule::unique('users')->ignore($user->id)],
            'telegram_id' => ['nullable','string','max:255'],
            // password no confirm now:
            'password' => ['nullable','min:6'],
            'avatar' => ['nullable','image','max:2048'],
            'remove_avatar' => ['nullable','boolean'],
            'active_phone_id' => ['nullable','integer','exists:user_phones,id'],
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
            } catch (\Throwable $e) {}

            $user->avatar()->updateOrCreate([], ['path' => $path]);
        } elseif ($request->boolean('remove_avatar')) {
            try {
                $old = $user->avatar;
                if ($old && $old->path) Storage::disk('public')->delete($old->path);
                $user->avatar()->delete();
            } catch (\Throwable $e) {}
        }

        $user->save();

        // set active phone if requested
        if (!empty($data['active_phone_id'])) {
            DB::transaction(function() use ($user, $data) {
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
            'phone' => ['required','string','max:50'],
        ]);

        $phone = new UserPhone();
        $phone->user_id = $user->id;
        $phone->phone = $data['phone'];
        $phone->is_active = 0;
        $phone->save();

        return redirect()->route('admin.users.show', $user->id)->with('success', __('messages.users.phone_added') ?? 'Phone added');
    }

    // delete phone
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

    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);

        try {
            $old = $user->avatar;
            if ($old && $old->path) Storage::disk('public')->delete($old->path);
            $user->avatar()->delete();
        } catch (\Throwable $e) {}

        $departmentId = $user->department_id;
        $user->delete();

        return redirect()->route('departments.users', $departmentId)
            ->with('success', __('messages.users.user_deleted') ?? 'User deleted');
    }
    

}
