#! /bin/sh 
#
# This script sets up a new virtual macine to a new Hadoop development environment
# 1. Add other bin to the path
# 2. Create /HADOOP_METADATA directory, search files, and bin
# 3. Add links and subdirecoties on the URL for accessing documents.

root_home="/root"
bash_profile=".bash_profile"
hadoop_dir="/HADOOP_METADATA"
root_subdir="${hadoop_dir}/root_home"
gen_metadata="${hadoop_dir}/bin/gen_hadoop_metadata_files.sh"
fn=`basename ${gen_metadata}|awk -F\. ('print $1')`
tmpf="/tmp/${fn}.$$"

# Source new .profile
if [ \( ! -f "${root_home}/${bash_profile}.orig" \) -a \( ! -f "${root_subdir}/${bash_profile}.new" \) ]
then
    echo "ERROR: Missing ${bash_profile}.new for new root profile"
    exit 1
elif [ \( -f "${root_subdir}/${bash_profile}.new" \) -a \( -f "${hadoop_dir}/${bash_profile}" \) ]
then
    mv ${hadoop_dir}/${bash_profile} ${hadoop_dir}/${bash_profile}.orig
    mv ${root_subdir}/${bash_profile}.new ${hadoop_dir}/${bash_profile}
    . ${hadoop_dir}/${bash_profile}
else
    . ${hadoop_dir}/${bash_profile}
fi

# Create search files
if [ \( ! -d "${hadoop_dir}" \) -a \( ! -f "${gen_metadata}" \) ]
then
    echo "ERROR: Missing ${hadoop_dir} direcotry"
    exit 1
else
    ${gen_metadata} 1> $tmpf 2>&1
fi