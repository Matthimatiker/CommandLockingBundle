<?php

namespace Matthimatiker\CommandLockingBundle\Tests\Locking;

use Matthimatiker\CommandLockingBundle\Locking\LockManagerInterface;
use Matthimatiker\CommandLockingBundle\Locking\NullLockManager;

class NullLockManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * System under test.
     *
     * @var NullLockManager
     */
    private $lockManager = null;

    /**
     * Initializes the test environment.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->lockManager = new NullLockManager();
    }

    /**
     * Cleans up the test environment.
     */
    protected function tearDown()
    {
        $this->lockManager = null;
        parent::tearDown();
    }

    public function testImplementsInterface()
    {
        $this->assertInstanceOf(LockManagerInterface::class, $this->lockManager);
    }
}
