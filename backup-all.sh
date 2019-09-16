#!/bin/sh
SCRIPT_DIR="`dirname $0`"
cd $SCRIPT_DIR
. ./parameters.sh

# Note: now use mysql_config_editor to store login details
# Use command like the following to set those values:
#mysql_config_editor set --login-path=myloginname --host=localhost --user=$MYSQL_USER --password

mysqldump --login-path=myloginname $MYSQL_DB --skip-extended-insert > database-backup.sql

BACKUP=../Symfony-`date +%F_%H%M`.7z
7z a -xr!app/cache -xr!app/logs -xr!var -xr!app/config/parameters.yml  -xr!\*~ -xr!.git $BACKUP .
cp $BACKUP /mnt/hgfs/vmware/
