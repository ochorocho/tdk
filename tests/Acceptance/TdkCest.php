<?php

declare(strict_types=1);

namespace Acceptance;

use \AcceptanceTester as AcceptanceTester;

class TdkCest
{
    private static string $coreDevFolder = 'typo3-core/';
    private static string $testFolder = __DIR__ . '/../../test-acceptance-tdk/';

    public function _before(AcceptanceTester $I)
    {
        chdir(self::$testFolder);
    }

    public function clone(AcceptanceTester $I): void
    {
        $I->runShellCommand('composer tdk:clone');
        $I->seeResultCodeIs(0);
        $I->seeInShellOutput('Cloning TYPO3 repository. This may take a while depending on your internet connection!');
        $I->seeInShellOutput('Cloning into');

        $I->runShellCommand('composer tdk:clone');
        $I->seeResultCodeIs(0);
        $I->seeInShellOutput('Repository exists! Therefore no download required.');
    }

    public function help(AcceptanceTester $I): void
    {
        $I->runShellCommand('composer tdk:help');

        $I->seeResultCodeIs(0);
        $I->seeInShellOutput('For more Details read the docs:', 'To be able to push to Gerrit, you need to add your public key');
    }

    /**
     * @depends clone
     * @param AcceptanceTester $I
     */
    public function gitConfig(AcceptanceTester $I): void
    {
        $I->runShellCommand('composer tdk:set-git-config -- --username=ochorocho');
        $I->amGoingTo('See expected response text of command');
        $I->seeInShellOutput('Set "remote.origin.pushurl" to "ssh://ochorocho@review.typo3.org:29418/Packages/TYPO3.CMS.git"');
        $I->seeInShellOutput('Set "user.email" to "rothjochen@gmail.com"');
        $I->seeInShellOutput('Set "user.name" to "Jochen Roth"');

        $I->amGoingTo('See newly set "remote.origin.pushurl"');
        $I->runShellCommand('git -C ' . self::$coreDevFolder . ' config --get remote.origin.pushurl');
        $I->seeInShellOutput('ssh://ochorocho@review.typo3.org');

        $I->amGoingTo('See newly set "user.name"');
        $I->runShellCommand('git -C ' . self::$coreDevFolder . ' config --get user.name');
        $I->seeInShellOutput('Jochen Roth');

        $I->amGoingTo('See newly set "user.email"');
        $I->runShellCommand('git -C ' . self::$coreDevFolder . ' config --get user.email');
        $I->seeInShellOutput('rothjochen@gmail.com');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function commitTemplate(AcceptanceTester $I): void
    {
        $I->runShellCommand('composer tdk:set-commit-template -- --file=./.gitmessage.txt');
        $I->seeInShellOutput('Set "commit.template" to ');

        $I->runShellCommand('git -C ' . self::$coreDevFolder . ' config --get commit.template');
        $I->seeInShellOutput('.gitmessage.txt');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function hooks(AcceptanceTester $I): void
    {
        $hooksFolder = self::$testFolder . self::$coreDevFolder . '.git/hooks/';

        $I->amGoingTo('Enable the hooks');
        $I->runShellCommand('composer tdk:enable-hooks -- --force');

        $I->seeResultCodeIs(0);
        $I->seeFileFound('commit-msg', $hooksFolder);
        $I->seeFileFound('pre-commit', $hooksFolder);

        $I->amGoingTo('Delete the hooks');
        $I->runShellCommand('composer tdk:remove-hooks');

        $I->seeResultCodeIs(0);
        $I->dontSeeFileFound('commit-msg', $hooksFolder);
        $I->dontSeeFileFound('pre-commit', $hooksFolder);
    }

    /**
     * @todo: Find a more generic way to test the tdk:apply-patch command
     *
     * @param AcceptanceTester $I
     */
    public function applyPatch(AcceptanceTester $I): void
    {
        $I->runShellCommand('composer tdk:apply-patch -- --ref=refs/changes/43/70643/35');
        $I->seeInShellOutput('Apply patch refs/changes/43/70643/35');

        $I->runShellCommand('git -C ' . self::$coreDevFolder . ' log -1 --oneline');
        $I->seeInShellOutput('Add configurable template for locked backend');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function checkoutBranch(AcceptanceTester $I): void
    {
        $I->runShellCommand('composer tdk:checkout -- --branch=main');
        $I->seeInShellOutput('Checking out branch "main"!');

        $I->runShellCommand('git -C ' . self::$coreDevFolder . ' branch --show-current');
        $I->seeInShellOutput('main');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function clear(AcceptanceTester $I): void
    {
        $I->runShellCommand('composer tdk:clear -- --force');
        $I->seeResultCodeIs(0);

        // Foreach is needed here, as we don't want to
        // re-run the command multiple times
        foreach ($this->clearDataProvider() as $file) {
            $I->dontSeeFileFound(self::$testFolder . $file);
        }
    }

    protected function clearDataProvider(): array
    {
        return [
            'composer.lock',
            'public/index.php',
            'public/typo3',
            'typo3-core',
            'var',
        ];
    }
}
