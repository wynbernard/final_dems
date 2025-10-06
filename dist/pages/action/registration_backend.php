<?php
session_start();
include '../../../database/conn.php';
require '../../../phpqrcode/qrlib.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $f_name = trim($_POST['f_name']);
    $m_name = trim($_POST['m_name']);
    $l_name = trim($_POST['l_name']);
    $name_extension = trim($_POST['name_extension'] ?? '');
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
    $beneficiary = isset($_POST['beneficiary']) ? 1 : 0;
    $ip = isset($_POST['ip']) ? 1 : 0;
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
    $location_id = trim($_POST['location_id'] ?? '');
    $relation_to_family = "Head of Family";
    $profile_pic = isset($_FILES['profile_pic']) ? $_FILES['profile_pic'] : null;
    
    // Pick-up Point Information
    $pickup_name = trim($_POST['pickup_name'] ?? '');
    $have_vehicle = trim($_POST['have_vehicle'] ?? '');
    $vehicle_type = trim($_POST['vehicle_type'] ?? '');
    $intend_evac = trim($_POST['intend_evac'] ?? '');
    $where_to_go = trim($_POST['where_to_go'] ?? '');
    $have_special_needs = trim($_POST['have_special_needs'] ?? '');
    $special_needs = trim($_POST['special_needs'] ?? '');

    $birthDate = new DateTime($dob);
    $today = new DateTime();
    $ageInterval = $birthDate->diff($today);
    $age = $ageInterval->y;

    // Validate email and contact
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid Email Format!']);
        exit();
    }
    if (!preg_match('/^[0-9]{10,15}$/', $contact_no)) {
        echo json_encode(['success' => false, 'message' => 'Invalid Contact Number!']);
        exit();
    }
    $dobFormatted = date('Y-m-d', strtotime($dob));
    if ($dobFormatted == "1970-01-01") {
        echo json_encode(['success' => false, 'message' => 'Invalid Birth Date!']);
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $family_id = 0;
    $solo_id = 0;

    // Account info
    $account_information_sql = "INSERT INTO account_information_table (bank_Ewallet, account_name, account_type, account_number) VALUES (?, ?, ?, ?)";
    $account_information_stmt = $conn->prepare($account_information_sql);
    $account_information_stmt->bind_param("ssss", $wallet, $account_name, $account_type, $account_number);
    if ($account_information_stmt->execute()) {
        $account_id = $account_information_stmt->insert_id;
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to insert account information!']);
        exit();
    }

    // Barangay
    $check_sql = "SELECT barangay_id FROM barangay_manegement_table WHERE barangay_name = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $barangay);
    $check_stmt->execute();
    $check_stmt->store_result();
    if ($check_stmt->num_rows > 0) {
        $check_stmt->bind_result($barangay_id);
        $check_stmt->fetch();
    } else {
        $barangay_sql = "INSERT INTO barangay_manegement_table (barangay_name) VALUES (?)";
        $barangay_stmt = $conn->prepare($barangay_sql);
        $barangay_stmt->bind_param("s", $barangay);
        if ($barangay_stmt->execute()) {
            $barangay_id = $barangay_stmt->insert_id;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to insert barangay!']);
            exit();
        }
    }

    // Address
    if (strtolower($registration_type) == "family") {
        $family_sql = "INSERT INTO family_table (region, province, city_municipality, district, barangay_id, house_block_number, street, sub_village, purok, zip_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $family_stmt = $conn->prepare($family_sql);
        $family_stmt->bind_param("ssssssssss", $region, $province, $city, $district, $barangay_id, $block_number, $street, $sub_div, $purok, $zip_code);
        if ($family_stmt->execute()) {
            $family_id = $family_stmt->insert_id;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to insert family address!']);
            exit();
        }
        $family_stmt->close();
    } else if (strtolower($registration_type) == "solo") {
        $solo_sql = "INSERT INTO solo_address_table (region, province, city_municipality, district, barangay_id, house_block_number, street, sub_village, purok, zip_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $solo_stmt = $conn->prepare($solo_sql);
        $solo_stmt->bind_param("ssssssssss", $region, $province, $city, $district, $barangay_id, $block_number, $street, $sub_div, $purok, $zip_code);
        if ($solo_stmt->execute()) {
            $solo_id = $solo_stmt->insert_id;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to insert solo address!']);
            exit();
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid Registration Type!']);
        exit();
    }

    // Age class
    if ($age < 1) {
        $age_class = 'Infant';
    } elseif ($age >= 1 && $age <= 3) {
        $age_class = 'Toddler';
    } elseif ($age >= 4 && $age <= 5) {
        $age_class = 'Pre-School';
    } elseif ($age >= 6 && $age <= 12) {
        $age_class = 'School-Age';
    } elseif ($age >= 13 && $age <= 19) {
        $age_class = 'Teenage';
    } elseif ($age >= 20 && $age <= 59) {
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

    // ID Card Image
    $icard_image = "";
    $targetDir = "../../id_card_image/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    if (isset($_FILES["ic_image"]) && $_FILES["ic_image"]["error"] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES["ic_image"]["tmp_name"];
        $fileName = basename($_FILES["ic_image"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($fileTmpPath);
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($fileTmpPath, $targetFilePath)) {
                $icard_image = $targetFilePath;
            }
        }
    }

    // Signature
    $signaturePath = "";
    $targetDir = "../../signature_image/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    function setWhiteBackgroundForPng($filepath)
    {
        if (exif_imagetype($filepath) !== IMAGETYPE_PNG) return;
        $image = imagecreatefrompng($filepath);
        $width = imagesx($image);
        $height = imagesy($image);
        $white_bg = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($white_bg, 255, 255, 255);
        imagefill($white_bg, 0, 0, $white);
        imagecopy($white_bg, $image, 0, 0, 0, 0, $width, $height);
        imagepng($white_bg, $filepath);
        imagedestroy($image);
        imagedestroy($white_bg);
    }
    if (isset($_FILES['signature_file']) && $_FILES['signature_file']['error'] === UPLOAD_ERR_OK) {
        $uploadName = basename($_FILES["signature_file"]["name"]);
        $extension = strtolower(pathinfo($uploadName, PATHINFO_EXTENSION));
        $filename = 'signature_upload_' . time() . '.' . $extension;
        $filepath = $targetDir . $filename;
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES["signature_file"]["tmp_name"]);
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES["signature_file"]["tmp_name"], $filepath)) {
                if ($extension === 'png') {
                    setWhiteBackgroundForPng($filepath);
                }
                $signaturePath = $filepath;
            }
        }
    }

    // Profile Pic
    $profilePicPath = "";
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../profile_images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
        $extension = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($extension, $allowedTypes)) {
            $fileName = uniqid('profile_', true) . '.' . $extension;
            $destPath = $uploadDir . $fileName;
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $profilePicPath = '../profile_images/' . $fileName;
            }
        }
    }
    // Insert pre_reg_table with pickup point fields
    $sql = "INSERT INTO pre_reg_table (f_name, m_name, l_name, name_ext, contact_no, email_address, password, gender, registered_as, solo_address_id, family_id,highest_education_attainment, age_class_id, registered_date, date_of_birth, place_of_birth, mother_maiden_name, religion, occupation, monthly_income, civil_status, id_card_presented, id_card_number,account_information_id,id_card_image,indigenous_people,4ps_beneficiary,ethnicity,signature,relation_to_family,profile_pic,pickup_point_name,have_vehicle,vehicle_type,intend_evacuation,where_to_go,have_special_needs,special_needs) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssiisisssssssssissssssssssssss", $f_name, $m_name, $l_name, $name_extension, $contact_no, $email, $hashed_password, $gender, $registration_type, $solo_id, $family_id, $education_attainment, $age_class_id, $dobFormatted, $pob, $mmn, $religion, $occupation, $monthly_income, $civil_status, $icp, $icn, $account_id, $icard_image, $ip, $beneficiary, $ethnicity, $signaturePath, $relation_to_family, $profilePicPath, $pickup_name, $have_vehicle, $vehicle_type, $intend_evac, $where_to_go, $have_special_needs, $special_needs);
    if ($stmt->execute()) {
        $pre_reg_id = $stmt->insert_id;
        $stmt->close();
        // QR Code generation
        $qr_data = "Pre_reg_id: $pre_reg_id\nName: $f_name $l_name\nEmail: $email\nPhone: $contact_no\nGender: $gender\nDOB: $dobFormatted\nAge: $age";
        $qr_dir = "../../../uploads/qr_codes/";
        if (!file_exists($qr_dir)) {
            mkdir($qr_dir, 0777, true);
        }
        $qr_filename = $qr_dir . time() . "_" . $pre_reg_id . ".png";
        $qr_db_path = "uploads/qr_codes/" . time() . "_" . $pre_reg_id . ".png";
        QRcode::png($qr_data, $qr_filename, QR_ECLEVEL_L, 6);
        $qr_sql = "INSERT INTO qr_table (pre_reg_id, code) VALUES (?, ?)";
        $qr_stmt = $conn->prepare($qr_sql);
        $qr_stmt->bind_param("is", $pre_reg_id, $qr_db_path);
        if ($qr_stmt->execute()) {
            $qr_id = $qr_stmt->insert_id;
            $update_sql = "UPDATE pre_reg_table SET qr_id = ? WHERE pre_reg_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $qr_id, $pre_reg_id);
            $update_stmt->execute();
            $update_stmt->close();
        }
        $qr_stmt->close();
        if (strtolower($registration_type) == "family" && isset($_POST['numFamilyMembers']) && intval($_POST['numFamilyMembers']) > 0) {
            $numMembers = intval($_POST['numFamilyMembers']);
            for ($i = 1; $i <= $numMembers; $i++) {
                $mfname = trim($_POST["member_fname_$i"] ?? '');
                $mmname = trim($_POST["member_mname_$i"] ?? '');
                $mlname = trim($_POST["member_lname_$i"] ?? '');
                $mname_extension = trim($_POST["member_name_extension_$i"] ?? '');
                $mdob = trim($_POST["member_dob_$i"] ?? '');
                $mgender = trim($_POST["member_gender_$i"] ?? '');
                $mrelation = trim($_POST["member_relation_$i"] ?? '');
                $mrelation_to_family = $mrelation;
                $mage = 0;
                $mage_class_id = null;
                if ($mdob) {
                    $mbirthDate = new DateTime($mdob);
                    $mtoday = new DateTime();
                    $mageInterval = $mbirthDate->diff($mtoday);
                    $mage = $mageInterval->y;
                    if ($mage < 1) {
                        $mage_class = 'Infant';
                    } elseif ($mage >= 1 && $mage <= 3) {
                        $mage_class = 'Toddler';
                    } elseif ($mage >= 4 && $mage <= 5) {
                        $mage_class = 'Pre-School';
                    } elseif ($mage >= 6 && $mage <= 12) {
                        $mage_class = 'School-Age';
                    } elseif ($mage >= 13 && $mage <= 19) {
                        $mage_class = 'Teenage';
                    } elseif ($mage >= 20 && $mage <= 59) {
                        $mage_class = 'Adult';
                    } else {
                        $mage_class = 'Senior';
                    }
                    $mclassSql = "SELECT age_class_id FROM age_class_table WHERE classification = ?";
                    $mstmt = $conn->prepare($mclassSql);
                    $mstmt->bind_param("s", $mage_class);
                    $mstmt->execute();
                    $mresult = $mstmt->get_result();
                    if ($mresult->num_rows > 0) {
                        $mrow = $mresult->fetch_assoc();
                        $mage_class_id = $mrow['age_class_id'];
                    } else {
                        $minsertClassSql = "INSERT INTO age_class_table (classification) VALUES (?)";
                        $mstmt2 = $conn->prepare($minsertClassSql);
                        $mstmt2->bind_param("s", $mage_class);
                        $mstmt2->execute();
                        $mage_class_id = $mstmt2->insert_id;
                        $mstmt2->close();
                    }

                    $mstmt->close();
                }
                $msql = "INSERT INTO pre_reg_table (f_name, m_name, l_name, name_ext, gender, registered_as, family_id, age_class_id, date_of_birth, relation_to_family) VALUES (?,?,?,?,?,?,?,?,?,?)";
                $mstmt = $conn->prepare($msql);
                $mregas = 'Family';
                $mstmt->bind_param("ssssssiiss", $mfname, $mmname, $mlname, $mname_extension, $mgender, $mregas, $family_id, $mage_class_id, $mdob, $mrelation_to_family);
                if ($mstmt->execute()) {
                    $member_pre_reg_id = $mstmt->insert_id;
                    // Generate QR code for member
                    $qr_data = "Pre_reg_id: $member_pre_reg_id\nName: $mfname $mlname\nGender: $mgender\nDOB: $mdob\nAge: $mage";
                    $qr_dir = "../../../uploads/qr_codes/";
                    if (!file_exists($qr_dir)) {
                        mkdir($qr_dir, 0777, true);
                    }
                    $qr_filename = $qr_dir . time() . "_" . $member_pre_reg_id . ".png";
                    $qr_db_path = "uploads/qr_codes/" . time() . "_" . $member_pre_reg_id . ".png";
                    QRcode::png($qr_data, $qr_filename, QR_ECLEVEL_L, 6);
                    $qr_sql = "INSERT INTO qr_table (pre_reg_id, code) VALUES (?, ?)";
                    $qr_stmt = $conn->prepare($qr_sql);
                    $qr_stmt->bind_param("is", $member_pre_reg_id, $qr_db_path);
                    if ($qr_stmt->execute()) {
                        $qr_id = $qr_stmt->insert_id;
                        $update_sql = "UPDATE pre_reg_table SET qr_id = ? WHERE pre_reg_id = ?";
                        $update_stmt = $conn->prepare($update_sql);
                        $update_stmt->bind_param("ii", $qr_id, $member_pre_reg_id);
                        $update_stmt->execute();
                        $update_stmt->close();
                    }
                    $qr_stmt->close();
                }
                $mstmt->close();
            }
        }
        // Send account details to the user's email (try Gmail if configured via environment variables)
        try {
            require_once '../../../vendor/autoload.php';
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            $gmailUser = getenv('GMAIL_USER');
            $gmailPass = getenv('GMAIL_PASS');

            if ($gmailUser && $gmailPass) {
                // Gmail SMTP (requires app password or OAuth credentials)
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $gmailUser;
                $mail->Password = $gmailPass;
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
            } else {
                // Fallback to Hostinger settings used elsewhere in the project
                $mail->isSMTP();
                $mail->Host = 'smtp.hostinger.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'dems_info@bccbsis.com';
                $mail->Password = '[nAgc/#^Jj7';
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;
            }

            $fromAddress = ($gmailUser) ? $gmailUser : 'dems_info@bccbsis.com';
            $mail->setFrom($fromAddress, 'DEMS');
            $mail->addAddress($email, $f_name . ' ' . $l_name);
            $mail->isHTML(true);
            $mail->Subject = 'DEMS Registration - Account Details';
            $mailBody = "<p>Dear " . htmlspecialchars($f_name) . ",</p>" .
                "<p>Your account has been created on the DEMS system. Please find your login details below:</p>" .
                "<p><strong>Email:</strong> " . htmlspecialchars($email) . "<br>" .
                "<strong>Password:</strong> " . htmlspecialchars($password) . "</p>" .
                "<p>Please change your password after first login for security reasons.</p>";
            $mail->Body = $mailBody;
            $mail->AltBody = "DEMS account created. Email: $email\nPassword: $password";

            $mail->send();
        } catch (Exception $e) {
            error_log('Registration email failed: ' . $e->getMessage());
        }

        echo json_encode(['success' => true, 'message' => 'Registration successful!']);
        // Register in evac_reg_table (for head/solo only)
        $evac_loc_id = isset($_POST['location_id']) ? intval($_POST['location_id']) : 0;
        $room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;
        $disaster_id = isset($_POST['disasterId']) ? intval($_POST['disasterId']) : 0;
        $date_reg = date('Y-m-d');
        if (!$evac_loc_id) {
            echo json_encode(['success' => false, 'message' => 'Missing evac_loc_id in registration.']);
            $conn->close();
            exit();
        }
        if (!$room_id) {
            echo json_encode(['success' => false, 'message' => 'Missing room_id in registration.']);
            $conn->close();
            exit();
        }
        if (!$disaster_id) {
            echo json_encode(['success' => false, 'message' => 'No disaster selected. Please select a disaster event before submitting.']);
            $conn->close();
            exit();
        }
        $evac_sql = "INSERT INTO evac_reg_table (pre_reg_id, evac_loc_id, room_id, date_reg, disaster_id , status) VALUES (?, ?, ?, ?, ? , 'Evacuated')";
        $evac_stmt = $conn->prepare($evac_sql);
        $evac_stmt->bind_param("iiisi", $pre_reg_id, $evac_loc_id, $room_id, $date_reg, $disaster_id);
        $evac_stmt->execute();
        // Insert log entry for this evac registration with status 'IN'
        $evac_reg_id = $evac_stmt->insert_id;
        if ($evac_reg_id) {
            $log_sql = "INSERT INTO logs_table (evac_reg_id, status, date_time) VALUES (?, ?, NOW())";
            $log_stmt = $conn->prepare($log_sql);
            if ($log_stmt) {
                $log_status = 'IN';
                $log_stmt->bind_param("is", $evac_reg_id, $log_status);
                $log_stmt->execute();
                $log_stmt->close();
            }
        }
        $evac_stmt->close();

		// Update barangay evac count in brgy_record_table (per barangay + disaster)
		function _increment_brgy_record($conn, $barangay_name, $disaster_id, $inc = 1)
		{
			if (!$barangay_name || !$disaster_id || $inc <= 0) return;
			$upd = $conn->prepare("UPDATE brgy_record_table SET total_evacuess = total_evacuess + ?, date = CURDATE(), status = 'Evacuated' WHERE barangay_name = ? AND disaster_id = ?");
			if ($upd) {
				$upd->bind_param('isi', $inc, $barangay_name, $disaster_id);
				$upd->execute();
				$affected = $upd->affected_rows;
				$upd->close();
				if ($affected === 0) {
					$ins = $conn->prepare("INSERT INTO brgy_record_table (barangay_name, total_evacuess, disaster_id, scale, date, status) VALUES (?, ?, ?, '', CURDATE(), 'Evacuated')");
					if ($ins) {
						$ins->bind_param('sii', $barangay_name, $inc, $disaster_id);
						$ins->execute();
						$ins->close();
					}
				}
			}
		}

		// Use barangay from submitted form for counting
		_increment_brgy_record($conn, $barangay, $disaster_id, 1);
        // Register each family member in evac_reg_table
        if (strtolower($registration_type) == "family" && isset($_POST['numFamilyMembers']) && intval($_POST['numFamilyMembers']) > 0) {
            $numMembers = intval($_POST['numFamilyMembers']);
            for ($i = 1; $i <= $numMembers; $i++) {
                $mfname = trim($_POST["member_fname_$i"] ?? '');
                $mmname = trim($_POST["member_mname_$i"] ?? '');
                $mlname = trim($_POST["member_lname_$i"] ?? '');
                $mname_extension = trim($_POST["member_name_extension_$i"] ?? '');
                $mdob = trim($_POST["member_dob_$i"] ?? '');
                $mgender = trim($_POST["member_gender_$i"] ?? '');
                $mrelation = trim($_POST["member_relation_$i"] ?? '');
                // Find the member's pre_reg_id
                $find_member_sql = "SELECT pre_reg_id FROM pre_reg_table WHERE f_name = ? AND m_name = ? AND l_name = ? AND name_ext = ? AND date_of_birth = ? AND gender = ? AND relation_to_family = ? AND family_id = ? ORDER BY pre_reg_id DESC LIMIT 1";
                $find_member_stmt = $conn->prepare($find_member_sql);
                $find_member_stmt->bind_param("sssssssi", $mfname, $mmname, $mlname, $mname_extension, $mdob, $mgender, $mrelation, $family_id);
                $find_member_stmt->execute();
                $find_member_result = $find_member_stmt->get_result();
                if ($find_member_result && $find_member_result->num_rows > 0) {
                    $member_row = $find_member_result->fetch_assoc();
                    $member_pre_reg_id = $member_row['pre_reg_id'];
                    $evac_stmt = $conn->prepare($evac_sql);
                    $evac_stmt->bind_param("iiisi", $member_pre_reg_id, $evac_loc_id, $room_id, $date_reg, $disaster_id);
                    $evac_stmt->execute();
                    // Insert corresponding log (status IN) for the member registration
                    $member_evac_reg_id = $evac_stmt->insert_id;
                    if ($member_evac_reg_id) {
                        $log_sql = "INSERT INTO logs_table (evac_reg_id, status, date_time) VALUES (?, ?, NOW())";
                        $log_stmt = $conn->prepare($log_sql);
                        if ($log_stmt) {
                            $log_status = 'IN';
                            $log_stmt->bind_param("is", $member_evac_reg_id, $log_status);
                            $log_stmt->execute();
                            $log_stmt->close();
                        }
                    }
					$evac_stmt->close();

					// Increment barangay evac count for each family member as well
					_increment_brgy_record($conn, $barangay, $disaster_id, 1);
                }
                $find_member_stmt->close();
            }
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    $conn->close();
    exit();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}
