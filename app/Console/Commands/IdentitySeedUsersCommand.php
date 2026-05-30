<?php

namespace Modules\Identity\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Modules\Identity\Contracts\UserRepositoryInterface;
use Modules\Identity\Enums\UserStatus;

class IdentitySeedUsersCommand extends Command
{
    protected $signature = 'identity:seed-users
                            {--count=100 : Number of users to create}
                            {--status=random : User status (active|inactive|suspended|pending|random)}';

    protected $description = 'Seed fake users for identity module (shared/owned aware)';

    public function __construct(protected UserRepositoryInterface $repository)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $count = max(1, (int) $this->option('count'));
        $statusOption = strtolower((string) $this->option('status'));

        $allowedStatuses = UserStatus::values();
        if ($statusOption !== 'random' && !in_array($statusOption, $allowedStatuses, true)) {
            $this->error('Invalid --status. Allowed: random|' . implode('|', $allowedStatuses));
            return self::INVALID;
        }

        $created = 0;
        $progress = $this->output->createProgressBar($count);
        $progress->start();

        for ($i = 0; $i < $count; $i++) {
            $firstName = fake()->firstName();
            $lastName = fake()->lastName();
            $status = $statusOption === 'random'
                ? fake()->randomElement($allowedStatuses)
                : $statusOption;

            $this->repository->create([
                'name' => trim($firstName . ' ' . $lastName),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => fake()->unique()->safeEmail(),
                'phone' => fake()->optional()->numerify('##########'),
                'username' => config('identity.features.username', true)
                    ? Str::lower(fake()->unique()->bothify($firstName . '.'.$lastName.'##'))
                    : null,
                'status' => $status,
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'timezone' => config('identity.defaults.timezone', 'UTC'),
                'locale' => config('identity.defaults.locale', 'en'),
            ]);

            $created++;
            $progress->advance();
        }

        $progress->finish();
        $this->newLine(2);
        $this->info("Seeded {$created} users successfully.");

        return self::SUCCESS;
    }
}
