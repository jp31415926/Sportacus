#!/bin/sh
SCRIPT_DIR="`dirname $0`"
cd $SCRIPT_DIR
. ./parameters.sh
DIR=../backups/`date +%Y-%m`
BACKUP=dbbackup-`date +%F_%H%M`.sql.7z

#mysqldump $MYSQL_DB -u $MYSQL_USER -p$MYSQL_PASS --skip-extended-insert > database-backup.sql
#mysqldump $MYSQL_DB -u $MYSQL_USER -p$MYSQL_PASS > database-backup.sql

# Note: now use mysql_config_editor to store login details
# Use command like the following to set those values:
#mysql_config_editor set --login-path=symfony --host=localhost --user=$MYSQL_USER --password

mysqldump --login-path=symfony $MYSQL_DB > database-backup.sql
mkdir -p $DIR
7z a $DIR/$BACKUP database-backup.sql > /dev/null
