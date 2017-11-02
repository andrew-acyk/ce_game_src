#!/bin/sh -x

# This script update_config_dynamic_sql.sh modifies mysql dynamic sql configuration file.
# The file contains list of label-value pairs.  The labels
# are labels in the dynamic sql template.  The values are
# corresponding values used in replacing the labels to get
# the final correct dynamic sql statement.
# The labels and values must follow the following conventions:
# NAME_1_LABEL={name_1}
# NAME_1_VALUE=name_1_string
# The label name in the configuration file must be in upper case 
# and ended with _LABEL.
# The label name in the dynamic sql must have the same name in lower case
# enclosed in square brackets.
# The label value in the configuration file must be in upper case
# and ended with _VALUE.
# The label value in the dynamic sql can be any string.

USAGE="Usage: $0 p_dynamic_sql_name p_game_id p_cur_period_id p_game_start_date p_debugl"

if [ "$#" != "5" ]
then
    echo "${USAGE}"
    exit 1
else
    p_dynamic_sql_name=$1
    p_game_id=$2
    p_cur_period_id=$3
    p_game_start_date=$4
    p_debugl=$5
fi

homedir="/home/ubuntu/"
workdir="${homedir}/ce_game_src/db/sql/"
script_f=`basename $0|sed -e "s/.sh//"`
dyn_sql="${p_dynamic_sql_name}"
orig_f="${dyn_sql}.sql.orig"
templ_f="${dyn_sql}.templ"
config_f="${dyn_sql}.cfg"
logdir="${workdir}/LOG"

cd ${workdir}
if [ \( ! -f "${templ_f}" \) -o \( ! -f "${orig_f}" \) -o \( ! -f "${config_f}" \) ]
then
    echo "ERROR: Missing original file, template, or configuration"
    exit 1
#elif [ -f "${config_f}.sav" ]
#then
#    rm ${config_f}.sav
fi

