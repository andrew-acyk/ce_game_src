#!/bin/sh -x
cd /home/ubuntu/ce_game_src/db/sql
./substitute_labels_dynamic_sql.sh start_of_period_proc 1> /tmp/run_substitute_labels.out 2>&1
