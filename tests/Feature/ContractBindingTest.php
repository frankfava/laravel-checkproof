<?php

namespace Tests\Feature;

use App\Actions\Users\CreateNewUser;
use App\Contracts\CreatesNewUser;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ContractBindingTest extends TestCase
{
    #[Test]
    public function test_create_new_user_contract_is_bound_to_concrete_implementation(): void
    {
        $resolved = app(CreatesNewUser::class);

        $this->assertInstanceOf(CreateNewUser::class, $resolved);
    }
}
