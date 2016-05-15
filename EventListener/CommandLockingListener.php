<?php

namespace Matthimatiker\CommandLockingBundle\EventListener;

use Matthimatiker\CommandLockingBundle\Locking\LockManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Hooks into the life-cycle of console commands and manages locks if requested.
 *
 * @see http://php-and-symfony.matthiasnoback.nl/2013/11/symfony2-add-a-global-option-to-console-commands-and-generate-pid-file/
 */
class CommandLockingListener implements EventSubscriberInterface
{
    /**
     * Name of the lock manager that is used if the manager is not explicitly
     * specified via command line option.
     *
     * @var string
     */
    private $defaultLockManagerName = null;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            ConsoleEvents::COMMAND => 'beforeCommand',
            ConsoleEvents::TERMINATE => 'afterCommand'
        );
    }

    /**
     * @param string $defaultLockManagerName
     */
    public function __construct($defaultLockManagerName)
    {
        $this->defaultLockManagerName = $defaultLockManagerName;
    }

    /**
     * @param string $name
     * @param LockManagerInterface $lockManager
     */
    public function registerLockManager($name, LockManagerInterface $lockManager)
    {

    }

    /**
     * Called before a command runs.
     *
     * @param ConsoleCommandEvent $event
     */
    public function beforeCommand(ConsoleCommandEvent $event)
    {
        $this->registerLockOption($event->getCommand());
    }

    /**
     * Called when a command terminates, either with success or via exception.
     *
     * @param ConsoleTerminateEvent $event
     */
    public function afterCommand(ConsoleTerminateEvent $event)
    {
    }

    /**
     * Registers the "--lock" option that is used to activate locking.
     *
     * @param Command $command
     */
    private function registerLockOption(Command $command)
    {
        $lockOption = new InputOption(
            '--lock',
            null,
            InputOption::VALUE_OPTIONAL,
            'Request a lock to ensure that the command does not run in parallel.',
            $this->defaultLockManagerName
        );
        // Register the option at application level to ensure that it shows up in the help.
        $command->getApplication()->getDefinition()->addOption($lockOption);
        // Register at command level to ensure that the option is parsed properly.
        $command->getDefinition()->addOption($lockOption);
    }
}