# Names used in configuration file.  Always in label/value pairs
# This script will update the values in the configuration file 
# with values from the input parameters or search values from database table
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
V_NEXT_GAME_PERIOD_ID_LCASE=v_next_game_period_id
V_GAME_PERIOD_ONE_ID_LABEL_NAME=V_GAME_PERIOD_ONE_ID_LABEL 
V_GAME_PERIOD_ONE_ID_VALUE_NAME=V_GAME_PERIOD_ONE_ID_VALUE 
V_GAME_PERIOD_ONE_ID_LCASE=v_game_period_one_id
V_CASE_ERROR_FLOAT_STR_LABEL_NAME=V_CASE_ERROR_FLOAT_STR_LABEL
V_CASE_ERROR_FLOAT_STR_VALUE_NAME=V_CASE_ERROR_FLOAT_STR_VALUE
V_CASE_ERROR_FLOAT_STR_LCASE=v_case_error_float_str
V_CASE_ERROR_INT_STR_LABEL_NAME=V_CASE_ERROR_INT_STR_LABEL
V_CASE_ERROR_INT_STR_VALUE_NAME=V_CASE_ERROR_INT_STR_VALUE
V_CASE_ERROR_INT_STR_LCASE=v_case_error_int_str
V_CASE_ERROR_STR_STR_LABEL_NAME=V_CASE_ERROR_STR_STR_LABEL
V_CASE_ERROR_STR_STR_VALUE_NAME=V_CASE_ERROR_STR_STR_VALUE
V_CASE_ERROR_STR_STR_LCASE=v_case_error_str_str
V_ALLOWNS_ALLOCATED_PERD_PERC_LABEL_NAME=V_ALLOWNS_ALLOCATED_PERD_PERC_LABEL
V_ALLOWNS_ALLOCATED_PERD_PERC_VALUE_NAME=V_ALLOWNS_ALLOCATED_PERD_PERC_VALUE
V_ALLOWNS_ALLOCATED_PERD_PERC_LCASE=v_allowns_allocated_perd_perc
V_CARBON_PRICE_LAST_PERIOD_LABEL_NAME=V_CARBON_PRICE_LAST_PERIOD_LABEL
V_CARBON_PRICE_LAST_PERIOD_VALUE_NAME=V_CARBON_PRICE_LAST_PERIOD_VALUE
V_CARBON_PRICE_LAST_PERIOD_LCASE=v_carbon_price_last_period
V_PROC_NAME_START_OF_PERD_LABEL_NAME=V_PROC_NAME_START_OF_PERD_LABEL
V_PROC_NAME_START_OF_PERD_VALUE_NAME=V_PROC_NAME_START_OF_PERD_VALUE
V_PROC_NAME_START_OF_PERD_LCASE=v_proc_name_start_of_perd
V_PROC_NAME_END_OF_PERD_LABEL_NAME=V_PROC_NAME_END_OF_PERD_LABEL
V_PROC_NAME_END_OF_PERD_VALUE_NAME=V_PROC_NAME_END_OF_PERD_VALUE
V_PROC_NAME_END_OF_PERD_LCASE=v_proc_name_end_of_perd
V_PROC_NAME_START_OF_GAME_LABEL_NAME=V_PROC_NAME_START_OF_GAME_LABEL
V_PROC_NAME_START_OF_GAME_VALUE_NAME=V_PROC_NAME_START_OF_GAME_VALUE
V_PROC_NAME_START_OF_GAME_LCASE=v_proc_name_start_of_game
V_PROC_NAME_END_OF_GAME_LABEL_NAME=V_PROC_NAME_END_OF_GAME_LABEL
V_PROC_NAME_END_OF_GAME_VALUE_NAME=V_PROC_NAME_END_OF_GAME_VALUE
V_PROC_NAME_END_OF_GAME_LCASE=v_proc_name_end_of_game
V_PROC_NAME_SYS_COMPLETN_LABEL_NAME=V_PROC_NAME_SYS_COMPLETN_LABEL
V_PROC_NAME_SYS_COMPLETN_VALUE_NAME=V_PROC_NAME_SYS_COMPLETN_VALUE
V_PROC_NAME_SYS_COMPLETN_LCASE=v_proc_name_sys_completn
V_PROC_NAME_TRADG_RECUR_JOB_LABEL_NAME=V_PROC_NAME_TRADG_RECUR_JOB_LABEL
V_PROC_NAME_TRADG_RECUR_JOB_VALUE_NAME=V_PROC_NAME_TRADG_RECUR_JOB_VALUE
V_PROC_NAME_TRADG_RECUR_JOB_LCASE=v_proc_name_tradg_recur_job
V_CUR_GAME_PERIOD_ID_LABEL_NAME=V_CUR_GAME_PERIOD_ID_LABEL
V_CUR_GAME_PERIOD_ID_VALUE_NAME=V_CUR_GAME_PERIOD_ID_VALUE
V_CUR_GAME_PERIOD_ID_LCASE=v_cur_game_period_id
V_EMISSIONS_CAP_PERD_PERC_LABEL_NAME=V_EMISSIONS_CAP_PERD_PERC_LABEL
V_EMISSIONS_CAP_PERD_PERC_VALUE_NAME=V_EMISSIONS_CAP_PERD_PERC_VALUE
V_EMISSIONS_CAP_PERD_PERC_LCASE=v_emissions_cap_perd_perc
# Parameter names use in ce_configuration_parameter table
DB_START_OF_GAME_NAME=ce_proc_name_start_of_game
DB_END_OF_GAME_NAME=ce_proc_name_end_of_game
DB_START_OF_PERD_NAME=ce_proc_name_start_of_perd
DB_END_OF_PERD_NAME=ce_proc_name_end_of_perd
DB_TRADG_RECUR_JOB_NAME=ce_proc_name_tradg_recur_job
DB_SYS_COMPLETN_NAME=ce_proc_name_sys_completn
DB_ALLOWNS_ALLOCATED_PERD_PERC=ce_allowns_allocated_perd_perc
DB_CARBON_PRICE_LAST_PERD=carbon_price_last_period
DB_EMISSIONS_CAP_PERD_PERC=ce_emissions_cap_perd_perc
# For sql files and logs of internal functions
FIND_ALLOWNS_ALLOCATED_PERD_PERC="find_allowns_allocated_perd_perc"
FIND_NEXT_GAME_PERIOD_ID="find_next_game_period_id"
FIND_GAME_PERIOD_ONE_ID="find_game_period_one_id"
FIND_CARBON_PRICE_LAST_PERIOD="find_carbon_price_last_period"
FIND_PROC_NAME_START_OF_PERD="find_proc_name_start_of_perd"
FIND_PROC_NAME_END_OF_PERD="find_proc_name_end_of_perd"
FIND_PROC_NAME_START_OF_GAME="find_proc_name_start_of_game"
FIND_PROC_NAME_END_OF_GAME="find_proc_name_end_of_game"
FIND_PROC_NAME_SYS_COMPLETN="find_proc_name_sys_completn"
FIND_PROC_NAME_TRADG_RECUR_JOB="find_proc_name_tradg_recur_job"
FIND_EMISSIONS_CAP_PERD_PERC="find_emissions_cap_perd_perc"
# Other constant variables
VAR_P_GAME_ID=p_game_id
VAR_P_CUR_PERIOD_ID=p_cur_period_id
VAR_P_GAME_START_DATE=p_game_start_date
VAR_P_DEBUGL=p_debugl
SEP=":"
DASH="-"
LABEL_SUFFIX=LABEL
VALUE_SUFFIX=VALUE
LIST_OF_INPUT_VARS="x${VAR_P_GAME_ID}x${VAR_P_CUR_PERIOD_ID}x${VAR_P_GAME_START_DATE}x${VAR_P_DEBUGL}x"

