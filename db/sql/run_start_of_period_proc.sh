#!/bin/sh -x
cd /home/ubuntu/ce_game_src/db/sql
./run_mysql_verbose_root.sh call_start_of_period_proc.sql 1> /tmp/run_start_of_period_proc.out 2>&1
