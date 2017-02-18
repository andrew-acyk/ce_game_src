#! /bin/sh

# This sciprt generates all the files in /METADATA directory:
# It can take a name of an application and generate application related files
# E.g. HADOOP, ANDROID, WORDPRESS
usage="$0 -d <metadata_dir_with_path> -app <app_name>"
fn=`echo $0|sed -e 's/\.sh//'`
if [ "$#" -gt "4" -o "$#" -lt "2" ]
then
    echo "USAGE: $usage"
    exit 1
fi

while [ "$1" != "" ]
do
    if [ "$1" = "-d" ]
    then
        meta_dir=$2
        shift; shift
    elif [ "$1" = "-app" ]
    then
        app=$2
        shift; shift
    else
        echo "ERROR: Unrecognized command option $1"
        exit 1
    fi
done
#echo "meta_dir: ${meta_dir}"
#echo "app: ${app}"

if [ "${meta_dir}" != "" -a ! -d "${meta_dir}" ]
then
    echo "ERROR: ${meta_dir} is not a directory"
    exit 1
fi
par_dir=`dirname ${meta_dir}`
meta_name=`basename ${meta_dir}`

if [ \( "${app}" != "HADOOP" -a "${app}" != "hadoop" \) -a \( "${app}" != "ANDROID" -a "${app}" != "android" \) -a \( "${app}" != "DB" -a "${app}" != "db" \) -a \( "${app}" != "PYTHON" -a "${app}" != "python" \) -a \( "${app}" != "WORDPRESS" -a "${app}" != "wordpress" \) -a \( "${app}" != "ALL" -a "${app}" != "all" \) ]
then
    echo "ERROR: application ${app} is not known"
    exit 1
fi
# Temporarily exit ??
#exit

