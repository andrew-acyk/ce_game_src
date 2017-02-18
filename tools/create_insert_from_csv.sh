#!/bin/sh -x

# This script turns .csv file into insert statement.
# You have to define two variables.  The first one
# defines the name of the csv file.  The second one
# defines the first part of the insert statement 
# from the beginning to the values clause

table_name="revappbiii.wdev_task_workstream_map"
insert1="insert into ${table_name}"
insert2="(v_task_num, v_task_name, v_process_name, v_run_name, v_workstream, f_workstream_start_task_flag, f_workstream_end_task_flag, v_task_name_token_spec_to_ws, f_unique_task_name_flag, v_task_status) values "
insert="${insert1} ${insert2}"

usage="Usage: $0 num_fields csv_file_path"
if [ "$#" != "2" ]
then
    echo "ERROR: Missing parameters."
    echo ${usage}
    exit 1
else
    num_fields=$1
    csv_file_path=$2
fi

if [ ! -f "${csv_file_path}" ]
then
    echo "ERROR: Missing file ${csv_file_path}."
    exit 1
else
    outf_n=`echo ${csv_file_path}|sed -e 's/\.csv//'`
    outf="${outf_n}.sql"
    outf1="${outf_n}1.sql"
    rm -f ${outf} ${outf1}
fi

# This will fail if num_fields is not numeric
res=`expr ${num_fields} + 0`

res=`cat ${csv_file_path}|head -1|awk -F, '{print NF}'`
if [ "${res}" -lt "${num_fields}" ]
then
    echo "ERROR: ${csv_file_path} has different number of fields."
    exit 1
fi

total_lines=`wc -l ${csv_file_path}|awk '{print $1}'`
# minus header line
total_lines=`expr ${total_lines} - 1`
if [ "${num_fields}" = "1" ]
then
    # Use <SQ> instead of single quote because of quoting difficulty in awk
    # Also Use <INS> to replace the whole insert clause
    tail -${total_lines} ${csv_file_path}|awk -F, '{print "<INS> (<SQ>" $1 "<SQ>); " }' > ${outf}
    sed -e "1,\$s/<SQ>/\'/g" ${outf}|sed -e "1,\$s/<INS>/${insert}/g" > ${outf1}
elif [ "${num_fields}" = "2" ]
then
    # Use <SQ> instead of single quote because of quoting difficulty in awk
    # Also Use <INS> to replace the whole insert clause
    tail -${total_lines} ${csv_file_path}|awk -F, '{print "<INS> (<SQ>" $1 "<SQ>, <SQ>" $2 "<SQ>); " }' > ${outf}
    sed -e "1,\$s/<SQ>/\'/g" ${outf}|sed -e "1,\$s/<INS>/${insert}/g" > ${outf1}
elif [ "${num_fields}" = "3" ]
then
    # Use <SQ> instead of single quote because of quoting difficulty in awk
    # Also Use <INS> to replace the whole insert clause
    tail -${total_lines} ${csv_file_path}|awk -F, '{print "<INS> (<SQ>" $1 "<SQ>, <SQ>" $2 "<SQ>, <SQ>" $3 "<SQ>); " }' > ${outf}
    sed -e "1,\$s/<SQ>/\'/g" ${outf}|sed -e "1,\$s/<INS>/${insert}/g" > ${outf1}
elif [ "${num_fields}" = "4" ]
then
    # Use <SQ> instead of single quote because of quoting difficulty in awk
    # Also Use <INS> to replace the whole insert clause
    tail -${total_lines} ${csv_file_path}|awk -F, '{print "<INS> (<SQ>" $1 "<SQ>, <SQ>" $2 "<SQ>, <SQ>" $3 "<SQ>, <SQ>" $4 "<SQ>); " }' > ${outf}
    sed -e "1,\$s/<SQ>/\'/g" ${outf}|sed -e "1,\$s/<INS>/${insert}/g" > ${outf1}
elif [ "${num_fields}" = "5" ]
then
    # Use <SQ> instead of single quote because of quoting difficulty in awk
    # Also Use <INS> to replace the whole insert clause
    tail -${total_lines} ${csv_file_path}|awk -F, '{print "<INS> (<SQ>" $1 "<SQ>, <SQ>" $2 "<SQ>, <SQ>" $3 "<SQ>, <SQ>" $4 "<SQ>, <SQ>" $5 "<SQ>); " }' > ${outf}
    sed -e "1,\$s/<SQ>/\'/g" ${outf}|sed -e "1,\$s/<INS>/${insert}/g" > ${outf1}
