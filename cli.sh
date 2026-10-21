#!/usr/bin/env bash

# ========================================================
# StackPulse Ops CLI - Instant Terminal Health Check
# ========================================================

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cd "$DIR"

php -r '
require_once "server.php";
$env = loadEnv();
$status = getStatus($env, false);

$GREEN = "\033[1;32m";
$CYAN = "\033[1;36m";
$YELLOW = "\033[1;33m";
$PURPLE = "\033[1;35m";
$RED = "\033[1;31m";
$RESET = "\033[0m";
$BOLD = "\033[1m";

echo "\n";
echo "  " . $CYAN . "==============================================================" . $RESET . "\n";
echo "  " . $BOLD . "         STACKPULSE OPS - ESTADO EN VIVO DE SERVICIOS         " . $RESET . "\n";
echo "  " . $CYAN . "==============================================================" . $RESET . "\n";
echo "  " . $YELLOW . "App:" . $RESET . " " . $status["app_name"] . "   |   " . $YELLOW . "Fecha:" . $RESET . " " . date("Y-m-d H:i:s") . "\n";
echo "  --------------------------------------------------------------\n";

// 1. MongoDB
$m = $status["mongodb"];
if ($m["enabled"]) {
    if ($m["online"]) {
        echo "  " . $GREEN . "[✓] MongoDB:" . $RESET . "            CONECTADO (" . $m["latency_ms"] . "ms)\n";
        echo "      Base de Datos:      " . $m["database"] . " (" . $m["cluster"] . ")\n";
        echo "      Colecciones:        " . count($m["collections"]) . "\n";
        echo "      Consola Web:        " . $CYAN . $m["dashboard_url"] . $RESET . "\n";
    } else {
        echo "  " . $RED . "[✗] MongoDB:" . $RESET . "            ERROR: " . ($m["error"] ?: "No se pudo conectar") . "\n";
    }
} else {
    echo "  " . $YELLOW . "[-] MongoDB:" . $RESET . "            Deshabilitado en .env\n";
}

echo "  --------------------------------------------------------------\n";

// 2. Storage
$s = $status["storage"];
if ($s["enabled"]) {
    if ($s["online"]) {
        echo "  " . $GREEN . "[✓] S3 / Object Storage:" . $RESET . " ACTIVO (" . $s["latency_ms"] . "ms)\n";
        echo "      Bucket:             " . $s["bucket"] . "\n";
        echo "      Total Archivos:     " . $s["total_files"] . " archivos\n";
        echo "      Consola Web:        " . $CYAN . $s["dashboard_url"] . $RESET . "\n";
    } else {
        echo "  " . $RED . "[✗] S3 / Storage:" . $RESET . "       ERROR: " . ($s["error"] ?: "No disponible") . "\n";
    }
} else {
    echo "  " . $YELLOW . "[-] S3 / Storage:" . $RESET . "       Deshabilitado en .env\n";
}

echo "  --------------------------------------------------------------\n";

// 3. AI / Microservice
$a = $status["ai_service"];
if ($a["enabled"]) {
    if ($a["online"]) {
        echo "  " . $GREEN . "[✓] " . $a["name"] . ":" . $RESET . " ONLINE (Puerto " . $a["port"] . ")\n";
        echo "      Docs / Swagger:     " . $CYAN . $a["docs_url"] . $RESET . "\n";
    } else {
        echo "  " . $RED . "[✗] " . $a["name"] . ":" . $RESET . " OFFLINE (Puerto " . $a["port"] . " apagado)\n";
    }
}

echo "  --------------------------------------------------------------\n";

// 4. Web App
$w = $status["web_app"];
if ($w["enabled"]) {
    if ($w["online"]) {
        echo "  " . $GREEN . "[✓] " . $w["name"] . ":" . $RESET . "   EN LÍNEA (" . $w["url"] . ")\n";
    } else {
        echo "  " . $YELLOW . "[!] " . $w["name"] . ":" . $RESET . "   NO DETECTADO en puerto " . $w["port"] . "\n";
    }
}

echo "  --------------------------------------------------------------\n";

// 5. Cloud Hosting (Render)
$c = $status["cloud_hosting"];
if ($c["enabled"]) {
    if ($c["online"]) {
        echo "  " . $GREEN . "[✓] " . $c["name"] . ":" . $RESET . " EN LÍNEA (" . ($c["latency_ms"] ? $c["latency_ms"] . "ms" : "OK") . ")\n";
        echo "      URL Producción:     " . $CYAN . $c["url"] . $RESET . "\n";
    } else {
        echo "  " . $RED . "[✗] " . $c["name"] . ":" . $RESET . " INACTIVO / SLEEPING\n";
    }
}

echo "  --------------------------------------------------------------\n";

// 6. Cerberus AI Security SIEM
$cb = $status["cerberus"];
if ($cb["enabled"]) {
    if ($cb["online"]) {
        echo "  " . $GREEN . "[✓] " . $cb["name"] . ":" . $RESET . " ACTIVO (Puerto " . $cb["port"] . ")\n";
        echo "      Consola SIEM:       " . $CYAN . $cb["dashboard_url"] . $RESET . "\n";
        echo "      Terminal Web:       " . $CYAN . $cb["terminal_url"] . $RESET . "\n";
    } else {
        echo "  " . $YELLOW . "[!] " . $cb["name"] . ":" . $RESET . " OFFLINE (Puerto " . $cb["port"] . " apagado)\n";
    }
}

echo "  " . $CYAN . "==============================================================" . $RESET . "\n";
echo "  " . $BOLD . "Tip:" . $RESET . " Abre la interfaz gráfica con:\n";
echo "  " . $GREEN . "bash start.sh" . $RESET . " (se abre en http://localhost:" . $status["system"]["server_port"] . ")\n\n";
'
