<?php

namespace App\Http\Controllers;
use App\Models\User;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function me(Request $request)
    {
        $user = auth()->user();
        return response()->json($request->user()->load('role', 'branch'));
    }

public function index(Request $request)
{
    $user = $request->user();

    // Tentukan role yang boleh diakses berdasarkan role user saat ini
    if ($user->id_role === 1) {
        // Role 1 melihat user dengan role 2 (misal: manager cabang)
        $targetRole = 2;

        $users = User::with('role', 'branch')
            ->where('id_role', $targetRole)
            ->get();

        return response()->json($users);

    } elseif ($user->id_role === 2) {
        // Role 2 melihat user dengan role 3 (misal: kasir di cabang yang sama)
        $targetRole = 3;

        $users = User::with('role', 'branch')
            ->where('id_role', $targetRole)
            ->where('id_branch', $user->id_branch)
            ->get();

        return response()->json($users);

    } else {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
}
}



