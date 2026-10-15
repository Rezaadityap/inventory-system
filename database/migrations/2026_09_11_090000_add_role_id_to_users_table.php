<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('photo')->constrained('roles')->nullOnDelete();
        });

        DB::table('model_has_roles')
            ->where('model_type', User::class)
            ->get(['model_id', 'role_id'])
            ->each(function ($modelHasRole) {
                DB::table('users')
                    ->where('id', $modelHasRole->model_id)
                    ->update(['role_id' => $modelHasRole->role_id]);
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });
    }
};
