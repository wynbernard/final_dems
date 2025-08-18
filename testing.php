<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Card Layout Preview - Kanlaon Evacuation Plan</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .preview-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .preview-header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .preview-header p {
            color: #666;
            margin: 5px 0;
        }
        
        .id-card {
            background: white;
            color: black;
            padding: 2rem;
            border: 2px solid #000;
            border-radius: 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            position: relative;
            margin-bottom: 30px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.4;
        }
        
        .card-header {
            text-align: center;
            margin-bottom: 2rem;
            border-bottom: 2px solid #000;
            padding-bottom: 1rem;
        }
        
        .card-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0;
            color: #000;
            text-transform: uppercase;
        }
        
        .card-subtitle {
            font-size: 1.25rem;
            font-weight: bold;
            margin: 0.5rem 0 0 0;
            color: #dc3545;
            text-transform: uppercase;
        }
        
        .form-section {
            margin-bottom: 1.5rem;
        }
        
        .form-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .form-table td {
            padding: 0.5rem 0;
            vertical-align: top;
        }
        
        .form-table td:first-child {
            width: 40%;
            font-weight: bold;
            text-transform: uppercase;
            padding-right: 1rem;
        }
        
        .form-table td:last-child {
            width: 60%;
            border-bottom: 1px solid #000;
            padding-bottom: 0.25rem;
        }
        
        .form-label-local {
            font-size: 0.8rem;
            color: #666;
            font-style: italic;
            text-transform: none;
            font-weight: normal;
            display: block;
            margin-top: 0.2rem;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .checkbox-box {
            width: 20px;
            height: 20px;
            border: 2px solid #000;
            display: inline-block;
            position: relative;
        }
        
        .checkbox-box.checked {
            background: #000;
        }
        
        .checkbox-box.checked::after {
            content: '✓';
            position: absolute;
            top: -2px;
            left: 2px;
            font-weight: bold;
            color: white;
        }
        
        .control-number-section {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #ccc;
        }
        
        .control-number-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .control-number-table td {
            padding: 0.5rem 0;
            vertical-align: middle;
        }
        
        .control-number-table td:first-child {
            width: 30%;
            font-weight: bold;
            text-transform: uppercase;
            padding-right: 1rem;
        }
        
        .control-number-table td:last-child {
            width: 70%;
        }
        
        .control-number-box {
            border: 1px solid #000;
            padding: 0.5rem 1rem;
            text-align: center;
            font-weight: bold;
            background: #f8f9fa;
            display: inline-block;
            min-width: 200px;
        }
        
        .authority-section {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
            padding: 1.5rem;
            background: #f4a460;
            border: 2px solid #000;
        }
        
        .logo-placeholder {
            width: 120px;
            height: 120px;
            border: 2px dashed #8b4513;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #deb887;
            font-size: 0.8rem;
            color: #8b4513;
            text-align: center;
            flex-shrink: 0;
        }
        
        .authority-list {
            flex-grow: 1;
            margin-left: 2rem;
        }
        
        .authority-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .authority-name {
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .authority-line {
            border-bottom: 1px solid #000;
            flex-grow: 1;
            margin-left: 1rem;
            min-width: 150px;
        }
        
        .authority-phone {
            font-size: 0.8rem;
            color: #666;
        }
        
        .footer {
            text-align: center;
            margin-top: 1rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .footer-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-top: 0.5rem;
        }
        
        .volcano-logo {
            width: 60px;
            height: 60px;
            border: 2px solid #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ff8c00;
            font-size: 0.6rem;
            text-align: center;
            position: relative;
        }
        
        .volcano-logo::before {
            content: '🌋';
            position: absolute;
            top: 2px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.5rem;
            font-weight: bold;
        }
        
        .volcano-logo::after {
            content: 'TASK FORCE\A KANLAON';
            position: absolute;
            bottom: 2px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.4rem;
            text-align: center;
            line-height: 1;
            white-space: pre-line;
        }
        
        .print-info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .print-info h3 {
            margin-top: 0;
            color: #333;
        }
        
        .print-info ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .print-info li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="preview-header">
            <h1>ID Card Layout Preview</h1>
            <p><strong>KANLAON EVACUATION PLAN BAKWIT CARD</strong></p>
            <p>This is how the ID card will appear when printed</p>
        </div>
        
        
        
        <div class="id-card">
            <!-- Header -->
            <div class="card-header">
                <div class="card-title">KANLAON EVACUATION PLAN</div>
                <div class="card-subtitle">BAKWIT CARD</div>
            </div>

            <!-- Main Information Section -->
            <div class="form-section">
                <table class="form-table">
                    <tr>
                        <td>
                            HOUSEHOLD HEAD:
                            <span class="form-label-local">(PANGULO SANG PANIMALAY)</span>
                        </td>
                        <td>Juan Dela Cruz Santos</td>
                    </tr>
                    <tr>
                        <td>
                            NO. OF HOUSEHOLD MEMBERS:
                            <span class="form-label-local">(KADAMUON/KADAGHANON SA PANIMALAY)</span>
                        </td>
                        <td>5</td>
                    </tr>
                    <tr>
                        <td>
                            ADDRESS:
                            <span class="form-label-local">(PULOY-AN/PUY-ANAN)</span>
                        </td>
                        <td>Purok 3, Barangay Canlaon, Canlaon City, Negros Oriental</td>
                    </tr>
                    <tr>
                        <td>
                            COLLECTION POINT/PICKUP POINT:
                            <span class="form-label-local">(TILIPUNAN PARA SA BAKWIT)</span>
                        </td>
                        <td>Canlaon City Hall Grounds</td>
                    </tr>
                    <tr>
                        <td>
                            VEHICLE FOR EVACUATION & DRIVER:
                            <span class="form-label-local">(SALAKYAN/SAKYANAN SA PAG BAKWIT KAG/UG DRAYBER)</span>
                        </td>
                        <td>LGU Truck / Mario Santos</td>
                    </tr>
                    <tr>
                        <td>
                            ASSIGNED EVACUATION CENTER:
                            <span class="form-label-local">(GINTALANA NGA EVACUATION CENTER)</span>
                        </td>
                        <td>Canlaon City National High School</td>
                    </tr>
                    <tr>
                        <td>
                            PHONE NUMBER OF FAMILY LEADER:
                            <span class="form-label-local">(NUMERO SA SELPON SANG PANGULO SANG PANIMALAY)</span>
                        </td>
                        <td>09123456789</td>
                    </tr>
                    <tr>
                        <td>
                            PERSONS WITH SPECIAL NEEDS:
                            <span class="form-label-local">(MIYEMBRO NGA MAY ESPESYAL NGA PANGINAHANGLANON)</span>
                        </td>
                        <td>Maria Santos (Elderly, 75 years old)</td>
                    </tr>
                    <tr>
                        <td>
                            STAYING INSIDE EVACUATION CENTER?:
                            <span class="form-label-local">(MUSULOD BA MO SA EVACUATION CENTER?)</span>
                        </td>
                        <td>
                            <div class="checkbox-group">
                                <div class="checkbox-item">
                                    <div class="checkbox-box checked"></div>
                                    <span>YES (Oo)</span>
                                </div>
                                <div class="checkbox-item">
                                    <div class="checkbox-box"></div>
                                    <span>NO (Indi)</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- Control Number Section -->
                <div class="control-number-section">
                    <table class="control-number-table">
                        <tr>
                            <td>CONTROL NUMBER:</td>
                            <td>
                                <div class="control-number-box">
                                    2024-CAN-001
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Authority Section -->
            <div class="authority-section">
                <div class="logo-placeholder">
                    Place LGU logo here
                </div>
                
                <div class="authority-list">
                    <div class="authority-item">
                        <div class="authority-name">LDRRMO</div>
                        <div class="authority-line"></div>
                    </div>
                    <div class="authority-item">
                        <div class="authority-name">PUNONG BARANGAY</div>
                        <div class="authority-line"></div>
                    </div>
                    <div class="authority-item">
                        <div class="authority-name">PUROK LEADER</div>
                        <div class="authority-line"></div>
                    </div>
                    <div class="authority-item">
                        <div class="authority-name">LOCAL POLICE STATION</div>
                        <div class="authority-line"></div>
                    </div>
                    <div class="authority-item">
                        <div class="authority-name">OFFICE OF CIVIL DEFENSE NIR</div>
                        <div class="authority-line">
                            <span class="authority-phone">09956112342 / 09177040134</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <div>REGIONAL TASK FORCE KANLAON</div>
                <div class="footer-logo">
                    <div class="volcano-logo"></div>
                </div>
            </div>
        </div>
        
        <div class="print-info">
            <h3>Print Information:</h3>
            <ul>
                <li><strong>Page Size:</strong> A4 (210mm x 297mm)</li>
                <li><strong>Cards Per Page:</strong> 1 card per page for optimal readability</li>
                <li><strong>Print Orientation:</strong> Portrait</li>
                <li><strong>Margins:</strong> 10mm on all sides</li>
                <li><strong>Font:</strong> Arial, 9pt for print version</li>
                <li><strong>Colors:</strong> Black text on white background, red subtitle, orange authority section</li>
                <li><strong>Layout:</strong> Table format with labels in left column, data in right column</li>
            </ul>
        </div>
    </div>
</body>
</html>
<style>
JM
.authority-list {
            flex-grow: 1;
            margin-left: 2rem;
        }
        
        .authority-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .authority-name {
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .authority-line {
            border-bottom: 1px solid #000;
            flex-grow: 1;
            margin-left: 1rem;
            min-width: 150px;
        }
        
        .authority-phone {
            font-size: 0.8rem;
            color: #666;
        }
        
        .footer {
            text-align: center;
            margin-top: 1rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .footer-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-top: 0.5rem;
        }
        
        .volcano-logo {
            width: 60px;
            height: 60px;
            border: 2px solid #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ff8c00;
            font-size: 0.6rem;
            text-align: center;
            position: relative;
        }
        
        .volcano-logo::before {
            content: '🌋';
            position: absolute;
            top: 2px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.5rem;
            font-weight: bold;
        }
        
        .volcano-logo::after {
            content: 'TASK FORCE\A KANLAON';
            position: absolute;
            bottom: 2px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.4rem;
            text-align: center;
            line-height: 1;
            white-space: pre-line;
        }
        
        .print-info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .print-info h3 {
            margin-top: 0;
            color: #333;
        }
        
        .print-info ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .print-info li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="preview-header">
            <h1>ID Card Layout Preview</h1>
            <p><strong>KANLAON EVACUATION PLAN BAKWIT CARD</strong></p>
            <p>This is how the ID card will appear when printed</p>
        </div>
        
        
        
        <div class="id-card">
            <!-- Header -->
            <div class="card-header">
                <div class="card-title">KANLAON EVACUATION PLAN</div>
                <div class="card-subtitle">BAKWIT CARD</div>
            </div>

            <!-- Main Information Section -->
            <div class="form-section">
                <table class="form-table">
                    <tr>
                        <td>
                            HOUSEHOLD HEAD:
                            <span class="form-label-local">(PANGULO SANG PANIMALAY)</span>
                        </td>
                        <td>Juan Dela Cruz Santos</td>
                    </tr>
                    <tr>
                        <td>
                            NO. OF HOUSEHOLD MEMBERS:
                            <span class="form-label-local">(KADAMUON/KADAGHANON SA PANIMALAY)</span>
                        </td>
                        <td>5</td>
                    </tr>
                    <tr>
                        <td>
                            ADDRESS:
                            <span class="form-label-local">(PULOY-AN/PUY-ANAN)</span>
                        </td>
                        <td>Purok 3, Barangay Canlaon, Canlaon City, Negros Oriental</td>
                    </tr>
                    <tr>
                        <td>
                            COLLECTION POINT/PICKUP POINT:
                            <span class="form-label-local">(TILIPUNAN PARA SA BAKWIT)</span>
                        </td>
                        <td>Canlaon City Hall Grounds</td>
                    </tr>
                    <tr>
                        <td>
                            VEHICLE FOR EVACUATION & DRIVER:
                            <span class="form-label-local">(SALAKYAN/SAKYANAN SA PAG BAKWIT KAG/UG DRAYBER)</span>
                        </td>
                        <td>LGU Truck / Mario Santos</td>
                    </tr>
                    <tr>
                        <td>
                            ASSIGNED EVACUATION CENTER:
                            <span class="form-label-local">(GINTALANA NGA EVACUATION CENTER)</span>
                        </td>
                        <td>Canlaon City National High School</td>
                    </tr>
                    <tr>
                        <td>
                            PHONE NUMBER OF FAMILY LEADER:
                            <span class="form-label-local">(NUMERO SA SELPON SANG PANGULO SANG PANIMALAY)</span>
                        </td>
                        <td>09123456789</td>
                    </tr>
                    <tr>
                        <td>
                            PERSONS WITH SPECIAL NEEDS:
                            <span class="form-label-local">(MIYEMBRO NGA MAY ESPESYAL NGA PANGINAHANGLANON)</span>
                        </td>
                        <td>Maria Santos (Elderly, 75 years old)</td>
                    </tr>
                    <tr>
                        <td>
                            STAYING INSIDE EVACUATION CENTER?:
                            <span class="form-label-local">(MUSULOD BA MO SA EVACUATION CENTER?)</span>
                        </td>
                        <td>
                            <div class="checkbox-group">
                                <div class="checkbox-item">
                                    <div class="checkbox-box checked"></div>
                                    <span>YES (Oo)</span>
                                </div>
                                <div class="checkbox-item">
                                    <div class="checkbox-box"></div>
                                    <span>NO (Indi)</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- Control Number Section -->
                <div class="control-number-section">
                    <table class="control-number-table">
                        <tr>
                            <td>CONTROL NUMBER:</td>
                            <td>
                                <div class="control-number-box">
                                    2024-CAN-001
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Authority Section -->
            <div class="authority-section">
                <div class="logo-placeholder">
                    Place LGU logo here
                </div>
                
                <div class="authority-list">
                    <div class="authority-item">
                        <div class="authority-name">LDRRMO</div>
                        <div class="authority-line"></div>
                    </div>
                    <div class="authority-item">
                        <div class="authority-name">PUNONG BARANGAY</div>
                        <div class="authority-line"></div>
                    </div>
                    <div class="authority-item">
                        <div class="authority-name">PUROK LEADER</div>
                        <div class="authority-line"></div>
                    </div>
                    <div class="authority-item">
                        <div class="authority-name">LOCAL POLICE STATION</div>
                        <div class="authority-line"></div>
                    </div>
                    <div class="authority-item">
                        <div class="authority-name">OFFICE OF CIVIL DEFENSE NIR</div>
                        <div class="authority-line">
                            <span class="authority-phone">09956112342 / 09177040134</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <div>REGIONAL TASK FORCE KANLAON</div>
                <div class="footer-logo">
                    <div class="volcano-logo"></div>
                </div>
            </div>
        </div>
        
        <div class="print-info">
            <h3>Print Information:</h3>
            <ul>
                <li><strong>Page Size:</strong> A4 (210mm x 297mm)</li>
                <li><strong>Cards Per Page:</strong> 1 card per page for optimal readability</li>
                <li><strong>Print Orientation:</strong> Portrait</li>
                <li><strong>Margins:</strong> 10mm on all sides</li>
                <li><strong>Font:</strong> Arial, 9pt for print version</li>
                <li><strong>Colors:</strong> Black text on white background, red subtitle, orange authority section</li>
                <li><strong>Layout:</strong> Table format with labels in left column, data in right column</li>
            </ul>
        </div>
    </div>
</body>
</html>
JM
<tr>
                        <td>
                            PHONE NUMBER OF FAMILY LEADER:
                            <span class="form-label-local">(NUMERO SA SELPON SANG PANGULO SANG PANIMALAY)</span>
                        </td>
                        <td>09123456789</td>
                    </tr>
                    <tr>
                        <td>
                            PERSONS WITH SPECIAL NEEDS:
                            <span class="form-label-local">(MIYEMBRO NGA MAY ESPESYAL NGA PANGINAHANGLANON)</span>
                        </td>
                        <td>Maria Santos (Elderly, 75 years old)</td>
                    </tr>
                    <tr>
                        <td>
                            STAYING INSIDE EVACUATION CENTER?:
                            <span class="form-label-local">(MUSULOD BA MO SA EVACUATION CENTER?)</span>
                        </td>
                        <td>
                            <div class="checkbox-group">
                                <div class="checkbox-item">
                                    <div class="checkbox-box checked"></div>
                                    <span>YES (Oo)</span>
                                </div>
                                <div class="checkbox-item">
                                    <div class="checkbox-box"></div>
                                    <span>NO (Indi)</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- Control Number Section -->
                <div class="control-number-section">
                    <table class="control-number-table">
                        <tr>
                            <td>CONTROL NUMBER:</td>
                            <td>
                                <div class="control-number-box">
                                    2024-CAN-001
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Authority Section -->
            <div class="authority-section">
                <div class="logo-placeholder">
                    Place LGU logo here
                </div>
                
                <div class="authority-list">
                    <div class="authority-item">
                        <div class="authority-name">LDRRMO</div>
                        <div class="authority-line"></div>
                    </div>
                    <div class="authority-item">
                        <div class="authority-name">PUNONG BARANGAY</div>
                        <div class="authority-line"></div>
                    </div>
                    <div class="authority-item">
                        <div class="authority-name">PUROK LEADER</div>
                        <div class="authority-line"></div>
                    </div>
                    <div class="authority-item">
                        <div class="authority-name">LOCAL POLICE STATION</div>
                        <div class="authority-line"></div>
                    </div>
                    <div class="authority-item">
                        <div class="authority-name">OFFICE OF CIVIL DEFENSE NIR</div>
                        <div class="authority-line">
                            <span class="authority-phone">09956112342 / 09177040134</span>
                        </div>
                    </div>
                </div>
            </div>

<<<<<<< HEAD
				const unmatched = [];
				if (!fnameMatch) unmatched.push("First Name");
				if (!mnameMatch) unmatched.push("Middle Name");
				if (!lnameMatch) unmatched.push("Last Name");
				if (!extMatch) unmatched.push("Extension");

				Swal.fire({
					icon: 'error',
					title: 'Name Mismatch',
					html: `
						<div><b>Detected ID Type:</b> ${detectedType}</div>
						<div><b>ID Number:</b> ${extractedIdNumber || '<i>Not Detected</i>'}</div>
						<div class="mt-2">❌ ${unmatched.join(", ")} not found on the ID.<br>The uploaded image has been cleared.</div>
					`,
					confirmButtonColor: '#dc3545'
				});
			}
		}).catch(err => {
			console.error(err);
			document.getElementById('ic_image').value = "";
			Swal.fire({
				icon: 'error',
				title: 'OCR Error',
				text: 'There was an error reading the ID image. Please try again.',
				confirmButtonColor: '#dc3545'
			});
		});
	});
</script>
=======
            <!-- Footer -->
            <div class="footer">
                <div>REGIONAL TASK FORCE KANLAON</div>
                <div class="footer-logo">
                    <div class="volcano-logo"></div>
                </div>
            </div>
        </div>
        
        <div class="print-info">
            <h3>Print Information:</h3>
            <ul>
                <li><strong>Page Size:</strong> A4 (210mm x 297mm)</li>
                <li><strong>Cards Per Page:</strong> 1 card per page for optimal readability</li>
                <li><strong>Print Orientation:</strong> Portrait</li>
                <li><strong>Margins:</strong> 10mm on all sides</li>
                <li><strong>Font:</strong> Arial, 9pt for print version</li>
                <li><strong>Colors:</strong> Black text on white background, red subtitle, orange authority section</li>
                <li><strong>Layout:</strong> Table format with labels in left column, data in right column</li>
            </ul>
        </div>
    </div>
</body>
</html>
>>>>>>> 9e32bc69daf28618ced9cf3af334bf4222abc637
