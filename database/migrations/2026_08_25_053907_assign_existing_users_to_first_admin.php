<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $ownerId = DB::table('users')
            ->where('role', 'admin')
            ->orderBy('id')
            ->value('id');

        if ($ownerId) {
            DB::table('users')
                ->whereNull('owner_id')
                ->update(['owner_id' => $ownerId]);
        }
    }

    public function down(): void
    {
        DB::table('users')->update(['owner_id' => null]);
    }
};
