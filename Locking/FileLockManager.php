<?php

namespace Matthimatiker\CommandLockingBundle\Locking;

use Symfony\Component\Filesystem\LockHandler;

/**
 * Uses files to create locks.
 *
 * @see \Symfony\Component\Filesystem\LockHandler
 * @see http://symfony.com/doc/current/components/filesystem/lock_handler.html
 */
class FileLockManager implements LockManagerInterface
{
    /**
     * The directory that contains the lock files.
     *
     * @var string
     */
    private $lockDirectory = null;

    /**
     * Contains the locks that are currently active.
     *
     * The name of the lock is used as key.
     *
     * @var array<string, LockHandler>
     */
    private $activeLocksByName = array();

    /**
     * @param string $lockDirectory Path to the directory that contains the locks.
     */
    public function __construct($lockDirectory)
    {
        $this->lockDirectory = $lockDirectory;
    }

    /**
     * Obtains a lock for the provided name.
     *
     * The lock must be released before it can be obtained again.
     *
     * @param string $name
     * @return boolean True if the lock was obtained, false otherwise.
     */
    public function lock($name)
    {
        if ($this->isLocked($name)) {
            return false;
        }
        $lock = new LockHandler($name . '.lock', $this->lockDirectory);
        if ($lock->lock()) {
            // Obtained lock.
            $this->activeLocksByName[$name] = $lock;
            return true;
        }
        return false;
    }

    /**
     * Releases the lock with the provided name.
     *
     * If the lock does not exist, then this method will do nothing.
     *
     * @param string $name
     */
    public function release($name)
    {
        if (!$this->isLocked($name)) {
            return;
        }
        /* @var $lock LockHandler */
        $lock = $this->activeLocksByName[$name];
        $lock->release();
        unset($this->activeLocksByName[$name]);
    }

    /**
     * Checks if a lock with the given name is active.
     *
     * @param string $name
     * @return boolean
     */
    private function isLocked($name)
    {
        return isset($this->activeLocksByName[$name]);
    }
}
