<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $firstAdminId = DB::table('users')
            ->where('role', 'admin')
            ->orderBy('id')
            ->value('id');

        if (!$firstAdminId) {
            return;
        }

        DB::table('users')
            ->where('role', 'admin')
            ->update(['owner_id' => DB::raw('id')]);

        DB::table('users')
            ->where('role', '!=', 'admin')
            ->whereNull('owner_id')
            ->update(['owner_id' => $firstAdminId]);
    }

    public function down(): void
    {
    }
};