elif [ "${num_fields}" = "6" ]
then
    # Use <SQ> instead of single quote because of quoting difficulty in awk
    # Also Use <INS> to replace the whole insert clause
    tail -${total_lines} ${csv_file_path}|awk -F, '{print "<INS> (<SQ>" $1 "<SQ>, <SQ>" $2 "<SQ>, <SQ>" $3 "<SQ>, <SQ>" $4 "<SQ>, <SQ>" $5 "<SQ>, <SQ>" $6 "<SQ>); " }' > ${outf}
    sed -e "1,\$s/<SQ>/\'/g" ${outf}|sed -e "1,\$s/<INS>/${insert}/g" > ${outf1}
elif [ "${num_fields}" = "7" ]
then
    # Use <SQ> instead of single quote because of quoting difficulty in awk
    # Also Use <INS> to replace the whole insert clause
    tail -${total_lines} ${csv_file_path}|awk -F, '{print "<INS> (<SQ>" $1 "<SQ>, <SQ>" $2 "<SQ>, <SQ>" $3 "<SQ>, <SQ>" $4 "<SQ>, <SQ>" $5 "<SQ>, <SQ>" $6 "<SQ>, <SQ>" $7 "<SQ>); " }' > ${outf}
    sed -e "1,\$s/<SQ>/\'/g" ${outf}|sed -e "1,\$s/<INS>/${insert}/g" > ${outf1}
elif [ "${num_fields}" = "8" ]
then
    # Use <SQ> instead of single quote because of quoting difficulty in awk
    # Also Use <INS> to replace the whole insert clause
    tail -${total_lines} ${csv_file_path}|awk -F, '{print "<INS> (<SQ>" $1 "<SQ>, <SQ>" $2 "<SQ>, <SQ>" $3 "<SQ>, <SQ>" $4 "<SQ>, <SQ>" $5 "<SQ>, <SQ>" $6 "<SQ>, <SQ>" $7 "<SQ>, <SQ>" $8 "<SQ>); " }' > ${outf}
    sed -e "1,\$s/<SQ>/\'/g" ${outf}|sed -e "1,\$s/<INS>/${insert}/g" > ${outf1}
elif [ "${num_fields}" = "9" ]
then
    # Use <SQ> instead of single quote because of quoting difficulty in awk
    # Also Use <INS> to replace the whole insert clause
    tail -${total_lines} ${csv_file_path}|awk -F, '{print "<INS> (<SQ>" $1 "<SQ>, <SQ>" $2 "<SQ>, <SQ>" $3 "<SQ>, <SQ>" $4 "<SQ>, <SQ>" $5 "<SQ>, <SQ>" $6 "<SQ>, <SQ>" $7 "<SQ>, <SQ>" $8 "<SQ>, <SQ>" $9 "<SQ>); " }' > ${outf}
    sed -e "1,\$s/<SQ>/\'/g" ${outf}|sed -e "1,\$s/<INS>/${insert}/g" > ${outf1}
elif [ "${num_fields}" = "10" ]
then
    # Use <SQ> instead of single quote because of quoting difficulty in awk
    # Also Use <INS> to replace the whole insert clause
    tail -${total_lines} ${csv_file_path}|awk -F, '{print "<INS> (<SQ>" $1 "<SQ>, <SQ>" $2 "<SQ>, <SQ>" $3 "<SQ>, <SQ>" $4 "<SQ>, <SQ>" $5 "<SQ>, <SQ>" $6 "<SQ>, <SQ>" $7 "<SQ>, <SQ>" $8 "<SQ>, <SQ>" $9 "<SQ>, <SQ>" $10 "<SQ>); " }' > ${outf}
    sed -e "1,\$s/<SQ>/\'/g" ${outf}|sed -e "1,\$s/<INS>/${insert}/g" > ${outf1}
elif [ "${num_fields}" = "11" ]
then
    # Use <SQ> instead of single quote because of quoting difficulty in awk
    # Also Use <INS> to replace the whole insert clause
    tail -${total_lines} ${csv_file_path}|awk -F, '{print "<INS> (<SQ>" $1 "<SQ>, <SQ>" $2 "<SQ>, <SQ>" $3 "<SQ>, <SQ>" $4 "<SQ>, <SQ>" $5 "<SQ>, <SQ>" $6 "<SQ>, <SQ>" $7 "<SQ>, <SQ>" $8 "<SQ>, <SQ>" $9 "<SQ>, <SQ>" $10 "<SQ>, <SQ>" $11 "<SQ>); " }' > ${outf}
    sed -e "1,\$s/<SQ>/\'/g" ${outf}|sed -e "1,\$s/<INS>/${insert}/g" > ${outf1}
