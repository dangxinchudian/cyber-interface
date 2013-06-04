#!/bin/bash

daemon1="constant.php"
daemon2="server.php"

kill -9 `ps axu | grep "$daemon1" | awk '{print $2}'` > /dev/null 2>&1
kill -9 `ps axu | grep "$daemon2" | awk '{print $2}'` > /dev/null 2>&1

nohup php ./$daemon1 &
nohup php ./$daemon2 &
