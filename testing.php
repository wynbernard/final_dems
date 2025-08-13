<script>
	document.getElementById('ic_image').addEventListener('change', function() {
		const file = this.files[0];
		const fname = document.getElementById('f_name').value.trim().toLowerCase();
		const mname = document.getElementById('m_name').value.trim().toLowerCase();
		const lname = document.getElementById('l_name').value.trim().toLowerCase();
		const ext = document.getElementById('name_extension').value.trim().toLowerCase();
		const idSelect = document.getElementById('icp');

		if (!file || !fname || !mname || !lname) {
			Swal.fire({
				icon: 'warning',
				title: 'Missing Input',
				text: 'Please fill out first, middle, and last name before uploading the ID.'
			});
			this.value = "";
			return;
		}

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
			const normalize = str =>
				str.toLowerCase().replace(/[^\w\s\-\/]/gi, '').replace(/\s+/g, ' ').trim();
			const cleanText = normalize(text);

			// Match ID number
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

			// Fallback detection based on format
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

			// Update select dropdown
			const matchOption = Array.from(idSelect.options).find(opt => opt.value === detectedType);
			if (matchOption) {
				idSelect.value = detectedType;
			}

			// Name matching
			const fnameMatch = cleanText.includes(normalize(fname));
			const mnameMatch = cleanText.includes(normalize(mname));
			const lnameMatch = cleanText.includes(normalize(lname));
			const extMatch = ext ? cleanText.includes(normalize(ext)) : true;

			// Final validation
			if (fnameMatch && mnameMatch && lnameMatch && extMatch) {
				Swal.fire({
					icon: extractedIdNumber ? 'success' : 'warning',
					title: extractedIdNumber ? 'ID Number Detected' : 'ID Number Not Found',
					html: `
						<div><b>Detected ID Type:</b> ${detectedType}</div>
						<div><b>ID Number:</b> ${extractedIdNumber || '<i>Not Detected</i>'}</div>
						<div class="mt-2">✅ Name matched successfully!</div>
					`,
					confirmButtonColor: '#198754'
				});
			} else {
				// Remove the uploaded image if names do not match
				document.getElementById('ic_image').value = "";

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