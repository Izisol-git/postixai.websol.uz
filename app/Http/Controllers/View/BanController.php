<?php

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class BanController extends Controller
{
    public function banUnban(Request $request)
    {
        $authUser = $request->user();
        $user=User::find($request->input('user_id'));
            if($authUser->role->name !=='superadmin' || $authUser->department_id !== $user->department_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to ban/unban this user.'
                ], 403);
            }
            


        

        // Return JSON response



        return response()->json([
            'success' => true,
            'is_banned' => $user->is_banned,
            'message' => $user->is_banned ? 'User has been banned.' : 'User has been unbanned.'
        ]);
    }
}
