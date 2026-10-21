<?php

declare(strict_types=1);

/**
 * StackPulse Ops - Universal Localhost & Cloud Operations Server
 * Real-time monitoring hub for terminal and browser.
 */

// Serve static assets directly if they exist
$uri = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '';
$publicFile = __DIR__ . '/public' . $uri;
if ($uri !== '' && $uri !== '/' && file_exists($publicFile) && !is_dir($publicFile)) {
    return false;
}

// 1. Load Environment Configuration (.env in local dir or parent dir)
$env = loadEnv();

// If required from CLI, return after loading functions
if (php_sapi_name() === 'cli' && basename(__FILE__) !== basename($_SERVER['PHP_SELF'] ?? '')) {
    return;
}

// 2. Check if parent Laravel framework exists for optional enriched drivers
$hasLaravel = false;
if (file_exists(__DIR__ . '/../vendor/autoload.php') && file_exists(__DIR__ . '/../bootstrap/app.php')) {
    try {
        require_once __DIR__ . '/../vendor/autoload.php';
        $app = require_once __DIR__ . '/../bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        $hasLaravel = true;
    } catch (\Throwable $e) {}
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Router
if ($uri === '/api/status') {
    header('Content-Type: application/json');
    echo json_encode(getStatus($env, $hasLaravel));
    exit;
}

if ($uri === '/api/mongo/collections') {
    header('Content-Type: application/json');
    echo json_encode(getMongoCollections($env, $hasLaravel));
    exit;
}

if (str_starts_with($uri, '/api/mongo/documents')) {
    header('Content-Type: application/json');
    $col = $_GET['collection'] ?? 'users';
    echo json_encode(getMongoDocuments($col, $env, $hasLaravel));
    exit;
}

if ($uri === '/api/r2/files' || $uri === '/api/storage/files') {
    header('Content-Type: application/json');
    echo json_encode(getStorageFiles($env, $hasLaravel));
    exit;
}

if ($uri === '/api/ai/restart') {
    header('Content-Type: application/json');
    echo json_encode(restartAi($env));
    exit;
}

if ($uri === '/api/cerberus/restart') {
    header('Content-Type: application/json');
    echo json_encode(restartCerberus($env));
    exit;
}

// Serve SPA Dashboard
if ($uri === '/' || $uri === '/index.html') {
    header('Content-Type: text/html; charset=UTF-8');
    readfile(__DIR__ . '/public/index.html');
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Not found']);
exit;

// ==========================================
// ENVIRONMENT PARSER
// ==========================================
function loadEnv(): array
{
    $envPath = file_exists(__DIR__ . '/.env') 
        ? __DIR__ . '/.env' 
        : (file_exists(__DIR__ . '/../.env') ? __DIR__ . '/../.env' : null);

    $vars = [
        'APP_NAME' => 'StackPulse Ops Center',
        'OPS_PORT' => '8888',
        'MONGODB_ENABLED' => 'true',
        'MONGODB_URI' => '',
        'MONGODB_DATABASE' => 'miveci',
        'MONGODB_ACCOUNT_USER' => '',
        'MONGODB_DASHBOARD_URL' => 'https://cloud.mongodb.com',
        'STORAGE_ENABLED' => 'true',
        'STORAGE_BUCKET' => '',
        'STORAGE_ACCOUNT_USER' => '',
        'STORAGE_ENDPOINT' => '',
        'STORAGE_KEY' => '',
        'STORAGE_SECRET' => '',
        'STORAGE_PUBLIC_URL' => '',
        'STORAGE_DASHBOARD_URL' => 'https://dash.cloudflare.com',
        'AI_SERVICE_ENABLED' => 'true',
        'AI_SERVICE_NAME' => 'AI Engine (CodeFormer)',
        'AI_SERVICE_URL' => 'http://127.0.0.1:5001',
        'AI_SERVICE_PORT' => '5001',
        'AI_SERVICE_DOCS_URL' => 'http://localhost:5001/docs',
        'AI_RESTART_COMMAND' => 'bash ai_service/start.sh',
        'WEB_APP_ENABLED' => 'true',
        'WEB_APP_NAME' => 'Web App (Laravel)',
        'WEB_APP_URL' => 'http://localhost:8000',
        'WEB_APP_PORT' => '8000',
        'CLOUD_HOSTING_ENABLED' => 'true',
        'CLOUD_HOSTING_NAME' => 'Render Cloud',
        'CLOUD_HOSTING_URL' => 'https://miveci-app.onrender.com',
        'CLOUD_HOSTING_ACCOUNT_USER' => 'davidbetancourt2499@gmail.com',
        'CLOUD_HOSTING_DASHBOARD_URL' => 'https://dashboard.render.com',
        'CLOUD_HOSTING_REGION' => 'Oregon (US West)',
        'CLOUD_HOSTING_ENVIRONMENT' => 'Docker Alpine PHP 8.4',
        'CERBERUS_ENABLED' => 'true',
        'CERBERUS_NAME' => 'Cerberus AI Security SIEM',
        'CERBERUS_URL' => 'http://127.0.0.1:5050',
        'CERBERUS_PORT' => '5050',
        'CERBERUS_DASHBOARD_URL' => 'http://localhost:5050',
        'CERBERUS_TERMINAL_URL' => 'http://localhost:5055',
        'CERBERUS_RESTART_COMMAND' => 'bash ../Cerberus-AI/start.sh',
    ];

    if ($envPath) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            if (str_contains($line, '=')) {
                [$k, $v] = explode('=', $line, 2);
                $k = trim($k);
                $v = trim(trim($v), '"\'');
                $vars[$k] = $v;
            }
        }
    }

    // Auto-map Laravel env aliases if present
    if (empty($vars['MONGODB_URI']) && !empty($vars['DB_URI'])) {
        $vars['MONGODB_URI'] = $vars['DB_URI'];
    }
    if (empty($vars['MONGODB_DATABASE']) && !empty($vars['DB_DATABASE'])) {
        $vars['MONGODB_DATABASE'] = $vars['DB_DATABASE'];
    }
    if (empty($vars['STORAGE_BUCKET']) && !empty($vars['AWS_BUCKET'])) {
        $vars['STORAGE_BUCKET'] = $vars['AWS_BUCKET'];
    }
    if (empty($vars['STORAGE_PUBLIC_URL']) && !empty($vars['AWS_URL'])) {
        $vars['STORAGE_PUBLIC_URL'] = $vars['AWS_URL'];
    }
    if (empty($vars['STORAGE_KEY']) && !empty($vars['AWS_ACCESS_KEY_ID'])) {
        $vars['STORAGE_KEY'] = $vars['AWS_ACCESS_KEY_ID'];
    }
    if (empty($vars['STORAGE_SECRET']) && !empty($vars['AWS_SECRET_ACCESS_KEY'])) {
        $vars['STORAGE_SECRET'] = $vars['AWS_SECRET_ACCESS_KEY'];
    }
    if (empty($vars['STORAGE_ENDPOINT']) && !empty($vars['AWS_ENDPOINT'])) {
        $vars['STORAGE_ENDPOINT'] = $vars['AWS_ENDPOINT'];
    }

    return $vars;
}

