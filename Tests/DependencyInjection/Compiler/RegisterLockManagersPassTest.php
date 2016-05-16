<?php

namespace Matthimatiker\CommandLockingBundle\Tests\DependencyInjection\Compiler;

use Matthimatiker\CommandLockingBundle\DependencyInjection\Compiler\RegisterLockManagersPass;
use Matthimatiker\CommandLockingBundle\EventListener\CommandLockingListener;
use Matthimatiker\CommandLockingBundle\Locking\NullLockManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

class RegisterLockManagersPassTest extends \PHPUnit_Framework_TestCase
{
    public function testRegistersTaggedLockManager()
    {
        $listener = $this->getCompiledContainer()->get(RegisterLockManagersPass::LOCKING_LISTENER_SERVICE_ID);

        $this->assertTrue($listener->hasLockManager('null'));
    }

    public function testRegistersLockManagerThatIsTaggedMultipleTimes()
    {
        $listener = $this->getCompiledContainer()->get(RegisterLockManagersPass::LOCKING_LISTENER_SERVICE_ID);

        $this->assertTrue($listener->hasLockManager('first'));
        $this->assertTrue($listener->hasLockManager('second'));
    }

    public function testThrowsExceptionIfAliasIsMissing()
    {
        $container = $this->createContainer();
        $container->getDefinition('matthimatiker_command_locking.console.lock_manager.multiple_tags')
            ->addTag(RegisterLockManagersPass::LOCK_MANAGER_TAG);

        $this->setExpectedException(InvalidArgumentException::class);
        $container->compile();
    }

    /**
     * Create a test container and compiles it.
     *
     * @return ContainerBuilder
     */
    private function getCompiledContainer()
    {
        $container = $this->createContainer();
        $container->compile();
        return $container;
    }

    /**
     * Creates a container that contains the definition for the locking listener
     * and several tagged lock managers.
     *
     * The container must be compiled, otherwise the pass is not called. The definitions
     * can be changed before compilation.
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
        return $container;
    }
}
