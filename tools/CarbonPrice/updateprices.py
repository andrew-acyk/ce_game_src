import pandas as pd
import numpy as np
import datetime
import mysql.connector
import datetime
file = open('priceconfig.txt', 'r')
line = file.readline()
params = {}
while line:
    if line[0] != '#':
        pair = line.split(':',1)
        params[pair[0]] = pair[1][1:-1]
    line = file.readline()
resp = pd.read_csv(params['URL'])
price = resp.tail(1).iloc[:,-2].values[0]
resp.drop('Unnamed: 0',axis=1,inplace=True)
def zeropad(date):
    date = date.split('/')
    date[0] = ('0'+date[0])[-2:]
    date[1] = ('0'+date[1])[-2:]
    return '-'.join(date)
def findprice(x):
    for i in reversed(x[1:]):
        if ~np.isnan(i):
            date = datetime.datetime.strptime(zeropad(x[0]), '%m-%d-%Y')
            return (date,i)
resp.drop_duplicates(subset=['date'],inplace=True)
prices = np.array(resp.apply(findprice, axis=1).dropna().values)
connection = mysql.connector.connect(user=params['Username'], password = params['Password'], host='localhost', database=params['Table Name'])
cur = connection.cursor(buffered = True)
insertion = ("INSERT INTO ce_carbon_market_price (market_id, market_name, price_per_allowance_ton, price_date, what_currency, created_date, created_by, updated_date, updated_by) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)")
today = datetime.date.today()
testvalues = ['1', 'demo_market_1', 1, today, 'american_dollar', today, params['Username'], today, params['Username']]
for price in prices:
    testvalues[2] = price[1]
    testvalues[3] = price[0]
    print(tuple(testvalues))
    cur.execute(insertion,tuple(testvalues))
connection.commit()
cur.close()
connection.close()

