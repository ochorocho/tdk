# TDK - Proof of Concept

Ease the TYPO3 Composer based Contribution Setup.
Run `composer create-project ochorocho/tdk <target-folder-name>` and answer
the command prompts as needed.

This command guides you through the process of setting up a
composer based TYPO3 CoreDev environment. 

## Command prompts

* What is your TYPO3/Gerrit Account Username? : Username used on https://review.typo3.org/ 
  which in most cases is your http://my.typo3.org login 
* Setup Commit Message Hook? [y/n] : default=y
* Setup Pre Commit Hook? [y/n] : default=y
* Create a basic ddev config? [y/n] : default=n

## Structure

```
├── composer.json   # Ordinary composer.json with some handy scripts
├── .gitmessage.txt # Commit message template
├── packages        # Additional local packages/extensions
└── typo3-core      # TYPO3 repository (master branch) git@github.com:TYPO3/typo3.git  
```

## Additional Composer commands/scripts

`composer tdk:<command>`

* `tdk:setup`: Setup everything to run a Composer based CoreDev Setup
* `tdk:clear`: Delete all files and folder
* `tdk:remove-hooks`: Delete created hooks in `.git/hooks`
* `tdk:enable-hooks`: Create hooks copied from the TYPO3 repository
* `tdk:set-commit-template`: Configure TYPO3 repository to use `.gitmessage.txt` as commit message template
* `tdk:set-push-url`: Set Gerrit as remote to push patches to
* `tdk:ddev-config`: Create a basic ddev configuration
* `tdk:help`: Show summary with links to the TYPO3 Contribution Guide
* `typo3`: Shortcut to run TYPO3 Commands 

