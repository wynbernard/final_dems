<?php
include '../../../database/user_session.php';
require '../../../phpqrcode/qrlib.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$pre_reg_id = $_POST['pre_reg_id'];
	$update_qr = false;
	$query = "";
	$params = [];

	// Password Update
	if (isset($_POST['current_password'], $_POST['new_password'], $_POST['confirm_password'])) {
		$current_password = $_POST['current_password'];
		$new_password = $_POST['new_password'];
		$confirm_password = $_POST['confirm_password'];

		$stmt = $conn->prepare("SELECT password FROM pre_reg_table WHERE pre_reg_id = ?");
		$stmt->bind_param("i", $pre_reg_id);
		$stmt->execute();
		$result = $stmt->get_result();
		$user = $result->fetch_assoc();
		$stmt->close();

		if (!$user || !password_verify($current_password, $user['password'])) {
			$_SESSION['error'] = "Current password is incorrect.";
			header("Location: ../user_page/profile_user.php");
			exit();
		}

		if ($new_password !== $confirm_password) {
			$_SESSION['error'] = "New passwords do not match.";
			header("Location: ../user_page/profile_user.php");
			exit();
		}

		$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
		$stmt = $conn->prepare("UPDATE pre_reg_table SET password = ? WHERE pre_reg_id = ?");
		$stmt->bind_param("si", $hashed_password, $pre_reg_id);
		$stmt->execute();
		$stmt->close();

		$_SESSION['success'] = "Password updated successfully.";
		header("Location: ../user_page/profile_user.php");
		exit();
	}

	// Other Profile Updates
	if (isset($_POST['email_address'])) {
		$query = "UPDATE pre_reg_table SET email_address = ? WHERE pre_reg_id = ?";
		$params = [$_POST['email_address'], $pre_reg_id];
		$update_qr = true;
	} elseif (isset($_POST['f_name'])) {
		$query = "UPDATE pre_reg_table SET f_name = ? WHERE pre_reg_id = ?";
		$params = [$_POST['f_name'], $pre_reg_id];
		$update_qr = true;
	} elseif (isset($_POST['l_name'])) {
		$query = "UPDATE pre_reg_table SET l_name = ? WHERE pre_reg_id = ?";
		$params = [$_POST['l_name'], $pre_reg_id];
		$update_qr = true;
	} elseif (isset($_POST['contact_no'])) {
		$query = "UPDATE pre_reg_table SET contact_no = ? WHERE pre_reg_id = ?";
		$params = [$_POST['contact_no'], $pre_reg_id];
		$update_qr = true;
	} elseif (isset($_POST['gender'])) {
		$query = "UPDATE pre_reg_table SET gender = ? WHERE pre_reg_id = ?";
		$params = [$_POST['gender'], $pre_reg_id];
	} elseif (isset($_POST['date_of_birth'])) {
		$query = "UPDATE pre_reg_table SET date_of_birth = ? WHERE pre_reg_id = ?";
		$params = [$_POST['date_of_birth'], $pre_reg_id];
		$update_qr = true;
	}

	if (!empty($query)) {
		$stmt = $conn->prepare($query);
		$stmt->bind_param("si", $params[0], $params[1]);
		if ($stmt->execute()) {
			$_SESSION['success'] = "<span style='color: green;'><i class='bi bi-check-circle-fill'></i></span> Profile updated successfully.";
		} else {
			$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i></span> Profile update failed: " . $stmt->error;
		}
		header("Location: ../user_page/profile_user.php");
		exit();
	} else {
		$_SESSION['error'] = "Invalid form submission.";
		header("Location: ../user_page/profile_user.php");
		exit();
	}
} else {
	$_SESSION['error'] = "Invalid request method.";
	header("Location: ../user_page/profile_user.php");
	exit();
}