find_value () {
    name=$1
    if [ "${name}" = "${V_NEXT_GAME_PERIOD_ID_LCASE}" ]
    then
        next_period=`expr ${p_cur_period_id} + 1`
        value1="${p_game_id}${DASH}${next_period}"
        echo "${value1}"
    elif [ "${name}" = "${V_GAME_PERIOD_ONE_ID_LCASE}" ]
    then
        value1="${p_game_id}${DASH}1"
        echo "${value1}"
    elif [ "${name}" = "${V_CUR_GAME_PERIOD_ID_LCASE}" ]
    then
        value1="${p_game_id}${DASH}${p_cur_period_id}"
        echo "${value1}"
    elif [ "${name}" = "${V_ALLOWNS_ALLOCATED_PERD_PERC_LCASE}" ]
    then
        value1=`find_allowns_allocated_perd_perc`
        echo "${value1}"
    elif [ "${name}" = "${V_CARBON_PRICE_LAST_PERIOD_LCASE}" ]
    then
        value1=`find_carbon_price_last_period ${p_game_id}`
        echo "${value1}"
    elif [ "${name}" = "${V_PROC_NAME_START_OF_PERD_LCASE}" ]
    then
        value1=`find_proc_name_start_of_perd`
        echo "${value1}"
    elif [ "${name}" = "${V_PROC_NAME_END_OF_PERD_LCASE}" ]
    then
        value1=`find_proc_name_end_of_perd`
        echo "${value1}"
    elif [ "${name}" = "${V_PROC_NAME_START_OF_GAME_LCASE}" ]
    then
        value1=`find_proc_name_start_of_game`
        echo "${value1}"
    elif [ "${name}" = "${V_PROC_NAME_END_OF_GAME_LCASE}" ]
    then
        value1=`find_proc_name_end_of_game`
        echo "${value1}"
    elif [ "${name}" = "${V_PROC_NAME_TRADG_RECUR_JOB_LCASE}" ]
    then
        value1=`find_proc_name_tradg_recur_job`
        echo "${value1}"
    elif [ "${name}" = "${V_PROC_NAME_SYS_COMPLETN_LCASE}" ]
    then
        value1=`find_proc_name_sys_completn` 
        echo "${value1}"
    elif [ "${name}" = "${V_EMISSIONS_CAP_PERD_PERC_LCASE}" ]
    then
        value1=`find_emissions_cap_perd_perc` 
        echo "${value1}"
    else
        echo "WARNG: Unrecognized name ${name}"
        exit 2
    fi
}

