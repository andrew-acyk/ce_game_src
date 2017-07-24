#!/bin/bash -x
# This script assume you have already generated a list of files in /tmp/f
# PATH will be substituted by the actual file name with path
cat /tmp/f|read_exec.sh -sep ":" -cmd "sudo chmod go+r PATH"
