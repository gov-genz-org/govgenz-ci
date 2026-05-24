<?php

declare(strict_types=1);

use App\Libraries\StaffAuthPolicy;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class StaffAuthPolicyTest extends CIUnitTestCase
{
    public function testLoginPasswordMinLengthInTestingEnvironmentIsSix(): void
    {
        $this->assertSame('testing', ENVIRONMENT);
        $this->assertSame(6, StaffAuthPolicy::loginPasswordMinLength());
    }
}