find_allowns_allocated_perd_perc () {
    rm -rf ${logdir}/${FIND_ALLOWNS_ALLOCATED_PERD_PERC}.sql ${logdir}/${FIND_ALLOWNS_ALLOCATED_PERD_PERC}.log
    cat > ${logdir}/${FIND_ALLOWNS_ALLOCATED_PERD_PERC}.sql << END0
use ce_schema;
select concat('${DB_ALLOWNS_ALLOCATED_PERD_PERC}${SEP}', concat(parameter_value, '${SEP}')) from ce_configuration_parameter
where parameter_name = '${DB_ALLOWNS_ALLOCATED_PERD_PERC}';
END0
    ./run_mysql.sh ${logdir}/${FIND_ALLOWNS_ALLOCATED_PERD_PERC}.sql
    value=`cat ${logdir}/${FIND_ALLOWNS_ALLOCATED_PERD_PERC}.log|grep -i "${DB_ALLOWNS_ALLOCATED_PERD_PERC}${SEP}"|sed -e "s/^.*${DB_ALLOWNS_ALLOCATED_PERD_PERC}${SEP}//"|sed -e "s/${SEP}//"`
    echo "${value}"
}

find_carbon_price_last_period () {
    game_id1=$1
    rm -rf ${logdir}/${FIND_CARBON_PRICE_LAST_PERIOD}.sql ${logdir}/${FIND_CARBON_PRICE_LAST_PERIOD}.log
    cat > ${logdir}/${FIND_CARBON_PRICE_LAST_PERIOD}.sql << END1
use ce_schema;
select concat('${DB_CARBON_PRICE_LAST_PERD}${SEP}', concat(carbon_price_last_perd, '${SEP}')) from ce_game
where game_id = '${game_id1}';
END1
    ./run_mysql.sh ${logdir}/${FIND_CARBON_PRICE_LAST_PERIOD}.sql
    value=`cat ${logdir}/${FIND_CARBON_PRICE_LAST_PERIOD}.log|grep -i "${DB_CARBON_PRICE_LAST_PERD}${SEP}"|sed -e "s/^.*${DB_CARBON_PRICE_LAST_PERD}${SEP}//"|sed -e "s/${SEP}//"`
    echo "${value}"
}

find_proc_name_start_of_game () {
    rm -rf ${logdir}/${FIND_PROC_NAME_START_OF_GAME}.sql ${logdir}/${FIND_PROC_NAME_START_OF_GAME}.log
    cat > ${logdir}/${FIND_PROC_NAME_START_OF_GAME}.sql << END2
use ce_schema;
select concat('${DB_START_OF_GAME_NAME}${SEP}', concat(parameter_value, '${SEP}')) from ce_configuration_parameter
where parameter_name = '${DB_START_OF_GAME_NAME}';
END2
    ./run_mysql.sh ${logdir}/${FIND_PROC_NAME_START_OF_GAME}.sql
    value=`cat ${logdir}/${FIND_PROC_NAME_START_OF_GAME}.log|grep -i "${DB_START_OF_GAME_NAME}${SEP}"|sed -e "s/^.*${DB_START_OF_GAME_NAME}${SEP}//"|sed -e "s/${SEP}//"`
    echo "${value}"
}

