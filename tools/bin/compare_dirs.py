#! /usr/bin/python
##! /cygdrive/c/python27/python

# This script compares two directories to find out
# what files exist in the left directory but not the right,
# and vise versa
#
# Usage: $0 [-c] [-d DEBUGL_XXX] [-ext ext] [-r] [-skip_same] l_dir r_dir
#
# Options:
#   -c:         Comprehensive mode: comparing each file.  Default is summary mode
#   -d:         Use provided debug level
#   -ext:       Look at files with extension
#   -r:         Look recursively in each directory level
#   -skip_same: Do not print files that have same size

import sys
import os
import datetime
import string
#import fileinput
#import re

# Global variables
USAGE = "USAGE: " + sys.argv[0] + " [-c] [-d DEBUGL_XXX] [-ext ext] [-r] [-skip_same] l_dir r_dir"
g_errors = 0
# Production level: 0, low level (api): 1, med level (data structure, algorithm, sql): 2, high level (details, values): 4
g_debugl, DEBUGL_PROD, DEBUGL_LOW, DEBUGL_MED, DEBUGL_HIGH = 0, 0, 1, 2, 4
# Two execution modes: summary or comprehensive
exec_mode = "SUMMARY"
# Recursive mode, default to no
recursive = 0
# Skip printing files of the same size
skip_same = 0
# Use file extension
file_ext = ''
# Dictionary for left and right directories.  All values are comma delimited strings
l_dict = {}
r_dict = {}
l_dir = ''
r_dir = ''
l_latest_mtime = 0
l_latest_mtime_str = ''
r_latest_mtime = 0
r_latest_mtime_str = ''
l_num_files = 0
r_num_files = 0
num_diff = 0


# Help functions
# Function extract value from delimited string
def extract_value(p_delimited_str, p_delimitor, p_pos):
    '''This function extract value from delimited string
       Usage: extract_value(p_delimited_str, p_delimitor, p_pos)'''
    func_n = "extract_value"
    if str_has_ch(p_delimited_str, p_delimitor) > 0:
        el=p_delimited_str.split(',', 2);
        if p_pos > 3:
            print func_n + " ERROR: Pos exceeds number of elements, pos: " + str(p_pos)
            sys.exit(1)  
        else:    
            return el[p_pos-1]
    else:
        return None        

# Function extract value from delimited string
def minus_list(p_list1, p_list1_label, p_list2, p_list2_label, p_minus_list):
    '''This function performs a minus operation on two list.
       The resultant list contains every element on the first list but not the second one.
       Usage: minus_list(p_list1, p_list1_label, p_list2, p_list2_label, p_minus_list)'''
    func_n = "minus_list"
    # List start with index = 1
    prev_j = 0
    # Range start with default value = 0 ??
    for i in range(len(p_list1)):
        j = prev_j
        exit_loop = False
        while (j < len(p_list2)) and (exit_loop != True):
            if p_list1_label == "L_DIR":
                filename1 = extract_value(l_dict[p_list1[i]], ',', 3)
                filename2 = extract_value(r_dict[p_list2[j]], ',', 3)
            else:
                filename1 = extract_value(r_dict[p_list1[i]], ',', 3)
                filename2 = extract_value(l_dict[p_list2[j]], ',', 3)          
            #if g_debugl >= DEBUGL_MED:
            #      print func_n + " i: " + str(i) + " filename1: " + str(filename1)
            #if g_debugl >= DEBUGL_MED:
            #      print func_n + " j: " + str(j) + " filename2: " + str(filename2)            
            if filename1 == filename2:
                prev_j = j
                exit_loop = True
            else:
                j += 1
        if j == len(p_list2):
            p_minus_list.append(p_list1[i])
            #print func_n + " add i: " + str(i) + " j: " + str(j) + " value_1: " + str(filename1)
    if g_debugl >= DEBUGL_MED:
        for i in range(1, len(p_list1)):
            print func_n + " p_list1 i: " + str(i) + " value: " + str(p_list1[i]) 
        for i in range(1, len(p_list2)):
            print func_n + " p_list2 i: " + str(i) + " value: " + str(p_list2[i])            
        for i in range(1, len(p_minus_list)):
            print func_n + " p_minus_list i: " + str(i) + " value: " + str(p_minus_list[i])