cd ${par_dir}
rm -f ${meta_name}/*_all_files
rm -f ${meta_name}/_all_*_files
rm -f ${meta_name}/*_all_dirs
rm -f ${meta_name}/_all_*_dirs
if [ -d ${par_dir}/bin ]
then
find ${par_dir}/bin -type f > ${meta_dir}/bin_all_files
find ${par_dir}/bin -type d > ${meta_dir}/bin_all_dirs
fi
if [ -d ${par_dir}/boot ]
then
find ${par_dir}/boot -type f > ${meta_dir}/boot_all_files
find ${par_dir}/boot -type d > ${meta_dir}/boot_all_dirs
fi
if [ -d ${par_dir}/dev ]
then
find ${par_dir}/dev -type f > ${meta_dir}/dev_all_files
find ${par_dir}/dev -type d > ${meta_dir}/dev_all_dirs
fi
if [ -d ${par_dir}/etc ]
then
find ${par_dir}/etc -type f > ${meta_dir}/etc_all_files
find ${par_dir}/etc -type d > ${meta_dir}/etc_all_dirs
fi
if [ -d ${par_dir}/hadoop ]
then
find ${par_dir}/hadoop -type f > ${meta_dir}/hadoop_all_files
find ${par_dir}/hadoop -type d > ${meta_dir}/hadoop_all_dirs
fi
if [ -d ${par_dir}/home ]
then
find ${par_dir}/home -type f > ${meta_dir}/home_all_files
find ${par_dir}/home -type d > ${meta_dir}/home_all_dirs
fi
if [ -d ${par_dir}/lib64 ]
then
find ${par_dir}/lib64 -type f > ${meta_dir}/lib64_all_files
find ${par_dir}/lib64 -type d > ${meta_dir}/lib64_all_dirs
fi
if [ -d ${par_dir}/lib ]
then
find ${par_dir}/lib -type f > ${meta_dir}/lib_all_files
find ${par_dir}/lib -type d > ${meta_dir}/lib_all_dirs
fi
if [ -d ${par_dir}/lib32 ]
then
find ${par_dir}/lib32 -type f > ${meta_dir}/lib32_all_files
find ${par_dir}/lib32 -type d > ${meta_dir}/lib32_all_dirs
fi
if [ -d ${par_dir}/libx32 ]
then
find ${par_dir}/libx32 -type f > ${meta_dir}/libx32_all_files
find ${par_dir}/libx32 -type d > ${meta_dir}/libx32_all_dirs
fi
if [ -d ${par_dir}/logs ]
then
find ${par_dir}/logs -type f > ${meta_dir}/logs_all_files
find ${par_dir}/logs -type d > ${meta_dir}/logs_all_dirs
fi
# Do not search external devices
#if [ -d ${par_dir}/media ]
#then
#find ${par_dir}/media -type f > ${meta_dir}/media_all_files
#find ${par_dir}/media -type d > ${meta_dir}/media_all_dirs
#fi
if [ -d ${par_dir}/opt ]
then
find ${par_dir}/opt -type f > ${meta_dir}/opt_all_files
find ${par_dir}/opt -type d > ${meta_dir}/opt_all_dirs
fi
if [ -d ${par_dir}/proc ]
then
find ${par_dir}/proc -type f > ${meta_dir}/proc_all_files
find ${par_dir}/proc -type d > ${meta_dir}/proc_all_dirs
fi
if [ -d ${par_dir}/root ]
then
find ${par_dir}/root -type f > ${meta_dir}/root_all_files
find ${par_dir}/root -type d > ${meta_dir}/root_all_dirs
fi
if [ -d ${par_dir}/run ]
then
find ${par_dir}/run -type f > ${meta_dir}/run_all_files
find ${par_dir}/run -type d > ${meta_dir}/run_all_dirs
fi
if [ -d ${par_dir}/sbin ]
then
find ${par_dir}/sbin -type f > ${meta_dir}/sbin_all_files
find ${par_dir}/sbin -type d > ${meta_dir}/sbin_all_dirs
fi
if [ -d ${par_dir}/selinux ]
then
find ${par_dir}/selinux -type f > ${meta_dir}/selinux_all_files
find ${par_dir}/selinux -type d > ${meta_dir}/selinux_all_dirs
fi
if [ -d ${par_dir}/srv ]
then
find ${par_dir}/srv -type f > ${meta_dir}/srv_all_files
find ${par_dir}/srv -type d > ${meta_dir}/srv_all_dirs
fi
if [ -d ${par_dir}/sys ]
then
find ${par_dir}/sys -type f > ${meta_dir}/sys_all_files
find ${par_dir}/sys -type d > ${meta_dir}/sys_all_dirs
fi
if [ -d ${par_dir}/usr ]
then
find ${par_dir}/usr -type f > ${meta_dir}/usr_all_files
find ${par_dir}/usr -type d > ${meta_dir}/usr_all_dirs
fi
if [ -d ${par_dir}/var ]
then
find ${par_dir}/var -type f > ${meta_dir}/var_all_files
find ${par_dir}/var -type d > ${meta_dir}/var_all_dirs
fi
if [ -d ${par_dir}/vargrant ]
then
find ${par_dir}/vargrant -type f > ${meta_dir}/vargrant_all_files
find ${par_dir}/vargrant -type d > ${meta_dir}/vargrant_all_dirs
fi
if [ -d ${par_dir}/vmlinuz ]
then
find ${par_dir}/vmlinuz -type f > ${meta_dir}/vmlinuz_all_files
find ${par_dir}/vmlinuz -type d > ${meta_dir}/vmlinuz_all_dirs
fi

me=`who am i|awk '{print $1}'`
cd ${meta_dir}
env > ${meta_dir}/_${me}_env
set > ${meta_dir}/_${me}_set

# grep common files
grep -i bin *_all_files > _all_bin_files
grep -i bin *_all_dirs > _all_bin_dirs
grep -i conf *_all_files > _all_conf_files
grep -i conf *_all_dirs > _all_conf_dirs
grep -i "\.css" *_all_files > _all_css_files
grep -i demo *_all_files > _all_demo_files
grep -i demo *_all_dirs > _all_demo_dirs
grep -i "\.desktop" *_all_files > _all_desktop_files
grep -i doc *_all_files > _all_doc_files
grep -i doc *_all_dirs > _all_doc_dirs
grep -i env *_all_files > _all_env_files
grep -i httpd *_all_files > _all_httpd_files
grep -i httpd *_all_dirs > _all_httpd_dirs
grep -i index *_all_files > _all_index_files
grep -i index *_all_dirs > _all_index_dirs
grep -i "\.ini" *_all_files > _all_ini_files
grep -i "\.jar" *_all_files > _all_jar_files
grep -i "\.java" *_all_files > _all_java_files
grep -i "\.jsp" *_all_files > _all_jsp_files
grep -i "\.js" *_all_files > _all_js_files
grep -i "\.log" *_all_files > _all_log_ext_files
grep -i log *_all_files | grep -iv "\.log" > _all_log_files
grep -i log *_all_dirs > _all_log_dirs
grep -i note *_all_files > _all_note_files
grep -i note *_all_dirs > _all_note_dirs
grep -i "\.php" *_all_files > _all_php_files
grep -i "\.py" *_all_files > _all_py_files
grep -i "\.rb" *_all_files > _all_rb_files
grep -i readme *_all_files > _all_readme_files
grep -i sdk *_all_files > _all_sdk_files
grep -i sdk *_all_dirs > _all_sdk_dirs
grep -i ample *_all_files > _all_sample_files
grep -i ample *_all_dirs > _all_sample_dirs
grep -i "\.sh" *_all_files > _all_sh_files
grep -i "\.so" *_all_files > _all_so_files
grep -i "\.sql" *_all_files > _all_sql_files
grep -i test *_all_files > _all_test_files
grep -i test *_all_dirs > _all_test_dirs
grep -i "\.xml" *_all_files > _all_xml_files

# grep files specific to interest
if [ \( "${app}" = "HADOOP" -o "${app}" = "hadoop" \) -o \( "${app}" = "ALL" -o "${app}" = "all" \) ]
then
grep -i falcon *_all_files > _all_falcon_files
grep -i flume *_all_files > _all_flume_files
grep -i hbase *_all_files > _all_hbase_files
grep -i hcatalog *_all_files > _all_hcatalog_files
grep -i hdfs *_all_files | grep -iv webhdfs > _all_hdfs_files
grep -i hive *_all_files > _all_hive_files
grep -i knox *_all_files > _all_knox_files
grep -i oozie *_all_files > _all_oozie_files
grep -i pig *_all_files > _all_pig_files
grep -i ranger *_all_files > _all_ranger_files
grep -i spark *_all_files > _all_spark_files
grep -i sqoop *_all_files > _all_sqoop_files
grep -i storm *_all_files > _all_storm_files
grep -i webhdfs *_all_files > _all_webhdfs_files
grep -i zookeeper *_all_files > _all_zookeeper_files
fi

if [ \( "${app}" = "ANDROID" -o "${app}" = "android" \) -o \( "${app}" = "ALL" -o "${app}" = "all" \) ]
then
grep -i android *_all_dirs > _all_android_dirs
grep -i adk *_all_files > _all_adk_files
grep -i adk *_all_dirs > _all_adk_dirs
fi

if [ \( "${app}" = "DB" -o "${app}" = "db" \) -o \( "${app}" = "ALL" -o "${app}" = "all" \) ]
then
grep -i mysql *_all_files > _all_mysql_files
grep -i mysql *_all_dirs > _all_mysql_dirs
grep -i sqlite *_all_files > _all_sqlite_files
grep -i sqlite *_all_dirs > _all_sqlite_dirs
grep -i db *_all_files > _all_db_files
fi

if [ \( "${app}" = "PYTHON" -o "${app}" = "python" \) -o \( "${app}" = "ALL" -o "${app}" = "all" \) ]
then
grep -i django *_all_files > _all_django_files
grep -i django *_all_dirs > _all_django_dirs
fi

if [ \( "${app}" = "WORDPRESS" -o "${app}" = "wordpress" \) -o \( "${app}" = "ALL" -o "${app}" = "all" \) ]
then
grep -i wordpress *_all_files > _all_wordpress_files
grep -i wordpress *_all_dirs > _all_wordpress_dirs
grep -i wp *_all_files > _all_wp_files
fi
