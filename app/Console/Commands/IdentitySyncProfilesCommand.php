<?php

namespace Modules\Identity\Console\Commands;

use Illuminate\Console\Command;
use Modules\Identity\Models\IdentityProfile;
use Modules\Identity\Support\IdentityConfig;

class IdentitySyncProfilesCommand extends Command
{
    protected $signature = 'identity:sync-profiles {--chunk=500}';
    protected $description = 'Backfill and sync identity_profiles from host users table in shared mode';

    public function handle(): int
    {
        if (IdentityConfig::isOwnedMode()) {
            $this->info('IDENTITY_MODE=owned. Sync is not required.');
            return self::SUCCESS;
        }

        $userModelClass = IdentityConfig::userModelClass();
        $chunkSize = (int) $this->option('chunk');
        $synced = 0;

        $this->info('Syncing identity profiles...');

        $userModelClass::query()
            ->orderBy('id')
            ->chunk($chunkSize, function ($users) use (&$synced) {
                foreach ($users as $user) {
                    $nameParts = preg_split('/\s+/', trim((string) ($user->name ?? '')), 2);
                    $derivedFirst = $nameParts[0] ?? null;
                    $derivedLast = $nameParts[1] ?? null;

                    IdentityProfile::query()->updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'first_name' => $user->first_name ?? $derivedFirst,
                            'last_name' => $user->last_name ?? $derivedLast,
                            'phone' => $user->phone ?? null,
                            'username' => $user->username ?? null,
                            'avatar_id' => $user->avatar_id ?? null,
                            'status' => $user->status?->value ?? $user->status ?? config('identity.user.default_status', 'active'),
                            'timezone' => $user->timezone ?? config('identity.defaults.timezone', 'UTC'),
                            'locale' => $user->locale ?? config('identity.defaults.locale', 'en'),
                            'two_factor_enabled' => (bool) ($user->two_factor_enabled ?? false),
                            'two_factor_secret' => $user->two_factor_secret ?? null,
                            'metadata' => $user->metadata ?? null,
                        ]
                    );
                    $synced++;
                }
            });

        $this->info("Identity profiles synced: {$synced}");
        return self::SUCCESS;
    }
}