# Function for doing nothing.  This is sometimes use in an "else" statement
def pass_func():
    pass
    
# Function for printing comprehensive comparison
def print_comprehensive_sum(p_str):
    '''This function prints the comprehensive comparison
       Usage: print_comprehensive_sum()'''
    func_n = "print_comprehensive_sum"
    global g_errors, g_debugl, file_ext, exec_mode, skip_same
    #print func_n + " skip_same: " + str(skip_same)
    num_diff = 0
    l_keys_list = l_dict.keys()
    if g_debugl >= DEBUGL_MED:
        print "l_keys_list:"
        print l_keys_list
    l_keys_list_sorted = sorted(l_keys_list)
    if g_debugl >= DEBUGL_MED:
        print "l_keys_list_sorted:"
        print l_keys_list_sorted    
    r_keys_list = r_dict.keys()
    if g_debugl >= DEBUGL_MED:
        print "r_keys_list:"
        print r_keys_list    
    r_keys_list_sorted = sorted(r_keys_list)
    if g_debugl >= DEBUGL_MED:
        print "r_keys_list_sorted:"
        print r_keys_list_sorted    
    r_keys_only_list = []
    minus_list(r_keys_list_sorted, "R_DIR", l_keys_list_sorted, "L_DIR", r_keys_only_list)
    print p_str
    for i in l_keys_list_sorted:
        l_file_size = extract_value(l_dict[i], ',', 1)
        if l_file_size is None:
            l_file_size = 0
        j = i.replace(l_dir, r_dir) 
        if j not in r_dict.keys():
            r_file_size = 0
        else:
            r_file_size = extract_value(r_dict[j], ',', 1)
            if r_file_size is None:
                r_file_size = 0        
        if l_file_size != r_file_size:
            num_diff += 1
            print "size_l: " + str(l_file_size) + '\t' + "size_r: " + str(r_file_size) + '\t' + str(i)
        else:
            if skip_same == 0:
                print "size_l: " + str(l_file_size) + '\t' + "size_r: same" + '\t' + str(i)
    for i in r_keys_only_list:
        r_file_size = extract_value(r_dict[i], ',', 1)
        if r_file_size is None:
            r_file_size = 0        
        #r_file_mtime_str = extract_value(r_dict[i], ',', 3)
        num_diff += 1
        print "size_l: 0" + '\t' + "size_r: " + str(r_file_size) + '\t' + str(i) 
    print "Number of differences: " + str(num_diff)

# Function for printing summary of comparison
def print_summary(p_str):
    '''This function print summary of comparison
       Usage: print_summary()'''
    func_n = "print_summary"
    global g_errors, g_debugl, l_latest_mtime, r_latest_mtime, l_dir, l_num_files, r_dir, r_num_files
    #l_latest_mtime_str = string.format(datetime.datetime.fromtimestamp(l_latest_mtime))
    #r_latest_mtime_str = string.format(datetime.datetime.fromtimestamp(r_latest_mtime))
    print p_str
    print "Left directory:\t\t" + str(l_dir)
    print "Number of files:\t" + str(l_num_files)
    print "Latest date:\t\t{}".format(datetime.datetime.fromtimestamp(l_latest_mtime))
    print "Right directory:\t" + str(r_dir)
    print "Number of files:\t" + str(r_num_files)
    print "Latest date:\t\t{}".format(datetime.datetime.fromtimestamp(r_latest_mtime))