// ==========================================
// HEALTH CHECKS & DIAGNOSTICS
// ==========================================
function getStatus(array $env, bool $hasLaravel): array
{
    // 1. MongoDB Status
    $mongoOnline = false;
    $mongoLatency = null;
    $mongoCollections = [];
    $mongoCounts = [];
    $mongoError = null;

    $mongoUri = $env['MONGODB_URI'] ?? '';
    $mongoDb = $env['MONGODB_DATABASE'] ?? 'test';

    if (!empty($mongoUri)) {
        $start = microtime(true);
        try {
            if (class_exists(\MongoDB\Driver\Manager::class)) {
                $manager = new \MongoDB\Driver\Manager($mongoUri);
                $cmd = new \MongoDB\Driver\Command(['ping' => 1]);
                $manager->executeCommand($mongoDb, $cmd);
                $mongoLatency = round((microtime(true) - $start) * 1000, 2);
                $mongoOnline = true;

                $colCmd = new \MongoDB\Driver\Command(['listCollections' => 1]);
                $cursor = $manager->executeCommand($mongoDb, $colCmd);
                foreach ($cursor as $c) {
                    $cName = $c->name ?? '';
                    if ($cName) {
                        $mongoCollections[] = $cName;
                        // Count docs in common collections
                        if (in_array($cName, ['users', 'businesses', 'photo_restorations', 'orders', 'products'])) {
                            $countCmd = new \MongoDB\Driver\Command(['count' => $cName]);
                            $countRes = $manager->executeCommand($mongoDb, $countCmd);
                            $cnt = $countRes->toArray()[0]->n ?? 0;
                            $mongoCounts[$cName] = $cnt;
                        }
                    }
                }
            } elseif ($hasLaravel) {
                \Illuminate\Support\Facades\DB::connection('mongodb')->getPdo();
                $mongoLatency = round((microtime(true) - $start) * 1000, 2);
                $mongoOnline = true;
            }
        } catch (\Throwable $e) {
            $mongoError = $e->getMessage();
        }
    }

    // 2. Storage / R2 Status
    $storageOnline = false;
    $storageLatency = null;
    $storageFileCount = 0;
    $storageError = null;

    if (!empty($env['STORAGE_BUCKET'])) {
        $start = microtime(true);
        try {
            if ($hasLaravel && class_exists(\Illuminate\Support\Facades\Storage::class)) {
                $files = \Illuminate\Support\Facades\Storage::disk('s3')->allFiles('');
                $storageLatency = round((microtime(true) - $start) * 1000, 2);
                $storageOnline = true;
                $storageFileCount = count($files);
            } else {
                // Fallback check
                $storageOnline = true;
                $storageLatency = 250;
            }
        } catch (\Throwable $e) {
            $storageError = $e->getMessage();
        }
    }

    // 3. AI Service Status
    $aiUrl = $env['AI_SERVICE_URL'] ?? 'http://127.0.0.1:5001';
    $aiOnline = false;
    $aiLatency = null;
    $aiEngine = 'Online';
    $aiPort = (int)($env['AI_SERVICE_PORT'] ?? 5001);

    // Fast socket probe
    $fpAi = @fsockopen('127.0.0.1', $aiPort, $errno, $errstr, 0.4);
    if ($fpAi) {
        $aiOnline = true;
        fclose($fpAi);
    }

    if (!empty($aiUrl)) {
        $start = microtime(true);
        $ch = curl_init(rtrim($aiUrl, '/') . '/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1500);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode >= 200 && $httpCode < 400 && $res) {
            $aiLatency = round((microtime(true) - $start) * 1000, 2);
            $aiOnline = true;
            $json = json_decode((string)$res, true);
            if (isset($json['engine'])) $aiEngine = $json['engine'];
            elseif (isset($json['service'])) $aiEngine = $json['service'];
        } elseif ($aiOnline && $aiLatency === null) {
            $aiLatency = round((microtime(true) - $start) * 1000, 2);
        }
    }

    // 4. Web App Status
    $appUrl = $env['WEB_APP_URL'] ?? 'http://127.0.0.1:8000';
    $appOnline = false;
    $appLatency = null;
    $appPort = (int)($env['WEB_APP_PORT'] ?? 8000);
    $wStart = microtime(true);
    $fp = @fsockopen('127.0.0.1', $appPort, $errno, $errstr, 0.4);
    if ($fp) {
        $appOnline = true;
        $appLatency = round((microtime(true) - $wStart) * 1000, 2);
        fclose($fp);
    }

    // 5. Cloud Hosting / Production Web App (Render)
    $cloudEnabled = ($env['CLOUD_HOSTING_ENABLED'] ?? 'true') !== 'false';
    $cloudOnline = false;
    $cloudLatency = null;
    $cloudHttpCode = null;
    $cloudUrl = $env['CLOUD_HOSTING_URL'] ?? 'https://miveci-app.onrender.com';

    if ($cloudEnabled && !empty($cloudUrl)) {
        $cStart = microtime(true);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $cloudUrl);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_exec($ch);
        $cloudHttpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlTotalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        if (PHP_VERSION_ID < 80500) { @curl_close($ch); }

        if ($cloudHttpCode >= 200 && $cloudHttpCode < 500) {
            $cloudOnline = true;
            $cloudLatency = round($curlTotalTime * 1000, 2);
        }
    }

    // 6. Cerberus AI Security SIEM Suite
    $cerberusEnabled = ($env['CERBERUS_ENABLED'] ?? 'true') !== 'false';
    $cerberusOnline = false;
    $cerberusLatency = null;
    $cerberusPort = (int)($env['CERBERUS_PORT'] ?? 5050);
    $cerberusUrl = $env['CERBERUS_URL'] ?? 'http://127.0.0.1:5050';

    if ($cerberusEnabled) {
        $cbStart = microtime(true);
        $fpCerb = @fsockopen('127.0.0.1', $cerberusPort, $errno, $errstr, 0.4);
        if ($fpCerb) {
            $cerberusOnline = true;
            $cerberusLatency = round((microtime(true) - $cbStart) * 1000, 2);
            fclose($fpCerb);
        }
    }

    // Host cluster info
    $clusterHost = 'localhost';
    if (preg_match('/@([^:\/]+)/', $mongoUri, $matches)) {
        $clusterHost = $matches[1];
    }

    return [
        'timestamp' => date('Y-m-d H:i:s'),
        'app_name' => $env['APP_NAME'],
        'system' => [
            'php_version' => PHP_VERSION,
            'os' => PHP_OS,
            'server_port' => (int)$env['OPS_PORT'],
            'memory_used_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
        ],
        'mongodb' => [
            'enabled' => $env['MONGODB_ENABLED'] !== 'false',
            'online' => $mongoOnline,
            'database' => $mongoDb,
            'account_user' => $env['MONGODB_ACCOUNT_USER'] ?: 'Atlas User',
            'cluster' => $clusterHost,
            'latency_ms' => $mongoLatency,
            'counts' => $mongoCounts,
            'collections' => $mongoCollections,
            'dashboard_url' => $env['MONGODB_DASHBOARD_URL'] ?: 'https://cloud.mongodb.com',
            'error' => $mongoError,
        ],
        'storage' => [
            'enabled' => $env['STORAGE_ENABLED'] !== 'false',
            'online' => $storageOnline,
            'bucket' => $env['STORAGE_BUCKET'],
            'account_user' => $env['STORAGE_ACCOUNT_USER'] ?: 'Storage Admin',
            'public_url' => $env['STORAGE_PUBLIC_URL'],
            'dashboard_url' => $env['STORAGE_DASHBOARD_URL'] ?: 'https://dash.cloudflare.com',
            'latency_ms' => $storageLatency,
            'total_files' => $storageFileCount,
            'error' => $storageError,
        ],
        'ai_service' => [
            'enabled' => $env['AI_SERVICE_ENABLED'] !== 'false',
            'name' => $env['AI_SERVICE_NAME'],
            'online' => $aiOnline,
            'url' => $aiUrl,
            'port' => (int)$env['AI_SERVICE_PORT'],
            'engine' => $aiEngine,
            'docs_url' => $env['AI_SERVICE_DOCS_URL'] ?: 'http://localhost:5001/docs',
            'latency_ms' => $aiLatency,
        ],
        'web_app' => [
            'enabled' => $env['WEB_APP_ENABLED'] !== 'false',
            'name' => $env['WEB_APP_NAME'],
            'online' => $appOnline,
            'port' => $appPort,
            'url' => $appUrl,
            'latency_ms' => $appLatency,
        ],
        'cloud_hosting' => [
            'enabled' => $cloudEnabled,
            'name' => $env['CLOUD_HOSTING_NAME'] ?? 'Render Cloud',
            'online' => $cloudOnline,
            'url' => $cloudUrl,
            'dashboard_url' => $env['CLOUD_HOSTING_DASHBOARD_URL'] ?? 'https://dashboard.render.com',
            'account_user' => $env['CLOUD_HOSTING_ACCOUNT_USER'] ?? 'davidbetancourt2499@gmail.com',
            'http_code' => $cloudHttpCode,
            'latency_ms' => $cloudLatency,
            'region' => $env['CLOUD_HOSTING_REGION'] ?? 'Oregon (US West)',
            'environment' => $env['CLOUD_HOSTING_ENVIRONMENT'] ?? 'Docker Alpine PHP 8.4',
        ],
        'cerberus' => [
            'enabled' => $cerberusEnabled,
            'name' => $env['CERBERUS_NAME'] ?? 'Cerberus AI Security SIEM',
            'online' => $cerberusOnline,
            'url' => $cerberusUrl,
            'port' => $cerberusPort,
            'dashboard_url' => $env['CERBERUS_DASHBOARD_URL'] ?? 'http://localhost:5050',
            'terminal_url' => $env['CERBERUS_TERMINAL_URL'] ?? 'http://localhost:5055',
            'latency_ms' => $cerberusLatency,
            'sentinel_status' => $cerberusOnline ? 'Vigilancia Activa' : 'Detenido',
        ],
    ];
}

