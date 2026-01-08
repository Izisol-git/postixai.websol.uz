<?php

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BanController extends Controller
{
    public function banUnban(Request $request)
    {
        $authUser = $request->user();

        $map = [
            'user'       => \App\Models\User::class,
            'phone'      => \App\Models\UserPhone::class,
            'department' => \App\Models\Department::class,
        ];

        $data = $request->validate([
            'bannable_type' => ['required', 'string'],
            'bannable_id'   => ['required', 'integer'],
            'action'        => ['nullable', 'string'],
            'starts_at'     => ['nullable', 'date'],
        ]);

        $rawType  = trim($data['bannable_type']);
        $id       = (int) $data['bannable_id'];
        $action   = isset($data['action']) ? strtolower($data['action']) : null;
        $lowerMap = array_change_key_case($map, CASE_LOWER);

        if (isset($lowerMap[strtolower($rawType)])) {
            $class = $lowerMap[strtolower($rawType)];
            $shortType = strtolower($rawType);
            if (! in_array($shortType, array_keys($map), true)) {
                $shortType = array_search($class, $map, true);
            }
        } elseif (class_exists($rawType) && in_array($rawType, $map, true)) {
            $class = $rawType;
            $shortType = array_search($class, $map, true);
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid bannable type.'], 422);
        }

        $model = $class::find($id);
        if (! $model) {
            return response()->json(['success' => false, 'message' => class_basename($class) . ' not found.'], 404);
        }

        $isSuperadmin = ($authUser->role->name ?? null) === 'superadmin';
        if (! $isSuperadmin) {
            $modelDept = $model->department_id ?? null;
            if ($modelDept === null || $modelDept !== $authUser->department_id) {
                return response()->json(['success' => false, 'message' => 'No permission.'], 403);
            }
        }

        $modelName = class_basename($class);
        $label = Str::headline($modelName);

        // existing ban (morphOne)
        $ban = $model->ban()->first();

        // parse starts_at when provided
        $startsAt = null;
        if (! empty($data['starts_at'])) {
            try {
                $startsAt = Carbon::parse($data['starts_at']);
            } catch (\Throwable $e) {
                return response()->json(['success' => false, 'message' => 'Invalid starts_at format.'], 422);
            }
        }

        //
        // Business rules (unchanged) but ensure on UNBAN we null starts_at
        //

        // EXPLICIT unban: always clear starts_at and deactivate if ban exists
        if ($action === 'unban') {
            if ($ban) {
                $ban->active = false;
                $ban->starts_at = null;      // <- ensure null on unban
                $ban->save();
                return response()->json([
                    'success' => true,
                    'model' => $modelName,
                    'is_banned' => false,
                    'starts_at' => null,
                    'message' => "{$label} unbanned.",
                ]);
            }

            return response()->json([
                'success' => true,
                'model' => $modelName,
                'is_banned' => false,
                'starts_at' => null,
                'message' => 'No active ban to remove.',
            ]);
        }

        // If ban exists and is active -> always unban on any request (also null starts_at)
        if ($ban && $ban->active) {
            $ban->active = false;
            $ban->starts_at = null; // <- ensure null
            $ban->save();

            return response()->json([
                'success' => true,
                'model' => $modelName,
                'is_banned' => false,
                'starts_at' => null,
                'message' => "{$label} unbanned.",
            ]);
        }

        // Now: either no ban or ban exists but inactive (scheduled)
        if ($shortType === 'user') {
            // Users: immediate ban only
            $now = Carbon::now();
            if ($ban) {
                $ban->starts_at = $now;
                $ban->active = true;
                $ban->save();
            } else {
                $ban = $model->ban()->create([
                    'starts_at' => $now,
                    'active' => true,
                ]);
            }

            return response()->json([
                'success' => true,
                'model' => $modelName,
                'is_banned' => true,
                'starts_at' => $ban->starts_at ? $ban->starts_at->toDateTimeString() : null,
                'message' => "{$label} banned now.",
            ]);
        }

        // phone/department: scheduling supported
        $now = Carbon::now();
        if ($startsAt && $startsAt->gt($now)) {
            // scheduled (inactive)
            if ($ban) {
                $ban->starts_at = $startsAt;
                $ban->active = false;
                $ban->save();
            } else {
                $ban = $model->ban()->create([
                    'starts_at' => $startsAt,
                    'active' => false,
                ]);
            }

            return response()->json([
                'success' => true,
                'model' => $modelName,
                'is_banned' => false,
                'starts_at' => $ban->starts_at ? $ban->starts_at->toDateTimeString() : null,
                'message' => "{$label} ban scheduled for {$ban->starts_at->toDateTimeString()}.",
            ]);
        } else {
            // immediate ban
            $start = $startsAt ?: $now;
            if ($ban) {
                $ban->starts_at = $start;
                $ban->active = true;
                $ban->save();
            } else {
                $ban = $model->ban()->create([
                    'starts_at' => $start,
                    'active' => true,
                ]);
            }

            return response()->json([
                'success' => true,
                'model' => $modelName,
                'is_banned' => true,
                'starts_at' => $ban->starts_at ? $ban->starts_at->toDateTimeString() : null,
                'message' => "{$label} banned now.",
            ]);
        }
    }
}
