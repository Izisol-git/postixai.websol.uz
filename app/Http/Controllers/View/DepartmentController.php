<?php

namespace App\Http\Controllers\View;

use Carbon\Carbon;
use App\Models\Ban;
use App\Models\User;
use App\Models\UserPhone;
use App\Models\Department;
use App\Models\MessageGroup;
use Illuminate\Http\Request;
use App\Models\TelegramMessage;
use Illuminate\Support\FacadesDB;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role->name, ['superadmin'])) {
            return redirect()->route('departments.dashboard', $user->department_id);
        }
        $range = $request->get('range', 'all');

        $since = null;
        if ($range === 'day') {
            $since = Carbon::now()->subDay();
        } elseif ($range === 'week') {
            $since = Carbon::now()->subWeek();
        } elseif ($range === 'month') {
            $since = Carbon::now()->subMonth();
        } elseif ($range === 'year') {
            $since = Carbon::now()->subYear();
        }
        $sinceStr = $since ? $since->toDateTimeString() : null;

        // Per-department aggregate using subqueries (memory-friendly)
        $selects = [
            'departments.id',
            'departments.name',
            'departments.deleted_at',
            DB::raw(
                $sinceStr
                    ? "(SELECT COUNT(*) FROM users WHERE users.department_id = departments.id AND users.created_at >= '{$sinceStr}') AS users_count"
                    : "(SELECT COUNT(*) FROM users WHERE users.department_id = departments.id) AS users_count"
            ),
            DB::raw(
                $sinceStr
                    ? "(SELECT COUNT(*) FROM user_phones up JOIN users u ON u.id = up.user_id WHERE u.department_id = departments.id AND up.is_active = 1 AND up.created_at >= '{$sinceStr}') AS active_phones_count"
                    : "(SELECT COUNT(*) FROM user_phones up JOIN users u ON u.id = up.user_id WHERE u.department_id = departments.id AND up.is_active = 1) AS active_phones_count"
            ),
            DB::raw(
                $sinceStr
                    ? "(SELECT COUNT(*) FROM message_groups mg JOIN user_phones up2 ON up2.id = mg.user_phone_id JOIN users u2 ON u2.id = up2.user_id WHERE u2.department_id = departments.id AND mg.created_at >= '{$sinceStr}') AS message_groups_count"
                    : "(SELECT COUNT(*) FROM message_groups mg JOIN user_phones up2 ON up2.id = mg.user_phone_id JOIN users u2 ON u2.id = up2.user_id WHERE u2.department_id = departments.id) AS message_groups_count"
            ),
            DB::raw(
                $sinceStr
                    ? "(SELECT COUNT(*) FROM telegram_messages tm JOIN message_groups mg2 ON mg2.id = tm.message_group_id JOIN user_phones up3 ON up3.id = mg2.user_phone_id JOIN users u3 ON u3.id = up3.user_id WHERE u3.department_id = departments.id AND tm.send_at >= '{$sinceStr}') AS telegram_messages_count"
                    : "(SELECT COUNT(*) FROM telegram_messages tm JOIN message_groups mg2 ON mg2.id = tm.message_group_id JOIN user_phones up3 ON up3.id = mg2.user_phone_id JOIN users u3 ON u3.id = up3.user_id WHERE u3.department_id = departments.id) AS telegram_messages_count"
            ),
        ];

        $deptStats = DB::table('departments')
            ->select($selects)
            ->orderBy('departments.name')
            ->get();

        // Build charts datasets
        $chartUsers = $deptStats->pluck('users_count', 'name');
        $chartPhones = $deptStats->pluck('active_phones_count', 'name');
        $chartGroups = $deptStats->pluck('message_groups_count', 'name');
        $chartMessages = $deptStats->pluck('telegram_messages_count', 'name');

        $totals = [
            'users' => $chartUsers->sum(),
            'phones' => $chartPhones->sum(),
            'groups' => $chartGroups->sum(),
            'messages' => $chartMessages->sum(),
        ];

        $n = max(1, $deptStats->count());
        $colors = [];
        for ($i = 0; $i < $n; $i++) {
            $h = intval(($i * 360) / $n);
            $s = 70;
            $l = 50;
            $colors[] = "hsl({$h}, {$s}%, {$l}%)";
        }
        return view('dashboard', compact(
            'deptStats',
            'chartUsers',
            'chartPhones',
            'chartGroups',
            'chartMessages',
            'totals',
            'colors',
            'range'
        ));
    }
    public function create()
    {
        return view('departments.create');
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
        ]);
        Department::create($data);
        return redirect()->route('departments.index');
    }
    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }
    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
        ]);

        $department->update($data);

        return redirect()->route('departments.show', $department->id)->with('success', 'Department updated successfully');
    }
    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->route('departments.index');
    }
    public function show(Request $request, $id)
    {
        $user = $request->user();

        if (!in_array($user->role->name, ['superadmin'])) {
            return redirect()->route('departments.dashboard', $user->department_id);
        }



        $department = DB::table('departments')->where('id', $id)->first();
        if (!$department) abort(404);




        $ban = DB::table('bans')
            ->where('bannable_type', Department::class)
            ->where('bannable_id', $id)
            ->orderByDesc('id')
            ->first();

        $now = Carbon::now();

        $isBannedActive = false;
        $isScheduled = false;
        $startsAt = null;
        $untilAt = null;

        if ($ban) {

            $active = (int) ($ban->active ?? 0);
            $starts = $ban->starts_at ? Carbon::parse($ban->starts_at) : null;
            $until  = $ban->until ? Carbon::parse($ban->until) : null;


            /* === ACTIVE BAN === */
            if ($active === 1) {

                if ($until && $until->lte($now)) {

                    DB::table('bans')
                        ->where('id', $ban->id)
                        ->update([
                            'active' => 0,
                            'updated_at' => $now
                        ]);

                    $active = 0;
                } else {

                    $isBannedActive = true;
                    $startsAt = $starts?->toDateTimeString();
                    $untilAt  = $until?->toDateTimeString();
                }
            }


            /* === NOT ACTIVE === */
            if ($active === 0) {

                if ($starts) {

                    if ($starts->lte($now)) {

                        DB::table('bans')
                            ->where('id', $ban->id)
                            ->update([
                                'active' => 1,
                                'updated_at' => $now
                            ]);

                        $isBannedActive = true;

                        $startsAt = $starts->toDateTimeString();
                        $untilAt  = $until?->toDateTimeString();
                    } else {

                        $isScheduled = true;

                        $startsAt = $starts->toDateTimeString();
                        $untilAt  = $until?->toDateTimeString();
                    }
                }
            }
        }


        $banMeta = [
            'isBannedActive' => $isBannedActive,
            'isScheduled'    => $isScheduled,
            'startsAt'       => $startsAt,
            'untilAt'        => $untilAt,
        ];


        /* =========================
        BASIC COUNTS
        ========================= */

        $usersCount = (int) DB::table('users')
            ->where('department_id', $id)
            ->count();


        $activePhonesCount = (int) DB::table('user_phones')
            ->join('users', 'users.id', '=', 'user_phones.user_id')
            ->where('users.department_id', $id)
            ->whereNull('user_phones.deleted_at')
            ->count();


        /* =========================
        MESSAGE TOTALS
        ========================= */

        $messageGroupsTotal = (int) DB::table('message_groups')
            ->join('user_phones', 'message_groups.user_phone_id', '=', 'user_phones.id')
            ->join('users', 'users.id', '=', 'user_phones.user_id')
            ->where('users.department_id', $id)
            ->count();


        $telegramMessagesTotalRow = DB::table('telegram_messages')
            ->whereIn('message_group_id', function ($q) use ($id) {

                $q->select('message_groups.id')
                    ->from('message_groups')
                    ->join('user_phones', 'message_groups.user_phone_id', '=', 'user_phones.id')
                    ->join('users', 'users.id', '=', 'user_phones.user_id')
                    ->where('users.department_id', $id);
            })
            ->selectRaw('COUNT(*) as cnt')
            ->first();


        $telegramMessagesTotal = (int) ($telegramMessagesTotalRow->cnt ?? 0);


        /* =========================
        GROUPS PAGINATION
     ========================= */

        $groupsQuery = DB::table('message_groups')
            ->select(
                'message_groups.*',
                'user_phones.phone as phone_number',
                'users.name as user_name',
                'user_phones.id as user_phone_id'
            )
            ->join('user_phones', 'message_groups.user_phone_id', '=', 'user_phones.id')
            ->join('users', 'users.id', '=', 'user_phones.user_id')
            ->where('users.department_id', $id)
            ->orderByDesc('message_groups.id');


        $perPage = 12;
        $page = (int) $request->query('page', 1);

        $totalGroups = $groupsQuery->count();

        $groups = $groupsQuery
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();


        $groupIds = $groups->pluck('id')->toArray();


        /* =========================
        TEXT STATS
        ========================= */

        $textStats = collect();

        if (!empty($groupIds)) {

            $samples = DB::table('telegram_messages')
                ->whereIn('message_group_id', $groupIds)
                ->orderBy('message_group_id')
                ->orderByDesc('sent_at')
                ->select('message_group_id', 'message_text')
                ->get()
                ->groupBy('message_group_id');


            foreach ($samples as $gid => $rows) {

                $textStats->put($gid, (object) [
                    'sample_text' => $rows->first()->message_text ?? null
                ]);
            }
        }


        /* =========================
        PEER STATUS
        ========================= */

        $peerStatusByGroup = [];
        $groupTotals = [];

        if (!empty($groupIds)) {

            $rows = DB::table('telegram_messages')
                ->whereIn('message_group_id', $groupIds)
                ->select(
                    'message_group_id',
                    'peer',
                    'status',
                    DB::raw('COUNT(*) as cnt')
                )
                ->groupBy('message_group_id', 'peer', 'status')
                ->get();


            foreach ($rows as $r) {

                $gid = $r->message_group_id;

                $peerStatusByGroup[$gid][$r->peer][$r->status] =
                    ($peerStatusByGroup[$gid][$r->peer][$r->status] ?? 0) + $r->cnt;

                $groupTotals[$gid][$r->status] =
                    ($groupTotals[$gid][$r->status] ?? 0) + $r->cnt;
            }


            foreach ($peerStatusByGroup as $gid => $peerMap) {

                foreach ($peerMap as $peer => $statusMap) {

                    $peerStatusByGroup[$gid][$peer] = [
                        'sent'      => (int) ($statusMap['sent'] ?? 0),
                        'failed'    => (int) ($statusMap['failed'] ?? 0),
                        'canceled'  => (int) ($statusMap['canceled'] ?? 0),
                        'scheduled' => (int) ($statusMap['scheduled'] ?? 0),
                    ];
                }
            }


            foreach ($groupTotals as $gid => $t) {

                $groupTotals[$gid] = [
                    'sent'      => (int) ($t['sent'] ?? 0),
                    'failed'    => (int) ($t['failed'] ?? 0),
                    'canceled'  => (int) ($t['canceled'] ?? 0),
                    'scheduled' => (int) ($t['scheduled'] ?? 0),
                ];
            }
        }


        /* =========================
        RECENT MULTI TEXT
        ========================= */

        $groupIdsWithMultipleTexts = DB::table('telegram_messages')
            ->join('message_groups', 'telegram_messages.message_group_id', '=', 'message_groups.id')
            ->join('user_phones', 'message_groups.user_phone_id', '=', 'user_phones.id')
            ->join('users', 'users.id', '=', 'user_phones.user_id')
            ->where('users.department_id', $id)
            ->select(
                'telegram_messages.message_group_id',
                DB::raw('COUNT(DISTINCT telegram_messages.message_text)')
            )
            ->groupBy('telegram_messages.message_group_id')
            ->havingRaw('COUNT(DISTINCT telegram_messages.message_text) > 1')
            ->pluck('message_group_id')
            ->toArray();


        $recentMessagesByGroup = [];

        if (!empty($groupIdsWithMultipleTexts)) {

            $recentRows = DB::table('telegram_messages')
                ->whereIn('message_group_id', $groupIdsWithMultipleTexts)
                ->orderByDesc('sent_at')
                ->limit(200)
                ->get()
                ->groupBy('message_group_id');


            foreach ($recentRows as $gid => $rows) {
                $recentMessagesByGroup[$gid] = $rows->take(10);
            }
        }


        /* =========================
        PAGINATOR
        ========================= */

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $groups,
            $totalGroups,
            $perPage,
            $page,
            [
                'path'  => $request->url(),
                'query' => $request->query()
            ]
        );

        return view('departments.show', [

            'department' => $department,

            'ban'     => $ban,
            'banMeta' => $banMeta,

            'usersCount' => $usersCount,
            'activePhonesCount' => $activePhonesCount,

            'messageGroupsTotal' => $messageGroupsTotal,
            'telegramMessagesTotal' => $telegramMessagesTotal,

            'recentMessagesByGroup' => $recentMessagesByGroup,

            'messageGroups' => $paginator,

            'textStats' => $textStats,

            'peerStatusByGroup' => $peerStatusByGroup,
            'groupTotals' => $groupTotals,
        ]);
    }
    public function users(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array($user->role->name, ['superadmin'])) {
            return redirect()->route('departments.dashboard', $user->department_id);
        }

        // department presence
        $department = DB::table('departments')->where('id', $id)->first();
        if (!$department) abort(404);

        // Fetch users in this department
        $users = DB::table('users')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->where('users.department_id', $id)
            ->select(
                'users.id',
                'users.name',
                'users.telegram_id',
                'users.email',
                'users.role_id',
                'roles.name as role_name',
                'users.deleted_at',
            )
            ->orderByDesc('users.id')
            ->get();


        $userIds = $users->pluck('id')->toArray();

        // Phones for users (single query)
        $phones = DB::table('user_phones')
            ->whereIn('user_id', $userIds)
            ->select('id', 'user_id', 'phone', 'is_active')
            ->get()
            ->groupBy('user_id');

        // Bans for users and phones


        // For phone bans, get by phone ids



        // user bans
        $userBans = DB::table('bans')
            ->where('bannable_type', User::class)
            ->whereIn('bannable_id', $userIds)
            ->get()
            ->keyBy('bannable_id');

        // roles (id => name)
        $roles = DB::table('roles')->pluck('name', 'id');

        $usersPrepared = $users->map(function ($u) use ($phones,  $userBans, $roles) {
            $u->phones = $phones[$u->id] ?? collect([]);
            $banRow = $userBans[$u->id] ?? null;
            $u->is_banned = (bool) ($banRow->active  ?? false);
            $u->ban = $banRow;



            $u->role_name = $roles[$u->role_id] ?? null;
            return $u;
        });
        $b = Ban::where('bans.bannable_type', '=', User::class)->get();
        // dd($usersPrepared->toArray());
        return view('departments.users', [
            'department' => $department,
            'users' => $usersPrepared,
        ]);
    }
    public function operations(Request $request, $id = null)
{
    $user = $request->user();
    $isSuperadmin = ($user->role->name ?? '') === 'superadmin';

    if (!$isSuperadmin) {
        $deptId = $id ?? $user->department_id;
        $department = Department::find($deptId);
        if (!$department) abort(404);
    } else {
        $department = $id ? Department::find($id) : null;
        if ($id && !$department) abort(404);
    }

    $q = trim((string) $request->get('q', ''));
    $status = $request->get('status', null);
    $from = $request->get('from', null);
    $to = $request->get('to', null);
    $selectedUserId = $request->get('user_id', null);
    $filterDeptId = $request->get('department_id', null); // for superadmin filter from UI

    if ($isSuperadmin) {
        if ($department) {
            $users = User::where('department_id', $department->id)->orderBy('name')->get();
        } elseif ($filterDeptId) {
            $users = User::where('department_id', $filterDeptId)->orderBy('name')->get();
        } else {
            $users = User::orderBy('name')->get();
        }
        $departments = Department::orderBy('name')->get();
    } else {
        $users = User::where('department_id', $department->id)->orderBy('name')->get();
        $departments = collect(); // empty
    }

    $phoneSub = function ($qPhone) use ($department, $filterDeptId, $selectedUserId) {
        $qPhone->select('user_phones.id')
            ->from('user_phones')
            ->join('users', 'users.id', '=', 'user_phones.user_id');

        if ($selectedUserId) {
            $qPhone->where('users.id', $selectedUserId);
            return;
        }

        if ($department) {
            $qPhone->where('users.department_id', $department->id);
            return;
        }

        if ($filterDeptId) {
            $qPhone->where('users.department_id', $filterDeptId);
            return;
        }

        // no filter = all user_phones
    };

    // Base query: message_groups for phones from phoneSub
    $base = MessageGroup::whereIn('user_phone_id', $phoneSub);

    // === SEARCH ===
    // If q present, search across:
    //  - message_groups.message_text
    //  - telegram_messages.message_text
    //  - telegram_messages.peer
    //  - user phone number (user_phones.phone) via phone relation
    //  - user name / username via phone->user relation
    if ($q !== '') {
        $term = "%{$q}%";
        $base->where(function ($w) use ($term) {
            // match group text
            $w->where('message_groups.message_text', 'like', $term);

            // or any telegram_messages in the group contain the text OR peer
            $w->orWhereExists(function ($sub) use ($term) {
                $sub->selectRaw(1)
                    ->from('telegram_messages')
                    ->whereColumn('telegram_messages.message_group_id', 'message_groups.id')
                    ->where(function ($s) use ($term) {
                        $s->where('telegram_messages.message_text', 'like', $term)
                          ->orWhere('telegram_messages.peer', 'like', $term);
                    });
            });

            // or the associated phone record contains the phone number itself
            $w->orWhereExists(function ($sub2) use ($term) {
                $sub2->selectRaw(1)
                    ->from('user_phones')
                    ->whereColumn('user_phones.id', 'message_groups.user_phone_id')
                    ->where('user_phones.phone', 'like', $term);
            });

            // or the associated user (via phone) name/username
            $w->orWhereExists(function ($sub3) use ($term) {
                $sub3->selectRaw(1)
                    ->from('user_phones as up')
                    ->join('users as u', 'u.id', '=', 'up.user_id')
                    ->whereColumn('up.id', 'message_groups.user_phone_id')
                    ->where(function ($u) use ($term) {
                        $u->where('u.name', 'like', $term)
                          ->orWhere('u.username', 'like', $term);
                    });
            });
        });
    }

    // Apply status filter: groups that have at least one telegram_messages with that status
    if ($status) {
        $base->whereExists(function ($sub) use ($status) {
            $sub->selectRaw(1)
                ->from('telegram_messages')
                ->whereColumn('telegram_messages.message_group_id', 'message_groups.id')
                ->where('telegram_messages.status', $status);
        });
    }

    // Apply date range on telegram_messages.sent_at
    if ($from || $to) {
        $base->whereExists(function ($sub) use ($from, $to) {
            $sub->selectRaw(1)
                ->from('telegram_messages')
                ->whereColumn('telegram_messages.message_group_id', 'message_groups.id');

            if ($from) {
                $sub->where('telegram_messages.sent_at', '>=', \Carbon\Carbon::parse($from)->startOfDay());
            }
            if ($to) {
                $sub->where('telegram_messages.sent_at', '<=', \Carbon\Carbon::parse($to)->endOfDay());
            }
        });
    }

    // Pagination and ordering
    $messageGroups = $base->orderByDesc('id')
        ->paginate(10, ['*'], 'groups_page')
        ->withQueryString();

    $groupIds = $messageGroups->pluck('id')->toArray();

    // TEXT STATS per group (total, started/ended)
    $textStats = collect();
    if (!empty($groupIds)) {
        $rawStats = DB::table('telegram_messages')
            ->whereIn('message_group_id', $groupIds)
            ->select(
                'message_group_id',
                DB::raw('COUNT(*) as total_messages'),
                DB::raw('MIN(sent_at) as started_at'),
                DB::raw('MAX(sent_at) as ended_at')
            )
            ->groupBy('message_group_id')
            ->get()
            ->keyBy('message_group_id');

        $groupTexts = DB::table('message_groups')
            ->whereIn('id', $groupIds)
            ->pluck('message_text', 'id');

        $textStats = $rawStats->map(function ($row, $gid) use ($groupTexts) {
            return (object) [
                'message_group_id' => $gid,
                'total_messages' => (int) $row->total_messages,
                'distinct_texts' => 1,
                'sample_text' => $groupTexts[$gid] ?? null,
                'started_at' => $row->started_at,
                'ended_at' => $row->ended_at,
            ];
        });
    }

    // Peer + status counts per group
    $peerStatusRaw = collect();
    if (!empty($groupIds)) {
        $peerStatusRaw = DB::table('telegram_messages')
            ->whereIn('message_group_id', $groupIds)
            ->whereIn('status', ['pending', 'scheduled', 'sent', 'canceled', 'failed'])
            ->select('message_group_id', 'peer', 'status', DB::raw('COUNT(*) as cnt'))
            ->groupBy('message_group_id', 'peer', 'status')
            ->get();
    }

    $peerStatusByGroup = [];
    $groupTotals = [];
    foreach ($peerStatusRaw as $row) {
        $gid = $row->message_group_id;
        $peer = $row->peer;
        $statusKey = $row->status;
        $peerStatusByGroup[$gid][$peer][$statusKey] = $row->cnt;
        $groupTotals[$gid][$statusKey] = ($groupTotals[$gid][$statusKey] ?? 0) + $row->cnt;
    }

    // Totals for header (respects same phoneSub)
    $messageGroupsTotal = MessageGroup::whereIn('user_phone_id', $phoneSub)->count();

    $telegramMessagesTotal = DB::table('telegram_messages')
        ->whereIn('message_group_id', function ($q) use ($phoneSub) {
            $q->select('id')
                ->from('message_groups')
                ->whereIn('user_phone_id', $phoneSub);
        })
        ->count();

    // recent messages (optional)
    $recentMessagesByGroup = [];
    if (!empty($groupIds)) {
        $recentRows = DB::table('telegram_messages')
            ->whereIn('message_group_id', $groupIds)
            ->orderByDesc('sent_at')
            ->get()
            ->groupBy('message_group_id');

        foreach ($recentRows as $gid => $rows) {
            $recentMessagesByGroup[$gid] = $rows->take(10);
        }
    }

    return view('departments.operations', compact(
        'department',
        'messageGroups',
        'textStats',
        'peerStatusByGroup',
        'groupTotals',
        'recentMessagesByGroup',
        'messageGroupsTotal',
        'telegramMessagesTotal',
        'q',
        'status',
        'from',
        'to',
        'users',
        'selectedUserId',
        'isSuperadmin',
        'departments',
        'filterDeptId'
    ));
}

}
