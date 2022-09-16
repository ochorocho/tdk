<?php

declare(strict_types=1);

namespace Ochorocho\TdkComposer\Command;

use Composer\Command\BaseCommand;
use Ochorocho\TdkComposer\Service\BaseService;
use Ochorocho\TdkComposer\Service\HookService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Exception\IOException;

final class HookCommand extends BaseCommand
{
    protected OutputInterface $output;
    protected HookService $hookService;

    protected function configure()
    {
        $this
            ->setName('tdk:hooks')
            ->setDescription('Enable hooks')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force to overwrite hooks')
            ->addArgument('action', InputArgument::OPTIONAL, 'Create/delete hooks')
            ->setHelp(<<<EOT
The package command creates an archive file (tar) and json file for Gitlab Packages.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->hookService = new HookService();

        $hooks = ['commit-msg', 'pre-commit'];
        $action = $input->getArgument('action');
        $force = (bool)($input->getOption('force') ?? getenv('TDK_HOOK_FORCE_CREATE') ?? false);
        $helper = $this->getHelper('question');

        switch ($action) {
            case 'create':
                $actionLabel = 'Create';
                break;
            case 'delete':
                $actionLabel = 'Delete';
                break;
            default:
                return $this->info($hooks);
        }

        $code = Command::SUCCESS;
        foreach ($hooks as $file) {
            if ($force) {
                $answer = true;
            } else {
                $message = $actionLabel . ' "' . $file . '" Hook? [<fg=cyan;options=bold>y</>/n] ';
                $question = new ConfirmationQuestion($message, true);
                $answer = $helper->ask($input, $output, $question);
            }

            if ($answer) {
                switch ($action) {
                    case 'create':
                        $code = $this->create($file);
                        break;
                    case 'delete':
                        $code = $this->delete($file);
                        break;
                }
            }
        }

        return $code;
    }

    protected function create(string $file): int
    {
        try {
            $this->hookService->create($file);
            $this->output->writeln('<info>Created "' . $file . '" hook</info>');
            return Command::SUCCESS;
        } catch (IOException $e) {
            $this->output->writeln('<warning>Failed to create "' . $file . '" hook:' . $e->getMessage() . '</warning>');
            return Command::FAILURE;
        }
    }

    protected function delete(string $file): int
    {
        try {
            $this->hookService->delete((array)$file);
            $this->output->writeln('<info>Deleted "' . $file . '" hook</info>');
            return Command::SUCCESS;
        } catch (IOException $e) {
            $this->output->writeln('<warning>Failed to delete "' . $file . '" hook:' . $e->getMessage() . '</warning>');
            return Command::FAILURE;
        }
    }

    protected function info(array $hooks): int
    {
        $code = Command::SUCCESS;
        foreach ($hooks as $file) {
            if ($this->hookService->exists($file)) {
                $this->output->writeln('<info>' . BaseService::ICON_SUCCESS . 'Hook "' . $file . '" exists</info>');
            } else {
                $this->output->writeln(BaseService::ICON_FAILED . 'Hook "' . $file . '" does not exist');
                $code = Command::FAILURE;
            }
        }

        if ($code !== Command::SUCCESS) {
            $this->output->writeln('You may run "composer tdk:hooks create"');
        }

        return $code;
    }
}
