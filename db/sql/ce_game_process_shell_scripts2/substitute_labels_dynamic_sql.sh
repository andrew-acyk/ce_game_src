#!/bin/sh -x

# This script substitute_labels_dynamic_sql.sh replaces labels with values in the dynamic sql template.
# It is difficult to construct dynamic sql statements in mysql stored procedure.
# The stored procedure is changed to be a template.  It contains labels to be replaced
# by correct string values at runtime to form dynamic sql statements.

USAGE="Usage: $0 p_dynamic_sql_name"

if [ "$#" != "1" ]
then
    echo "${USAGE}"
    exit 1
else
    p_dynamic_sql_name=$1
fi

homedir="/home/ubuntu/"
workdir="${homedir}/ce_game_src/db/sql/"
dyn_sql="${p_dynamic_sql_name}"
script_f=`basename $0|sed -e "s/.sh//"`
orig_f="${dyn_sql}.sql.orig"
templ_f="${dyn_sql}.templ"
config_f="${dyn_sql}.cfg"
sql_f1="${dyn_sql}.sql1"
sql_f="${dyn_sql}.sql"

cd ${workdir}
if [ \( ! -f "${templ_f}" \) -o \( ! -f "${orig_f}" \) -o \( ! -f "${config_f}" \) ]
then
    echo "ERROR: Missing original file, template, or configuration"
    exit 1
fi

# Names used in configuration file
P_GAME_ID_LABEL_NAME=P_GAME_ID_LABEL
P_GAME_ID_VALUE_NAME=P_GAME_ID_VALUE
P_CUR_PERIOD_ID_LABEL_NAME=P_CUR_PERIOD_ID_LABEL 
P_CUR_PERIOD_ID_VALUE_NAME=P_CUR_PERIOD_ID_VALUE 
P_GAME_START_DATE_LABEL_NAME=P_GAME_START_DATE_LABEL 
P_GAME_START_DATE_VALUE_NAME=P_GAME_START_DATE_VALUE 
P_DEBUGL_LABEL_NAME=P_DEBUGL_LABEL 
P_DEBUGL_VALUE_NAME=P_DEBUGL_VALUE 
V_NEXT_GAME_PERIOD_ID_LABEL_NAME=V_NEXT_GAME_PERIOD_ID_LABEL 
V_NEXT_GAME_PERIOD_ID_VALUE_NAME=V_NEXT_GAME_PERIOD_ID_VALUE 
V_GAME_PERIOD_ONE_ID_LABEL_NAME=V_GAME_PERIOD_ONE_ID_LABEL 
V_GAME_PERIOD_ONE_ID_VALUE_NAME=V_GAME_PERIOD_ONE_ID_VALUE 
V_CASE_ERROR_FLOAT_STR_LABEL_NAME=V_CASE_ERROR_FLOAT_STR_LABEL
V_CASE_ERROR_FLOAT_STR_VALUE_NAME=V_CASE_ERROR_FLOAT_STR_VALUE
V_ALLOWNS_ALLOCATED_PERD_PERC_LABEL_NAME=V_ALLOWNS_ALLOCATED_PERD_PERC_LABEL
V_ALLOWNS_ALLOCATED_PERD_PERC_VALUE_NAME=V_ALLOWNS_ALLOCATED_PERD_PERC_VALUE
V_CARBON_PRICE_LAST_PERIOD_LABEL_NAME=V_CARBON_PRICE_LAST_PERIOD_LABEL
V_CARBON_PRICE_LAST_PERIOD_VALUE_NAME=V_CARBON_PRICE_LAST_PERIOD_VALUE
V_PROC_NAME_START_OF_PERD_LABEL_NAME=V_PROC_NAME_START_OF_PERD_LABEL
V_PROC_NAME_START_OF_PERD_VALUE_NAME=V_PROC_NAME_START_OF_PERD_VALUE
# Other constant variables
LABEL_SUFFIX=LABEL
VALUE_SUFFIX=VALUE

cp ${templ_f} ${sql_f1}
# Find all the labels (encluded in square brackets) in the configuration file
list_of_labels=`cat ${config_f}|grep -i "${LABEL_SUFFIX}="|sed -e "s/^[^=]*=//"|sed -e "s/{//"|sed -e "s/}//"`
for i in ${list_of_labels}
do
    i_label="{${i}}"
    i_value_name=`echo ${i}|tr "a-z" "A-Z"`
    i_value_name="${i_value_name}_${VALUE_SUFFIX}="
    i_value=`cat ${config_f}|grep -i ${i_value_name}|sed -e "s/${i_value_name}//"`
    # Replace the label with its corresponding value
    cat ${sql_f1}|sed -e "s/${i_label}/${i_value}/g" > /tmp/${script_f}$$_f
    cp /tmp/${script_f}$$_f ${sql_f1}
done
cp ${sql_f1} ${sql_f}
