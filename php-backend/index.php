<?php
/**
 * Vista Chandra CRM - Main API Entry Point
 * Stable Router Version
 */

error_reporting(E_ALL);
require_once __DIR__ . '/helpers/RequestContext.php';

ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$requestStartTime = microtime(true);
$traceId = bin2hex(random_bytes(8));
header('X-Trace-Id: ' . $traceId);
RequestContext::setTraceId($traceId);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/* =========================
   AUTOLOAD
========================= */
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/models/' . $class . '.php',
        __DIR__ . '/controllers/' . $class . '.php',
        __DIR__ . '/helpers/' . $class . '.php',
    ];
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

/* =========================
   REQUEST PARSING
========================= */

// Use PATH_INFO if rewrite enabled
$path = $_SERVER['PATH_INFO'] ?? '';

// Fallback if rewrite not working
if (!$path) {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = str_replace('/chandra_crm/php-backend', '', $path);
    $path = str_replace('/index.php', '', $path);
}

$path = trim($path, '/');
$segments = $path ? explode('/', $path) : [];

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$queryParams = $_GET;

/* =========================
   RESPONSE HELPER
========================= */
function respond($result) {
    global $requestStartTime, $method, $path;

    $code = $result['code'] ?? 200;
    http_response_code($code);

    if (isset($result['error'])) {
        $message = $result['error'];
        $errorCode = $result['error_code'] ?? 'API_ERROR';
        $fieldErrors = $result['field_errors'] ?? (object) [];
        echo json_encode([
            'detail' => $message,
            'error' => [
                'code' => $errorCode,
                'message' => $message,
                'trace_id' => RequestContext::getTraceId(),
                'field_errors' => $fieldErrors
            ]
        ]);
    } else {
        echo json_encode($result['data']);
    }

    $durationMs = (int) round((microtime(true) - $requestStartTime) * 1000);
    error_log(json_encode([
        'type' => 'request_metric',
        'trace_id' => RequestContext::getTraceId(),
        'method' => $method,
        'route' => '/' . trim($path, '/'),
        'status_code' => $code,
        'duration_ms' => $durationMs
    ]));

    exit;
}

/* =========================
   AUTH MIDDLEWARE
========================= */
function authenticate() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';

    if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        respond(['error' => 'Authorization required', 'code' => 401]);
    }

    try {
        $payload = JWT::decode($matches[1]);

        $db = Database::getInstance();
        $user = $db->fetch(
            "SELECT id, username, role, extension, is_active FROM users WHERE id = ?",
            [$payload['user_id']]
        );

        if (!$user || !$user['is_active']) {
            respond(['error' => 'Unauthorized', 'code' => 401]);
        }

        return $user;

    } catch (Exception $e) {
        respond(['error' => 'Invalid token', 'code' => 401]);
    }
}

/* =========================
   ROUTER
========================= */

try {

    $resource = $segments[0] ?? '';
    $id       = $segments[1] ?? null;
    $action   = $segments[2] ?? null;

    switch ($resource) {

        /* ===== HEALTH ===== */
        case '':
        case 'health':
            respond([
                'data' => [
                    'status' => 'healthy',
                    'app' => 'Chandra CRM API',
                    'php' => PHP_VERSION,
                    'time' => date('c')
                ]
            ]);
            break;
/* ===== DASHBOARD ===== */
case 'dashboard':

    $user = authenticate();
    $controller = new DashboardController();

    if ($id === 'stats' && $method === 'GET') {
        respond($controller->getStats());
    }

    if ($id === 'agents' && $method === 'GET') {
        respond($controller->getLiveAgents());
    }

    respond(['error' => 'Dashboard endpoint not found', 'code' => 404]);
    break;

        /* ===== AUTH ===== */
        case 'auth':
            $auth = new AuthController();

            if ($id === 'login' && $method === 'POST') {
                respond($auth->login($input));
            }

            if ($id === 'register' && $method === 'POST') {
                respond($auth->register($input));
            }

            if ($id === 'me' && $method === 'GET') {
                $user = authenticate();
                respond($auth->me($user['id']));
            }

            respond(['error' => 'Auth endpoint not found', 'code' => 404]);
            break;

        /* ===== USERS ===== */
        case 'users':
            $user = authenticate();
            $controller = new UserController();

            if ($method === 'GET' && !$id)
                respond($controller->getAll($queryParams));

            if ($method === 'GET' && $id)
                respond($controller->getById($id));

            if ($method === 'PUT' && $id)
                respond($controller->update($id, $input, $user));

            if ($method === 'DELETE' && $id)
                respond($controller->delete($id, $user));

            respond(['error' => 'Users endpoint not found', 'code' => 404]);
            break;

        /* ===== CAMPAIGNS ===== */
        case 'campaigns':
            $user = authenticate();
            $controller = new CampaignController();

            if ($method === 'GET' && !$id)
                respond($controller->getAll($queryParams));

            if ($method === 'POST')
                respond($controller->create($input, $user));

            if ($method === 'PUT' && $id)
                respond($controller->update($id, $input, $user));

            if ($method === 'DELETE' && $id)
                respond($controller->delete($id, $user));

            respond(['error' => 'Campaign endpoint not found', 'code' => 404]);
            break;

        /* ===== PBX ===== */
        case 'pbx':
            $user = authenticate();
            $controller = new PBXController();

            if ($id === 'agent' && $action === 'status' && $method === 'POST')
                respond($controller->updateAgentStatus($input, $user));

            if ($id === 'dial' && $method === 'POST')
                respond($controller->dial($input, $user));

            respond(['error' => 'PBX endpoint not found', 'code' => 404]);
            break;
            case 'process-master':
    $controller=new ProcessMasterController();

    if($method==='GET') respond($controller->getAll());
    if($method==='POST') respond($controller->create($input));
    if($method==='DELETE' && $id) respond($controller->delete($id));

    break;

        default:
            respond(['error' => "Endpoint '{$resource}' not found", 'code' => 404]);
    }

} catch (Exception $e) {
    respond(['error' => $e->getMessage(), 'code' => 500]);
}
