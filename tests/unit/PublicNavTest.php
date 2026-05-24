<?php

declare(strict_types=1);

use App\Libraries\PublicNav;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class PublicNavTest extends CIUnitTestCase
{
    public function testProjectsListMenuActive(): void
    {
        $this->assertTrue(PublicNav::isActive('projects', 'projects', '', ''));
        $this->assertTrue(PublicNav::isActive('projets-programme', 'projects', '', ''));
        $this->assertFalse(PublicNav::isActive('projects', 'home', '', ''));
    }

    public function testPositionsListMenuActive(): void
    {
        $this->assertTrue(PublicNav::isActive('positions', 'positions', '', ''));
        $this->assertTrue(PublicNav::isActive('positions-program', 'positions', '', ''));
        $this->assertFalse(PublicNav::isActive('positions', 'projects', '', ''));
    }

    public function testNoneMatchKeyNeverActive(): void
    {
        $this->assertFalse(PublicNav::isActive('none', 'projects', 'foo', ''));
        $this->assertFalse(PublicNav::isActive('', 'projects', 'foo', ''));
    }
}
