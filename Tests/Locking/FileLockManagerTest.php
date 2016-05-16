<?php

namespace Matthimatiker\CommandLockingBundle\Tests\Locking;

use Matthimatiker\CommandLockingBundle\Locking\FileLockManager;
use Matthimatiker\CommandLockingBundle\Locking\LockManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

class FileLockManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * System under test.
     *
     * @var FileLockManager
     */
    private $lockManager = null;

    /**
     * Initializes the test environment.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->createLockDirectory();
        $this->lockManager = new FileLockManager($this->getLockDirectory());
    }

    /**
     * Cleans up the test environment.
     */
    protected function tearDown()
    {
        $this->lockManager = null;
        $this->removeLockDirectory();
        parent::tearDown();
    }

    public function testImplementsInterface()
    {
        $this->assertInstanceOf(LockManagerInterface::class, $this->lockManager);
    }

    public function testCanGetLock()
    {
        $this->assertTrue($this->lockManager->lock('test'));
    }

    public function testCannotGetSameLockTwice()
    {
        $this->lockManager->lock('test');
        $this->assertFalse($this->lockManager->lock('test'));
    }

    public function testCanGetLockAfterRelease()
    {
        $this->lockManager->lock('test');
        $this->lockManager->release('test');
        $this->assertTrue($this->lockManager->lock('test'));
    }

    public function testCanGetDifferentLocks()
    {
        $this->assertTrue($this->lockManager->lock('first'));
        $this->assertTrue($this->lockManager->lock('second'));
    }

    public function testCanCallReleaseEvenIfLockDoesNotExist()
    {
        $this->setExpectedException(null);
        $this->lockManager->release('missing');
    }

    public function testDifferentListenersCannotHoldSameLock()
    {
        $this->lockManager->lock('test');

        $anotherLockManager = new FileLockManager($this->getLockDirectory());
        $this->assertFalse($anotherLockManager->lock('test'));
    }

    /**
     * Creates a fresh lock directory.
     */
    private function createLockDirectory()
    {
        $this->removeLockDirectory();
        (new Filesystem())->mkdir($this->getLockDirectory());
    }

    /**
     * Removes the lock directory if it exists.
     */
    private function removeLockDirectory()
    {
        (new Filesystem())->remove($this->getLockDirectory());
    }

    /**
     * Returns the path to the lock directory.
     *
     * @return string
     */
    private function getLockDirectory()
    {
        return __DIR__ . '/_files/FileLockManager';
    }
}
