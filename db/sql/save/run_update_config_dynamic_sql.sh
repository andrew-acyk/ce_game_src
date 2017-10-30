#!/bin/sh -x
cd /home/ubuntu/ce_game_src/db/sql
./update_config_dynamic_sql.sh start_of_period_proc 1 1 2016-09-26 1 1> /tmp/run_update_config.out 2>&1
