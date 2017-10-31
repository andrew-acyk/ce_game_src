#!/usr/bin/python3

#date: August 26th, 2017
#Author: Adish Jain
#This Initial Game Builder helps store data into the database upon start of game

import mysql.connector
import datetime

#connection needs to be established on aws, but that doesn't have the pymysql module?
#build cursor object and connection
connection = mysql.connector.connect(user='ce_user', password = '4Apri1Fu11', host='localhost', database='ce_schema')
cur = connection.cursor(buffered = True)

#import parameter file
file = open('parameterfile1.txt', 'r') #edit so you can find the file even if its not in the same directory as the program

#error check functions
#to check whether date is correct
def validate(date_text):
    try:
        datetime.datetime.strptime(date_text, '%Y-%m-%d')
    except ValueError:
        raise ValueError("Incorrect data format, should be YYYY-MM-DD")

#to check whether the argument is from the given list of choices
def inList(bigString, subString):
    if bigString.index(subString) != -1:
        return True
    else:
        return False

#to check whether there are repeats in the list
def repeats(lst):
    seen = set()
    return any(i in seen or seen.add(i) for i in lst)

def has_duplicates(dict, key):
    cnt = key
    dictToList = []
    while cnt <= len(dict):
        dictToList = dictToList + list(dict[str(cnt)])
        cnt = cnt + 1
    return repeats(dictToList)

#to make the values that are derived from accountids which must be inserted into ce_account table
def makeAccountNames(lst):
    return ["team" + i for i in lst]

def makeAccountDescr(lst):
    return ["team" + i for i in lst]

def makeCompanyNames(lst):
    return ["company" + i for i in lst]

def makeCompanyDescr(lst):
    return ["company" + i for i in lst]


#insert into database scripts and variables
created_date = datetime.date.today()
created_by = 'adishj'
updated_date = datetime.date.today()
updated_by = 'adishj'

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

preCheck = open('parameterfile1.txt').read()
arbitrary = preCheck.count("Team member names:")
lineCount = 1
line = file.readline()
x = 0 #to account for how many team member names lines there are, since this is arbitrary

while line and lineCount <= 12 + arbitrary:
    mystring = line.split(':')[1].strip()
    checkagainst = line.split(':')[0].strip()
    #print(mystring + " and " + checkagainst)
    if lineCount == 1:
        gameid = mystring
        game_period_id = gameid + '-0'
    if lineCount == 2:
        gamename = mystring
    if lineCount == 3:
        gamedesc = mystring
    if lineCount == 4:
        gametype = mystring
        if not inList(checkagainst, mystring):
            #print("Game type not valid")
            raise Exception("Game type not valid")
        cur.execute(check_game_type_in_table_query, (gametype, ))
        print('check_game_type_in_table_query: %(check_game_type_in_table_query)s' % locals())
        print('gametype: %(gametype)s' % locals())
        if cur.rowcount == 0:
            #print("Game type not in database")
            raise Exception("Game type not in database")
    if lineCount == 5:
        startdate = mystring
        validate(startdate)
    if lineCount == 6:
        marketid = mystring
        if not inList(checkagainst, mystring):
            #print("Market ID not valid")
            raise Exception("Market ID not valid")
        cur.execute(check_market_id_in_table_query, (marketid, )) #check whether market_id is in database
        print('check_market_id_in_table_query: %(check_market_id_in_table_query)s' % locals())
        print('marketid: %(marketid)s' % locals())
        if cur.rowcount == 0:
            #print("Market ID not in database")
            raise Exception("Market ID not in database")
    if lineCount == 7:
        cur.execute(check_game_id_and_start_date_unique_query, (gameid, startdate))
        print('check_game_id_and_start_date_unique_query: %(check_game_id_and_start_date_unique_query)s' % locals())
        print('gameid: %(gameid)s' % locals())
        print('startdate: %(startdate)s' % locals())
        if cur.rowcount != 0:
            #print("This game_id and start_date record already exists in the table")
            raise Exception("This game_id and start_date record already exists in the table")
        ce_game_columns = (gameid, gamename, gamedesc, gametype, startdate, marketid, game_period_id, created_date, created_by, updated_date, updated_by)
        cur.execute(add_to_ce_game_query, ce_game_columns)
        print('add_to_ce_game_query: %(add_to_ce_game_query)s' % locals())
        print('ce_game_columns: %(ce_game_columns)s' % locals())
        #this part is for ce_accounts now
        accountids = mystring.replace(" ", "").split(',') #a list of account ids
        accountnames = makeAccountNames(accountids) #constructing a list of account names derived from accountids list
        accountdescr = makeAccountDescr(accountids) #constructing a list of account descriptions derived from accountids list
        if repeats(accountids):
            #print("Account ID repeated")
            raise Exception("Account ID repeated")
    if lineCount == 8:
        loginids = mystring.replace(" ", "").split(',') #a list of login ids
        if repeats(loginids):
            #print("Login ID repeated")
            raise Exception("Login ID repeated")
    if lineCount == 9:
        teamMemberNames = {}
        while x < len(accountids):
            accountid = line.split(':')[1].strip()
            if not inList(accountids, accountid):
                #print("account ID invalid")
                raise Exception("account ID invalid")
            teamMemberString = line.split(':')[2].strip()
            teamMembers = tuple([item.strip() for item in teamMemberString.split(',')]) #list of team member names
            teamMemberNames[accountid] = teamMembers
            line = file.readline()
            lineCount = lineCount + 1
            x = x + 1
        if has_duplicates(teamMemberNames, int(accountids[0])):
            #print("One player on multiple teams")
            raise Exception("One player on multiple teams")
    if lineCount == 9 + x:
        firCount = 0
        while firCount < len(accountids):
            ce_account_columns = (accountids[firCount], accountnames[firCount], accountdescr[firCount], loginids[firCount], str(teamMemberNames[accountids[firCount]]).replace("(", "").replace("\'", "").replace(")", ""), created_date, created_by, updated_date, updated_by)
            cur.execute(check_account_id_and_login_id_exist_query, (accountids[firCount], loginids[firCount]))
            print('check_account_id_and_login_id_exist_query: %(check_account_id_and_login_id_exist_query)s' % locals())
            print('accountids[firCount]: %(accountids[firCount])s' % locals())
            print('loginids[firCount]: %(loginids[firCount])s' % locals())
            if cur.rowcount != 0:
                pass
            #else:
            #    cur.execute(check_login_id_exist_query, (loginids[firCount], ))
            #    if cur.rowcount == 0:
            #        print("Error: loginID does not exist")
            else:
                cur.execute(check_account_id_exist_query, (accountids[firCount], ))
                print('check_account_id_exist_query: %(check_account_id_exist_query)s' % locals())
                print('accountids[firCount]: %(accountids[firCount])s' % locals())
                if cur.rowcount == 0:
                    cur.execute(add_to_ce_account_query, ce_account_columns)
                    print('all_to_ce_account_query: %(all_to_ce_account_query)s' % locals())
                    print('ce_account_columns: %(ce_account_columns)s' % locals())
            firCount = firCount + 1
        numPlayersExpected = int(line.split(':')[1].strip())
        numPlayersActual = 0
        counter = 0
        while counter < len(teamMemberNames.keys()):
            numPlayersActual = numPlayersActual + len(list(teamMemberNames.values())[counter])
            counter = counter + 1
        if numPlayersExpected != numPlayersActual:
            #print("Wrong number of players")
            raise Exception("Wrong number of players")
    if lineCount == 10 + x:
        companyids = mystring.replace(" ", "").split(',') #a list of company ids
        if repeats(companyids):
            #print("Company ID repeated")
            raise Exception("Company ID repeated")
        companynames = makeCompanyNames(companyids)
        companydescr = makeCompanyDescr(companyids)
    if lineCount == 11 + x:
        secCount = 0
        while secCount < len(companyids):
            ce_company_columns = (companyids[secCount], companynames[secCount], companydescr[secCount], created_date, created_by, updated_date, updated_by) #need the comma to make it a tuple
            cur.execute(check_company_id_exist_query, (companyids[secCount],))
            print('check_company_id_exist_query: %(check_company_id_exist_query)s' % locals())
            print('companyids[secCount]: %(companyids[secCount])s' % locals())
            if cur.rowcount == 0:
                cur.execute(add_to_ce_company_query, ce_company_columns)
                print('add_to_ce_company_query: %(add_to_ce_company_query)s' % locals())
                print('ce_company_columns: %(ce_company_columns)s' % locals())
            secCount = secCount + 1
        plantids = mystring.replace(" ", "").split(',') #a list of plant ids
        if repeats(plantids):
            #print("Plant ID repeated")
            raise Exception("Plant ID repeated")
    if lineCount == 12 + x:
        thirdCount = 0
        while thirdCount < len(plantids):
            cur.execute(check_plant_id_exist_query, (plantids[thirdCount], ))
            print('check_plant_id_exist_query: %(check_plant_id_exist_query)s' % locals())
            print('plantids[thirdCount]: %(plantids[thirdCount])s' % locals())
            if cur.rowcount == 0:
                #print("Plant ID not in table")
                raise Exception("Plant ID not in table")
            thirdCount = thirdCount + 1
    lineCount = lineCount + 1
    line = file.readline()