find_proc_name_start_of_perd () {
    rm -rf ${logdir}/${FIND_PROC_NAME_START_OF_PERD}.sql ${logdir}/${FIND_PROC_NAME_START_OF_PERD}.log
    cat > ${logdir}/${FIND_PROC_NAME_START_OF_PERD}.sql << END3
use ce_schema;
select concat('${DB_START_OF_PERD_NAME}${SEP}', concat(parameter_value, '${SEP}')) from ce_configuration_parameter
where parameter_name = '${DB_START_OF_PERD_NAME}';
END3
    ./run_mysql.sh ${logdir}/${FIND_PROC_NAME_START_OF_PERD}.sql
    value=`cat ${logdir}/${FIND_PROC_NAME_START_OF_PERD}.log|grep -i "${DB_START_OF_PERD_NAME}${SEP}"|sed -e "s/^.*${DB_START_OF_PERD_NAME}${SEP}//"|sed -e "s/${SEP}//"`
    echo "${value}"
}

find_proc_name_end_of_perd () {
    rm -rf ${logdir}/${FIND_PROC_NAME_END_OF_PERD}.sql ${logdir}/${FIND_PROC_NAME_END_OF_PERD}.log
    cat > ${logdir}/${FIND_PROC_NAME_END_OF_PERD}.sql << END4
use ce_schema;
select concat('${DB_END_OF_PERD_NAME}${SEP}', concat(parameter_value, '${SEP}')) from ce_configuration_parameter
where parameter_name = '${DB_END_OF_PERD_NAME}';
END4
    ./run_mysql.sh ${logdir}/${FIND_PROC_NAME_END_OF_PERD}.sql
    value=`cat ${logdir}/${FIND_PROC_NAME_END_OF_PERD}.log|grep -i "${DB_END_OF_PERD_NAME}${SEP}"|sed -e "s/^.*${DB_END_OF_PERD_NAME}${SEP}//"|sed -e "s/${SEP}//"`
    echo "${value}"
}

find_proc_name_end_of_game () {
    rm -rf ${logdir}/${FIND_PROC_NAME_END_OF_GAME}.sql ${logdir}/${FIND_PROC_NAME_END_OF_GAME}.log
    cat > ${logdir}/${FIND_PROC_NAME_END_OF_GAME}.sql << END5
use ce_schema;
select concat('${DB_END_OF_GAME_NAME}${SEP}', concat(parameter_value, '${SEP}')) from ce_configuration_parameter
where parameter_name = '${DB_END_OF_GAME_NAME}';
END5
    ./run_mysql.sh ${logdir}/${FIND_PROC_NAME_END_OF_GAME}.sql
    value=`cat ${logdir}/${FIND_PROC_NAME_END_OF_GAME}.log|grep -i "${DB_END_OF_GAME_NAME}${SEP}"|sed -e "s/^.*${DB_END_OF_GAME_NAME}${SEP}//"|sed -e "s/${SEP}//"`
    echo "${value}"
}

find_proc_name_tradg_recur_job () {
    rm -rf ${logdir}/${FIND_PROC_NAME_TRADG_RECUR_JOB}.sql ${logdir}/${FIND_PROC_NAME_TRADG_RECUR_JOB}.log
    cat > ${logdir}/${FIND_PROC_NAME_TRADG_RECUR_JOB}.sql << END6
use ce_schema;
select concat('${DB_TRADG_RECUR_JOB_NAME}${SEP}', concat(parameter_value, '${SEP}')) from ce_configuration_parameter
where parameter_name = '${DB_TRADG_RECUR_JOB_NAME}';
END6
    ./run_mysql.sh ${logdir}/${FIND_PROC_NAME_TRADG_RECUR_JOB}.sql
    value=`cat ${logdir}/${FIND_PROC_NAME_TRADG_RECUR_JOB}.log|grep -i "${DB_TRADG_RECUR_JOB_NAME}${SEP}"|sed -e "s/^.*${DB_TRADG_RECUR_JOB_NAME}${SEP}//"|sed -e "s/${SEP}//"`
    echo "${value}"
}

