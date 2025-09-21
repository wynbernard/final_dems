<?php
// Return JSON for AJAX consumers
// Start output buffering to prevent accidental HTML/whitespace from breaking JSON
ob_start();
header('Content-Type: application/json; charset=utf-8');

// Include DB connection (user_session.php redirects which breaks AJAX calls)
require_once '../../../database/conn.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	ob_clean();
	echo json_encode(['success' => false, 'message' => 'invalid_method']);
	exit;
}

// Validate user session for AJAX (return JSON instead of redirect)
$pre_reg_session = $_SESSION['pre_reg_id'] ?? null;
$user_session_token = $_SESSION['user_session_token'] ?? null;
if (!$pre_reg_session || !$user_session_token) {
	http_response_code(401);
	ob_clean();
	echo json_encode(['success' => false, 'message' => 'unauthenticated']);
	exit;
}

// Verify session token matches the DB record
$chk = $conn->prepare("SELECT user_session_token FROM pre_reg_table WHERE pre_reg_id = ?");
$chk->bind_param('i', $pre_reg_session);
$chk->execute();
$res = $chk->get_result();
$user = $res->fetch_assoc();
$chk->close();
if (!$user || $user['user_session_token'] !== $user_session_token) {
	http_response_code(401);
	ob_clean();
	echo json_encode(['success' => false, 'message' => 'unauthorized']);
	exit;
}

$pre_reg_id = intval($_POST['pre_reg_id'] ?? 0);
$f_name = trim($_POST['f_name'] ?? '');
$l_name = trim($_POST['l_name'] ?? '');
$date_of_birth = trim($_POST['date_of_birth'] ?? '');
$contact_no = trim($_POST['contact_no'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$relation = trim($_POST['relation_to_family'] ?? '');

if ($pre_reg_id <= 0) {
	ob_clean();
	echo json_encode(['success' => false, 'message' => 'Invalid member ID']);
	exit;
}

// Ownership check: ensure the member belongs to the same family as the session user
$famChk = $conn->prepare("SELECT family_id FROM pre_reg_table WHERE pre_reg_id = ?");
$famChk->bind_param('i', $pre_reg_session);
$famChk->execute();
$famRes = $famChk->get_result();
$sessionUser = $famRes->fetch_assoc();
$famChk->close();
$session_family_id = $sessionUser['family_id'] ?? null;

$memberFamChk = $conn->prepare("SELECT family_id FROM pre_reg_table WHERE pre_reg_id = ?");
$memberFamChk->bind_param('i', $pre_reg_id);
$memberFamChk->execute();
$memberRes = $memberFamChk->get_result();
$memberRow = $memberRes->fetch_assoc();
$memberFamChk->close();
$member_family_id = $memberRow['family_id'] ?? null;

if ($session_family_id === null || $member_family_id === null || $session_family_id != $member_family_id) {
	http_response_code(403);
	ob_clean();
	echo json_encode(['success' => false, 'message' => 'forbidden']);
	exit;
}

$update = $conn->prepare("UPDATE pre_reg_table 
SET f_name = ?,
    l_name = ?,
    date_of_birth = ?,
    contact_no = ?,
    gender = ?,
    relation_to_family = ?
WHERE pre_reg_id = ?;");
if (!$update) {
	ob_clean();
	http_response_code(500);
	echo json_encode(['success' => false, 'message' => $conn->error]);
	exit;
}
$update->bind_param('ssssssi', $f_name, $l_name, $date_of_birth, $contact_no, $gender, $relation, $pre_reg_id);
if ($update->execute()) {
	ob_clean();
	echo json_encode(['success' => true]);
} else {
	ob_clean();
	http_response_code(500);
	echo json_encode(['success' => false, 'message' => $conn->error]);
}
$update->close();
// Flush output buffer and end
ob_end_flush();
exit;
