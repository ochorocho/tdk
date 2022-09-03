<?php

require dirname(__DIR__) . '/../Scripts/BaseScript.php';
use Ochorocho\Tdk\Scripts\CommonScript;

$branch = getenv('TDK_BRANCH') ?: 'main';
$composerFile = 'https://raw.githubusercontent.com/TYPO3/typo3/' . $branch . '/composer.json';
echo CommonScript::getPhpVersion($composerFile) . PHP_EOL;