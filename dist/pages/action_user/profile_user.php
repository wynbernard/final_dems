<?php
include '../../../database/user_session.php';
require '../../../phpqrcode/qrlib.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$pre_reg_id = $_POST['pre_reg_id'] ?? null;
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

	// 1. Get input values from POST
		$region = $_POST['region'] ?? '';
		$province = $_POST['province'] ?? '';
		$city_municipality = $_POST['city_municipality'] ?? '';
		$district = $_POST['district'] ?? '';
		$barangay_name = $_POST['barangay_name'] ?? '';
		$house_block_number = $_POST['house_block_number'] ?? '';
		$street = $_POST['street'] ?? '';
		$sub_village = $_POST['sub_village'] ?? '';
		$zip_code = $_POST['zip_code'] ?? '';
		$solo_address_id = $_POST['solo_address_id'] ?? null;
		$family_id = $_POST['family_id'] ?? null;



		

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

		
	} elseif (isset($_POST['registered_as']) && $_POST['registered_as'] == "Solo") {
				$region = $_POST['region'] ?? '';
				$province = $_POST['province'] ?? '';
				$city_municipality = $_POST['city_municipality'] ?? '';
				$district = $_POST['district'] ?? '';
				$barangay_name = $_POST['barangay_name'] ?? '';
				$house_block_number = $_POST['house_block_number'] ?? '';
				$street = $_POST['street'] ?? '';
				$sub_village = $_POST['sub_village'] ?? '';
				$zip_code = $_POST['zip_code'] ?? '';
				$solo_address_id = $_POST['solo_address_id'] ?? null;

				$query = "SELECT barangay_id FROM barangay_manegement_table WHERE barangay_name = ?";
				$stmt = $conn->prepare($query);
				$stmt->bind_param("s", $barangay_name);
				$stmt->execute();
				$result = $stmt->get_result();
						$barangay_id = null;
							if ($row = $result->fetch_assoc()) {
								$barangay_id = $row['barangay_id'];
							} else {
								die("Barangay not found.");
							}
							$stmt->close();
		$query = "UPDATE solo_address_table 
                 SET region = ?, province = ?, city_municipality = ?, district = ?, barangay_id = ?, 
                     house_block_number = ?, street = ?, sub_village = ?, zip_code = ? 
                 WHERE solo_address_id = ?";
					$update_stmt = $conn->prepare($query);
					$update_stmt->bind_param("ssssissssi", 
						$region, 
						$province, 
						$city_municipality, 
						$district, 
						$barangay_id, 
						$house_block_number, 
						$street, 
						$sub_village, 
						$zip_code, 
						$solo_address_id
					);
	} elseif (isset($_POST['registered_as']) && $_POST['registered_as'] == "Family") {
					$region = $_POST['region'] ?? '';
					$province = $_POST['province'] ?? '';
					$city_municipality = $_POST['city_municipality'] ?? '';
					$district = $_POST['district'] ?? '';
					$barangay_name = $_POST['barangay_name'] ?? '';
					$house_block_number = $_POST['house_block_number'] ?? '';
					$street = $_POST['street'] ?? '';
					$sub_village = $_POST['sub_village'] ?? '';
					$zip_code = $_POST['zip_code'] ?? '';
					$family_id = $_POST['family_id'] ?? null;

					// Get barangay_id from barangay_name
					$query = "SELECT barangay_id FROM barangay_manegement_table WHERE barangay_name = ?";
					$stmt = $conn->prepare($query);
					$stmt->bind_param("s", $barangay_name);
					$stmt->execute();
					$result = $stmt->get_result();
					$barangay_id = null;

					if ($row = $result->fetch_assoc()) {
						$barangay_id = $row['barangay_id'];
					} else {
						die("Barangay not found.");
					}
					$stmt->close();

					// Update family_table
					$query = "UPDATE family_table 
							SET region = ?, province = ?, city_municipality = ?, district = ?, barangay_id = ?, 
								house_block_number = ?, street = ?, sub_village = ?, zip_code = ? 
							WHERE family_id = ?";
					
					$stmt = $conn->prepare($query);
					$stmt->bind_param("ssssissssi", 
						$region, 
						$province, 
						$city_municipality, 
						$district, 
						$barangay_id, 
						$house_block_number, 
						$street, 
						$sub_village, 
						$zip_code, 
						$family_id
					);

					if ($stmt->execute()) {
						echo "Family address updated successfully.";
					} else {
						echo "Failed to update family address: " . $stmt->error;
					}
					$stmt->close();
}

	if (!empty($query) && !empty($params) && count($params) === 2) {
	$stmt = $conn->prepare($query);
	if (!$stmt) {
		$_SESSION['error'] = "SQL Prepare failed: " . $conn->error;
		header("Location: ../user_page/profile_user.php");
		exit();
	}
	
	$stmt->bind_param("si", $params[0], $params[1]);
	if ($stmt->execute()) {
		$_SESSION['success'] = "<span style='color: green;'><i class='bi bi-check-circle-fill'></i></span> Profile updated successfully.";
	} else {
		$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i></span> Profile update failed: " . $stmt->error;
	}
	$stmt->close();
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
