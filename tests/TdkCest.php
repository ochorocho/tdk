<?php

use Codeception\Example;

class TdkCest
{
    private static string $coreDevFolder = 'typo3-core/';
    private static string $testFolder = __DIR__ . '/../test-acceptance-tdk/';

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
        $I->seeInShellOutput('Updating files:');

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
    public function pushUrl(AcceptanceTester $I): void
    {
        $I->runShellCommand('composer tdk:set-push-url -- --username=username');
        $I->seeInShellOutput('Set "remote.origin.pushurl" to "ssh://username@review.typo3.org:29418/Packages/TYPO3.CMS.git"');

        $I->runShellCommand('git -C ' . self::$coreDevFolder . ' config --get remote.origin.pushurl');
        $I->seeInShellOutput('ssh://username@review.typo3.org');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function commitTemplate(AcceptanceTester $I): void
    {
        $I->runShellCommand('composer tdk:set-commit-template');
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
