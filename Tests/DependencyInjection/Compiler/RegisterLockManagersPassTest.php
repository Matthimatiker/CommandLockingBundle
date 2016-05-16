<?php

namespace Matthimatiker\CommandLockingBundle\Tests\DependencyInjection\Compiler;

use Matthimatiker\CommandLockingBundle\DependencyInjection\Compiler\RegisterLockManagersPass;
use Matthimatiker\CommandLockingBundle\EventListener\CommandLockingListener;
use Matthimatiker\CommandLockingBundle\Locking\NullLockManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterLockManagersPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Initializes the test environment.
     */
    protected function setUp()
    {
        parent::setUp();

    }

    /**
     * Cleans up the test environment.
     */
    protected function tearDown()
    {

        parent::tearDown();
    }

    public function testRegistersTaggedLockManager()
    {
        $listener = $this->createContainer()->get(RegisterLockManagersPass::LOCKING_LISTENER_SERVICE_ID);

        $this->assertTrue($listener->hasLockManager('null'));
    }

    public function testRegistersLockManagerThatIsTaggedMultipleTimes()
    {
        $listener = $this->createContainer()->get(RegisterLockManagersPass::LOCKING_LISTENER_SERVICE_ID);

        $this->assertTrue($listener->hasLockManager('first'));
        $this->assertTrue($listener->hasLockManager('second'));
    }

    /**
     * Creates a compiled container.
     *
     * @return ContainerBuilder
     */
    private function createContainer()
    {
        $container = new ContainerBuilder();
        $container->setDefinition(
            RegisterLockManagersPass::LOCKING_LISTENER_SERVICE_ID,
            new Definition(CommandLockingListener::class, array('null'))
        );
        $container->setDefinition(
            'matthimatiker_command_locking.console.lock_manager.single_tag',
            (new Definition(NullLockManager::class))
                ->addTag(RegisterLockManagersPass::LOCK_MANAGER_TAG, array('alias' => 'null'))
        );
        $container->setDefinition(
            'matthimatiker_command_locking.console.lock_manager.multiple_tags',
            (new Definition(NullLockManager::class))
                ->addTag(RegisterLockManagersPass::LOCK_MANAGER_TAG, array('alias' => 'first'))
                ->addTag(RegisterLockManagersPass::LOCK_MANAGER_TAG, array('alias' => 'second'))
        );
        $container->addCompilerPass(new RegisterLockManagersPass());
        $container->compile();
        return $container;
    }
}
