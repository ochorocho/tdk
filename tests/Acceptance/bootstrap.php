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

shell_exec('rsync -avz --exclude={\'var\',\'public\',\'typo3-core\',\'.idea\',\'.git\',\'composer.lock\',\'.gitattributes\',\'vendor\',\'tests\',} ./ test-acceptance-tdk --delete');
