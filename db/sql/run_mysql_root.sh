#! /bin/sh -x

# This script run sql script on mysql
# USAGE: $0 sql_script

USAGE="$0 sql_script"

if [ "$#" != "1" ]
then
    echo ${USAGE}
    exit 1
else
    sql_f=$1
fi

if [ ! -f "${sql_f}" ]
then
    echo "ERROR: sql_script ${sql_f} does not exist."
    exit 1
fi

MYSQL_USER="root"
MYSQL_DB="ce_schema"
SCRIPT_LOG="./LOG"

if [ ! -d "${SCRIPT_LOG}" ]
then
    echo "ERROR: Log directory ${SCRIPT_LOG} for script does not exist."
    exit 1
fi

filename=`basename ${sql_f}|awk -F. '{print $1}'`
sql_log="${filename}.log"
mysql -u${MYSQL_USER} -p -hlocalhost ${MYSQL_DB} < ${sql_f} 1> ${SCRIPT_LOG}/${sql_log} 2>&1
exit 0
