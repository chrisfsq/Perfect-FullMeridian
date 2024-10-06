#!/bin/bash

kill $(ps aux | grep 'completou a missao' | awk '{print $2}')  > /dev/null 2>&1 &
pkill -f script_task.sh

echo "Parado com sucesso!"