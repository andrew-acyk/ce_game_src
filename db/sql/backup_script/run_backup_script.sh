#!/bin/sh -x

# This script backs up database based to input parameter.

USAGE="Usage: $0 initialGameBuild"
if [ "$#" = "1" ]
then
    par1=$1
else
    echo "ERROR: Missing parameter."
    echo $USAGE
    exit 1
fi

fname=`basename $0|awk -F. '{print $1}'`
homedir="."
bindir="/home/ubuntu/ce_game_src/db/sql/"
tmpdir="${homedir}/LOG/"
tmpsql="${tmpdir}/${fname}_${par1}$$.sql"
tmpf="${tmpdir}/${fname}_${par1}$$.log"

if [ \( ! -d "${homedir}" \) -o \( ! -d "${bindir}" \) -o \( ! -d "${tmpdir}" \) ]
then
    echo "ERROR: one of the following directories does not exist"
    echo "${homedir}, ${bindir}, ${tmpdir}"
    exit 1
fi

cd ${homedir}
mmdd=`date +%m%d`
rm -f ${tmpdir}/${fname}*.*
touch ${tmpsql}
if [ "${par1}" = "initialGameBuild" ]
then
    # create backup tables for
    # ce_game, ce_account, ce_company, ce_account_company_plant_map,
    cat >> ${tmpsql} <<EOD1
create table ce_game_${mmdd}
as select * from ce_game;
create table ce_account_${mmdd}
as select * from ce_account;
create table ce_company_${mmdd}
as select * from ce_company;
create table ce_account_company_plant_map_${mmdd}
as select * from ce_account_company_plant_map;
EOD1
fi

${bindir}/run_mysql_root.sh ${tmpsql} 1> ${tmpf} 2>&1