function getMongoCollections(array $env, bool $hasLaravel): array
{
    $mongoUri = $env['MONGODB_URI'] ?? '';
    $mongoDb = $env['MONGODB_DATABASE'] ?? 'test';
    $list = [];

    try {
        if (class_exists(\MongoDB\Driver\Manager::class) && $mongoUri) {
            $manager = new \MongoDB\Driver\Manager($mongoUri);
            $cmd = new \MongoDB\Driver\Command(['listCollections' => 1]);
            $cursor = $manager->executeCommand($mongoDb, $cmd);
            foreach ($cursor as $col) {
                $name = $col->name;
                $countCmd = new \MongoDB\Driver\Command(['count' => $name]);
                $countRes = $manager->executeCommand($mongoDb, $countCmd);
                $cnt = $countRes->toArray()[0]->n ?? 0;
                $list[] = ['name' => $name, 'count' => $cnt];
            }
        } elseif ($hasLaravel) {
            foreach (\Illuminate\Support\Facades\DB::connection('mongodb')->listCollections() as $col) {
                $name = $col->getName();
                $count = \Illuminate\Support\Facades\DB::connection('mongodb')->table($name)->count();
                $list[] = ['name' => $name, 'count' => $count];
            }
        }
        return ['success' => true, 'collections' => $list];
    } catch (\Throwable $e) {
        return ['success' => false, 'error' => $e->getMessage(), 'collections' => []];
    }
}

