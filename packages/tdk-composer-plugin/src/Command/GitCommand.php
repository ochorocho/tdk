<?php

declare(strict_types=1);

namespace Ochorocho\TdkComposer\Command;

use Composer\Command\BaseCommand;
use Ochorocho\TdkComposer\Service\GitService;
use Ochorocho\TdkComposer\Service\ValidationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Completion\CompletionSuggestions;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;

final class GitCommand extends BaseCommand
{
    protected GitService $gitService;
    protected InputInterface $input;
    protected OutputInterface $output;
    protected ValidationService $validationService;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->gitService = new GitService();
        $this->validationService = new ValidationService($this->getIO(), $this->requireComposer());
        parent::initialize($input, $output);
    }

    protected function configure()
    {
        $this
            ->setName('tdk:git')
            ->setDescription('Do some git operations')
            ->addArgument('action', InputArgument::OPTIONAL, 'Manage git related files.')
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'Gerrit/TYPO3 account username')
            ->addOption('file', 'f', InputOption::VALUE_OPTIONAL, 'Relative path to your git commit template.')
            ->addOption('ref', null, InputOption::VALUE_OPTIONAL, 'Relative path to your git commit template.')
            ->addOption('branch', null, InputOption::VALUE_OPTIONAL, 'Checkout a certain git branch.')
            ->setHelp(
                <<<EOT
Run some git commands
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $action = $input->getArgument('action');

        switch ($action) {
            case 'config':
                $this->setConfig();
                break;
            case 'template':
                $this->setCommitTemplate();
                break;
            case 'apply':
                $this->applyPatch();
                break;
            case 'clone':
                $this->cloneRepository();
                break;
            case 'checkout':
                $this->checkout();
                break;
        }

        return Command::SUCCESS;
    }

    protected function setConfig()
    {
        $username = $this->input->getOption('username') ?? getenv('TDK_USERNAME') ?? false;
        if ($username === 'none') {
            return Command::SUCCESS;
        }

        if ($username) {
            $userData = $this->validationService->user()($username);
        } else {
            $userData = $this->getIO()->askAndValidate('What is your TYPO3/Gerrit Account Username? ', $this->validationService->user(), 3);
        }

        $pushUrl = 'ssh://' . $userData['username'] . '@review.typo3.org:29418/Packages/TYPO3.CMS.git';

        $gitConfigValues = [
            'remote.origin.pushurl' => $pushUrl,
            'user.name' => $userData['display_name'] ?? $userData['name'] ?? $userData['username'],
            'user.email' => $userData['email'],
        ];

        $code = Command::SUCCESS;

        foreach ($gitConfigValues as $key => $value) {
            try {
                $this->gitService->setGitConfigValue($key, $value);
                $this->getIO()->write('<info>Set "' . $key . '" to "' . $value . '"</info>');
            } catch (IOException $exception) {
                $this->getIO()->writeError('<error>' .$exception->getMessage() . '"</error>');
                $code = Command::FAILURE;
            }
        }

        return $code;
    }

    protected function setCommitTemplate(): int
    {
        $filePath = $this->input->getOption('file');

        if ($filePath ?? false) {
            $file = $this->validationService->filePath()($filePath);
        } else {
            $file = $this->getIO()->askAndValidate('Set TYPO3 commit message template [default: .gitmessage.txt]? ', $this->validationService->filePath(), 3, '.gitmessage.txt');
        }

        $status = $this->gitService->setCommitTemplate($file);

        if ($status) {
            $this->getIO()->writeError('<error>Could not enable Git Commit Template!</error>');
            return Command::FAILURE;
        }

        $template = realpath($file);
        $this->getIO()->write('<info>Set "commit.template" to ' . $template . ' </info>');

        return Command::SUCCESS;
    }

    public function applyPatch()
    {
        $ref = $this->input->getOption('ref') ?? getenv('TDK_PATCH_REF') ?? false;
        if (empty($ref)) {
            $this->getIO()->write('<warning>No patch ref given</warning>');
            return Command::FAILURE;
        }

        if (!$this->gitService->repositoryExists()) {
            $this->getIO()->write('Could not apply patch, repository does not exist. Please run "composer tdk:clone"');
            return Command::FAILURE;
        }

        if ($this->gitService->applyPatch($ref)) {
            $this->getIO()->write('<warning>Could not apply patch ' . $ref . ' </warning>');
            return Command::FAILURE;
        } else {
            $this->getIO()->write('<info>Apply patch ' . $ref . '</info>');
            return Command::SUCCESS;
        }
    }

    public function cloneRepository(): int
    {
        if ($this->gitService->repositoryExists()) {
            $this->getIO()->write('Repository exists! Therefore no download required.');
            return Command::SUCCESS;
        }

        $this->getIO()->overwrite('<info>Cloning TYPO3 repository. This may take a while depending on your internet connection!</info>');

        $gitRemoteUrl = 'https://github.com/TYPO3/typo3.git';
        if ($this->gitService->cloneRepository($gitRemoteUrl)) {
            $this->getIO()->write('<warning>Could not download git repository ' . $gitRemoteUrl . ' </warning>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    public function checkout()
    {
        $branch = $this->input->getOption('branch') ?? getenv('TDK_BRANCH') ?? false;
        if (empty($branch)) {
            $branch = 'main';
        }

        $this->getIO()->write('<info>Checking out branch "' . $branch . '"!</info>');
        if ($this->gitService->checkout($branch)) {
            $this->getIO()->write('<warning>Could not checkout branch ' . $branch . ' </warning>');
        }
    }

    public function complete(CompletionInput $input, CompletionSuggestions $suggestions): void
    {
        if ($input->mustSuggestOptionValuesFor('action')) {
            $suggestions->suggestValues(['config', 'template', 'apply', 'clone', 'checkout']);
        }
    }
}
