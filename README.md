# CommandLockingBundle #

[![Build Status](https://travis-ci.org/Matthimatiker/CommandLockingBundle.svg?branch=master)](https://travis-ci.org/Matthimatiker/CommandLockingBundle)
[![Coverage Status](https://coveralls.io/repos/Matthimatiker/CommandLockingBundle/badge.svg?branch=master&service=github)](https://coveralls.io/github/Matthimatiker/CommandLockingBundle?branch=master)

Sometimes you want to ensure that a Symfony console command does not run in parallel.
This bundle adds an optional locking feature to all console commands that can be used to prevent parallel execution.

## Installation ##

Install the bundle via [Composer](https://getcomposer.org):

    composer require matthimatiker/command-locking-bundle
    
Then register the bundle in your ``AppKernel``:

    <?php
    // app/AppKernel.php
    
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new \Matthimatiker\CommandLockingBundle\MatthimatikerCommandLockingBundle()
        );
        // ...
    }

## Usage ##

Simply pass the ``--lock`` option to *any* command to ensure that parallel runs are prohibited:

    app/console cache:warmup --lock
    
If the command did not terminate yet and the same command is called again (in lock mode), then
it is simply skipped and a notice is shown.

The default locking implementation relies on the filesystem and uses Symfony's 
[LockHandler](https://symfony.com/doc/current/components/filesystem/lock_handler.html). 
This avoids parallel execution as long as your application runs on a single system.
If you need a distributed lock, then you will have to write your own lock manager.

### Custom Lock Managers ###

A custom lock manager must implement ``\Matthimatiker\CommandLockingBundle\Locking\LockManagerInterface``.
Afterwards it must be registered as service and tagged as ``matthimatiker_command_locking.console.lock_manager``:

    my.custom_lock_manager:
        class: My\Custom\LockManager
        tags:
            - { name: matthimatiker_command_locking.console.lock_manager, alias: custom }

An alias must be defined together with the tag (``custom`` in this example) and is used to reference 
the new lock manager:

    app/console cache:warmup --lock=custom

## Known Issues ##

When sub-command are called as described in the [official documentation](http://symfony.com/doc/current/components/console/introduction.html#calling-an-existing-command),
then locking cannot be used for the sub-commands as the life-cycle events are not fired.


## Initialization Tasks (remove this block once you are done) ##

- Activate builds in [Travis CI](https://travis-ci.org/)
- Activate repository at [Coveralls](https://coveralls.io)
- Publish at [Packagist](https://packagist.org/)
- Create webhook that pushes repository updates to [Packagist](https://packagist.org/)
