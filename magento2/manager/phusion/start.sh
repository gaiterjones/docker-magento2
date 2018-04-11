#!/bin/sh
set -x
printenv > /etc/environment
/sbin/my_init