finalCount = 0
counterForPlants = 0
while finalCount < arbitrary: #actualACPlist has spaces that need to be removed
    actualACPlist = []
    expectedACPlist = line.replace(" ","").strip().split(',')
    if counterForPlants == len(plantids):
        counterForPlants = 0
    actualACPlist = actualACPlist + [accountids[finalCount]] + [companyids[finalCount]] + [plantids[counterForPlants]]
    if expectedACPlist != actualACPlist:
        #print("Account, company, and plant mapping tuple is wrong.")
        raise Exception("Account, company, and plant mapping tuple is wrong.")
    else:
        cur.execute(add_to_ce_account_company_plant_map, (gameid, actualACPlist[0], actualACPlist[1], actualACPlist[2], created_date, created_by, updated_date, updated_by))
        print('add_to_ce_account_company_plant_map: %(add_to_ce_account_company_plant_map)s' % locals())
        print('gameid, actualACPlist[0], actualACPlist[1]: %(gameid)s, %(actualACPlist[0])s, %(actualACPlist[1])s' % locals())
        print('actualACPlist[2], created_date, created_by: %(actualACPlist[2])s %(created_date)s %(created_by)s' % locals())
        print('updated_date, updated_by: %(updated_date)s %(updated_by)s' % locals())
    line = file.readline()
    finalCount = finalCount + 1
    counterForPlants = counterForPlants + 1


#check:
print('gameid: %(gameid)s' % locals())
print('gamename: %(gamename)s' % locals())
print('gamedesc: %(gamedesc)s' % locals())
print('startdate: %(startdate)s' % locals())
print('marketid: %(marketid)s' % locals())
print('accountids: %(accountids)s' % locals())
print('loginids: %(loginids)s' % locals())
print('teamMemberNames: %(teamMemberNames)s' % locals())
print('numPlayersExpected: %(numPlayerExpected)s' % locals())
print('numPlayersActual: %(numPlayersActual)s' % locals())
print('companyids: %(companyids)s' % locals())
print('plantids: %(plantids)s' % locals())
print('actualACPlist: %(actualACPlist)s' % locals())

connection.commit()
cur.close()
connection.close()
