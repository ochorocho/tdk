<?php

declare(strict_types=1);

namespace Ochorocho\TdkComposer\Service;

use Composer\Util\ProcessExecutor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Exception\IOException;

class GitService extends BaseService
{
    public function setCommitTemplate(string $filePath): int
    {
        $process = new ProcessExecutor();
        $template = realpath($filePath);
        return $process->execute('git config commit.template ' . $template, $output, BaseService::CORE_DEV_FOLDER);
    }

    public function setGitConfigValue(string $config, string $value): void
    {
        $process = new ProcessExecutor();
        $command = 'git config ' . $config . ' "' . $value . '"';
        $status = $process->execute($command, $output, BaseService::CORE_DEV_FOLDER);
        if ($status > 0) {
            throw new IOException('Could not set Git "' . $config . '" to "' . $value);
        }
    }

    public function applyPatch($ref)
    {
        $process = new ProcessExecutor();
        $command = 'git fetch https://review.typo3.org/Packages/TYPO3.CMS ' . $ref . ' && git cherry-pick FETCH_HEAD';

        return $process->executeTty($command, BaseService::CORE_DEV_FOLDER);
    }

    public function cloneRepository($url): int
    {
        $process = new ProcessExecutor();
        $command = sprintf('git clone %s %s', ProcessExecutor::escape($url), ProcessExecutor::escape(BaseService::CORE_DEV_FOLDER));

        return $process->executeTty($command);
    }

    public function checkout(string $branch)
    {
        $process = new ProcessExecutor();
        $command = sprintf('git checkout %s', ProcessExecutor::escape($branch));
        return $process->executeTty($command, BaseService::CORE_DEV_FOLDER);
    }

    public function repositoryExists(): bool
    {
        return $this->filesystem->exists(BaseService::CORE_DEV_FOLDER . '/.git');
    }

    public function latestCommit(): string
    {
        $process = new ProcessExecutor();
        $command = 'git log -n 1 --pretty=\'format:%C(auto)%h (%s, %ad)\'';
        $process->execute($command, $output, BaseService::CORE_DEV_FOLDER);

        return $output;
    }
}
