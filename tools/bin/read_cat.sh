#! /bin/sh

# Read_cat.sh
# This script reads one line/file at a time from stdin
# and run "cat" with the file name
# -d option: find and cat file in the given source directory
#
# Usage: Read_cat.sh -d <src_dir> -sep<ch>

usage="$0 -d <src_dir> -sep <ch>"
fn=`echo $0|sed -e 's/\.sh//'`
if [ "$#" -gt "4"  ]
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
    else
        echo "ERROR: Unrecognized command option $1"
        exit 1
    fi
done
#echo "src_dir: ${src_dir}"
#echo "re_str: ${re_str}"

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
            echo "++++++++++"
            echo "File ${ln}"
            echo
            cat ${ln}
            echo
        fi
    fi
    read ln
    #echo ${ln}
done
