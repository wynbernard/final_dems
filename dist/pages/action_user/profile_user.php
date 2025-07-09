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
			$_SESSION['success'] = "Profile updated successfully.";

			if ($update_qr) {
				// Fetch updated data
				$stmt = $conn->prepare("
					SELECT pre_reg_table.*, qr_table.code 
					FROM pre_reg_table 
					LEFT JOIN qr_table ON pre_reg_table.pre_reg_id = qr_table.pre_reg_id 
					WHERE pre_reg_table.pre_reg_id = ?
				");
				$stmt->bind_param("i", $pre_reg_id);
				$stmt->execute();
				$result = $stmt->get_result();
				$user = $result->fetch_assoc();
				$stmt->close();

				if ($user) {
					$qrData = "Name: {$user['f_name']} {$user['l_name']}\nEmail: {$user['email_address']}\nPhone: 0{$user['contact_no']}\nGender: {$user['gender']}\nDOB: {$user['date_of_birth']}";
					if (!empty($user['code']) && file_exists($user['code'])) {
						unlink($user['code']);
					}

					$qr_dir = "../../../qrcodes/";
					if (!file_exists($qr_dir)) {
						mkdir($qr_dir, 0777, true);
					}

					$qrFilePath = $qr_dir . time() . "_$pre_reg_id.png";
					QRcode::png($qrData, $qrFilePath, QR_ECLEVEL_L, 6);

					if (!empty($user['code'])) {
						$qrStmt = $conn->prepare("UPDATE qr_table SET code = ? WHERE pre_reg_id = ?");
					} else {
						$qrStmt = $conn->prepare("INSERT INTO qr_table (code, pre_reg_id) VALUES (?, ?)");
					}
					$qrStmt->bind_param("si", $qrFilePath, $pre_reg_id);
					$qrStmt->execute();
					$qrStmt->close();

					$_SESSION['success'] = "✅ Profile updated & QR regenerated!";
				} else {
					$_SESSION['error'] = "QR regeneration failed.";
				}
			}
		} else {
			$_SESSION['error'] = "Error updating profile.";
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
