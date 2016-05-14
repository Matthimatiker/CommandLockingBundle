<?php

namespace Matthimatiker\CommandLockingBundle\Locking;

/**
 * Defines how locks are obtained and released.
 *
 * Cannot hold 2 locks with the same name at the same time:
 *
 *     $manager->lock('first'); // returns true
 *     $manager->lock('first'); // returns false as 'first' is locked
 *
 * Hold multiple locks:
 *
 *     $manager->lock('first'); // returns true
 *     $manager->lock('second'); // returns true
 *
 * Renew lock after it was released:
 *
 *     $manager->lock('first'); // returns true
 *     $manager->release('first');
 *     $manager->lock('first'); // returns true
 */
interface LockManagerInterface
{
    /**
     * Obtains a lock for the provided name.
     *
     * The lock must be released before it can be obtained again.
     *
     * @param string $name
     * @return boolean True if the lock was obtained, false otherwise.
     */
    public function lock($name);

    /**
     * Releases the lock with the provided name.
     *
     * If the lock does not exist, then this method will do nothing.
     *
     * @param string $name
     */
    public function release($name);
}
