# Sportacus Scheduler

System for scheduling referees for soccer games.

Our vision is for Sportacus to be an online sports resource scheduler. It will be designed to help our users schedule games, practices, field maintenance, volunteers, referees and more! Perhaps even automatically schedule resources and assign people as needed, if availability and conflict information is provided accurately.

Write now it is a referee schedule system.

- It detects conflicts as schedules are imported or changed.
- It sends reminder emails and texts to users for games.
- It also sends alerts when a game is moved or changed.
- It supports multiple region play.
- It has somewhat configurable referee positions.
- It automatically calculates referee points and allows referees to assign themselves to teams.

Sportac.us is provided as a free service for AYSO Area 5C and surrounding areas.

Sportac.us was originally written by John Price with ideas and contributions from Art Hundiak (author of zAYSO).

As of 2020, John was the Regional Commissioner of AYSO Region 894 (Harvest, Alabama USA), Area Referee Administrator and Area Scheduler for AYSO Area 5C. He has volunteered for AYSO since 2012.

As of 2020, Art is currently the Assistant Referee Administrator of AYSO Section 5 and volunteered in many areas for Area 5C and Region 894 since at least 2000.

## Install for development

You will need to get apache2, php and mysql configured and running on your server. This is left as an exercise for the reader. :)

1. Make sure you have git, apache2, mysql, PHP, and composer installed and the following PHP extensions: php-xml, php-mysql, php-dom, php-gd

1. Setup git if you have never done so:

   ```bash
   # git config --global user.name "Your Name"
   # git config --global user.email you@domain.com
   # git config --global push.default simple
   ```

1. Create directory for project and make it the current directory.

   ```bash
   # mkdir sportacus
   # cd sportacus
   ```

1. Run

   ```bash
   # git clone https://github.com/jp31415926/Sportacus.git .
   ```

1. Run composer. If there are errors, address them (usually missing packages).

   ```bash
   # composer install
   ```

1. Make sure all files are readable by all (or at least your web server).

1. create var/cache & var/logs and make them world writeable/readable.

   ```bash
   # mkdir -p var/cache
   # chmod a+rw var/cache
   # mkdir -p var/logs
   # chmod a+rw var/logs
   ```

1. Run the following to build bootstrap.php.cache.

   ```bash
   # php ./vendor/sensio/distribution-bundle/Resources/bin/build_bootstrap.php
   ```

1. Create a database and import data (change db, user, user-pass to your own values):

   ```bash
   # sudo mysql -p
   mysql> create database db;
   mysql> grant all on db.* to 'user'@'localhost' identified by 'user-pass';
   ```

1. Import data

   ```bash
   # mysql -p db < database-backup.sql
   ```

   or, to create a clean database.

   ```bash
   # bin/console doctrine:schema:create
   ```

1. Copy parameters.sh.dist to parameters.sh and app/config/parameters.yml.dist to app/config/parameters.yml. Edit as needed.
   You probably need to change "httpProtocol: https" to "httpProtocol: http" in app/config/parameters.yml if you don't have https setup.

1. Point your browser at web/app_dev.php. For production environment, configure your server to map the root of the webpage to web/app.php.

1. Register an admin user on the webpage and then promote that user to admin:

   ```bash
   # bin/console fos:user:promote admin ROLE_ADMIN
   ```

You should have a working copy by now. Look at var/log/[dev|prod].log to get hints when you have issues.

To push changes to the repo, fork the project, make your changes, then create a pull request.
