# TYPO3 TDK

> See the terminal below for details.            
> Once finished TYPO3 will open in a separate tab

### Local commands

* `tdk preview <fe|be|install|mailhog>` - Open frontend, backend, installTool or mailcatcher (MailHog)
* `tdk db <create|delete>` - Create or delete the default database (db)
* `tdk php <PHP_VERSION>` - Switch php version for cli and apache 
  (Versions available 8.1, 8.2, 8.3, 8.4)
* `tdk cron` - Run the scheduler task. This script is triggered by cron every minute

### Services 

* `sudo service mysql <start|stop|status>` - Run MySQL
* `sudo service apache2 <start|stop|status>` - Run Apache2 webserver
* `sudo service mailhog <start|stop|status>` - Run MailHog

### Database

* User: db
* Password: db
* Database: db
* Host: 127.0.0.1

Root user (`mysql -uroot`):
* User: root
* Password: No password set!

### SSH Private/Public Key Authentication

> **WARNING**
>
> Adding a private key to GitPod might be a security concern.
> To reduce the risk, generate a private/public key pair which will be used for Gerrit exclusively.

Generate a GitPod dedicated private/public key pair:

```
ssh-keygen -t ed25519 -f gitpod_key -C gitpod
```

[Add a variable](https://gitpod.io/variables) named `SSH_PRIVATE_KEY` and
paste your generated private key. To be able to push to Gerrit you need to add
your public key in your [Gerrit settings](https://review.typo3.org/settings/#SSHKeys)
