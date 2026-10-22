#!/usr/bin/env bash

# ========================================================
# StackPulse Ops - Universal Localhost Operations Center
# ========================================================

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cd "$DIR"

# Auto-copy .env if missing
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        echo -e "\033[1;33m[!] No se encontró .env. Creando .env desde .env.example...\033[0m"
        cp .env.example .env
    elif [ -f "../.env" ]; then
        echo -e "\033[1;32m[✓] Detectado .env del proyecto principal.\033[0m"
    fi
fi

# Read port from .env if present
PORT=8888
if [ -f ".env" ]; then
    ENV_PORT=$(grep -E "^OPS_PORT=" .env | cut -d '=' -f2 | tr -d '"'\'' ')
    if [ -n "$ENV_PORT" ]; then
        PORT=$ENV_PORT
    fi
fi

# Terminal Colors
GREEN="\033[1;32m"
CYAN="\033[1;36m"
YELLOW="\033[1;33m"
PURPLE="\033[1;35m"
RED="\033[1;31m"
RESET="\033[0m"
BOLD="\033[1m"

clear

echo -e "${CYAN}"
echo "  ███████╗████████╗ █████╗  ██████╗██╗  ██╗██████╗ ██╗   ██╗██╗     ███████╗███████╗"
echo "  ██╔════╝╚══██╔══╝██╔══██╗██╔════╝██║ ██╔╝██╔══██╗██║   ██║██║     ██╔════╝██╔════╝"
echo "  ███████╗   ██║   ███████║██║     █████╔╝ ██████╔╝██║   ██║██║     ███████╗█████╗  "
echo "  ╚════██║   ██║   ██╔══██║██║     ██╔═██╗ ██╔═══╝ ██║   ██║██║     ╚════██║██╔══╝  "
echo "  ███████║   ██║   ██║  ██║╚██████╗██║  ██╗██║     ╚██████╔╝███████╗███████║███████╗"
echo "  ╚══════╝   ╚═╝   ╚═╝  ╚═╝ ╚═════╝╚═╝  ╚═╝╚═╝      ╚═════╝ ╚══════╝╚══════╝╚══════╝"
echo -e "${RESET}"
echo -e "${BOLD}================================================================================${RESET}"
echo -e "${PURPLE}  ⚡ UNIVERSAL LOCALHOST OPERATIONS CENTER (PUERTO ${PORT})${RESET}"
echo -e "${BOLD}================================================================================${RESET}"
echo -e "  ${YELLOW}Directorio:${RESET} $DIR"
echo -e ""

# Free port if occupied
PID=$(lsof -ti :$PORT)
if [ -n "$PID" ]; then
    echo -e "  ${YELLOW}[i]${RESET} Liberando puerto ${PORT} (PID anterior: $PID)..."
    kill -9 $PID 2>/dev/null
    sleep 0.4
fi

echo -e "  ${GREEN}[✓] Servidor Ops iniciado:${RESET} ${BOLD}http://localhost:${PORT}${RESET}"
echo ""
echo -e "  ${CYAN}Abriendo consola en tu navegador...${RESET}"
open "http://localhost:${PORT}" 2>/dev/null || open "http://127.0.0.1:${PORT}" 2>/dev/null

echo -e "${BOLD}--------------------------------------------------------------------------------${RESET}"
echo -e "  Presiona ${RED}Ctrl + C${RESET} en esta terminal cuando desees detener el monitor."
echo -e "${BOLD}--------------------------------------------------------------------------------${RESET}"
echo ""

php -d max_execution_time=0 -d memory_limit=512M -S 127.0.0.1:$PORT server.php
