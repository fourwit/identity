<?php

namespace Modules\Identity\Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Support\BootstrapsIdentitySchema;

class IdentityDoctorCommandTest extends TestCase
{
    use RefreshDatabase;
    use BootstrapsIdentitySchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootstrapIdentitySchemaForTests();
    }

    public function test_identity_doctor_passes_for_shared_mode_with_required_columns(): void
    {
        config(['identity.mode' => 'shared']);

        $this->artisan('identity:doctor')
            ->expectsOutputToContain("Host compatibility OK for table 'users'.")
            ->assertExitCode(0);
    }
}