elif [ "${num_fields}" = "12" ]
then
    # Use <SQ> instead of single quote because of quoting difficulty in awk
    # Also Use <INS> to replace the whole insert clause
    tail -${total_lines} ${csv_file_path}|awk -F, '{print "<INS> (<SQ>" $1 "<SQ>, <SQ>" $2 "<SQ>, <SQ>" $3 "<SQ>, <SQ>" $4 "<SQ>, <SQ>" $5 "<SQ>, <SQ>" $6 "<SQ>, <SQ>" $7 "<SQ>, <SQ>" $8 "<SQ>, <SQ>" $9 "<SQ>, <SQ>" $10 "<SQ>, <SQ>" $11 "<SQ>, <SQ>" $12 "<SQ>); " }' > ${outf}
    sed -e "1,\$s/<SQ>/\'/g" ${outf}|sed -e "1,\$s/<INS>/${insert}/g" > ${outf1}
elif [ "${num_fields}" = "13" ]
then
    # Use <SQ> instead of single quote because of quoting difficulty in awk
    # Also Use <INS> to replace the whole insert clause
    tail -${total_lines} ${csv_file_path}|awk -F, '{print "<INS> (<SQ>" $1 "<SQ>, <SQ>" $2 "<SQ>, <SQ>" $3 "<SQ>, <SQ>" $4 "<SQ>, <SQ>" $5 "<SQ>, <SQ>" $6 "<SQ>, <SQ>" $7 "<SQ>, <SQ>" $8 "<SQ>, <SQ>" $9 "<SQ>, <SQ>" $10 "<SQ>, <SQ>" $11 "<SQ>, <SQ>" $12 "<SQ>, <SQ>" $13 "<SQ>); " }' > ${outf}
    sed -e "1,\$s/<SQ>/\'/g" ${outf}|sed -e "1,\$s/<INS>/${insert}/g" > ${outf1}
elif [ "${num_fields}" = "14" ]
then
    # Use <SQ> instead of single quote because of quoting difficulty in awk
    # Also Use <INS> to replace the whole insert clause
    tail -${total_lines} ${csv_file_path}|awk -F, '{print "<INS> (<SQ>" $1 "<SQ>, <SQ>" $2 "<SQ>, <SQ>" $3 "<SQ>, <SQ>" $4 "<SQ>, <SQ>" $5 "<SQ>, <SQ>" $6 "<SQ>, <SQ>" $7 "<SQ>, <SQ>" $8 "<SQ>, <SQ>" $9 "<SQ>, <SQ>" $10 "<SQ>, <SQ>" $11 "<SQ>, <SQ>" $12 "<SQ>, <SQ>" $13 "<SQ>, <SQ>" $14 "<SQ>); " }' > ${outf}
    sed -e "1,\$s/<SQ>/\'/g" ${outf}|sed -e "1,\$s/<INS>/${insert}/g" > ${outf1}
elif [ "${num_fields}" = "15" ]
then
    # Use <SQ> instead of single quote because of quoting difficulty in awk
    # Also Use <INS> to replace the whole insert clause
    tail -${total_lines} ${csv_file_path}|awk -F, '{print "<INS> (<SQ>" $1 "<SQ>, <SQ>" $2 "<SQ>, <SQ>" $3 "<SQ>, <SQ>" $4 "<SQ>, <SQ>" $5 "<SQ>, <SQ>" $6 "<SQ>, <SQ>" $7 "<SQ>, <SQ>" $8 "<SQ>, <SQ>" $9 "<SQ>, <SQ>" $10 "<SQ>, <SQ>" $11 "<SQ>, <SQ>" $12 "<SQ>, <SQ>" $13 "<SQ>, <SQ>" $14 "<SQ>, <SQ>" $15 "<SQ>); " }' > ${outf}
    sed -e "1,\$s/<SQ>/\'/g" ${outf}|sed -e "1,\$s/<INS>/${insert}/g" > ${outf1}
elif [ "${num_fields}" = "16" ]
then
    # Use <SQ> instead of single quote because of quoting difficulty in awk
    # Also Use <INS> to replace the whole insert clause
    tail -${total_lines} ${csv_file_path}|awk -F, '{print "<INS> (<SQ>" $1 "<SQ>, <SQ>" $2 "<SQ>, <SQ>" $3 "<SQ>, <SQ>" $4 "<SQ>, <SQ>" $5 "<SQ>, <SQ>" $6 "<SQ>, <SQ>" $7 "<SQ>, <SQ>" $8 "<SQ>, <SQ>" $9 "<SQ>, <SQ>" $10 "<SQ>, <SQ>" $11 "<SQ>, <SQ>" $12 "<SQ>, <SQ>" $13 "<SQ>, <SQ>" $14 "<SQ>, <SQ>" $15 "<SQ>, <SQ>" $16 "<SQ>); " }' > ${outf}
    sed -e "1,\$s/<SQ>/\'/g" ${outf}|sed -e "1,\$s/<INS>/${insert}/g" > ${outf1}
