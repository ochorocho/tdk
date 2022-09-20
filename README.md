# TDK - Proof of Concept

Ease the TYPO3 Composer based Contribution Setup.
Run `composer create-project ochorocho/tdk <target-folder-name>` and answer
the command prompts as needed. If you want the current dev version add `--stability=dev`
to the command.

This command guides you through the process of setting up a
composer based TYPO3 CoreDev environment.

## Command prompts

* What is your TYPO3/Gerrit Account Username? : Username used on https://review.typo3.org/ 
  which in most cases is your http://my.typo3.org login 
* Setup Commit Message Hook? [y/n] : default=y
* Setup Pre Commit Hook? [y/n] : default=y
* Create a basic ddev config? [y/n] : default=y

## Structure

```
├── composer.json   # Ordinary composer.json with some handy scripts
├── .gitmessage.txt # Commit message template
├── packages        # Additional local packages/extensions
└── typo3-core      # TYPO3 repository (master branch) git@github.com:TYPO3/typo3.git  
```

## Additional Composer commands

`composer <command>`

* `tdk:cleanup`: Delete all files and folder
* `tdk:hooks <create|delete>`: Create/delete created hooks in `.git/hooks`
* `tdk:git <action>`
  * `config`: Set git name, email and pushurl
  * `template`: Configure TYPO3 repository to use `.gitmessage.txt` as commit message template
  * `apply`: Apply Gerrit patch e.g. `composer tdk:git apply --ref=refs/changes/60/69360/6`
  * `clone`: Download and store the repository in `./typo3-core`
* `tdk:set-push-url`: Set Gerrit as remote to push patches to
* `tdk:ddev`: Create a basic ddev configuration
* `tdk:help <summary|done>`: Show informational text
* `tdk:doctor`: Show potential issues 

## Demo run

[![asciicast](https://asciinema.org/a/xuY3Zx6k7I7OdLLRkLJBiUDnT.svg)](https://asciinema.org/a/xuY3Zx6k7I7OdLLRkLJBiUDnT)
