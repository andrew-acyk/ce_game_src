#!/usr/bin/python3

import mysql.connector

DATABASE = 'ce_schema'
USER = 'ce_user'
PASSWORD = '4Apri1Fu11'
HOST = 'localhost'

def main():
    cnx = mysql.connector.connect(user=USER, database=DATABASE, password=PASSWORD, host=HOST)

    cursor1 = cnx.cursor()
    
    carbon_price_query = ("SELECT carbon_price_last_perd "
                          "FROM ce_game "
                          "WHERE game_id = '%s'")
                          
    carbon_price_last_perd = 0
    # Need to change game_id ??
    game_id = '1'

    cursor1.execute(carbon_price_query, (game_id))
    carbon_price_last_perd = cursor1.fetchone()
    print("carbon price in float:", carbon_price_last_perd);

    cursor1.close()
    cnx.close()

if __name__ == '__main__':
    main()