# Function for reading command line arguments        
def read_cmdline():
     '''This function reads and parse the command line
        Usage: read_cmdline()'''
     func_n = "read_cmdline"
     global USAGE, g_errors, g_debugl, DEBUGL_MED, exec_mode, file_ext, l_dir, r_dir, recursive, skip_same
     # len(sys.argv) include argv[0], the command itself
     if (len(sys.argv) < 3) or (len(sys.argv) > 6):
         print func_n + " ERROR: Unexpected number of parameters, argc: " + str(len(sys.argv))
         print USAGE
         sys.exit(1)
     else:
         if g_debugl >= DEBUGL_MED:
             for i in range(len(sys.argv[:])):
                 print func_n + " i: " + str(i) + " argv: " + str(sys.argv[i])
     # Read command line
     # Do not like argparse(), getopt(), or optparse().  They are over-kill
     i = 1
     #print func_n + " argc: " + str(len(sys.argv))
     while i < len(sys.argv):
         #print func_n + " i: " + str(i)
         if sys.argv[i] == "-c":
             exec_mode = "COMPREHENSIVE"  
         elif sys.argv[i] == "-d":
             if (str(sys.argv[i+1]).upper()).startswith("DEBUGL_") is False:
                 print func_n + " ERROR: parameter is not for debugl: " + str(sys.argv[i+1])
                 sys.exit(1)
             else:
                 g_debugl = (str(sys.argv[i+1])).upper()
                 i = i+1             
         elif sys.argv[i] == "-ext":
             # extension cannot contain '*' or '.'
             if (str_has_ch(str(sys.argv[i+1]), '*') > 0) or (str_has_ch(str(sys.argv[i+1]), '.') > 0):
                 print func_n + " ERROR: file extension cannot contain wild card: " + str(sys.argv[i+1])
                 sys.exit(1)
             else:
                 file_ext = "*." + str(sys.argv[i+1])
                 i = i+1
         elif sys.argv[i] == "-r":
             recursive = 1
         elif sys.argv[i] == "-skip_same":
             skip_same = 1             
         elif i == len(sys.argv)-2:
             l_dir = sys.argv[i]
         elif i == len(sys.argv)-1:
             r_dir = sys.argv[i]
         else:
             print func_n + " ERROR: Unrecognized option: " + str(sys.argv[i])
             print USAGE
             sys.exit(1)         
         i = i+1
     # Check errors
     if l_dir == '' or (not os.path.exists(l_dir)):
         print func_n + " ERROR: Directory does not exist, l_dir: " + l_dir
         g_errors += 1
     if r_dir == '' or (not os.path.exists(r_dir)):
         print func_n + " ERROR: Directory does not exist, r_dir: " + r_dir
         g_errors += 1
     if g_errors > 0:
         print func_n + " ERROR: Number of errors: " + str(g_errors)
         sys.exit(1)

# Function for reading files in l_dir or r_dir
def read_files_fr_dir(p_dir_path, p_dir_label):
    '''This function reads the files in the given directory
       Usage: read_files_fr_dir(p_dir)'''
    func_n = "read_files_fr_dir"
    global g_errors, g_debugl, recursive
    for path, dirs, files in os.walk(p_dir_path):
        if recursive == 0:
            # No recursion means path is same as p_dir_path
            if path == p_dir_path:
                read_files_fr_dir_basic(path, dirs, files, p_dir_label)
            else:
                # Do nothing
                pass_func()
        else:           
            read_files_fr_dir_basic(path, dirs, files, p_dir_label) 
    if g_debugl >= DEBUGL_MED:
        if p_dir_label == "L_DIR":
            for i in l_dict.keys():
                print func_n + " key: " + str(i) + " value: " + str(l_dict[i])
        else:
            for i in r_dict.keys():
                print func_n + " key: " + str(i) + " value: " + str(r_dict[i])               

