<?php

declare(strict_types=1);

namespace Ochorocho\TdkComposer;

use Composer\Composer;
use Composer\Console\Application;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use Composer\Plugin\Capable as CapableInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Ochorocho\TdkComposer\Command\CommandProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Filesystem\Filesystem;

final class Plugin implements PluginInterface, CapableInterface, EventSubscriberInterface
{
    private const PACKAGE_NAME = 'ochorocho/tdk-composer-plugin';

    /**
     * @var Application $application
     */
    protected $application;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->application = new Application();
        $this->application->setAutoExit(false);
    }

    public static function getSubscribedEvents()
    {
        return [
            PackageEvents::POST_PACKAGE_INSTALL => [
                ['cloneRepository', 0]
            ],
            ScriptEvents::POST_CREATE_PROJECT_CMD => [
                ['gitConfig', 0],
                ['createHooks', 0],
                ['ddevConfig', 0],
                ['commitTemplate', 0],
                ['showInformation', 0]
            ]
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

    public function cloneRepository(PackageEvent $event): int
    {
        $operation = $event->getOperation();
        if ($operation instanceof InstallOperation) {
            $package = $operation->getPackage()->getName();

            if ($package === self::PACKAGE_NAME) {
                $filsesystem = new Filesystem();
                $filsesystem->remove('typo3-core');

                $input = new ArrayInput(array('command' => 'tdk:git', 'action' => 'clone'));
                $this->application->run($input);

//                $repository = $event->getComposer()->getRepositoryManager()->createRepository('path', ['url' => 'typo3-core/typo3/sysext/*'], 'typo3-core-packages');
//                $event->getComposer()->getRepositoryManager()->prependRepository($repository);

                $coreExtensionPackages = [];
                $coreExtensions = [
                    'typo3/cms-core:@dev',
                    'typo3/cms-backend:@dev',
                    'typo3/cms-frontend:@dev',
                    'typo3/cms-install:@dev',
                ];

                $input = new ArrayInput(array('command' => 'require', 'packages' => $coreExtensions));
                $this->application->run($input);

//                $localRepository = $event->getComposer()->getRepositoryManager()->getLocalRepository();
//                foreach($coreExtensions as $c) {
//                    $coreExtensionPackages[] = $event->getComposer()->getRepositoryManager()->getLocalRepository()->findPackage($c, '@dev');
//                    //$coreExtensionPackages[] = $event->getComposer()->getRepositoryManager()->findPackages($c, '@dev');
//                }
//                foreach($coreExtensionPackages as $corePackage) {
//                    $event->getComposer()->getInstallationManager()->install($localRepository, new InstallOperation($corePackage));
//                }
            }
        }

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
