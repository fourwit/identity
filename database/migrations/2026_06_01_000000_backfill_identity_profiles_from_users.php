<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $usersTable = config('identity.tables.users', 'users');
        $profilesTable = config('identity.tables.profiles', 'identity_profiles');

        if (!Schema::hasTable($usersTable) || !Schema::hasTable($profilesTable)) {
            return;
        }

        $mappableColumns = [
            'uuid',
            'first_name',
            'last_name',
            'username',
            'phone',
            'avatar_id',
            'status',
            'phone_verified_at',
            'timezone',
            'locale',
            'last_login_at',
            'last_login_ip',
            'remember_me',
            'two_factor_enabled',
            'two_factor_secret',
            'metadata',
            'deleted_at',
            'created_at',
            'updated_at',
        ];

        $userColumns = array_values(array_filter($mappableColumns, fn (string $column) => Schema::hasColumn($usersTable, $column)));
        if (empty($userColumns)) {
            return;
        }

        $rows = DB::table($usersTable)->select(array_merge(['id'], $userColumns))->get();

        foreach ($rows as $row) {
            $payload = ['user_id' => $row->id];
            foreach ($userColumns as $column) {
                $payload[$column] = $row->{$column};
            }

            DB::table($profilesTable)->updateOrInsert(
                ['user_id' => $row->id],
                $payload
            );
        }
    }

    public function down(): void
    {
        // No-op: backfill is intentionally non-reversible.
    }
};
