<?php

namespace Matthimatiker\CommandLockingBundle\Tests\Functional;

use Matthimatiker\CommandLockingBundle\DependencyInjection\Compiler\RegisterLockManagersPass;
use Matthimatiker\CommandLockingBundle\EventListener\CommandLockingListener;
use Matthimatiker\CommandLockingBundle\MatthimatikerCommandLockingBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Tests the service configuration.
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    const DEFAULT_LOCK_MANAGER_PARAMETER = 'matthimatiker_command_locking.console.default_lock_manager_alias';

    public function testHasListener()
    {
        $container = $this->createContainer();

        $this->assertTrue(
            $container->has(RegisterLockManagersPass::LOCKING_LISTENER_SERVICE_ID),
            'Service ' . RegisterLockManagersPass::LOCKING_LISTENER_SERVICE_ID . ' is missing.'
        );
        $this->assertInstanceOf(
            CommandLockingListener::class,
            $container->get(RegisterLockManagersPass::LOCKING_LISTENER_SERVICE_ID)
        );
    }

    public function testDefaultLockManagerIsAvailable()
    {
        $container = $this->createContainer();
        $defaultLockManagerAlias = $container->getParameter(self::DEFAULT_LOCK_MANAGER_PARAMETER);
        /* @var $listener CommandLockingListener */
        $listener = $container->get(RegisterLockManagersPass::LOCKING_LISTENER_SERVICE_ID);

        $this->assertTrue(
            $listener->hasLockManager($defaultLockManagerAlias),
            'Listener does not support the default lock manager "' . $defaultLockManagerAlias . '".'
        );
    }

    /**
     * @return ContainerInterface
     */
    private function createContainer()
    {
        $container = new ContainerBuilder();
        // Simulate a cache directory. The directory itself should not be used in this test case.
        $container->setParameter('kernel.cache_dir', __DIR__);
        $bundle = new MatthimatikerCommandLockingBundle();
        $bundle->getContainerExtension()->load(array(), $container);
        $bundle->build($container);
        $container->compile();
        return $container;
    }
}
