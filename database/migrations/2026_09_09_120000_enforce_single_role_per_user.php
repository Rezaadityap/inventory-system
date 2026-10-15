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
        DB::table('model_has_roles')->where('model_type', '!=', User::class)->delete();

        DB::table('model_has_roles')
            ->whereNotIn('model_id', DB::table('users')->select('id'))
            ->delete();

        $duplicates = DB::table('model_has_roles')
            ->select('model_id', DB::raw('MIN(role_id) as kept_role_id'))
            ->groupBy('model_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('model_has_roles')
                ->where('model_id', $duplicate->model_id)
                ->where('role_id', '!=', $duplicate->kept_role_id)
                ->delete();
        }

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropIndex('model_has_roles_model_id_model_type_index');
            $table->unique('model_id', 'model_has_roles_model_id_unique');
            $table->foreign('model_id', 'model_has_roles_model_id_foreign')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });

        $this->forgetPermissionCache();
    }

    public function down(): void
    {
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropForeign('model_has_roles_model_id_foreign');
            $table->dropUnique('model_has_roles_model_id_unique');
            $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
        });

        $this->forgetPermissionCache();
    }

    private function forgetPermissionCache(): void
    {
        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }
};
