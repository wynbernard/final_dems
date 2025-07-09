<?php
session_start();
include '../../../../database/conn.php';
require '../../../../phpqrcode/qrlib.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$f_name = trim($_POST['f_name']);
	$m_name = trim($_POST['m_name']);
	$l_name = trim($_POST['l_name']);
	$name_extension = trim($_POST['name_extension']);
	$contact_no = trim($_POST['contact_no']);
	$email = trim($_POST['email']);
	$education_attainment = trim($_POST['education_attainment']);
	$pob = trim($_POST['pob']);
	$mmn = trim($_POST['mmn']);
	$religion = trim($_POST['religion']);
	$occupation = trim($_POST['occupation']);
	$monthly_income = trim($_POST['monthly_income']);
	$civil_status = trim($_POST['civil_status']);
	$icp = trim($_POST['icp']);
	$icn = trim($_POST['icn']);
	$beneficiary = trim($_POST['beneficiary']);
	$ip = trim($_POST['ip']);
	$ethnicity = trim($_POST["ethnicity"]);
	$region = trim($_POST['region']);
	$province = trim($_POST['province']);
	$gender = trim($_POST['gender']);
	$password = trim($_POST['password']);
	$registration_type = trim($_POST['registration_type']);
	$dob = trim($_POST['dob']);
	$city = trim($_POST['city']);
	$district = trim($_POST['district']);
	$barangay = trim($_POST['barangay']);
	$block_number = trim($_POST['block_number']);
	$street = trim($_POST['street']);
	$sub_div = trim($_POST['sub_div']);
	$zip_code = trim($_POST['zip_code']);
	$purok = trim($_POST['purok']);
	$wallet = trim($_POST['wallet']);
	$account_name = trim($_POST['account_name']);
	$account_type = trim($_POST['account_type']);
	$account_number = trim($_POST['account_number']);
	$address = "$purok, $barangay, $city";
	$relation_to_family = "Head of Family";
	$profile_pic = isset($_FILES['profile_pic']) ? $_FILES['profile_pic'] : null;

	$birthDate = new DateTime($dob);
	$today = new DateTime();
	$age = $birthDate->diff($today)->y;

	if (empty($f_name) || empty($l_name) || empty($contact_no) || empty($email) || empty($password) || empty($gender) || empty($registration_type) || empty($dob)) {
		$_SESSION['error'] = "⚠️ All fields are required. Please complete the form.";
		header("Location: ../auth/user_registration.php");
		exit();
	}

	if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
		$uploadDir = '../../profile_images/'; // Make sure this folder exists and is writable
		if (!is_dir($uploadDir)) {
			mkdir($uploadDir, 0777, true);
		}
		$fileTmpPath = $_FILES['profile_pic']['tmp_name'];
		$fileName = uniqid('profile_', true) . '.' . pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
		$destPath = $uploadDir . $fileName;

		if (move_uploaded_file($fileTmpPath, $destPath)) {
			// Save relative path for database
			$profilePicPath = '../profile_images/' . $fileName;
		}
	}

	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$_SESSION['error'] = "Invalid Email Format!";
		header("Location: ../auth/user_registration.php");
		exit();
	}

	if (!preg_match('/^[0-9]{10,15}$/', $contact_no)) {
		$_SESSION['error'] = "Invalid Contact Number!";
		header("Location: ../auth/user_registration.php");
		exit();
	}

	$dobFormatted = date('Y-m-d', strtotime($dob));
	if ($dobFormatted == "1970-01-01") {
		$_SESSION['error'] = "Invalid Birth Date!";
		header("Location: ../auth/user_registration.php");
		exit();
	}

	$hashed_password = password_hash($password, PASSWORD_BCRYPT);
	$family_id = 0;
	$solo_id = 0;

	$account_information_sql = "INSERT INTO account_information_table (bank_Ewallet, account_name, account_type, account_number) VALUES (?, ?, ?, ?)";
	$account_information_stmt = $conn->prepare($account_information_sql);
	$account_information_stmt->bind_param("ssss", $wallet, $account_name, $account_type, $account_number);
	if ($account_information_stmt->execute()) {
		$account_id = $account_information_stmt->insert_id;
	} else {
		$_SESSION['error'] = "Failed to insert account information!";
		header("Location: ../auth/user_registration.php");
		exit();
	}

	$check_sql = "SELECT barangay_id FROM barangay_manegement_table WHERE barangay_name = ?";
	$check_stmt = $conn->prepare($check_sql);
	$check_stmt->bind_param("s", $barangay);
	$check_stmt->execute();
	$check_stmt->store_result();

	if ($check_stmt->num_rows > 0) {
		// Barangay already exists, get the ID
		$check_stmt->bind_result($barangay_id);
		$check_stmt->fetch();
	} else {
		// Insert new barangay
		$barangay_sql = "INSERT INTO barangay_manegement_table (barangay_name) VALUES (?)";
		$barangay_stmt = $conn->prepare($barangay_sql);
		$barangay_stmt->bind_param("s", $barangay);

		if ($barangay_stmt->execute()) {
			$barangay_id = $barangay_stmt->insert_id;
		} else {
			$_SESSION['error'] = "Failed to insert barangay!";
			header("Location: ../auth/user_registration.php");
			exit();
		}
	}


	if (strtolower($registration_type) == "family") {
		$family_sql = "INSERT INTO family_table (region, province, city_municipality, district, barangay_id, house_block_number, street, sub_village, zip_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$family_stmt = $conn->prepare($family_sql);
		$family_stmt->bind_param("sssssssss", $region, $province, $city, $district, $barangay_id, $block_number, $street, $sub_div, $zip_code);
		if ($family_stmt->execute()) {
			$family_id = $family_stmt->insert_id;
		} else {
			$_SESSION['error'] = "Failed to insert family address!";
			header("Location: ../auth/user_registration.php");
			exit();
		}
		$family_stmt->close();
	} else if (strtolower($registration_type) == "solo") {
		$solo_sql = "INSERT INTO solo_address_table (region, province, city_municipality, district, barangay_id, house_block_number, street, sub_village, zip_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$solo_stmt = $conn->prepare($solo_sql);
		$solo_stmt->bind_param("sssssssss", $region, $province, $city, $district, $barangay_id, $block_number, $street, $sub_div, $zip_code);
		if ($solo_stmt->execute()) {
			$solo_id = $solo_stmt->insert_id;
		} else {
			$_SESSION['error'] = "Failed to insert solo address!";
			header("Location: ../auth/user_registration.php");
			exit();
		}
	} else {
		$_SESSION['error'] = "Invalid Registration Type!";
		header("Location: ../auth/user_registration.php");
		exit();
	}

	if ($age <= 2) {
		$age_class = 'Infant';
	} elseif ($age <= 12) {
		$age_class = 'Child';
	} elseif ($age <= 17) {
		$age_class = 'Teen';
	} elseif ($age <= 59) {
		$age_class = 'Adult';
	} else {
		$age_class = 'Senior';
	}

	$checkClassSql = "SELECT age_class_id FROM age_class_table WHERE classification = ?";
	$stmt = $conn->prepare($checkClassSql);
	$stmt->bind_param("s", $age_class);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($result->num_rows > 0) {
		$row = $result->fetch_assoc();
		$age_class_id = $row['age_class_id'];
	} else {
		$insertClassSql = "INSERT INTO age_class_table (classification) VALUES (?)";
		$stmt = $conn->prepare($insertClassSql);
		$stmt->bind_param("s", $age_class);
		$stmt->execute();
		$age_class_id = $stmt->insert_id;
	}
	$stmt->close();

	$targetDir = "../../id_card_image/";
	if (!file_exists($targetDir)) {
		mkdir($targetDir, 0777, true); // Create the folder if it doesn't exist
	}

	if (isset($_FILES["ic_image"]) && $_FILES["ic_image"]["error"] === UPLOAD_ERR_OK) {
		$fileTmpPath = $_FILES["ic_image"]["tmp_name"];
		$fileName = basename($_FILES["ic_image"]["name"]);
		$targetFilePath = $targetDir . $fileName;

		// Optional: Validate file type
		$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
		$fileType = mime_content_type($fileTmpPath);

		if (in_array($fileType, $allowedTypes)) {
			if (move_uploaded_file($fileTmpPath, $targetFilePath)) {
				echo "Image uploaded successfully!";
				$icard_image = $targetFilePath; // Store the file path in the database
			} else {
				echo "Failed to move uploaded file.";
			}
		} else {
			echo "Invalid file type.";
		}
	} else {
		echo "No file uploaded or upload error.";
	}

	$targetDir = "../../signature_image/";
	if (!file_exists($targetDir)) {
		mkdir($targetDir, 0755, true);
	}

	$signaturePath = "";

	// Function to set white background for PNG images
	function setWhiteBackgroundForPng($filepath)
	{
		// Check if the file is a PNG
		if (exif_imagetype($filepath) !== IMAGETYPE_PNG) {
			return;
		}

		$image = imagecreatefrompng($filepath);
		$width = imagesx($image);
		$height = imagesy($image);

		// Create a white background image
		$white_bg = imagecreatetruecolor($width, $height);
		$white = imagecolorallocate($white_bg, 255, 255, 255);
		imagefill($white_bg, 0, 0, $white);

		// Copy the original image onto the white background
		imagecopy($white_bg, $image, 0, 0, 0, 0, $width, $height);

		// Save the result
		imagepng($white_bg, $filepath);

		// Free memory
		imagedestroy($image);
		imagedestroy($white_bg);
	}

	if ($_POST['signature_option'] === 'draw' && !empty($_POST['signature_data'])) {
		// Save drawn signature
		$data = $_POST['signature_data'];
		if (strpos($data, 'data:image/png;base64,') === 0) {
			$data = str_replace('data:image/png;base64,', '', $data);
			$data = str_replace(' ', '+', $data);
			$image = base64_decode($data);
			$filename = 'signature_draw_' . time() . '.png';
			$filepath = $targetDir . $filename;
			file_put_contents($filepath, $image);

			// Process to set white background
			setWhiteBackgroundForPng($filepath);

			$signaturePath = $filepath;
		}
	} elseif ($_POST['signature_option'] === 'upload' && isset($_FILES['signature_file'])) {
		// Save uploaded signature
		$uploadName = basename($_FILES["signature_file"]["name"]);
		$filename = 'signature_upload_' . time() . '_' . $uploadName;
		$filepath = $targetDir . $filename;

		if (move_uploaded_file($_FILES["signature_file"]["tmp_name"], $filepath)) {
			// Process only if it's a PNG
			setWhiteBackgroundForPng($filepath);
			$signaturePath = $filepath;
		}
	}




	$sql = "INSERT INTO pre_reg_table (f_name, m_name, l_name, name_ext, contact_no, email_address, password, gender, registered_as, solo_address_id, family_id,highest_education_attainment, age_class_id, registered_date, date_of_birth, place_of_birth, mother_maiden_name, religion, occupation, monthly_income, civil_status, id_card_presented, id_card_number,account_information_id,id_card_image,indigenous_people,4ps_beneficiary,ethnicity,signature,relation_to_family,profile_pic) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("sssssssssiisisssssssssisssssss", $f_name, $m_name, $l_name, $name_extension, $contact_no, $email, $hashed_password, $gender, $registration_type, $solo_id, $family_id, $education_attainment, $age_class_id, $dobFormatted, $pob, $mmn, $religion, $occupation, $monthly_income, $civil_status, $icp, $icn, $account_id, $icard_image, $ip, $beneficiary, $ethnicity, $signaturePath, $relation_to_family, $profilePicPath);

	if ($stmt->execute()) {
		$pre_reg_id = $stmt->insert_id;
		$stmt->close();



		// QR Code generation
		$qr_data = "Pre_reg_id: $pre_reg_id\nName: $f_name $l_name\nEmail: $email\nPhone: $contact_no\nGender: $gender\nDOB: $dobFormatted\nAge: $age";
		// Set directory and filename
		$qr_dir = "../../../../uploads/qr_codes/";
		if (!file_exists($qr_dir)) {
			mkdir($qr_dir, 0777, true);
		}
		$qr_filename = $qr_dir . time() . "_$pre_reg_id.png";
		$qr_db_path = "uploads/qr_codes/" . time() . "_$pre_reg_id.png";

		// Generate QR code
		QRcode::png($qr_data, $qr_filename, QR_ECLEVEL_L, 6);

		// Insert QR record and get qr_id
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

		$_SESSION['success'] = "✅ Registration Successful! Your QR Code has been generated.";
		header("Location: ../../auth/log_in.php");
	} else {
		$_SESSION['error'] = "Something went wrong!";
		header("Location: ../../auth/user_registration.php");
	}
	$conn->close();
} else {
	header("Location: ../../auth/user_registration.php");
	exit();
}
