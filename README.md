# ⚡ StackPulse Ops

> **Universal Localhost & Cloud Operations Hub for Developers**  
> Monitor your Database, Object Storage, Microservices, and Web Apps in real-time from your terminal and a dedicated local port — without switching between 4 different browser tabs.

[![License: MIT](https://img.shields.io/badge/License-MIT-emerald.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://php.net)
[![Localhost](https://img.shields.io/badge/Port-8888-purple.svg)](http://localhost:8888)
[![Status](https://img.shields.io/badge/Status-Production%20Ready-success.svg)]()

---

## 🎯 What is StackPulse Ops?

**StackPulse Ops** is a lightweight, zero-dependency operations dashboard that runs directly in your terminal and serves a modern, dark-mode web console on `http://localhost:8888`.

Instead of constantly logging into cloud providers (MongoDB Atlas, Cloudflare R2, AWS, Docker, AI servers), **StackPulse Ops connects directly via low-level protocols in milliseconds**, giving you:
- **Instant Health & Latency Semaphores:** Live ping to your database, object storage, microservices, and web servers.
- **Embedded Database Explorer:** Browse collections and inspect live documents formatted in clean JSON.
- **Visual Storage Gallery:** Preview uploaded images, file sizes, and CDN URLs directly.
- **One-Click Official Links:** Direct deep-links to your cloud consoles and local Swagger/API documentation.
- **Terminal CLI Mode:** 1-second colored status check directly in your terminal.

---

## 🚀 Quick Start (in 30 seconds)

### 1. Clone the repository
```bash
git clone https://github.com/ricardodevcorex99/StackPulse-Ops.git
cd StackPulse-Ops
```

### 2. Configure your `.env`
```bash
cp .env.example .env
```
Open `.env` and fill in your connection details (MongoDB, S3/R2, AI service port, web app port).

### 3. Launch the Ops Center
```bash
bash start.sh
```
*Your terminal will launch the hub on `http://localhost:8888` and automatically open your default browser.*

---

## 🤖 Prompt for AI Assistants (Cursor, ChatGPT, Claude, Gemini)

Want to connect this monitor to your existing codebase automatically? Copy and paste this prompt to your AI assistant:

```text
Please integrate StackPulse Ops with my application:
1. Clone https://github.com/ricardodevcorex99/StackPulse-Ops into a local tools directory (or as a submodule).
2. Read my existing application configuration (database credentials, S3/R2 storage keys, microservice and backend ports).
3. Generate the corresponding .env file inside StackPulse-Ops.
4. Provide the exact bash commands to run the visual dashboard (port 8888) and the quick CLI check.
```

---

## 🛠️ CLI Mode (Terminal Only)

If you don't want to open a browser and just want an instant 1-second terminal status check:

```bash
bash cli.sh
```

**Output:**
```text
  ==============================================================
           STACKPULSE OPS - ESTADO EN VIVO DE SERVICIOS         
  ==============================================================
  App: StackPulse Ops Center   |   Fecha: 2026-10-20 21:55:00
  --------------------------------------------------------------
  [✓] MongoDB:            CONECTADO (25.4ms)
      Base de Datos:      production_db (cluster.mongodb.net)
      Colecciones:        4
      Consola Web:        https://cloud.mongodb.com
  --------------------------------------------------------------
  [✓] S3 / Object Storage: ACTIVO (280.1ms)
      Bucket:             my-app-storage
      Total Archivos:     20 archivos
      Consola Web:        https://dash.cloudflare.com
  --------------------------------------------------------------
  [✓] AI Microservice:    ONLINE (Puerto 5001)
      Docs / Swagger:     http://localhost:5001/docs
  --------------------------------------------------------------
  [✓] Web App:            EN LÍNEA (http://localhost:8000)
  ==============================================================
```

---

## ⚙️ Configuration Reference (`.env`)

| Variable | Description | Example / Default |
| :--- | :--- | :--- |
| `APP_NAME` | Name shown in the dashboard header | `"My App Ops Center"` |
| `OPS_PORT` | Local port for this dashboard | `8888` |
| `MONGODB_ENABLED` | Enable/disable MongoDB monitoring | `true` |
| `MONGODB_URI` | MongoDB Atlas / local connection string | `mongodb+srv://user:pass@cluster...` |
| `MONGODB_DATABASE` | Database name to query | `my_database` |
| `MONGODB_DASHBOARD_URL` | Deep-link to cloud database dashboard | `https://cloud.mongodb.com` |
| `STORAGE_ENABLED` | Enable/disable S3/R2 storage monitoring | `true` |
| `STORAGE_BUCKET` | S3 / Cloudflare R2 bucket name | `my-bucket-2026` |
| `STORAGE_PUBLIC_URL` | Public CDN base URL for files | `https://pub-xxx.r2.dev` |
| `STORAGE_DASHBOARD_URL` | Deep-link to cloud storage dashboard | `https://dash.cloudflare.com` |
| `AI_SERVICE_ENABLED` | Enable/disable AI microservice check | `true` |
| `AI_SERVICE_URL` | Microservice root or health check URL | `http://127.0.0.1:5001` |
| `AI_SERVICE_PORT` | Port of the microservice | `5001` |
| `AI_SERVICE_DOCS_URL` | URL to Swagger / API documentation | `http://localhost:5001/docs` |
| `WEB_APP_ENABLED` | Enable/disable main web app check | `true` |
| `WEB_APP_URL` | Local or production web app URL | `http://localhost:8000` |
| `WEB_APP_PORT` | Port of the web application | `8000` |

---

## 📦 Requirements

- **PHP 8.1+** CLI (pre-installed on macOS and Linux).
- Compatible with all major stacks: **Laravel, Node.js, Next.js, Django, FastAPI, Docker, Supabase, Cloudflare R2, MongoDB Atlas, AWS S3**.

---

## 📄 License

This project is licensed under the [MIT License](LICENSE). Feel free to use, modify, and distribute it in your personal and commercial projects.
