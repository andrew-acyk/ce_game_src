#!/bin/bash
# 
# This script removes the limit on the size of core dump.
sudo sh -c "ulimit -c unlimited && exec su $LOGNAME"
