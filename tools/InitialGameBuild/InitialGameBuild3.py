#date: March 17th, 2018
#Author: Adish Jain
#This Initial Game Builder helps store data into the database upon start of game

import mysql.connector
import datetime
from collections import defaultdict

#connection needs to be established on aws, but that doesn't have the pymysql module?
#build cursor object and connection
connection = mysql.connector.connect(user='ce_user', password = '4Apri1Fu11', host='localhost', database='ce_schema')
cur = connection.cursor(buffered = True)

#import parameter file
file1 = open('parameterfile.txt', 'r') #edit so you can find the file even if its not in the same directory as the program
file2 = open('parameterfile.txt', 'r')
created_date = datetime.date.today()
created_by = 'adishj'
updated_date = datetime.date.today()
updated_by = 'adishj'

#SQL statements to add and check the database
add_to_ce_game_query = ("INSERT INTO ce_game (game_id, game_name, game_desc, game_type, game_start_date, market_id, created_date, created_by, updated_date, updated_by) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)")
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

#variable place holders if all checks pass, then stored into database
gameid = 0
gamename = ""
gamedesc = ""
gametype = ""
startdate = ""
marketid = ""
accountids = []
numAccounts = 0 #this won't get put into DB, but is here for reference
loginids = []
membernames = defaultdict(list) #dictionary with keys: account id and values: names of members
numPlayers = 0 #this won't get put into DB, but is here for reference
companyids = []
plantids = []

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


#parse the entire parameter file first
line = file1.readline()

while line:
    if line[0] == "#":
        pass
    else:
        attr = line.split(":")
        if attr[0].strip() == "GAME ID": #check whether game ID is a positive value
            if int(attr[1].strip()) < 0:
                raise Exception("Invalid Game ID account.")
            gameid = attr[1].strip()
        elif "GAME NAME" in attr[0].strip():
            gamename = attr[1].strip()
        elif "GAME DESCRIPTION" in attr[0].strip():
            gamedesc = attr[1].strip()
        elif "GAME TYPE" in attr[0].strip(): #check whether its in the parantheses
            if not inList(attr[0].strip(), attr[1].strip()):
                raise Exception("Invalid Game Type.")
            gametype = attr[1].strip()
        elif attr[0].strip() == "START DATE": #check whether correct Date format
            validate(attr[1].strip())
            startdate = attr[1].strip()
        elif "MARKET ID" in attr[0].strip(): #check whether its in the parantheses
            if not inList(attr[0].strip(), attr[1].strip()):
                raise Exception("Invalid Market ID.")
            marketid = attr[1].strip()
        elif attr[0].strip() == "ACCOUNT ID": #can not exist in the table already!! (check this as well)
            if repeats(attr[1].strip().split(",")):
                raise Exception("Repeated Account ID.")
            else:
                for i in attr[1].strip().split(","):
                    cur.execute(check_account_id_exist_query, (i, ))
                    if cur.rowcount != 0:
                        raise Exception("Account ID already in table.")
            numAccounts = len(attr[1].strip().split(","))
            accountids = attr[1].strip().replace(" ", "").split(",")
        elif attr[0].strip() == "LOGIN ID (ONE LOGIN PER ACCOUNT)": #must already exist in the table (check this as well)
            if repeats(attr[1].strip().split(",")):
                raise Exception("Repeated Login ID.")
            elif len(attr[1].strip().split(",")) != numAccounts:
                raise Exception("Different number of Login IDs than Account IDs.")
            else:
                for i in attr[1].strip().split(","):
                    i = i.strip();
                    cur.execute(check_login_id_exist_query, (i, ))
                    if cur.rowcount == 0:
                        raise Exception("Login ID isn't in table.")
            loginids = attr[1].strip().split(",")
        elif attr[0].strip() == "TEAM MEMBER NAMES": #check whether the account IDs match up
            if not attr[1].split("-")[0].strip() in accountids:
                raise Exception("Specified an Account ID which does not exist.")
            else:
                numPlayers += len(attr[1].split(","))
            membernames[attr[1].split("-")[0].strip()].append(attr[1].split("-")[1].strip().split(","))
            #membernames.update({attr[1].split("-")[0].strip(), attr[1].split("-")[1].strip().split(",")})
        elif attr[0].strip() == "TOTAL MEMBERS": #check whether its the same as the sum of the team member names
            if int(attr[1].strip()) != numPlayers:
                raise Exception("Incorrect number of players.")
        elif attr[0].strip() == "COMPANY ID (ONE COMPANY PER ACCOUNT)": #check whether same number as account IDs
            if repeats(attr[1].strip().split(",")):
                raise Exception("Repeated Company ID.")
            elif len(attr[1].strip().split(",")) != numAccounts:
                raise Exception("Different number of Company IDs than Account IDs.")
            companyids = attr[1].strip().replace(" ", "").split(",")
        elif attr[0].strip() == "PLANT ID (ID EXISTS IN CE_PLANT TABLE)": #check whether ID exists in the plant table already and check whether there are at least as many plant IDs as account IDs
            if repeats(attr[1].strip().split(",")):
                raise Exception("Repeated Plant ID.")
            elif len(attr[1].strip().split(",")) < numAccounts:
                raise Exception("Less Plant IDs than Account IDs.")
            else:
                for i in attr[1].strip().split(","):
                    i = i.strip()
                    cur.execute(check_plant_id_exist_query, (i, ))
                    if cur.rowcount == 0:
                        raise Exception("Plant ID isn't in table.")
            plantids = attr[1].strip().replace(" ", "").split(",")
        elif attr[0].strip() == "PROVIDE ACCOUNT, COMPANY, PLANT MAPPING TUPLES BELOW": #check whether all the numbers match up from before
            i = 0
            while i < numAccounts:
                line = file1.readline().strip()
                expectedLine = accountids[i] + ", " + companyids[i] + ", " + plantids[i]
                if line != expectedLine:
                    raise Exception("Mapping Tuple is incorrect.")
                i += 1
    line = file1.readline()


#if the parameter file is complete and correct, execute queries to store into DB
#add to ce_game
ce_game_columns = (gameid, gamename, gamedesc, gametype, startdate, marketid, created_date, created_by, updated_date, updated_by)
cur.execute(add_to_ce_game_query, ce_game_columns)

#add to ce_account and ce_company
i = 0
while i < numAccounts:
    membernamesString = ""
    for j in membernames[accountids[i]]:
        for k in j:
            membernamesString += k

    ce_account_columns = (accountids[i], "team" + accountids[i], "team" + accountids[i], loginids[i], membernamesString, created_date, created_by, updated_date, updated_by)
    cur.execute(add_to_ce_account_query, ce_account_columns)

    ce_company_columns = (companyids[i], "company" + companyids[i], "company" + companyids[i], created_date, created_by, updated_date, updated_by)
    cur.execute(add_to_ce_company_query, ce_company_columns)

    i += 1

#add to ce_account_company_plant_map
j = 0
while j < numAccounts:
    cur.execute(add_to_ce_account_company_plant_map, (gameid, accountids[j], companyids[j], plantids[j], created_date, created_by, updated_date, updated_by))
    j += 1


connection.commit()
cur.close()
connection.close()