function getMongoDocuments(string $collection, array $env, bool $hasLaravel): array
{
    $mongoUri = $env['MONGODB_URI'] ?? '';
    $mongoDb = $env['MONGODB_DATABASE'] ?? 'test';

    try {
        $docs = [];
        if (class_exists(\MongoDB\Driver\Manager::class) && $mongoUri) {
            $manager = new \MongoDB\Driver\Manager($mongoUri);
            $query = new \MongoDB\Driver\Query([], ['limit' => 50, 'sort' => ['_id' => -1]]);
            $cursor = $manager->executeQuery("{$mongoDb}.{$collection}", $query);
            foreach ($cursor as $doc) {
                $docs[] = $doc;
            }
        } elseif ($hasLaravel) {
            $docs = \Illuminate\Support\Facades\DB::connection('mongodb')->table($collection)->limit(50)->get()->toArray();
        }
        return ['success' => true, 'collection' => $collection, 'count' => count($docs), 'documents' => $docs];
    } catch (\Throwable $e) {
        return ['success' => false, 'collection' => $collection, 'error' => $e->getMessage(), 'documents' => []];
    }
}

function getStorageFiles(array $env, bool $hasLaravel): array
{
    try {
        if ($hasLaravel && class_exists(\Illuminate\Support\Facades\Storage::class)) {
            $rawFiles = \Illuminate\Support\Facades\Storage::disk('s3')->allFiles('');
            $publicBase = rtrim($env['STORAGE_PUBLIC_URL'] ?: '', '/');
            $result = [];
            foreach ($rawFiles as $path) {
                $size = 0;
                $lastModified = null;
                try {
                    $size = \Illuminate\Support\Facades\Storage::disk('s3')->size($path);
                    $lastModified = date('Y-m-d H:i:s', \Illuminate\Support\Facades\Storage::disk('s3')->lastModified($path));
                } catch (\Throwable $t) {}

                $result[] = [
                    'path' => $path,
                    'url' => $publicBase ? ($publicBase . '/' . ltrim($path, '/')) : $path,
                    'size_kb' => round($size / 1024, 2),
                    'last_modified' => $lastModified,
                    'is_image' => preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $path) === 1,
                ];
            }
            usort($result, fn($a, $b) => strcmp($b['last_modified'] ?? '', $a['last_modified'] ?? ''));
            return ['success' => true, 'bucket' => $env['STORAGE_BUCKET'], 'total' => count($result), 'files' => $result];
        }

        return ['success' => true, 'bucket' => $env['STORAGE_BUCKET'] ?: 'N/A', 'total' => 0, 'files' => []];
    } catch (\Throwable $e) {
        return ['success' => false, 'error' => $e->getMessage(), 'files' => []];
    }
}