find_proc_name_sys_completn () {
    rm -rf ${logdir}/${FIND_PROC_NAME_SYS_COMPLETN}.sql ${logdir}/${FIND_PROC_NAME_SYS_COMPLETN}.log
    cat > ${logdir}/${FIND_PROC_NAME_SYS_COMPLETN}.sql << END7
use ce_schema;
select concat('${DB_SYS_COMPLETN_NAME}${SEP}', concat(parameter_value, '${SEP}')) from ce_configuration_parameter
where parameter_name = '${DB_SYS_COMPLETN_NAME}';
END7
    ./run_mysql.sh ${logdir}/${FIND_PROC_NAME_SYS_COMPLETN}.sql
    value=`cat ${logdir}/${FIND_PROC_NAME_SYS_COMPLETN}.log|grep -i "${DB_SYS_COMPLETN_NAME}${SEP}"|sed -e "s/^.*${DB_SYS_COMPLETN_NAME}${SEP}//"|sed -e "s/${SEP}//"`
    echo "${value}"
}

find_emissions_cap_perd_perc () {
    rm -rf ${logdir}/${FIND_EMISSIONS_CAP_PERD_PERC}.sql ${logdir}/${FIND_EMISSIONS_CAP_PERD_PERC}.log
    cat > ${logdir}/${FIND_EMISSIONS_CAP_PERD_PERC}.sql << END8
use ce_schema;
select concat('${DB_EMISSIONS_CAP_PERD_PERC}${SEP}', concat(parameter_value, '${SEP}')) from ce_configuration_parameter
where parameter_name = '${DB_EMISSIONS_CAP_PERD_PERC}';
END8
    ./run_mysql.sh ${logdir}/${FIND_EMISSIONS_CAP_PERD_PERC}.sql
    value=`cat ${logdir}/${FIND_EMISSIONS_CAP_PERD_PERC}.log|grep -i "${DB_EMISSIONS_CAP_PERD_PERC}${SEP}"|sed -e "s/^.*${DB_EMISSIONS_CAP_PERD_PERC}${SEP}//"|sed -e "s/${SEP}//"`
    echo "${value}"
}


# Find all the labels (encluded in square brackets) in the configuration file
list_of_labels=`cat ${config_f}|grep -i "${LABEL_SUFFIX}="|sed -e "s/^[^=]*=//"|sed -e "s/{//"|sed -e "s/}//"`
for i in ${list_of_labels}
do
    i_value_name=`echo ${i}|tr "a-z" "A-Z"`
    i_value_name="${i_value_name}_${VALUE_SUFFIX}"
    # Check if the i_value is one of the input variables or not
    in_list=`echo ${LIST_OF_INPUT_VARS}|grep -i "x${i}x"`
    if [ "${in_list}" != "" ]
    then
        if [ "${i}" = "${VAR_P_GAME_ID}" ]
        then
            i_value=${p_game_id}
        elif [ "${i}" = "${VAR_P_CUR_PERIOD_ID}" ]
        then
            i_value=${p_cur_period_id}
        elif [ "${i}" = "${VAR_P_GAME_START_DATE}" ]
        then
            i_value=${p_game_start_date}
        elif [ "${i}" = "${VAR_P_DEBUGL}" ]
        then
            i_value=${p_debugl}
        else
            echo "ERROR: Unrecognized input parameter ${i}"
            exit 1
        fi
    else
        i_value=`find_value "${i}"`
        retval="$?"
        if [ "${retval}" = "1" ]
        then
            exit 1
        elif [ "${retval}" = "2" ]
        then
            continue
        fi
    fi
    i_value_str="${i_value_name}=${i_value}"
    if [ ! -f "${config_f}.sav" ]
    then
        cp ${config_f} ${config_f}.sav
    fi
    # Write i_value_str to configuration file
    cat ${config_f}|sed -e "s/^${i_value_name}=.*$/${i_value_str}/" > /tmp/${script_f}$$_f
    mv /tmp/${script_f}$$_f ${config_f}
done



