<?php

namespace Matthimatiker\CommandLockingBundle\EventListener;

use Matthimatiker\CommandLockingBundle\Locking\LockManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Hooks into the life-cycle of console commands and manages locks if requested.
 */
class CommandLockingListener implements EventSubscriberInterface
{
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
        // TODO: Implement getSubscribedEvents() method.
    }

    /**
     * @param string $defaultLockManagerName
     */
    public function __construct($defaultLockManagerName)
    {

    }

    /**
     * @param string $name
     * @param LockManagerInterface $lockManager
     */
    public function registerLockManager($name, LockManagerInterface $lockManager)
    {

    }
}
