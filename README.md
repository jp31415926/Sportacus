Sportacus Scheduler

System for scheduling referees for soccer games.

INSTALL for development.

1. Make sure you have git, apache2, mysql and PHP installed and the following PHP extensions: php-xml, php-mysql, php-dom, php-gd

2. Setup git if you have never done so:
```
# git config --global user.name "Your Name"
# git config --global user.email you@domain.com
# git config --global push.default simple
```

3. Create directory for project and make it the current directory.

4. Run
```
# git init
# git pull https://jp31415926@bitbucket.org/jp31415926/sportacus.git
# git remote add origin https://jp31415926@bitbucket.org/jp31415926/sportacus.git
```

5. Install composer (or download it) and run composer install.

6. Make sure all files are readable by all (or at least your web server).

7. Run the following to build bootstrap.php.cache.
```
php vendor/sensio/distribution-bundle/Sensio/Bundle/DistributionBundle/Resources/bin/build_bootstrap.php
```

8. create var/cache/ and make it world writeable/readable
```
# mkdir -p var/cache
# chmod a+rw var/cache
```

9. Create database and import data (change db, user, user-pass to your own values):
```
# sudo mysql -p
mysql> create database db;
mysql> grant all on db.* to 'user'@'localhost' identified by 'user-pass';
# mysql -p db < database-backup.sql

or, to create a clean database and create an admin user:

# app/console doctrine:schema:create
```

10. Copy parameters.sh.dist to parameters.sh and app/config/parameters.yml.dist to app/config/parameters.yml. Edit as needed.
You might need to change "httpProtocol: https" to "httpProtocol: http" in app/config/parameters.yml.

11. Point your browser at web/debug_webpage.php. For production environment, configure your server to map the root of the webpage to web/app.php and delete the web/debug_webpage.php file.

12. Register an admin user on the webpage (example: username admin) and then promote that user to admin:
```
# app/console fos:user:promote admin ROLE_ADMIN
```

You should have a working copy by now. Look at var/log/[dev|prod].log to get hints when you have issues.

To push changes to the repo:
```
git push --set-upstream origin master
```
