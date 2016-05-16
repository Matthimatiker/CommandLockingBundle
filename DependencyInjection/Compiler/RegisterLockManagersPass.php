<?php

namespace Matthimatiker\CommandLockingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Registers all tagged LockManagers.
 */
class RegisterLockManagersPass implements CompilerPassInterface
{
    /**
     * ID of the locking listener service.
     */
    const LOCKING_LISTENER_SERVICE_ID = 'matthimatiker_command_locking.console.locking_listener';

    /**
     * Name of the tag that is used to register lock managers.
     */
    const LOCK_MANAGER_TAG = 'matthimatiker_command_locking.console.lock_manager';

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        // TODO: Implement process() method.
    }
}
