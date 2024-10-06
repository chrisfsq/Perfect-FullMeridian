#!/bin/bash
SERVICE="script_task.sh"
if pgrep -x "$SERVICE" >/dev/null
then
    echo "Meridiano ja esta sendo executado."
  exit 1
else
  nohup ./script_task.sh > /dev/null 2>&1 &
  echo -e "\e[1;35mMeridiano Iniciando\e[1;32m[...]\e[0m"
  sleep 2
  echo "Meridiano Iniciado com sucesso!"
fi
