# -*- coding: utf-8 -*-
"""
Created on Sat May  2 11:10:33 2015

@author: andrew
"""
# For Postgresql
#import psycopg2
# For Oracle
import cx_Oracle
import re
from spreadsheet_loader_com_const import DEF_DB_NAME, DEF_DB_HOST, DEF_DB_USER, \
    DEF_DB_PASSWD, DEF_DB_PORT, SPREADSHEET_LOADER_SUPPORT
import sys

lfh = None

__all__ = ['db_trans_manager']


# Class of db_trans_manager
class db_trans_manager:
    """Class of db_trans_manager"""

    def __init__(self, db_host=DEF_DB_HOST, db_name=DEF_DB_NAME, \
                db_port=DEF_DB_PORT, exec_flag=True, spldr_lfh=None, pflag=True):
        """db_trans_manager constructor"""
        global lfh
        self.db_name = DEF_DB_NAME
        self.db_host = DEF_DB_HOST
        self.db_user = None
        self.db_passwd = None
        self.db_port = DEF_DB_PORT
        # Connection object
        self.db_conn = None
        # Cursor object
        self.db_cur = None
        # Execution flag.  Set to False for print  ing sql statements only
        self.exec_flag = True
        self.pflag = True
        self.lfh = None
        self.sql = None

        self.db_name = db_name
        self.db_host = db_host
        self.db_port = db_port
        self.exec_flag = exec_flag
        if spldr_lfh != None:
            self.lfh = lfh = spldr_lfh
        self.pflag = pflag
        if self.pflag:
            print >>lfh,  "__init__: db_trans_manager: %(self)s" % locals()


    # For print  ing the object instance
    def __str__(self):
        """db_trans_manager __str__ function"""
        return "db_trans_manager:" + "\n" + \
            "attribute: db_name, value: " + str(self.db_name) + "\n" + \
            "attribute: db_host, value: " + str(self.db_host) + "\n" + \
            "attribute: db_port, value: " + str(self.db_port) + "\n" + \
            "attribute: db_user, value: " + str(self.db_user) + "\n" + \
            "attribute: db_passwd, value: " + str(self.db_passwd) + "\n" + \
            "attribute: db_port, value: " + str(self.db_port) + "\n" + \
            "attribute: db_conn, value: " + str(self.db_conn) + "\n" + \
            "attribute: db_cur, value: " + str(self.db_cur) + "\n" + \
            "attribute: exec_flag, value: " + str(self.exec_flag) + "\n" + \
            "attribute: pflag, value: " + str(self.pflag) + "\n" + \
            "attribute: sql, value: " + str(self.sql) + "\n"


    # This function set the value of exec_flag
    def set_exec_flag(self, exec_flag):
        """This function set the value of exec_flag"""
        fname = "db_trans_manager.set_exec_flag"
        # Just assignment, no checking.
        self.exec_flag = exec_flag
        
        
    # This function is for connecting to database
    def connect_db(self, db_user=DEF_DB_USER, db_passwd=DEF_DB_PASSWD):
        """This function is for connecting to database"""
        fname = "db_trans_manager.connect_db"
        global SPREADSHEET_LOADER_SUPPORT, lfh
        self.db_user = db_user
        self.db_passwd = db_passwd
        try:
            # Use keywords (use "database"), not connection string (use "dbname")
            #db_conn = psycopg2.connect(host=self.db_host, \
            #        database=self.db_name, port=self.db_port, user=db_user, password=db_passwd)
            #db_conn = cx_Oracle.connect(host=self.db_host, \
            #        database=self.db_name, port=self.db_port, user=db_user, password=db_passwd)
            connect_str = db_user + '/' + db_passwd + '@' + self.db_host + ':' + \
                          str(self.db_port) + '/' + self.db_name
            if self.pflag:
                print >>lfh,  """%(fname)s connect_str: %(connect_str)s""" % locals()
            #db_conn = cx_Oracle.connect(u'symc_mdm_vld/symcvalprd@143.127.39.32:1541/MDMMP')
            db_conn = cx_Oracle.connect(connect_str)
        except cx_Oracle.DatabaseError as e:
            error, = e.args
            print """ERROR: Cannot connect to the database.
                     Please contact %s for assistance""" % (SPREADSHEET_LOADER_SUPPORT)
            print """%(fname)s connect_str: %(connect_str)s""" % locals() 
            print("code: " + str(error.code))
            print("message: " + error.message)            
            sys.exit(1)            
        self.db_conn = db_conn
        db_cur = db_conn.cursor()
        self.db_cur = db_cur


    # For performing select from database
    # It returns a dictionary of individual values or lists
    # It also returns the number of selected records
    # sql_stmt should not contain ";"
    def select_trans(self, sql_stmt, retn_dict):
        """This function is for performing select from database
           It returns a dictionary of individual values or lists
           It also returns the number of selected records"""
        fname = "db_trans_manager.select_trans"
        global SPREADSHEET_LOADER_SUPPORT, lfh
        if self.db_cur == None:
            print "ERROR: Cursor is not initialized"
            print "%(fname)s db_trans_manager: %(self)s" % locals()
            sys.exit(1)        
        retn_dict.clear()
        self.sql = sql_stmt1 = sql_stmt.lstrip().rstrip()
        if self.pflag:
            print >>lfh,  "calling %(fname)s sql: %(sql_stmt)s" % locals()
        try:
            self.db_cur.execute(sql_stmt1)
        #except:
        #    print   "ERROR: problem in executing sql statement:"
        #    print   "%(fname)s sql: %(sql_stmt1)s" % locals()
        except cx_Oracle.DatabaseError as e:
            error, = e.args
            print """ERROR: Cannot execute the sql statement
                     Please contact %s for assistance""" % (SPREADSHEET_LOADER_SUPPORT)
            print """%(fname)s sql: %(sql_stmt)s""" % locals() 
            print("code: " + str(error.code))
            print("message: " + error.message)            
            sys.exit(1)
        # Fetch the result set to rows.  If the result set has one record,
        # then rows is a list.  If the result set has multiple records,
        # rows is a list of lists.  Each inner list contains all the column
        # values of a record
        rows = self.db_cur.fetchall()
        rows_count = len(rows)
        if self.pflag:
            print >>lfh,  "%(fname)s rows_count: %(rows_count)d" % locals()
        if len(rows) == 0:
            return 0
        column_list = self.find_column_list(sql_stmt1)
        if self.pflag:
            print >>lfh,  "%(fname)s column_list: %(column_list)s" % locals()
        row_count = self.save_dataset_to_dict(rows, column_list, retn_dict)
        return row_count


    # This function saves data set to the dictionary.
    # The data set can have multiple rows.  Each row has the same identical columns.
    # The dictionary uses column as key.  Each column has a list containing values
    # of the same column on each row.
    def save_dataset_to_dict(self, rows, column_list, retn_dict):
        """This function saves data set to the dictionary.
           The data set can have multiple rows.  Each row has the same identical columns.
           The dictionary uses column as key.  Each column has a list containing values
           of the same column on each row."""
        fname = "db_trans_manager.save_dataset_to_dict"
        global lfh
        num_of_col = len(column_list)
        # rows has rows of records, each record is a list of col values
        row_count = len(rows)
        # Has multiple records
        if row_count > 1:
            # transpose the rows as matrix so that retn_dict contains
            # columns as keys and each key has list of values from each record
            # The transposed matrix is list of lists. The inner list is list of
            # values from each record for the same column.
            # The first for loop on the right sets the index value (or column)
            # used for pulling the inner list
            # The first for loop on the left pulls the corr column of all the rows
            # into the inner list
            # The outer list loop through every column
            rows_transposed = \
                    [[row[i].lstrip().rstrip() for row in rows] for i in range(num_of_col)]
            # select statement return multiple rows
            for i in range(num_of_col):
                # The value is a list
                retn_dict[column_list[i]] = rows_transposed[i]
            return row_count
        else:
            # There is only one record
            row_list = rows[0]
            for i in range(len(row_list)):
                # The value is a scalar
                #print >>lfh,  str(i) + " column_list: " + str(column_list[i]) \
                #    + " row_list: " + str(row_list[i])
                retn_dict[column_list[i]] = row_list[i]
            return row_count


    # THis function parse the select statement and return a column list
    def find_column_list(self, sql_stmt):
        """THis function parse the select statement and return a column list"""
        fname = "db_trans_manager.find_column_list"
        global lfh
        # sql_stmt is a multi-lines string
        # change it to single line
        lines = sql_stmt.upper().splitlines()
        all_lines = ' '.join(str(j) for j in lines)
        #print >>lfh,  "%(fname) all_lines: %(all_lines)s" % locals()
        # re works on single line string only, not multi-lines string
        # Look for column list between the words "select" and "from"
        if self.pflag:
            print >>lfh,  "%(fname)s concatenated lines: %(all_lines)s" % locals()
        # Change sql statement to upper() only in find_column_list()
        matched = re.search("SELECT (.*) FROM", all_lines)
        # string contains the column list
        if matched:
            column_list_str = matched.group(1) # Column list is the first group
            #print >>lfh,  column_list_str
        else:
            print >>lfh,  "Cannot find match with \"SELECT FROM\""
        column_list = column_list_str.split(",")
        column_list1 = [i.lstrip().rstrip() for i in column_list]
        column_list2 = []
        # convert "alias.column" to column, "alias.column as column" to column,
        # "alias.column column" to column
        for i in column_list1:
            #print >>lfh,  "i: %(i)s" % locals()
            matched = re.search(r"\w* as (\w*)", i)
            if matched:
                column_list2.append(matched.group(1))
            elif i.find(' ') > 0:
                sp_index = i.index(' ')
                item = i[sp_index+1:]
                column_list2.append(item)
            elif i.find('.') > 0:
                dot_index = i.index('.')
                item = i[dot_index+1:]
                column_list2.append(item)
            else:
                column_list2.append(i)
        # Column_list2 contains only column names
        #for j in column_list2:
            #print >>lfh,  j
        return column_list2


    # For performing insert/update/delete
    # sql_stmt should not contain ";"
    def iud_trans(self, sql_stmt, skip_commit = False):
        """This function is for performing insert/update/delete"""
        fname = "db_trans_manager.iud_trans"
        global SPREADSHEET_LOADER_SUPPORT, lfh
        if self.db_cur == None:
            print "ERROR: Cursor is not initialized"
            print "%(fname)s db_trans_manager: %(self)s" % locals()
            sys.exit(1)
        self.sql = sql_stmt1 = sql_stmt.lstrip().rstrip()
        if self.pflag:
            print >>lfh,  "calling %(fname)s sql: %(sql_stmt1)s" % locals()
        try:
            if self.exec_flag:
                self.db_cur.execute(sql_stmt1)
        except cx_Oracle.DatabaseError as e:
            error, = e.args
            print """ERROR: Cannot execute the sql statement.
                     Please contact %s for assistance""" % (SPREADSHEET_LOADER_SUPPORT)
            print """%(fname)s sql: %(sql_stmt)s""" % locals() 
            print("code: " + str(error.code))
            print("message: " + error.message)            
            sys.exit(1)
        count = self.db_cur.rowcount
        if not skip_commit:
            self.db_conn.commit()
        return count


    # For performing DDL statement
    # sql_stmt should not contain ";"
    def ddl_trans(self, sql_stmt):
        """This function is for performing DDL statement"""
        fname = "db_trans_manager.ddl_trans"
        global SPREADSHEET_LOADER_SUPPORT, lfh
        if self.db_cur == None:
            print "ERROR: Cursor is not initialized"
            print "%(fname)s db_trans_manager: %(self)s" % locals()
            sys.exit(1)
        self.sql = sql_stmt1 = sql_stmt.lstrip().rstrip()
        if self.pflag:
            print >>lfh,  "calling %(fname)s sql: %(sql_stmt)s" % locals()
        try:
            if self.exec_flag:
                self.db_cur.execute(sql_stmt1)
            return None
        #except psycopg2.Error as e:
        except cx_Oracle.DatabaseError as e:
            error, = e.args
            print """ERROR: Cannot execute the sql statement.
                     Please contact %s for assistance""" % (SPREADSHEET_LOADER_SUPPORT)
            print """%(fname)s sql: %(sql_stmt)s""" % locals() 
            print("code: " + str(error.code))
            print("message: " + error.message)            
            return error # Can skip error


    # For checking permissions by executing different sql statements
    # For read permission: select * from table
    # For write permission: update table set column = value
    # sql_stmt should not contain ";"
    def check_db_permissions(self, sql_stmt):
        """This function checks permissions by executing different sql statements
           For read permission: select * from table
           For write permission: update table set column = value"""
        fname = "db_trans_manager.check_db_permissions"
        global SPREADSHEET_LOADER_SUPPORT, lfh
        if self.db_cur == None:
            print "ERROR: Cursor is not initialized"
            print "%(fname)s db_trans_manager: %(self)s" % locals()
            sys.exit(1)
        self.sql = sql_stmt1 = sql_stmt.lstrip().rstrip()
        if self.pflag:
            print >>lfh,  "calling %(fname)s sql: %(sql_stmt)s" % locals()
        try:
            self.db_cur.execute(sql_stmt1)
            return None
        #except psycopg2.Error as e:
        except cx_Oracle.DatabaseError as e:
            error, = e.args
            print """ERROR: Do not have proper Oracle database permission.
                     Please contact %s for assistance""" % (SPREADSHEET_LOADER_SUPPORT)
            print """%(fname)s sql: %(sql_stmt)s""" % locals() 
            print("code: " + str(error.code))
            print("message: " + error.message)            
            return error


    # Checking if the record exist by counting
    # The sql_stmt is a select count statement.  It always returns one value
    # e.g. select count(*) from table where clause
    # sql_count_stmt should not contain ";"
    def count_records(self, sql_count_stmt):
        """This function is used for checking if the record exist"""
        fname = "db_trans_manager.count_records"
        global SPREADSHEET_LOADER_SUPPORT, lfh
        self.sql = sql_count_stmt1 = sql_count_stmt.lstrip().rstrip()
        if self.pflag:
            print >>lfh,  "calling %(fname)s sql: %(sql_count_stmt)s" % locals()
        #self.add_to_attrib("sql", sql_count_stmt1)
        if self.db_cur == None:
            print "ERROR: Cursor is not initialized"
            print "%(fname)s db_trans_manager: %(self)s" % locals()
            sys.exit(1)
        try:
            self.db_cur.execute(sql_count_stmt1)
        except cx_Oracle.DatabaseError as e:
            error, = e.args
            print """ERROR: Cannot execute the sql statement.
                     Please contact %s for assistance""" % (SPREADSHEET_LOADER_SUPPORT)
            print """%(fname)s sql: %(sql_count_stmt)s""" % locals() 
            print("code: " + str(error.code))
            print("message: " + error.message)            
            sys.exit(1)
        # Fetch only one record, the row is a tuple (0L,)
        row = self.db_cur.fetchone()
        if type(row[0]).__name__ != "int" and type(row[0]).__name__ != "long":
            print """ERROR: Cannot execute the sql statement.
                     Please contact %s for assistance""" % (SPREADSHEET_LOADER_SUPPORT)
            print "%(fname)s sql: %(sql_count_stmt)s" % locals()
            sys.exit(1)
        else:
            return int(row[0])
            

    # This function checks for duplicated records by counting the number of duplications
    def check_duplicated_records(self, sql_count_stmt):
        """This function checks for duplicated records by counting the number of duplications"""
        fname = "db_trans_manager.check_duplicated_records"
        global SPREADSHEET_LOADER_SUPPORT, lfh
        self.sql = sql_count_stmt1 = sql_count_stmt.lstrip().rstrip()
        if self.pflag:
            print >>lfh,  "calling %(fname)s sql: %(sql_count_stmt)s" % locals()
        #self.add_to_attrib("sql", sql_count_stmt1)
        if self.db_cur == None:
            print "ERROR: Cursor is not initialized"
            print "%(fname)s db_trans_manager: %(self)s" % locals()
            sys.exit(1)
        try:
            self.db_cur.execute(sql_count_stmt1)
        except cx_Oracle.DatabaseError as e:
            error, = e.args
            print """ERROR: Cannot execute the sql statement.
                     Please contact %s for assistance""" % (SPREADSHEET_LOADER_SUPPORT)
            print """%(fname)s sql: %(sql_count_stmt)s""" % locals() 
            print("code: " + str(error.code))
            print("message: " + error.message)            
            sys.exit(1)
        # Fetch only one record, the row is a tuple (0L,)
        row = self.db_cur.fetchone()
        if type(row[0]).__name__ != "int" and type(row[0]).__name__ != "long":
            print """ERROR: Cannot execute the sql statement.
                     Please contact %s for assistance""" % (SPREADSHEET_LOADER_SUPPORT)
            print "%(fname)s sql: %(sql_count_stmt)s" % locals()
            sys.exit(1)
        else:
            return int(row[0])
            
            
    # Get the next id
    # The sql_stmt is a select statement on the next id of the primary key column
    # e.g. select max(primary_key_column)+1 from table
    # sql_stmt should not contain ";"
    def get_next_id(self, sql_stmt):
        """This function is used for getting the next id
           of the primary key column"""
        fname = "db_trans_manager.get_next_id"
        global SPREADSHEET_LOADER_SUPPORT, lfh
        self.sql = sql_stmt1 = sql_stmt.lstrip().rstrip()
        if self.pflag:
            print >>lfh,  "calling %(fname)s sql: %(sql_stmt)s" % locals()
        if self.db_cur == None:
            print "ERROR: Cursor is not initialized"
            print "%(fname)s db_trans_manager: %(self)s" % locals()
            sys.exit(1)
        try:
            self.db_cur.execute(sql_stmt1)
        except cx_Oracle.DatabaseError as e:
            error, = e.args
            print "ERROR: problem in executing sql statement:"
            print """%(fname)s sql: %(sql_stmt)s""" % locals() 
            print("code: " + str(error.code))
            print("message: " + error.message)            
            sys.exit(1)
        # Fetch only one record, the row is a tuple (0L,)
        row = self.db_cur.fetchone()
        if type(row[0]).__name__ != "int" and type(row[0]).__name__ != "long":
            print """ERROR: Cannot execute the sql statement.
                     Please contact %s for assistance""" % (SPREADSHEET_LOADER_SUPPORT)
            print "%(fname)s sql: %(sql_stmt)s" % locals()
            sys.exit(1)
        else:
            return int(row[0])


    # For loading a table into a dictionary
    # sql_stmt should not contain ";"
    def load_table(self, sql_stmt, retn_dict):
        """This function loads the data set from a table into a dictionary
           The sql statement is a select statement on all the relevant columns of the given table"""
        fname = "db_trans_manager.load_table"
        global lfh
        retn_dict.clear()
        self.sql = sql_stmt1 = sql_stmt.lstrip().rstrip()
        row_count = self.select_trans(sql_stmt1, retn_dict)
        return row_count


    # For loading name value pairs from a table into a dictionary
    # sql_stmt should not contain ";"
    def load_table_domain_values(self, sql_stmt, name_column, value_column, final_dict):
        """This function loads the name value pairs from a table into a dictionary
           The sql statement is a select statement of two columns: name_column and value_column.
           The function transposes the dictionary returned by the select_trans() call to the final dictionary"""
        fname = "db_trans_manager.load_table_domain_values"
        global lfh
        select_dict = {}
        final_dict.clear()
        self.sql = sql_stmt1 = sql_stmt.lstrip().rstrip()
        column_list = []
        # Column list is in upper()
        column_list = self.find_column_list(sql_stmt1)
        name_col_idx = column_list.index(name_column.upper())
        value_col_idx = column_list.index(value_column.upper())
        # Check if the column list does not have 2 columns or name_column and value_column are not in the list
        if (len(column_list) != 2) or (name_col_idx == -1) or \
           (value_col_idx == -1):
            print "ERROR: name_column or value_column is not in sql_stmt column list"
            print """%(fname)s sql: %(self)s, name_col: %(name_column)s, 
                     value_col: %(value_column)s""" % locals()
            sys.exit(1)
        # retn_dict has two keys: name_column and value_column.  Each key has
        # a list of values corresponding to the column from each record
        row_count = self.select_trans(sql_stmt1, select_dict)
        # Load a second dictionary.  Keys come from the list of the name_column in retn_dict dictionary.
        # Values come from the corr. element in the list of the value_column.
        for key in select_dict[name_column]:
            idx = select_dict[name_column].index(key)
            final_dict[key] = select_dict[value_column][idx]
        if self.pflag:
            print >>lfh,  "%(fname)s final_dict: %(final_dict)s" % locals()
        return row_count


    # For discounting from the database
    def disconnect_db(self):
        """This function is for disconnecting from the database"""
        fname = "db_trans_manager.disconnect_db"
        global lfh
        if self.db_cur <> None:
            self.db_cur.close()
            self.db_cur = None
        if self.db_conn <> None:
            self.db_conn.close()
            self.db_conn = None