# Function read_files_fr_dir_basic called by read_files_fr_dir
def read_files_fr_dir_basic(p_walk_path, p_walk_dirs, p_walk_files, p_dir_label):
    '''This function finds the file stats based on file name and path from caller
       Usage: read_files_fr_dir_basic(p_walk_path, p_walk_dirs, p_walk_files, p_dir_label)'''
    func_n = "read_files_fr_dir_basic"
    global g_errors, g_debugl, file_ext, l_dict, r_dict, l_latest_mtime, l_latest_mtime_str, r_latest_mtime, r_latest_mtime_str, l_latest_mtime_file, r_latest_mtime_file, l_num_files, r_num_files
    if file_ext != '':
        for filename in fnmatch.filter(p_walk_files, file_ext):
            filepath = os.path.join(p_walk_path, filename)
            f_size = os.stat(filepath).st_size
            f_mtime = os.stat(filepath).st_mtime
            #f_mtime_str = string.format(datetime.datetime.fromtimestamp(f_mtime))
            if p_dir_label == "L_DIR":
                # Value is comma delimited string.  First: size, seoond: mtime, third: filename (without path)
                l_dict[filepath] = str(f_size) + ',' + str(f_mtime) + ',' + str(filename)
                if f_mtime > l_latest_mtime:
                    l_latest_mtime = f_mtime
                    l_latest_mtime_file = filepath
                l_num_files += 1
            elif p_dir_label == "R_DIR":
                # Value is comma delimited string.  First: size, seoond: mtime, third: filename (without path)
                r_dict[filepath] = str(f_size) + ',' + str(f_mtime) + ',' + str(filename)
                if f_mtime > r_latest_mtime:
                    r_latest_mtime = f_mtime
                    r_latest_mtime_file = filepath 
                r_num_files += 1
            else:
                print func_n + " ERROR: Unrecognized dir_label: " + str(p_dir_label)
                sys.exit(1)
    else:
        for filename in p_walk_files:
            filepath = os.path.join(p_walk_path, filename)
            f_size = os.stat(filepath).st_size
            f_mtime = os.stat(filepath).st_mtime
            #f_mtime_str = string.format(datetime.datetime.fromtimestamp(f_mtime))
            if p_dir_label == "L_DIR":
                # Value is comma delimited string.  First: size, seoond: mtime, third: filename (without path)
                l_dict[filepath] = str(f_size) + ',' + str(f_mtime) + ',' + str(filename)
                if f_mtime > l_latest_mtime:
                    l_latest_mtime = f_mtime
                    l_latest_mtime_file = filepath
                l_num_files += 1;
            elif p_dir_label == "R_DIR":
                # Value is comma delimited string.  First: size, seoond: mtime, third: filename (without path)
                r_dict[filepath] = str(f_size) + ',' + str(f_mtime) + ',' + str(filename)
                if f_mtime > r_latest_mtime:
                    r_latest_mtime = f_mtime
                    r_latest_mtime_file = filepath 
                r_num_files += 1;
            else:
                print func_n + " ERROR: Unrecognized dir_label: " + str(p_dir_label)
                sys.exit(1)
                            
# Function checks string containing the character
def str_has_ch(p_str, p_ch):
     '''This function checks if the string containing the given character
        Usage: str_has_ch(p_str, p_ch)'''
     func_n = "str_has_ch"
     rc=0
     try:
         rc=string.index(p_str, p_ch)
     except ValueError:
         rc=0 
     return rc
    

## Function test str_has_ch
#rc1, rc2 = 0, 1
#str1 = "abc" + ',' + "def"
#rc1 = str_has_ch(str1, ',')
#print "str1: " + str(str1) + " rc1: " + str(rc1)
#str2 = "abc" + "def"
#rc2 = str_has_ch(str2, ',')
#print "str2: " + str(str2) + " rc2: " + str(rc2)
#sys.exit(0)


# Function Main
func_n = "Compare_dirs"        
read_cmdline()
read_files_fr_dir(l_dir, 'L_DIR')
read_files_fr_dir(r_dir, 'R_DIR')
str1 = func_n + " print summary:"
print_summary(str1)
print
if exec_mode == "COMPREHENSIVE":
    str1 = func_n + " print comprehensive comparison:"
    print_comprehensive_sum(str1)
sys.exit(0)
