# TYPO3 TDK

> See the terminal below for details.            
> Once finished TYPO3 will open in a separate tab

### Local commands

* `tdk preview <fe|be|install|mailhog>` - Open frontend, backend, installTool or mailcatcher (MailHog)
* `tdk db <create|delete>` - Create or delete the default database (db)
* `tdk php <PHP_VERSION>` - Switch php version for cli and apache 
  (Versions available 5.6, 7.0, 7.1, 7.2, 7.3, 7.4, 8.0, 8.1, 8.2)
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

Root user:
* User: root
* Password: <none> 
