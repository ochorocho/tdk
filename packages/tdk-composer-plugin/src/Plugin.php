<?php

declare(strict_types=1);

namespace Ochorocho\TdkComposer;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use Composer\Plugin\Capable as CapableInterface;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PreFileDownloadEvent;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Ochorocho\TdkComposer\Command\CommandProvider;
use Ochorocho\TdkComposer\Command\GitCommand;
use Ochorocho\TdkComposer\Service\GitService;
use Symfony\Component\Console\Command\Command;

final class Plugin implements PluginInterface, CapableInterface, EventSubscriberInterface
{
    /**
     * @var IOInterface $io
     */
    protected $io;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->io = $io;
    }

    public static function getSubscribedEvents()
    {
        return [
//            'post-root-package-install' => [
//                ['cloneRepository', 0]
//            ],
            'post-install-cmd' => [
                ['cloneRepository', 0]
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
        $gitService = new GitService();

        if ($gitService->repositoryExists()) {
            $this->io->writeError('Repository exists! Therefore no download required.');
            return Command::SUCCESS;
        }

        echo "'<info>Cloning TYPO3 repository. This may take a while depending on your internet connection!</info>'";
        $event->getIO()->debug('<info>Cloning TYPO3 repository. This may take a while depending on your internet connection!</info>');
        $gitRemoteUrl = 'https://github.com/TYPO3/typo3.git';
        if ($gitService->cloneRepository($gitRemoteUrl)) {
            $event->getIO()->write('<warning>Could not download git repository ' . $gitRemoteUrl . ' </warning>');
            return Command::FAILURE;
        }

        $event->getComposer()->getRepositoryManager()->createRepository('path', ['url' => 'typo3-core/typo3/sysext/*'], 'typo3-core-packages');

        return Command::SUCCESS;
    }
}
