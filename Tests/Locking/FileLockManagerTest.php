<?php

namespace Matthimatiker\CommandLockingBundle\Tests\Locking;

use Matthimatiker\CommandLockingBundle\Locking\FileLockManager;
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

    public function testCanGetLock()
    {

    }

    public function testCannotGetSameLockTwice()
    {

    }

    public function testCanGetLockAfterRelease()
    {

    }

    public function testCanGetDifferentLocks()
    {

    }

    public function testCanCallReleaseEvenIfLockDoesNotExist()
    {

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
