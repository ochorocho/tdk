<?php

declare(strict_types=1);

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;
    /**
     * Define custom actions here
     */
    public function loadRootComposerJsonToArray(): array
    {
        return json_decode(file_get_contents('composer.json'), true, 512, JSON_THROW_ON_ERROR);
    }

    public function loadExampleComposerJsonToArray(string $path)
    {
        return json_decode(file_get_contents(codecept_data_dir($path)), true, 512, JSON_THROW_ON_ERROR);
    }
}
