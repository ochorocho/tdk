<?php

/**
 * Prepare environment
 *
 * Copy all required files into a dedicated
 * folder (./test-acceptance-tdk) and run tests in there
 */

if (is_dir('test-acceptance-tdk')) {
    shell_exec('rm -Rf test-acceptance-tdk/');
}

shell_exec('rsync -avz --exclude={\'var\',\'public\',\'typo3-core\',\'.idea\',\'.git\',\'.gitattributes\',\'vendor\',\'tests\',\'.ddev\',} ./ test-acceptance-tdk --delete');

// GitHub actions seems to ignore rsync excludes therefore removing
// folders that get in the way
if (is_dir('test-acceptance-tdk/typo3-core')) {
    shell_exec('rm -Rf test-acceptance-tdk/typo3-core');
}

if (is_dir('test-acceptance-tdk/vendor')) {
    shell_exec('rm -Rf test-acceptance-tdk/vendor');
}

$windows = strpos(PHP_OS, 'WIN') === 0;
$test = $windows ? 'where' : 'command -v';

if (is_executable(trim(shell_exec($test . ' ddev') ?? ''))) {
    exec('ddev stop --unlist typo3-dev-tdk');
}
