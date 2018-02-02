#date: January 20th, 2018
#Author: Adish Jain
#This Initial Game Builder helps store data into the database upon start of game

import mysql.connector
import datetime

#connection needs to be established on aws, but that doesn't have the pymysql module?
#build cursor object and connection
connection = mysql.connector.connect(user='ce_user', password = '4Apri1Fu11', host='localhost', database='ce_schema')
cur = connection.cursor(buffered = True)

#import parameter file
file = open('parameterfile.txt', 'r') #edit so you can find the file even if its not in the same directory as the program

#SQL statements to add and check the database
add_to_ce_game_query = ("INSERT INTO ce_game (game_id, game_name, game_desc, game_type, game_start_date, market_id, game_period_id, created_date, created_by, updated_date, updated_by) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)")
add_to_ce_account_query = ("INSERT INTO ce_account (account_id, account_name, account_desc, wp_login_id, team_member, created_date, created_by, updated_date, updated_by) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)")
add_to_ce_company_query = ("INSERT INTO ce_company (company_id, company_name, company_desc, created_date, created_by, updated_date, updated_by) VALUES (%s, %s, %s, %s, %s, %s, %s)")
add_to_ce_account_company_plant_map = ("INSERT INTO ce_account_company_plant_map (game_id, account_id, company_id, plant_id, created_date, created_by, updated_date, updated_by) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)")
check_game_type_in_table_query = ("SELECT * FROM ce_game WHERE game_type = (%s)") #for ce_game
check_market_id_in_table_query = ("SELECT * FROM ce_game WHERE market_id = (%s)") #for ce_game
check_game_id_and_start_date_unique_query = ("SELECT * FROM ce_game WHERE game_id = (%s) AND game_start_date = (%s)")
check_account_id_and_login_id_exist_query = ("SELECT * FROM ce_account WHERE account_id = (%s) AND wp_login_id = (%s)")
check_account_id_exist_query = ("SELECT * FROM ce_account WHERE account_id = (%s)")
check_login_id_exist_query = ("SELECT * FROM ce_account WHERE wp_login_id = (%s)")
check_company_id_exist_query = ("SELECT * FROM ce_company WHERE company_id = (%s)")
check_plant_id_exist_query = ("SELECT * FROM ce_plant WHERE plant_id = (%s)")

#parse the entire parameter file first
line = file.readline()
numAccounts = 0
accountids = []
numPlayers = 0
while line:
    if line[0] == "#":
        pass
    else:
        attr = line.split(":")
        if attr[0].strip() == "GAME ID": #check whether game ID is a positive value
            if attr[1].strip() < 0:
                raise Exception("Invalid Game ID account.")
        else if "GAME TYPE" in attr[0].strip(): #check whether its in the parantheses
            if !inList(attr[0].strip(), attr[1].strip()):
                raise Exception("Invalid Game Type.")
        else if attr[0].strip() == "START DATE": #check whether correct Date format
            validate(attr[1].strip())
        else if "MARKET ID" in attr[0].strip(): #check whether its in the parantheses
            if !inList(attr[0].strip(), attr[1].strip()):
                raise Exception("Invalid Market ID.")
        else if attr[0].strip() == "ACCOUNT ID": #can not exist in the table already!! (check this as well)
            if repeats(attr[1].strip().split(",")):
                raise Exception("Repeated Account ID.")
            else:
                for i : attr[1].strip().split(","):
                    if cur.execute(check_account_id_exist_query, (i, )):
                        raise Exception("Account ID already in table.")
            numAccounts = len(attr[1].strip().split(","))
            accountids = attr[1].strip().split(",")
        else if attr[0].strip() == "LOGIN ID (ONE LOGIN PER ACCOUNT)": #must already exist in the table (check this as well)
            if repeats(attr[1].strip().split(",")):
                raise Exception("Repeated Login ID.")
            else if len(attr[1].strip().split(",")) != numAccounts:
                raise Exception("Different number of Login IDs than Account IDs.")
        else:
            for i : attr[1].strip().split(","):
                if !cur.execute(check_login_id_exist_query, (i, )):
                    raise Exception("Login ID isn't in table.")
        else if attr[0].strip() == "TEAM MEMBER NAMES": #check whether the account IDs match up
            if !(attr[1].split(":")[0] in accountids):
                raise Exception("Specified an Account ID which does not exist.")
            else:
                numPlayers += len(attr[1].split(","))
        else if attr[0].strip() == "TOTAL MEMBERS": #check whether its the same as the sum of the team member names
            if int(attr[1].strip()) != numPlayers:
                raise Exception("Incorrect number of players.")
        else if attr[0].strip() == "COMPANY ID": #check whether same number as account IDs
            if repeats(attr[1].strip().split(",")):
                raise Exception("Repeated Company ID.")
            else if len(attr[1].strip().split(",")) != numAccounts:
                raise Exception("Different number of Company IDs than Account IDs.")
        else if attr[0].strip() == "PLANT ID": #check whether ID exists in the plant table already and check whether there are at least as many plant IDs as account IDs
            if repeats(attr[1].strip().split(",")):
                raise Exception("Repeated Plant ID.")
            else if len(attr[1].strip().split(",")) < numAccounts:
                raise Exception("Less Plant IDs than Account IDs.")
            else:
                for i : attr[1].strip().split(","):
                    if !cur.execute(check_plant_id_exist_query, (i, )):
                        raise Exception("Plant ID isn't in table.")
        #======================================
        else if attr[0].strip() == "PROVIDE ACCOUNT, COMPANY, PLANT MAPPING TUPLES BELOW": #check whether all the numbers match up from before
            if repeats(attr[1].strip().split(",")):
                raise Exception("Repeated Login ID.")
            else if len(attr[1].strip().split(",")) != numAccounts:
                raise Exception("Different number of Login IDs than Account IDs.")
        print(line)
    line = file.readline()

#if the parameter file is complete and correct, then start adding the records into the table




#helper functions
#validates the date format of a text
def validate(date_text):
    try:
        datetime.datetime.strptime(date_text, '%Y-%m-%d')
    except ValueError:
        raise ValueError("Incorrect data format, should be YYYY-MM-DD")

#to check whether there are repeats in the list
def repeats(lst):
    seen = set()
    return any(i in seen or seen.add(i) for i in lst)

#to check whether the argument is from the given list of choices
def inList(bigString, subString):
    if bigString.index(subString) != -1:
        return True
    else:
        return False
