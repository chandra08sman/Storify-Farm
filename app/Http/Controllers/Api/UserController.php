<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\User;
use App\Notifications\PasswordResetNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,supervisor,petugas'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'owner_id' => $request->user()->id,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'theme' => 'light',
            'accent' => 'green',
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'User dibuat.',
            'user' => $user->toPublicArray(),
        ], 201);
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['ok' => false, 'message' => 'Tidak bisa menghapus akun sendiri.'], 422);
        }

        if ($user->owner_id !== $request->user()->id) {
            return response()->json(['ok' => false, 'message' => 'User bukan bagian dari akun Anda.'], 403);
        }

        $user->delete();

        return response()->json(['ok' => true, 'message' => 'User dihapus.']);
    }

    public function resetPassword(Request $request, User $user)
    {
        if ($user->owner_id !== $request->user()->id || $user->id === $request->user()->id) {
            return response()->json(['ok' => false, 'message' => 'User tidak dapat direset.'], 403);
        }

        $data = $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
        $user->update(['password' => Hash::make($data['password'])]);
        $user->notify(new PasswordResetNotification($request->user()->name));

        AppNotification::create([
            'owner_id' => $request->user()->id,
            'user_id' => $request->user()->id,
            'type' => 'security',
            'title' => 'Password user direset',
            'message' => $request->user()->name.' mereset password '.$user->name.'.',
        ]);

        return response()->json(['ok' => true, 'message' => 'Password user berhasil direset.']);
    }
}
