<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'theme' => ['required', 'in:light,dark,system'],
            'accent' => ['required', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'current_password' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $allowedAccents = ['green', 'blue', 'orange', 'purple'];
        $accentValue = strtolower(trim((string) $data['accent']));
        if (! in_array($accentValue, $allowedAccents, true) && ! preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $accentValue)) {
            return response()->json(['ok' => false, 'message' => 'Warna aksen tidak valid.'], 422);
        }

        $user = $request->user();
        $user->name = $data['name'];
        $user->theme = $data['theme'];
        $user->accent = $accentValue;

        if (! empty($data['password'])) {
            if (empty($data['current_password']) || ! Hash::check($data['current_password'], $user->password)) {
                return response()->json(['ok' => false, 'message' => 'Password saat ini salah.'], 422);
            }
            $user->password = Hash::make($data['password']);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();

        if (! empty($data['password'])) {
            AppNotification::create([
                'owner_id' => $user->workspaceOwnerId(),
                'user_id' => $user->id,
                'type' => 'security',
                'title' => 'Password diubah',
                'message' => $user->name.' mengubah password akunnya.',
            ]);
            $user->notify(new PasswordChangedNotification());
        }

        return response()->json([
            'ok' => true,
            'message' => 'Pengaturan tersimpan.',
            'user' => $user->fresh()->toPublicArray(),
        ]);
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
        ]);
        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            return response()->json(['ok' => false, 'message' => 'Password saat ini salah.'], 422);
        }

        DB::transaction(function () use ($user) {
            $memberIds = User::where('owner_id', $user->id)->pluck('id');
            $productIds = Product::withTrashed()->where('owner_id', $user->id)->pluck('id');

            Transaction::whereIn('user_id', $memberIds->push($user->id))->delete();
            Transaction::whereIn('product_id', $productIds)->delete();
            Batch::whereIn('product_id', $productIds)->delete();
            Product::withTrashed()->whereIn('id', $productIds)->forceDelete();
            AppNotification::where('owner_id', $user->id)->delete();
            User::whereIn('id', $memberIds)->delete();

            $user->delete();
        });

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['ok' => true, 'message' => 'Akun dan data workspace berhasil dihapus.']);
    }
}
