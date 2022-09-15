<?php

declare(strict_types=1);

namespace Ochorocho\TdkComposer\Command;

use Composer\Command\BaseCommand;
use Composer\Factory;
use Composer\IO\NullIO;
use Composer\Json\JsonFile;
use Composer\Plugin\CommandEvent;
use Composer\Plugin\PluginEvents;
use Composer\Repository\VcsRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class TdkCommand extends BaseCommand
{
    /**
     * @var string
     */
    protected $buildPath = 'build';

    /**
     * @var array
     */
    protected $versionDetails = [];

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
    }

    protected function configure()
    {
        $this
            ->setName('tdk:package')
            ->setDescription('Generate a package for Gitlab')
            ->setDefinition([
                new InputOption('json', 'j', InputOption::VALUE_REQUIRED, 'Composer json file', 'composer.json'),
            ])
            ->setHelp(<<<EOT
The package command creates an archive file (tar) and json file for Gitlab Packages.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $composer = $this->requireComposer();
        $commandEvent = new CommandEvent(PluginEvents::COMMAND, 'package', $input, $output);
        $composer->getEventDispatcher()->dispatch($commandEvent->getName(), $commandEvent);
        $output->writeln('Hooooray .... ');
    }
}
