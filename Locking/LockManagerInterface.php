<?php

namespace Matthimatiker\CommandLockingBundle\Locking;

interface LockManagerInterface
{
    /**
     * @param string $name
     * @return boolean True if the lock was obtained, false otherwise.
     */
    public function lock($name);

    /**
     * @param string $name
     */
    public function release($name);
}
