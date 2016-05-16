<?php

namespace Matthimatiker\CommandLockingBundle\Locking;

/**
 * Null implementation of a lock manager. Simulates successful locking.
 */
class NullLockManager implements LockManagerInterface
{
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
        // TODO: Implement lock() method.
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
        // TODO: Implement release() method.
    }
}
