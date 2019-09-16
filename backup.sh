#!/bin/sh
SCRIPT_DIR="`dirname $0`"
cd $SCRIPT_DIR
. ./parameters.sh

# Note: now use mysql_config_editor to store login details. The name in the
# --login-path= option has to match what you use for the mysql_config_editor utility.

# Reads options from the named login path in the .mylogin.cnf login path file. A “login
# path” is an option group containing options that specify which MySQL server to connect
# to and which account to authenticate as. To create or modify a login path file, use
# the mysql_config_editor utility. See mysql_config_editor(1).

# Use command like the following to set those values:
#mysql_config_editor set --login-path=myloginname --host=localhost --user=$MYSQL_USER --password

mysqldump --login-path=myloginname $MYSQL_DB > database-backup.sql

BACKUP=../dbbackup-`date +%F_%H%M`.7z
7z a -xr!app/cache -xr!app/logs -xr!var -xr!vendor -xr!.git -xr!\*~ -xr!\*.zip $BACKUP .
cp $BACKUP /mnt/hgfs/vmware/
