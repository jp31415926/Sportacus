#!/bin/sh
SCRIPT_DIR="`dirname $0`"
cd $SCRIPT_DIR
. ./parameters.sh

# Note: now use mysql_config_editor to store login details
# Use command like the following to set those values:
#mysql_config_editor set --login-path=symfony --host=localhost --user=$MYSQL_USER --password

mysqldump --login-path=symfony $MYSQL_DB > database-backup.sql

rm -fr avro
cp -a vendor/avro .
BACKUP=../Symfony-`date +%F_%H%M`.7z
7z a -xr!app/cache -xr!app/logs -xr!var -xr!vendor -xr!.git -xr!\*~ -xr!\*.zip $BACKUP .
cp $BACKUP /mnt/hgfs/vmware/
