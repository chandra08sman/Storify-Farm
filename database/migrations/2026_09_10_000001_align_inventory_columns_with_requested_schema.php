<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'user_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('owner_id')->constrained('users');
            });
        }

        if (! Schema::hasColumn('batches', 'user_id')) {
            Schema::table('batches', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('received_at')->constrained('users');
            });
        }

        DB::statement('UPDATE products SET user_id = owner_id WHERE user_id IS NULL');
        DB::statement('UPDATE batches SET user_id = products.owner_id FROM products WHERE batches.product_id = products.id AND batches.user_id IS NULL');

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('capacity', 12, 2)->change();
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->decimal('quantity', 12, 2)->change();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('quantity', 12, 2)->change();

            if (! Schema::hasColumn('transactions', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'updated_at')) {
                $table->dropColumn('updated_at');
            }

            $table->unsignedBigInteger('quantity')->change();
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->unsignedBigInteger('quantity')->change();
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('capacity')->change();
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};