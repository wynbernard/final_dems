<?php
header('Content-Type: application/json; charset=utf-8');

$sessionPath = __DIR__ . '/../../../database/session.php';
if (!file_exists($sessionPath)) {
	http_response_code(500);
	echo json_encode(['status' => 'error', 'message' => 'Session file missing']);
	exit;
}
include_once $sessionPath;
$role = $_SESSION['role'] ?? '';
if (strtolower($role) !== 'admin') {
	http_response_code(403);
	echo json_encode(['status' => 'forbidden']);
	exit;
}

$script = realpath(__DIR__ . '/predict_ts.py');
if (!$script || !file_exists($script)) {
	http_response_code(500);
	echo json_encode(['status' => 'error', 'message' => 'predict_ts.py not found']);
	exit;
}

$pythonCandidates = ['python3', 'python'];
$found = null;
foreach ($pythonCandidates as $p) {
	$which = trim(shell_exec("where $p 2>&1"));
	if ($which !== '') {
		$found = $p;
		break;
	}
}
if ($found === null) {
	$which = trim(shell_exec("where py 2>&1"));
	if ($which !== '') $found = 'py';
}
if ($found === null) {
	http_response_code(500);
	echo json_encode(['status' => 'error', 'message' => 'No python interpreter found']);
	exit;
}

$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) mkdir($logDir, 0755, true);
$logFile = $logDir . '/predict_ts_' . date('Ymd_His') . '.log';
$cmd = escapeshellcmd($found) . ' ' . escapeshellarg($script) . ' 2>&1';
$output = [];
exec($cmd, $output, $exit);
file_put_contents($logFile, "# run: " . date('c') . "\n# cmd: $cmd\n\n" . implode("\n", $output) . "\n\nExit: $exit\n");
http_response_code($exit === 0 ? 200 : 500);
echo json_encode(['status' => $exit === 0 ? 'ok' : 'error', 'exit' => $exit, 'log' => basename($logFile), 'preview' => array_slice($output, -40)]);
