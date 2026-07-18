<?php

namespace Modules\Identity\Tests\Contract;

use Tests\TestCase;
use Modules\Identity\Contracts\IdentityContract;
use Modules\Identity\Facades\Identity;
use Modules\Identity\Services\IdentityManager;

class IdentityContractBindingTest extends TestCase
{
    public function test_identity_contract_is_bound_to_identity_manager(): void
    {
        $this->assertInstanceOf(IdentityManager::class, app(IdentityContract::class));
    }

    public function test_facade_resolves_identity_contract(): void
    {
        $this->assertSame(app(IdentityContract::class), Identity::getFacadeRoot());
    }
}
