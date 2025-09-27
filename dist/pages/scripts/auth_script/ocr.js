document.getElementById('ic_image').addEventListener('change', function() {
								const file = this.files[0];
								const fname = document.getElementById('f_name').value.trim().toLowerCase();
								const mname = document.getElementById('m_name').value.trim().toLowerCase();
								const lname = document.getElementById('l_name').value.trim().toLowerCase();
								const ext = document.getElementById('name_extension').value.trim().toLowerCase();
								const idSelect = document.getElementById('icp');

							if (!file || !fname || !lname) {
									Swal.fire({
										icon: 'warning',
										title: 'Missing Input',
									text: 'Please fill out first and last name before uploading the ID.'
									});
									this.value = "";
									return;
								}

								const reader = new FileReader();
								reader.onload = function() {
									const img = new Image();
									img.onload = function() {
										const canvas = document.createElement('canvas');
										const ctx = canvas.getContext('2d');
										canvas.width = img.width;
										canvas.height = img.height;
										ctx.drawImage(img, 0, 0);
										const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
										const qrCode = jsQR(imageData.data, canvas.width, canvas.height);


										if (qrCode) {
											let qrData;
											qrData = JSON.parse(qrCode.data);

											const qrFname  = (qrData.subject?.fName || "").trim().toLowerCase();
											const qrMname  = (qrData.subject?.mName || "").trim().toLowerCase();
											const qrLname  = (qrData.subject?.lName || "").trim().toLowerCase();
											const qrSuffix = (qrData.subject?.Suffix || "").trim().toLowerCase();

											 // Compare each field (middle name & suffix optional if left blank)
											const isFnameMatch = fname === qrFname;
											const isMnameMatch = mname ? mname === qrMname : true;
											const isLnameMatch = lname === qrLname;
											const isExtMatch   = ext   ? ext === qrSuffix : true;


											// Extract PCN (PhilSys number format: ####-####-####-####)
											 if (qrData.subject?.PCN) {
												document.getElementById('icn').value = qrData.subject.PCN;
												console.log("PCN:", qrData.subject.PCN);
												console.log(qrFname);
											}

											if (isFnameMatch && isMnameMatch && isLnameMatch && isExtMatch) {
												idSelect.value = 'Philippine National ID';
											Swal.fire({
												icon: 'success',
												title: 'QR Name Match',
												text: 'QR code successfully verified.',
												confirmButtonColor: '#198754'
											});
											} else {
											const mismatches = [];
											if (!isFnameMatch) mismatches.push('First Name');
											if (!isMnameMatch) mismatches.push('Middle Name');
											if (!isLnameMatch) mismatches.push('Last Name');
											if (!isExtMatch) mismatches.push('Extension');

											const shouldClear = !isFnameMatch || !isLnameMatch; // only clear if core names mismatch
											if (shouldClear) {
												document.getElementById('ic_image').value = "";
											}

											const message = `❌ ${mismatches.join(", ")} not found on the ID.${shouldClear ? '<br>The uploaded image has been cleared.' : ''}`;
											Swal.fire({
												icon: 'error',
												title: 'QR Name Mismatch',
												html: message,
												confirmButtonColor: '#dc3545'
											});
											}
										} else {
										// Fallback to OCR with preprocessing and rotations
										runTesseractOCR(file, fname, mname, lname, ext, idSelect);
										}
									};
									img.src = reader.result;
								};
								reader.readAsDataURL(file);
							});

							function runTesseractOCR(file, fname, mname, lname, ext, idSelect) {
								Swal.fire({
									title: 'Checking ID...',
									html: 'Extracting text from the ID image.<br><b>Please wait.</b>',
									allowOutsideClick: false,
									didOpen: () => Swal.showLoading()
								});

								Tesseract.recognize(file, 'eng', {
									logger: m => {
										if (m.status === "recognizing text") {
											Swal.update({
												html: `Extracting text... <b>${Math.round(m.progress * 100)}%</b>`
											});
										}
									}
								}).then(({
									data: {
										text
									}
								}) => {
									const normalize = str => str.toLowerCase().replace(/[^\w\s\-\/]/gi, '').replace(/\s+/g, ' ').trim();
									const cleanText = normalize(text);

									// ID number extraction
									const idNumberMatch = cleanText.match(/\b([A-Z0-9]{3,}-[A-Z0-9]{2,}-[A-Z0-9]{3,}(?:-[A-Z0-9]+)?)\b|\b\d{4}-\d{4}-\d{4}-\d{4}\b/);
									const extractedIdNumber = idNumberMatch ? idNumberMatch[0] : "";
									document.getElementById('icn').value = extractedIdNumber;

									// ID type detection
									const idTypeMap = {
										"philippine national id": "Philippine National ID",
										"philsys": "Philippine National ID",
										"passport": "Passport",
										"driver": "Driver's License",
										"lto": "Driver's License",
										"umid": "UMID",
										"sss": "SSS ID",
										"prc": "PRC ID",
										"voter": "Voter's ID",
										"tin": "TIN ID",
										"philhealth": "PhilHealth ID"
									};

									let detectedType = "Unknown";
									for (const keyword in idTypeMap) {
										if (cleanText.includes(keyword)) {
											detectedType = idTypeMap[keyword];
											break;
										}
									}

									if (detectedType === "Unknown") {
										if (/^\d{4}-\d{4}-\d{4}-\d{4}$/.test(extractedIdNumber)) {
											detectedType = "Philippine National ID";
										} else if (/^[A-Z]{1,3}-\d{2}-\d{6,7}$/.test(extractedIdNumber)) {
											detectedType = "Driver's License";
										} else if (/^\d{2}-\d{9,10}$/.test(extractedIdNumber)) {
											detectedType = "PhilHealth ID";
										} else if (/^\d{9}$/.test(extractedIdNumber)) {
											detectedType = "TIN ID";
										} else if (/^\d{2}-\d{7,10}$/.test(extractedIdNumber)) {
											detectedType = "SSS ID";
										}
									}

									const matchOption = Array.from(idSelect.options).find(opt => opt.value === detectedType);
									if (matchOption) {
										idSelect.value = detectedType;
									}

									const fnameMatch = cleanText.includes(fname);
									const mnameMatch = mname ? cleanText.includes(mname) : true; // optional
									const lnameMatch = cleanText.includes(lname);
									const extMatch = ext ? cleanText.includes(ext) : true;

									if (fnameMatch && mnameMatch && lnameMatch && extMatch) {
										Swal.fire({
											icon: 'success',
											title: 'Name Matched',
											text: '✅ Name matched successfully!',
											confirmButtonColor: '#198754'
										});
									} else {
										const unmatched = [];
										if (!fnameMatch) unmatched.push("First Name");
										if (!mnameMatch) unmatched.push("Middle Name");
										if (!lnameMatch) unmatched.push("Last Name");
										if (!extMatch) unmatched.push("Extension");

										const shouldClear = !fnameMatch || !lnameMatch; // only clear if core names mismatch
										if (shouldClear) {
											document.getElementById('ic_image').value = "";
										}

										const message = `❌ ${unmatched.join(", ")} not found on the ID.${shouldClear ? '<br>The uploaded image has been cleared.' : ''}`;
										Swal.fire({
											icon: 'error',
											title: 'Name Mismatch',
											html: message,
											confirmButtonColor: '#dc3545'
										});
									}
								}).catch(err => {
									console.error(err);
									document.getElementById('ic_image').value = "";
									Swal.fire({
										icon: 'error',
										title: 'OCR Error',
										text: 'There was an error reading the ID image.',
										confirmButtonColor: '#dc3545'
									});
								});
}