#!/bin/sh
SCRIPT_DIR="`dirname $0`"
cd $SCRIPT_DIR
. ./parameters.sh

mysql --user=$MYSQL_USER --password=$MYSQL_PASS $MYSQL_DB < database-backup.sql
