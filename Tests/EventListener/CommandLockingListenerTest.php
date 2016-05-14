<?php

namespace Matthimatiker\CommandLockingBundle\Tests\EventListener;

use Matthimatiker\CommandLockingBundle\EventListener\CommandLockingListener;
use Webfactory\Constraint\IsEventSubscriberConstraint;

class CommandLockingListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * System under test.
     *
     * @var CommandLockingListener
     */
    private $listener = null;

    /**
     * Initializes the test environment.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->listener = new CommandLockingListener('mock');
    }

    /**
     * Cleans up the test environment.
     */
    protected function tearDown()
    {
        $this->listener = null;
        parent::tearDown();
    }

    public function testIsValidEventSubscriber()
    {
        $this->assertThat($this->listener, new IsEventSubscriberConstraint());
    }

    public function testAddsLockOption()
    {

    }

    public function testDoesNotLockIfNotRequested()
    {

    }

    public function testLocksIfRequested()
    {

    }

    public function testUsesExplicitlyRequestedLockManager()
    {

    }

    public function testThrowsExceptionIfRequestedLockManagerIsNotAvailable()
    {

    }

    public function testRunsCommandIfNoLockIsRequested()
    {

    }

    public function testDoesNotRunCommandIfLockingFails()
    {

    }

    public function testRunsCommandIfLockingSucceeds()
    {

    }

    public function testReleasesLockWhenLockedCommandTerminates()
    {

    }

    public function testDoesNotReleaseWhenCommandWasNotLocked()
    {

    }

    public function testDoesNotInterfereWithOptionsOfCommand()
    {

    }
}