elif [ "${num_fields}" = "17" ]
then
    # Use <SQ> instead of single quote because of quoting difficulty in awk
    # Also Use <INS> to replace the whole insert clause
    tail -${total_lines} ${csv_file_path}|awk -F, '{print "<INS> (<SQ>" $1 "<SQ>, <SQ>" $2 "<SQ>, <SQ>" $3 "<SQ>, <SQ>" $4 "<SQ>, <SQ>" $5 "<SQ>, <SQ>" $6 "<SQ>, <SQ>" $7 "<SQ>, <SQ>" $8 "<SQ>, <SQ>" $9 "<SQ>, <SQ>" $10 "<SQ>, <SQ>" $11 "<SQ>, <SQ>" $12 "<SQ>, <SQ>" $13 "<SQ>, <SQ>" $14 "<SQ>, <SQ>" $15 "<SQ>, <SQ>" $16 "<SQ>, <SQ>" $17 "<SQ>); " }' > ${outf}
    sed -e "1,\$s/<SQ>/\'/g" ${outf}|sed -e "1,\$s/<INS>/${insert}/g" > ${outf1}
elif [ "${num_fields}" = "18" ]
then
    # Use <SQ> instead of single quote because of quoting difficulty in awk
    # Also Use <INS> to replace the whole insert clause
    tail -${total_lines} ${csv_file_path}|awk -F, '{print "<INS> (<SQ>" $1 "<SQ>, <SQ>" $2 "<SQ>, <SQ>" $3 "<SQ>, <SQ>" $4 "<SQ>, <SQ>" $5 "<SQ>, <SQ>" $6 "<SQ>, <SQ>" $7 "<SQ>, <SQ>" $8 "<SQ>, <SQ>" $9 "<SQ>, <SQ>" $10 "<SQ>, <SQ>" $11 "<SQ>, <SQ>" $12 "<SQ>, <SQ>" $13 "<SQ>, <SQ>" $14 "<SQ>, <SQ>" $15 "<SQ>, <SQ>" $16 "<SQ>, <SQ>" $17 "<SQ>, <SQ>" $18 "<SQ>); " }' > ${outf}
    sed -e "1,\$s/<SQ>/\'/g" ${outf}|sed -e "1,\$s/<INS>/${insert}/g" > ${outf1}
elif [ "${num_fields}" = "19" ]
then
    # Use <SQ> instead of single quote because of quoting difficulty in awk
    # Also Use <INS> to replace the whole insert clause
    tail -${total_lines} ${csv_file_path}|awk -F, '{print "<INS> (<SQ>" $1 "<SQ>, <SQ>" $2 "<SQ>, <SQ>" $3 "<SQ>, <SQ>" $4 "<SQ>, <SQ>" $5 "<SQ>, <SQ>" $6 "<SQ>, <SQ>" $7 "<SQ>, <SQ>" $8 "<SQ>, <SQ>" $9 "<SQ>, <SQ>" $10 "<SQ>, <SQ>" $11 "<SQ>, <SQ>" $12 "<SQ>, <SQ>" $13 "<SQ>, <SQ>" $14 "<SQ>, <SQ>" $15 "<SQ>, <SQ>" $16 "<SQ>, <SQ>" $17 "<SQ>, <SQ>" $18 "<SQ>, <SQ>" $19 "<SQ>); " }' > ${outf}
    sed -e "1,\$s/<SQ>/\'/g" ${outf}|sed -e "1,\$s/<INS>/${insert}/g" > ${outf1}
elif [ "${num_fields}" = "20" ]
then
    # Use <SQ> instead of single quote because of quoting difficulty in awk
    # Also Use <INS> to replace the whole insert clause
    tail -${total_lines} ${csv_file_path}|awk -F, '{print "<INS> (<SQ>" $1 "<SQ>, <SQ>" $2 "<SQ>, <SQ>" $3 "<SQ>, <SQ>" $4 "<SQ>, <SQ>" $5 "<SQ>, <SQ>" $6 "<SQ>, <SQ>" $7 "<SQ>, <SQ>" $8 "<SQ>, <SQ>" $9 "<SQ>, <SQ>" $10 "<SQ>, <SQ>" $11 "<SQ>, <SQ>" $12 "<SQ>, <SQ>" $13 "<SQ>, <SQ>" $14 "<SQ>, <SQ>" $15 "<SQ>, <SQ>" $16 "<SQ>, <SQ>" $17 "<SQ>, <SQ>" $18 "<SQ>, <SQ>" $19 "<SQ>, <SQ>" $20 "<SQ>); " }' > ${outf}
    sed -e "1,\$s/<SQ>/\'/g" ${outf}|sed -e "1,\$s/<INS>/${insert}/g" > ${outf1}
else
    echo "ERROR: Can only print up to 20 fields."
    exit 1
fi
