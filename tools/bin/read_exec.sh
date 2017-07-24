#! /bin/bash -x

# Read_exec.sh
# This script reads one line/file at a time from stdin
# and run the command provided (see complete_cmd) with the file name
# -d option: find file in the given source directory if the path is not given
# -sep option: identify separator where the actual file path separated 
#     from the file it was contained.  See example /METADATA/_all_xml_files.
# -cmd option: next parameter is the complete command enclosed in double quotes
# complete_cmd: the command to be executed on the path.  The entire command 
#     must be enclosed by double quotes and in the following format:
#     <cmd_and_parameter> PATH <rest_of_cmd>
# At runtime, PATH will be replaced by the file path.
# The command will be executed with the file path
#
# Usage: Read_exec.sh -d <src_dir> -sep <ch> -cmd "complete_cmd"

usage1="$0 -d <src_dir> -sep <ch> -cmd \"complete_cmd\""
usage2="Where complete_cmd has format: <cmd_and_parameter> PATH <rest_of_cmd>"
usage="${usage1}\n${usage2}"
fn=`echo $0|sed -e 's/\.sh//'`
if [ "$#" -gt "6" ]
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
    elif [ "$1" = "-cmd" ]
    then
        # May include spaces
        cmd=$2
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
cmd_len=`expr length "${cmd}"`
chk_path=`expr match "${cmd}" ".*PATH.*"`
if [ "${cmd_len}" != "${chk_path}" ]
then
    echo "ERROR: \"${cmd}\" must contain PATH label"
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
        sub_cmd=`echo "${cmd}"|sed -e 's/PATH/\"${ln}\"/'`
        echo "execute command: \"${sub_cmd}\""
        eval "${sub_cmd}"
        if [ "$?" != "0" ]
        then
            echo "ERROR: problem in executing command: \"${sub_cmd}\""
            exit 1
        fi
    fi
    read ln
    #echo ${ln}
done
