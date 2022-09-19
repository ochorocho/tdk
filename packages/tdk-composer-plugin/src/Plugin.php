<?php

declare(strict_types=1);

namespace Ochorocho\TdkComposer;

use Composer\Composer;
use Composer\Console\Application;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use Composer\Plugin\Capable as CapableInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Ochorocho\TdkComposer\Command\CommandProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;

final class Plugin implements PluginInterface, CapableInterface, EventSubscriberInterface
{
    /**
     * @var IOInterface $io
     */
    protected $io;

    /**
     * @var Application $application
     */
    protected $application;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->io = $io;
        $this->application = new Application();
        $this->application->setAutoExit(false);
    }

    public static function getSubscribedEvents()
    {
        return [
            // GitPod: Ensure the typo3-core folder exists
            'post-install-cmd' => [
                ['cloneRepository', 0]
            ],
            // TDK initialization: Clone and configure local TYPO3 Core environment
            'post-create-project-cmd' => [
                ['cloneRepository', 0],
                ['gitConfig', 0],
                ['createHooks', 0],
                ['ddevConfig', 0],
                ['commitTemplate', 0],
                ['showInformation', 0]
            ],
        ];
    }

    public function getCapabilities(): array
    {
        return [
            CommandProviderCapability::class => CommandProvider::class
        ];
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
        // TODO: Implement deactivate() method.
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
        // TODO: Implement uninstall() method.
    }

    public function cloneRepository(Event $event): int
    {
        $input = new ArrayInput(array('command' => 'tdk:git', 'action' => 'clone'));
        $this->application->run($input);
        $event->getComposer()->getRepositoryManager()->createRepository('path', ['url' => 'typo3-core/typo3/sysext/*'], 'typo3-core-packages');

        return Command::SUCCESS;
    }

    public function gitConfig(Event $event): int
    {
        $input = new ArrayInput(array('command' => 'tdk:git', 'action' => 'config'));
        $this->application->run($input);

        return Command::SUCCESS;
    }

    public function createHooks(Event $event): int
    {
        $input = new ArrayInput(array('command' => 'tdk:hooks', 'action' => 'create'));
        $this->application->run($input);

        return Command::SUCCESS;
    }

    public function ddevConfig(Event $event): int
    {
        $input = new ArrayInput(array('command' => 'tdk:ddev'));
        $this->application->run($input);

        return Command::SUCCESS;
    }

    public function commitTemplate(Event $event): int
    {
        $input = new ArrayInput(array('command' => 'tdk:git', 'action' => 'template'));
        $this->application->run($input);

        return Command::SUCCESS;
    }

    public function showInformation(Event $event): int
    {
        $input = new ArrayInput(array('command' => 'tdk:help', 'type' => 'summary'));
        $this->application->run($input);

        $input = new ArrayInput(array('command' => 'tdk:help', 'type' => 'done'));
        $this->application->run($input);

        return Command::SUCCESS;
    }
}
