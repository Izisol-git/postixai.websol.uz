<?php

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BanController extends Controller
{
    public function banUnban(Request $request)
    {
        try {
            $authUser = $request->user();
            $role     = $authUser->role->name ?? null;

            $isSuperadmin = $role === 'superadmin';
            $isAdmin      = $role === 'admin';

            /* =======================
             | Bannable map
             ======================= */
            $map = [
                'user'       => \App\Models\User::class,
                'phone'      => \App\Models\UserPhone::class,
                'department' => \App\Models\Department::class,
            ];

            /* =======================
             | Validation
             ======================= */
            $data = $request->validate([
                'bannable_type' => ['required', 'string'],
                'bannable_id'   => ['required', 'integer'],
                'action'        => ['nullable', 'string'],
                'starts_at'     => ['nullable', 'date'],
            ]);

            $type   = strtolower(trim($data['bannable_type']));
            $action = strtolower($data['action'] ?? '');
            $id     = (int) $data['bannable_id'];

            if (!isset($map[$type])) {
                return $this->error(__('messages.ban.invalid_type'), 422);
            }

            $class = $map[$type];
            $model = $class::find($id);

            if (!$model) {
                return $this->error(
                    __('messages.ban.not_found', ['model' => class_basename($class)]),
                    404
                );
            }

            /* =======================
             | PERMISSIONS
             ======================= */

            // admin departmentni ban qila olmaydi
            if ($isAdmin && $type === 'department') {
                return $this->error(__('messages.ban.admin_department_forbidden'), 403);
            }

            // admin boshqa adminni ban qila olmaydi
            if ($isAdmin && $type === 'user') {
                if (($model->role->name ?? null) === 'admin') {
                    return $this->error(__('messages.ban.admin_to_admin_forbidden'), 403);
                }
            }

            // superadmin emas → faqat o‘z departmenti
            if (!$isSuperadmin && $type !== 'department') {
                if (
                    !isset($model->department_id) ||
                    $model->department_id !== $authUser->department_id
                ) {
                    return $this->error(__('messages.ban.no_permission'), 403);
                }
            }

            /* =======================
             | Ban logic
             ======================= */
            $ban   = $model->ban()->first();
            $label = Str::headline(class_basename($class));
            $now   = Carbon::now();

            // starts_at parse
            $startsAt = null;
            if (!empty($data['starts_at'])) {
                try {
                    $startsAt = Carbon::parse($data['starts_at']);
                } catch (\Throwable $e) {
                    return $this->error(__('messages.ban.invalid_date'), 422);
                }
            }

            /* ===== UNBAN ===== */
            if ($action === 'unban') {
                if ($ban) {
                    $ban->update(['active' => false, 'starts_at' => null]);
                }

                return $this->success([
                    'model' => $label,
                    'is_banned' => false,
                    'starts_at' => null,
                ], __('messages.ban.unbanned', ['model' => $label]));
            }

            /* ===== TOGGLE OFF ===== */
            if ($ban && $ban->active) {
                $ban->update(['active' => false, 'starts_at' => null]);

                return $this->success([
                    'model' => $label,
                    'is_banned' => false,
                    'starts_at' => null,
                ], __('messages.ban.unbanned', ['model' => $label]));
            }

            /* ===== USER → instant ===== */
            if ($type === 'user') {
                $ban = $model->ban()->updateOrCreate([], [
                    'starts_at' => $now,
                    'active'    => true,
                ]);

                return $this->success([
                    'model' => $label,
                    'is_banned' => true,
                    'starts_at' => $ban->starts_at?->toDateTimeString(),
                ], __('messages.ban.banned_now', ['model' => $label]));
            }

            /* ===== PHONE / DEPARTMENT ===== */
            $start  = ($startsAt && $startsAt->gt($now)) ? $startsAt : $now;
            $active = !($startsAt && $startsAt->gt($now));

            $ban = $model->ban()->updateOrCreate([], [
                'starts_at' => $start,
                'active'    => $active,
            ]);

            return $this->success([
                'model' => $label,
                'is_banned' => $active,
                'starts_at' => $ban->starts_at?->toDateTimeString(),
            ], $active
                ? __('messages.ban.banned_now', ['model' => $label])
                : __('messages.ban.scheduled', ['model' => $label])
            );
        } catch (\Throwable $e) {
            Log::error('BanController error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => optional($request->user())->id,
            ]);

            return $this->error(__('messages.ban.internal_error'), 500);
        }
    }
}
