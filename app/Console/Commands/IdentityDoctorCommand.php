<?php

namespace Modules\Identity\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Modules\Identity\Support\IdentityConfig;
use Throwable;

class IdentityDoctorCommand extends Command
{
    protected $signature = 'identity:doctor';
    protected $description = 'Validate host app compatibility for fourwit/identity shared mode';

    public function handle(): int
    {
        if (IdentityConfig::isOwnedMode()) {
            $this->info('IDENTITY_MODE=owned. Host compatibility checks are skipped.');
            return self::SUCCESS;
        }

        $usersTable = config('identity.tables.users', 'users');
        $missing = [];

        try {
            if (!Schema::hasTable($usersTable)) {
                $this->error("Missing users table: {$usersTable}");
                $this->line("Hint: set IDENTITY_USERS_TABLE correctly or create table '{$usersTable}'.");
                return self::FAILURE;
            }
        } catch (Throwable $e) {
            $this->error('Database connection check failed.');
            $this->line('Hint: verify DB credentials/host in .env and ensure DB server is reachable.');
            $this->line('Details: '.$e->getMessage());
            return self::FAILURE;
        }

        $requiredColumns = ['id', 'name', 'email', 'password'];
        foreach ($requiredColumns as $column) {
            try {
                $hasColumn = Schema::hasColumn($usersTable, $column);
            } catch (Throwable $e) {
                $this->error("Column check failed for '{$column}'.");
                $this->line('Details: '.$e->getMessage());
                return self::FAILURE;
            }

            if (!$hasColumn) {
                $missing[] = $column;
            }
        }

        if (!empty($missing)) {
            $this->error('Missing required columns on host users table: '.implode(', ', $missing));
            $this->line('Suggested migration snippet:');
            $this->line("Schema::table('{$usersTable}', function (Blueprint \$table) {");
            foreach ($missing as $column) {
                $type = $column === 'id' ? "id('id')" : "string('{$column}')";
                $this->line("    \$table->{$type};");
            }
            $this->line('});');
            return self::FAILURE;
        }

        $this->info("Host compatibility OK for table '{$usersTable}'.");
        $this->line('Required columns present: id, name, email, password');

        return self::SUCCESS;
    }
}
