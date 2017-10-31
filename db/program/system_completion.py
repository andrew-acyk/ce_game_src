#!/usr/bin/python

import datetime
import sys
import mysql.connector
import argparse
from datetime import datetime, date, time

DATABASE = 'ce_schema'
USER = 'ce_user'
PASSWORD = '4Apri!Fu!!'
HOST = 'localhost'

def main(argv):
    parser = argparse.ArgumentParser()
    parser.add_argument("GAME_ID")
    parser.add_argument("GAME_START_DATE", help="format YYYY-MM-DD ", type=valid_date)
    parser.add_argument("GAME_PERIOD_ID")
    parser.add_argument("DEBUG")
    parser.parse_args()
    run(argv[1], argv[2])

def valid_date(d):
    try:
        return datetime.strptime(d, "%Y-%m-%d")
    except ValueError:
        raise argparse.ArgumentTypeError("Invalid date format: " + d)

def run(game_id, game_start_date):
    cnx = mysql.connector.connect(user=USER, database=DATABASE, password=PASSWORD, host=HOST)

    cursor1 = cnx.cursor()
    cursor2 = cnx.cursor()

    carbon_price_query = ("SELECT carbon_price_last_perd "
                          "FROM ce_game "
                          "WHERE game_id = '%s'")

    sys_completn_charge_perc_query = ("SELECT parameter_value "
                                 "FROM ce_configuration_parameter "
                                 "WHERE parameter_name = 'ce_sys_completn_charge_perc'")

    updated_by_system_query = ("SELECT parameter_value "
                                 "FROM ce_configuration_parameter "
                                 "WHERE parameter_name = 'ce_proc_nm_tradg_sys_completn'")

    carbon_price_last_perd_query = ("SELECT carbon_price_last_perd FROM ce_game WHERE game_id = %s")

    sys_completn_charge_perc = 0
    updated_by = ''
    carbon_price_last_perd = 0
    game_id = '1'

    cursor1.execute(carbon_price_query, (game_id))
    carbon_price_last_perd = cursor1.fetchone()

    cursor1.execute(sys_completn_charge_perc_query)
    sys_completn_charge_perc = cursor1.fetchone()

    cursor1.execute(updated_by_system_query)
    updated_by = cursor1.fetchone()

    # Game is defined by game_id and game_start_date
    buy_query = ("SELECT transaction_id, allowances_buy_amt, allowances_buy_price "
                 "FROM ce_emissions_trade a, ce_game b "
                 "WHERE b.game_id = '%s' "
                 "AND b.game_start_date = '%s'"
                 "AND a.game_id = b.game_id "
                 "AND a.game_start_date = b.game_start_date "
                 "AND a.game_period_id = b.game_period_id "
                 "AND a.market_id = b.market_id "
                 "and a.buy_or_sell = 'b' "
                 "AND a.status IN ('queued') "
                 "ORDER BY submit_datetime asc")

    sell_query = ("SELECT transaction_id, allowances_sell_amt, allowances_sell_price "
                 "FROM ce_emissions_trade a, ce_game b "
                 "WHERE b.game_id = '%s' "
                 "AND b.game_start_date = '%s'"
                 "AND a.game_id = b.game_id "
                 "AND a.game_start_date = b.game_start_date "
                 "AND a.game_period_id = b.game_period_id "
                 "AND a.market_id = b.market_id "
                 "and a.buy_or_sell = 's' "
                 "AND a.status IN ('queued') "
                 "ORDER BY submit_datetime asc")

    buy_update = ("UPDATE ce_emissions_trade a "
                 "SET status = 'system_completed', "
                 "  executed_amt = allowances_buy_amt, "
                 "  executed_price = %(executed_price)s, "
                 "  sys_completn_charge_perc = %(sys_completn_charge_perc)s, "
                 "  updated_date = %(updated_date)s, "
                 "  updated_by = %(updated_by)s "
                 "WHERE a.game_id = %(game_id)s "
                 "AND a.transaction_id = %(transaction_id)s "
                 "AND a.game_start_date = %(game_start_date)s "
                 "AND a.game_period_id = %(game_period_id)s ")

    sell_update = ("UPDATE ce_emissions_trade a "
                 "SET status = 'system_completed', "
                 "  executed_amt = allowances_sell_amt, "
                 "  executed_price = %(executed_price)s, "
                 "  sys_completn_charge_perc = %(sys_completn_charge_perc)s, "
                 "  updated_date = %(updated_date)s, "
                 "  updated_by = %(updated_by)s "
                 "WHERE a.game_id = %(game_id)s "
                 "AND a.transaction_id = %(transaction_id)s "
                 "AND a.game_start_date = %(game_start_date)s "
                 "AND a.game_period_id = %(game_period_id)s ")

    cursor1.execute(buy_query, (game_id, game_start_date))
    (this_transaction_id, this_buy_amt, this_buy_price) = cursor1.fetchone()
    while this_transaction_id is not none:
        executed_price = max(carbon_price_last_perd, float(row['allowances_buy_price']))
        system_completion_charge = float(sys_completn_charge_perc) * executed_price
        updated_date = datetime.now().strftime("%Y-%m-%d")
        new_data = {
            'executed_price': executed_price,
            'sys_completn_charge_perc': system_completion_charge,
            'updated_date': updated_date,
            'updated_by': updated_by,
            'game_id': row['game_id'],
            'transaction_id': row['transaction_id'],
            'game_start_date': row['game_start_date'],
            'game_period_id': row['game_period_id'],
        }
        print(buy_update, new_data);
        cursor2.execute(buy_update, new_data)

    cursor1.execute(sell_query, (game_id, game_start_date))
    for row in cursor1:
        executed_price = min(carbon_price_last_perd, float(row['allowances_sell_price']))
        system_completion_charge = float(sys_completn_charge_perc) * executed_price
        updated_date = datetime.now().strftime("%Y-%m-%d")
        new_data = {
            'executed_price': executed_price,
            'sys_completn_charge_perc': system_completion_charge,
            'updated_date': updated_date,
            'updated_by': updated_by,
            'game_id': row['game_id'],
            'transaction_id': row['transaction_id'],
            'game_start_date': row['game_start_date'],
            'game_period_id': row['game_period_id'],
        }
        print(sell_update, new_data);
        cursor2.execute(sell_update, new_data)

    cnx.commit()
    cursor1.close()
    cursor2.close()
    cnx.close()

if __name__ == '__main__':
    main(sys.argv[1:])
