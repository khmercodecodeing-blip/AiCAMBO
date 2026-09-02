<?php

/**
 * GitHub Webhook Receiver — Auto-deploy via cPanel Git Version Control (UAPI)
 * On every push to the configured branch, tells cPanel to pull + deploy the repo automatically.
 * Keep this file's secret in .env only — never commit real secrets to git.
 */

require_once dirname(__DIR__) . '/config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$secret = DEPLOY_WEBHOOK_SECRET;
$payload = file_get_contents('php://input');
$signatureHeader = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

// Verify the request really came from GitHub using the shared secret (HMAC, timing-safe compare)
if (empty($secret) || empty($signatureHeader) || !hash_equals(
    'sha256=' . hash_hmac('sha256', $payload, $secret),
    $signatureHeader
)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

$data = json_decode($payload, true);
$branch = isset($data['ref']) ? str_replace('refs/heads/', '', $data['ref']) : '';
$deployBranch = DEPLOY_BRANCH;

if ($branch !== $deployBranch) {
    echo json_encode(['status' => 'ignored', 'reason' => "not $deployBranch branch"]);
    exit;
}

// Ask cPanel (UAPI) to pull the latest commit and deploy it to the document root
$cpanelHost   = CPANEL_HOST;   // e.g. server.yourhost.com
$cpanelUser   = CPANEL_USER;
$cpanelToken  = CPANEL_API_TOKEN;
$repoRoot     = CPANEL_REPO_ROOT; // e.g. /home/username/repositories/website

$logFile = APP_ROOT . '/storage/deploy.log';
$log = fn(string $msg) => file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] $msg\n", FILE_APPEND);

if (empty($cpanelHost) || empty($cpanelUser) || empty($cpanelToken) || empty($repoRoot)) {
    $log('Deploy skipped: missing cPanel API configuration in .env');
    http_response_code(500);
    echo json_encode(['error' => 'Server not configured for auto-deploy']);
    exit;
}

$results = [];
foreach (['update', 'deploy'] as $action) {
    $ch = curl_init("https://{$cpanelHost}:2083/execute/Gitcontrol/{$action}?repo_root=" . urlencode($repoRoot));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ["Authorization: cpanel {$cpanelUser}:{$cpanelToken}"],
        CURLOPT_TIMEOUT        => 30,
    ]);
    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    $results[$action] = $curlError ?: $response;
    $log("{$action}: " . ($curlError ?: $response));
}

echo json_encode(['status' => 'deployed', 'branch' => $branch]);
