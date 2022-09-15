<?php

declare(strict_types=1);

namespace Ochorocho\TdkComposer\Service;

use Composer\Util\ProcessExecutor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Exception\IOException;

class GitService extends BaseService
{
    public function setConfig(array $userData)
    {
        $pushUrl = 'ssh://' . $userData['username'] . '@review.typo3.org:29418/Packages/TYPO3.CMS.git';
        $this->setGitConfigValue('remote.origin.pushurl', $pushUrl);
        $this->setGitConfigValue('user.name', $userData['display_name'] ?? $userData['name'] ?? $userData['username']);
        $this->setGitConfigValue('user.email', $userData['email']);

        return Command::SUCCESS;
    }

    public function setCommitTemplate(string $filePath): int
    {
        $process = new ProcessExecutor();
        $template = realpath($filePath);
        return $process->execute('git config commit.template ' . $template, $output, $this->coreDevFolder);
    }

    private function setGitConfigValue(string $config, string $value): void
    {
        $process = new ProcessExecutor();
        $command = 'git config ' . $config . ' "' . $value . '"';
        $status = $process->execute($command, $output, $this->coreDevFolder);
        if ($status > 0) {
            throw new IOException('Could not set Git "' . $config . '" to "' . $value);
        }
    }

    public function applyPatch($ref)
    {
        $process = new ProcessExecutor();
        $command = 'git fetch https://review.typo3.org/Packages/TYPO3.CMS ' . $ref . ' && git cherry-pick FETCH_HEAD';

        return $process->executeTty($command, $this->coreDevFolder);
    }

    public function cloneRepository($url): int
    {
        $process = new ProcessExecutor();
        $command = sprintf('git clone %s %s', ProcessExecutor::escape($url), ProcessExecutor::escape($this->coreDevFolder));

        return $process->executeTty($command);
    }

    public function checkout(string $branch)
    {
        $process = new ProcessExecutor();
        $command = sprintf('git checkout %s', ProcessExecutor::escape($branch));
        return $process->executeTty($command, $this->coreDevFolder);
    }

    public function repositoryExists(): bool
    {
        return $this->filesystem->exists($this->coreDevFolder);
    }
}
