<?php

namespace Matthimatiker\CommandLockingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

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
        $listener = $container->findDefinition(self::LOCKING_LISTENER_SERVICE_ID);
        foreach ($container->findTaggedServiceIds(self::LOCK_MANAGER_TAG) as $serviceId => $tags) {
            /* @var $serviceId string */
            /* @var $tags array<string, string>[] */
            foreach ($tags as $tag) {
                /* @var $tag array<string, string> */
                if (!isset($tag['alias'])) {
                    $message = '%s tag for service %s must also contain an alias attribute that '
                             . 'is used to reference the lock manager.';
                    throw new InvalidArgumentException(sprintf($message, self::LOCK_MANAGER_TAG, $serviceId));
                }
                $listener->addMethodCall('registerLockManager', array($tag['alias'], new Reference($serviceId)));
            }
        }
    }
}
