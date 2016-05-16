<?php

namespace Matthimatiker\CommandLockingBundle\Tests\EventListener;

use Matthimatiker\CommandLockingBundle\EventListener\CommandLockingListener;
use Matthimatiker\CommandLockingBundle\Locking\LockManagerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Webfactory\Constraint\IsEventSubscriberConstraint;

class CommandLockingListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * System under test.
     *
     * @var CommandLockingListener
     */
    private $listener = null;

    /**
     * @var LockManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $lockManager = null;

    /**
     * @var Application
     */
    private $application = null;

    /**
     * Initializes the test environment.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->lockManager = $this->getMock(LockManagerInterface::class);
        $this->listener = new CommandLockingListener('mock');
        $this->listener->registerLockManager('mock', $this->lockManager);
        $this->application = $this->createApplicationWith($this->listener);
    }

    /**
     * Cleans up the test environment.
     */
    protected function tearDown()
    {
        $this->application = null;
        $this->listener = null;
        $this->lockManager = null;
        parent::tearDown();
    }

    public function testIsValidEventSubscriber()
    {
        $this->assertThat($this->listener, new IsEventSubscriberConstraint());
    }

    public function testAddsLockOption()
    {
        $this->application->find('test:cmd')->setCode(function (InputInterface $input) {
            $this->assertTrue($input->hasOption('lock'), '--lock option not defined.');
        });

        $this->runApplication('test:cmd');
    }

    public function testDoesNotLockIfNotRequested()
    {
        $this->lockManager->expects($this->never())->method('lock');

        $this->runApplication('test:cmd');
    }

    public function testLocksIfRequested()
    {
        $this->lockManager->expects($this->once())->method('lock')->willReturn(true);

        $this->runApplication('test:cmd --lock');
    }

    public function testUsesExplicitlyRequestedLockManager()
    {
        $this->lockManager->expects($this->never())->method('lock');
        $customLockManager = $this->getMock(LockManagerInterface::class);
        $customLockManager->expects($this->once())->method('lock')->willReturn(true);
        $this->listener->registerLockManager('custom', $customLockManager);

        $this->runApplication('test:cmd --lock=custom');
    }

    public function testThrowsExceptionIfRequestedLockManagerIsNotAvailable()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $this->runApplication('test:cmd --lock=custom');
    }

    public function testRunsCommandIfNoLockIsRequested()
    {
        $this->assertCommandWillRun($this->once());

        $this->runApplication('test:cmd');
    }

    public function testDoesNotRunCommandIfLockingFails()
    {
        $this->lockManager->expects($this->once())->method('lock')->willReturn(false);
        $this->assertCommandWillRun($this->never());

        $this->runApplication('test:cmd --lock', ConsoleCommandEvent::RETURN_CODE_DISABLED);
    }

    public function testRunsCommandIfLockingSucceeds()
    {
        $this->lockManager->expects($this->once())->method('lock')->willReturn(true);
        $this->assertCommandWillRun($this->once());

        $this->runApplication('test:cmd --lock');
    }

    public function testReleasesLockWhenLockedCommandTerminates()
    {
        $this->lockManager->expects($this->any())->method('lock')->willReturn(true);
        $this->lockManager->expects($this->once())->method('release');

        $this->runApplication('test:cmd --lock');
    }

    public function testDoesNotReleaseWhenNoLockWasRequested()
    {
        $this->lockManager->expects($this->never())->method('release');

        $this->runApplication('test:cmd');
    }

    public function testDoesNotReleaseWhenCommandLockingFails()
    {
        $this->lockManager->expects($this->any())->method('lock')->willReturn(false);
        $this->lockManager->expects($this->never())->method('release');

        $this->runApplication('test:cmd --lock', ConsoleCommandEvent::RETURN_CODE_DISABLED);
    }

    public function testDoesNotInterfereWithOptionsOfCommand()
    {
        $command = new Command('test:with-param');
        $commandInputDefinition = new InputDefinition();
        $commandInputDefinition->addOption(new InputOption('--command-option', null, InputOption::VALUE_NONE));
        $command->setDefinition($commandInputDefinition);
        $command->setCode(function (InputInterface $input) {
            $this->assertTrue($input->getOption('command-option'));
        });
        $this->application->add($command);

        $this->runApplication('test:with-param --command-option');
    }

    /**
     * @see http://symfony.com/doc/current/components/console/introduction.html#calling-an-existing-command
     */
    public function testWorksIfSubCommandIsCalled()
    {
        $this->lockManager->expects($this->any())->method('lock')->willReturn(true);
        $subCommand = new Command('test:sub-cmd');
        $subCommand->setCode(function () {});
        $this->application->add($subCommand);
        // Ensure that the sub-command is called.
        $this->application->find('test:cmd')->setCode(function (InputInterface $input, OutputInterface $output) {
            $command = $this->application->find('test:sub-cmd');
            return $command->run(new StringInput('test:sub-cmd'), $output);
        });

        $this->setExpectedException(null);
        $this->runApplication('test:cmd --lock');
    }

    /**
     * Asserts that the test command will run the given number of times.
     *
     * Example:
     *
     *     $this->assertCommandWillRun($this->once());
     *
     * @param \PHPUnit_Framework_MockObject_Matcher_Invocation $invocations
     */
    private function assertCommandWillRun(\PHPUnit_Framework_MockObject_Matcher_Invocation $invocations)
    {
        $codeMock = $this->getMock(\stdClass::class, array('__invoke'));
        $codeMock->expects($invocations)
            ->method('__invoke');
        $command = $this->application->find('test:cmd');
        $command->setCode($codeMock);
    }

    /**
     * Runs the console application with the given argument line.
     *
     * Example:
     *
     *     $this->runApplication('test:cmd --lock');
     *
     * @param string $commandLineArguments
     * @param integer $expectedExitCode
     */
    private function runApplication($commandLineArguments, $expectedExitCode = 0)
    {
        $output = new BufferedOutput();
        $exitCode = $this->application->run(new StringInput($commandLineArguments), $output);
        $this->assertEquals($expectedExitCode, $exitCode, $output->fetch());
    }

    /**
     * @param CommandLockingListener $listener
     * @return Application
     */
    private function createApplicationWith(CommandLockingListener $listener)
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber($listener);
        $application = new Application();
        $application->setDispatcher($dispatcher);
        $application->setAutoExit(false);
        $application->setCatchExceptions(false);
        $command = new Command('test:cmd');
        $command->setCode(function() {});
        $application->add($command);
        return $application;
    }
}
