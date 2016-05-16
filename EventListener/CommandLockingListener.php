<?php

namespace Matthimatiker\CommandLockingBundle\EventListener;

use Matthimatiker\CommandLockingBundle\Locking\LockManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Hooks into the life-cycle of console commands and manages locks if requested.
 *
 * @see http://symfony.com/doc/current/components/console/events.html
 * @see http://php-and-symfony.matthiasnoback.nl/2013/11/symfony2-add-a-global-option-to-console-commands-and-generate-pid-file/
 */
class CommandLockingListener implements EventSubscriberInterface
{
    /**
     * Alias of the lock manager that is used if the manager is not explicitly
     * specified via command line option.
     *
     * @var string
     */
    private $defaultLockManagerAlias = null;

    /**
     * The registered lock managers.
     *
     * The alias of the lock manager is used as key.
     *
     * @var array<string, LockManagerInterface>
     */
    private $lockManagers = array();

    /**
     * Contains all commands that are locked.
     *
     * @var \SplObjectStorage
     */
    private $lockedCommands = null;

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
     * @param string $defaultLockManagerAlias
     */
    public function __construct($defaultLockManagerAlias)
    {
        $this->lockedCommands = new \SplObjectStorage();
        $this->defaultLockManagerAlias = $defaultLockManagerAlias;
    }

    /**
     * Registers a lock manager and uses the given alias to reference it.
     *
     * If necessary, the same lock manager can be registered multiple times with different aliases.
     *
     * @param string $alias
     * @param LockManagerInterface $lockManager
     */
    public function registerLockManager($alias, LockManagerInterface $lockManager)
    {
        $this->lockManagers[$alias] = $lockManager;
    }

    /**
     * Checks if this listener has a lock manager with the provided alias.
     *
     * @param string $alias
     * @return boolean
     */
    public function hasLockManager($alias)
    {
        return isset($this->lockManagers[$alias]);
    }

    /**
     * Called before a command runs.
     *
     * @param ConsoleCommandEvent $event
     */
    public function beforeCommand(ConsoleCommandEvent $event)
    {
        $this->registerLockOption($event->getCommand());
        // Bind the definition to ensure that we can read the lock option from the input.
        $event->getInput()->bind($event->getCommand()->getDefinition());
        if (!$this->isLockingRequested($event->getInput())) {
            // No locking required.
            return;
        }
        $lockManagerAlias = $event->getInput()->getOption('lock');
        if (!$this->lock($event->getCommand(), $lockManagerAlias)) {
            $event->disableCommand();
            $message = '<comment>Cannot get lock from lock manager "%s", execution of command "%s" skipped.</comment>';
            $event->getOutput()->writeln(sprintf($message, $lockManagerAlias, $event->getCommand()->getName()));
        }
    }

    /**
     * Called when a command terminates, either with success or via exception.
     *
     * @param ConsoleTerminateEvent $event
     */
    public function afterCommand(ConsoleTerminateEvent $event)
    {
        if ($this->isLocked($event->getCommand())) {
            $this->releaseLock($event->getCommand());
        }
    }

    /**
     * Checks if locking was requested for the current command.
     *
     * @param InputInterface $input
     * @return boolean
     */
    private function isLockingRequested(InputInterface $input)
    {
        return $input->hasParameterOption('--lock') !== false;
    }

    /**
     * Checks if the given command is locked.
     *
     * @param Command $command
     * @return boolean
     */
    private function isLocked(Command $command)
    {
        return $this->lockedCommands->contains($command);
    }

    /**
     * @param Command $command
     * @param string $lockManagerAlias
     * @return boolean True if the lock was obtained.
     */
    private function lock(Command $command, $lockManagerAlias)
    {
        if (!$this->getLockManager($lockManagerAlias)->lock($this->getLockNameFor($command))) {
            return false;
        }
        $this->lockedCommands->attach($command, $lockManagerAlias);
        return true;
    }

    /**
     * Releases the active lock for the given command.
     *
     * @param Command $command
     */
    private function releaseLock(Command $command)
    {
        $lockManagerAlias = $this->lockedCommands->offsetGet($command);
        $this->getLockManager($lockManagerAlias)->release($this->getLockNameFor($command));
        $this->lockedCommands->detach($command);
    }

    /**
     * @param string $alias
     * @return LockManagerInterface
     * @throws \InvalidArgumentException If the requested lock manager does not exist.
     */
    private function getLockManager($alias)
    {
        if (!$this->hasLockManager($alias)) {
            throw new \InvalidArgumentException('No lock manager was registered for alias "' . $alias . '".');
        }
        return $this->lockManagers[$alias];
    }

    /**
     * Returns the lock name for the given command.
     *
     * @param Command $command
     * @return string
     */
    private function getLockNameFor(Command $command)
    {
        return (new \ReflectionClass($this))->getShortName() . '-' . $command->getName();
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
            'Request a lock to ensure that the command does not run in parallel. ' .
            'To use a specific lock manager pass its alias.',
            $this->defaultLockManagerAlias
        );
        // Register the option at application level to ensure that it shows up in the help.
        $command->getApplication()->getDefinition()->addOption($lockOption);
        // Register at command level to ensure that the option is parsed properly.
        $command->getDefinition()->addOption($lockOption);
    }
}
