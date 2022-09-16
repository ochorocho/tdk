<?php

declare(strict_types=1);

namespace Ochorocho\TdkComposer\Command;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;

final class CommandProvider
    implements CommandProviderCapability
{
    public function getCommands(): array
    {
        return [
            new GitCommand(),
            new HookCommand(),
            new DoctorCommand(),
            new CleanupCommand(),
            new DdevConfigCommand(),
        ];
    }
}
