#! /bin/sh

# Read_grep.sh
# This script reads one line/file at a time from stdin
# and greps string from the file.
# -d option: find and grep file in the given source directory
#
# Usage: Read_grep.sh -d <src_dir> <reg_exp_str>

usage="$0 -d <src_dir> -sep <ch> <reg_exp_str>"
fn=`echo $0|sed -e 's/\.sh//'`
if [ "$#" -gt "5" -o "$#" -lt "1" ]
then
    echo "ERROR: $usage"
    exit 1
fi

while [ "$1" != "" ]
do
    if [ "$1" = "-d" ]
    then
        src_dir=$2
        shift; shift
    elif [ "$1" = "-sep" ]
    then
        sep=$2
        shift; shift
    # All option start with "-"
    elif [ `expr index "$1" "-"` = "1" ]
    then
        echo "ERROR: Uncognized option $1"
        exit 1
    else
        re_str=$1
        shift
    fi
done
#echo "src_dir: ${src_dir}"
echo "re_str: ${re_str}"

if [ "${src_dir}" != "" -a ! -d "${src_dir}" ]
then
    echo "ERROR: ${src_dir} is not a directory"
    exit 1
fi

read ln
#echo ${ln}
while [ "${ln}" != "" ]
do
    if [ "${sep}" != "" ]
    then
        # May mix file names with or whith header
        rc=`expr index "${ln}" ":"`
        if [ "${rc}" != "0" ]
        then
            ln=`echo ${ln}|awk -F${sep} {'print $2'}`
        fi
    fi
    if [ "${src_dir}" != "" ]
    then
        ln="${src_dir}/${ln}"
    fi
    if [ ! -f "${ln}" ]
    then
        echo "WARNG: ${ln} is not a file"
    else
        rc=`file "${ln}"|grep -i "text"|wc -l`
        if [ "${rc}" = "1" ]
        then
            grep -q "${re_str}" ${ln}
            if [ "$?" = "0" ]
            then
                echo "++++++++++"
                echo "File ${ln}"
                echo
                cat ${ln}
            fi
        fi
    fi
    read ln
    #echo ${ln}
done
