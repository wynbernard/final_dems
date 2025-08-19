<?php
include '../../../database/user_session.php'; // Session connection
require '../../../phpqrcode/qrlib.php'; // QR Code library

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$f_name = trim($_POST['f_name'] ?? '');
	$l_name = trim($_POST['l_name'] ?? '');
	$birth_date = trim($_POST['birth_date'] ?? '');
	$gender = trim($_POST['gender'] ?? '');
	$contact_no = trim($_POST['contact_no'] ?? '');
	$family_id = trim($_POST['family_id'] ?? '');
	$relation = trim($_POST['relation'] ?? '');
	$relation_other = trim($_POST['relation_other'] ?? '');

	if ($relation === 'Other' && !empty($relation_other)) {
		$relation = $relation_other;
	}

	// Validation
	if (empty($f_name) || empty($l_name) || empty($birth_date) || empty($gender) || empty($family_id) || empty($relation)) {
		$_SESSION['error'] = "⚠️ All fields are required.";
		header("Location: ../user_page/family.php");
		exit();
	}

	$birthDateObj = DateTime::createFromFormat('Y-m-d', $birth_date);
	if (!$birthDateObj) {
		$_SESSION['error'] = "⚠️ Invalid birth date format.";
		header("Location: ../user_page/family.php");
		exit();
	}
	$today = new DateTime();
	$age = $birthDateObj->diff($today)->y;

	// Check if family exists
	$stmt = $conn->prepare("SELECT family_id FROM family_table WHERE family_id = ?");
	$stmt->bind_param("i", $family_id);
	$stmt->execute();
	$stmt->store_result();
	if ($stmt->num_rows == 0) {
		$_SESSION['error'] = "❌ Family ID does not exist.";
		$stmt->close();
		header("Location: ../user_page/family.php");
		exit();
	}
	$stmt->close();

	// Determine age class (Infant: 0-11 months, Toddler: 1-3, Pre-School: 4-5, School-Age: 6-12, Teenage: 13-19, Adult: 20-59, Senior: 60+)
	$birthDateObj = DateTime::createFromFormat('Y-m-d', $birth_date);
	$today = new DateTime();
	$ageInterval = $birthDateObj->diff($today);
	$years = $ageInterval->y;
	$months = $ageInterval->m + ($ageInterval->y * 12);

	if ($years < 1) {
		$age_class = 'Infant';
	} elseif ($years >= 1 && $years <= 3) {
		$age_class = 'Toddler';
	} elseif ($years >= 4 && $years <= 5) {
		$age_class = 'Pre-School';
	} elseif ($years >= 6 && $years <= 12) {
		$age_class = 'School-Age';
	} elseif ($years >= 13 && $years <= 19) {
		$age_class = 'Teenage';
	} elseif ($years >= 20 && $years <= 59) {
		$age_class = 'Adult';
	} else {
		$age_class = 'Senior';
	}

	// Get or insert age_class_id
	$stmt = $conn->prepare("SELECT age_class_id FROM age_class_table WHERE classification = ?");
	$stmt->bind_param("s", $age_class);
	$stmt->execute();
	$result = $stmt->get_result();
	if ($result && $row = $result->fetch_assoc()) {
		$age_class_id = $row['age_class_id'];
	} else {
		$stmt = $conn->prepare("INSERT INTO age_class_table (classification) VALUES (?)");
		$stmt->bind_param("s", $age_class);
		$stmt->execute();
		$age_class_id = $stmt->insert_id;
	}
	$stmt->close();

	// Insert new family member
	$stmt = $conn->prepare("INSERT INTO pre_reg_table (f_name, l_name, age_class_id, gender, contact_no, family_id, date_of_birth ,relation_to_family) VALUES (?, ?, ?, ?, ?, ?, ?, ?	)");
	$stmt->bind_param("ssississ", $f_name, $l_name, $age_class_id, $gender, $contact_no, $family_id, $birth_date, $relation);
	if ($stmt->execute()) {
		$pre_reg_id = $stmt->insert_id;
		$stmt->close();
		// QR Code generation
		$dobFormatted = date('Y-m-d', strtotime($birth_date));
		$qr_data = "Pre_reg_id: $pre_reg_id\nName: $f_name $l_name\nPhone: $contact_no\nGender: $gender\nDOB: $dobFormatted\nAge: $age";
		// Set directory and filename
		$qr_dir = '../../../uploads/qr_codes/';
		if (!file_exists($qr_dir)) {
			mkdir($qr_dir, 0777, true);
		}
		$qr_filename = $qr_dir . time() . "_" . $pre_reg_id . ".png";
		QRcode::png($qr_data, $qr_filename, QR_ECLEVEL_L, 6);
		$qr_db_path = "uploads/qr_codes/" . time() . "_" . $pre_reg_id . ".png";

		// Debug: Test file creation in QR directory
		$test_file = $qr_dir . 'test_write.txt';
		$test_content = 'QR code write test at ' . date('Y-m-d H:i:s');
		$test_result = @file_put_contents($test_file, $test_content);
		if ($test_result === false) {
			$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i> Failed to write test file in QR code folder.<br>Test path: $test_file<br>Please check folder permissions and path correctness.</span>";
			header("Location: ../user_page/family.php");
			exit();
		}
		// Generate QR code
		
		// Check if QR code was created
		if (!file_exists($qr_filename)) {
			$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i> Failed to upload QR code to the folder.<br>Path attempted: $qr_filename<br>Please check folder permissions and path correctness. GD extension may also be required.</span>";
			header("Location: ../user_page/family.php");
			exit();
		}
		// Insert QR record and get qr_id (if qr_table exists)
		if ($conn->query("SHOW TABLES LIKE 'qr_table'")->num_rows) {
			$qr_sql = "INSERT INTO qr_table (pre_reg_id, code) VALUES (?, ?)";
			$qr_stmt = $conn->prepare($qr_sql);
			$qr_stmt->bind_param("is", $pre_reg_id, $qr_db_path);
			if ($qr_stmt->execute()) {
				$qr_id = $qr_stmt->insert_id;
				// Update pre_reg_table with qr_id
				$update_sql = "UPDATE pre_reg_table SET qr_id = ? WHERE pre_reg_id = ?";
				$update_stmt = $conn->prepare($update_sql);
				$update_stmt->bind_param("ii", $qr_id, $pre_reg_id);
				$update_stmt->execute();
				$update_stmt->close();
			}
			$qr_stmt->close();
		}

		$_SESSION['success'] = "<span style='color:green;'><i class='bi bi-check-circle-fill'></i> Add Family Member Successfully!.</span>";
	} else {
		$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i> Failed to insert family member: " . $stmt->error . "</span>";
	}

	$conn->close();
	header("Location: ../user_page/family.php");
	exit();
}