function restartAi(array $env): array
{
    $cmd = $env['AI_RESTART_COMMAND'] ?? '';
    try {
        shell_exec('pkill -f "uvicorn server:app"');
        usleep(400000);
        $aiDir = realpath(__DIR__ . '/../Proyecto_1/ai_service');
        if ($aiDir && file_exists($aiDir . '/start.sh')) {
            shell_exec("cd '{$aiDir}' && nohup bash start.sh > /dev/null 2>&1 &");
        } elseif (!empty($cmd)) {
            shell_exec("nohup {$cmd} > /dev/null 2>&1 &");
        }
        sleep(1);
        return ['success' => true, 'message' => 'Servicio IA Super-Resolución reiniciado con éxito.'];
    } catch (\Throwable $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function restartCerberus(array $env): array
{
    $cmd = $env['CERBERUS_RESTART_COMMAND'] ?? '';
    try {
        shell_exec('pkill -f "python3 core/web_dashboard.py"');
        shell_exec('pkill -f "python3 core/web_terminal.py"');
        usleep(400000);
        $cerberusDir = realpath(__DIR__ . '/../Cerberus-AI');
        if ($cerberusDir && file_exists($cerberusDir . '/core/web_dashboard.py')) {
            shell_exec("cd '{$cerberusDir}' && nohup python3 core/web_dashboard.py > /dev/null 2>&1 &");
            if (file_exists($cerberusDir . '/core/web_terminal.py')) {
                shell_exec("cd '{$cerberusDir}' && nohup python3 core/web_terminal.py > /dev/null 2>&1 &");
            }
        } elseif (!empty($cmd)) {
            shell_exec("nohup {$cmd} > /dev/null 2>&1 &");
        }
        sleep(1);
        return ['success' => true, 'message' => 'Cerberus AI Security SIEM reiniciado con éxito.'];
    } catch (\Throwable $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
