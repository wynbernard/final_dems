
	// REVEAL PASSWORD
function toggleVisibility(fieldId, iconSpan) {
	const input = document.getElementById(fieldId);
	const icon = iconSpan.querySelector("i");
	if (input.type === "password") {
		input.type = "text";
		icon.classList.remove("fa-eye-slash");
		icon.classList.add("fa-eye");
	} else {
		input.type = "password";
		icon.classList.remove("fa-eye");
		icon.classList.add("fa-eye-slash");
	}
}

// REAL TIME VALIDATION PASSWORD
function validatePassword() {
		const password = document.getElementById("password").value;
		const confirmPassword = document.getElementById("confirm_password").value;
		const message = document.getElementById("passwordMatchMessage");

		if (password === "" && confirmPassword === "") {
			message.textContent = "";
			return;
		}

		if (password === confirmPassword) {
			message.textContent = "✅ Passwords match!";
			message.classList.remove("text-danger");
			message.classList.add("text-success");
		} else {
			message.textContent = "❌ Passwords do not match!";
			message.classList.remove("text-success");
			message.classList.add("text-danger");
		}
		 const message1 = document.getElementById("passwordHelp");

	let errors = [];

	if (password.length < 8) errors.push("8+ chars");
	if (!/[A-Z]/.test(password)) errors.push("1 uppercase");
	if (!/[a-z]/.test(password)) errors.push("1 lowercase");
	if (!/[0-9]/.test(password)) errors.push("1 number");
	if (!/[!@#$%^&*]/.test(password)) errors.push("1 special (!@#$%^&*)");

	if (errors.length === 0) {
		message1.classList.remove("text-danger");
		message1.classList.add("text-success");
		message1.textContent = "✅ Password is strong ";
	} else {
		message1.classList.remove("text-success");
		message1.classList.add("text-danger");
		message1.textContent = "Include: " + errors.join(", ");
	}
	}

// EMAIL VALIDATION 
document.addEventListener("DOMContentLoaded", function() {
		let emailInput = document.getElementById("email");
		let feedback = document.getElementById("emailFeedback");
		let submitBtn = document.getElementById("submitBtn"); // Make sure your submit button has this ID

		emailInput.addEventListener("input", function() {
			let email_address = emailInput.value.trim();

			if (email_address === "") {
				feedback.innerHTML = "";
				emailInput.classList.remove("is-valid", "is-invalid");
				submitBtn.disabled = true;
				return;
			}

			if (!validateEmail(email_address)) {
				feedback.innerHTML = "Invalid email format.";
				feedback.style.color = "red";
				emailInput.classList.add("is-invalid");
				emailInput.classList.remove("is-valid");
				submitBtn.disabled = true;
				return;
			}

			checkEmailAvailability(email_address);
		});

		function validateEmail(email_address) {
			let re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			return re.test(email_address);
		}

		function checkEmailAvailability(email_address) {
			fetch("../check_validation/user_email.php", {
					method: "POST",
					headers: {
						"Content-Type": "application/x-www-form-urlencoded"
					},
					body: "email_address=" + encodeURIComponent(email_address)
				})
				.then(response => response.text())
				.then(data => {
					if (data.trim() === "taken") {
						feedback.innerHTML = "Email already registered.";
						feedback.style.color = "red";
						emailInput.classList.add("is-invalid");
						emailInput.classList.remove("is-valid");
						submitBtn.disabled = true;
					} else if (data.trim() === "available") {
						feedback.innerHTML = "Email available.";
						feedback.style.color = "green";
						emailInput.classList.add("is-valid");
						emailInput.classList.remove("is-invalid");
						submitBtn.disabled = false;
					} else {
						feedback.innerHTML = "Error checking email.";
						feedback.style.color = "orange";
						emailInput.classList.add("is-invalid");
						emailInput.classList.remove("is-valid");
						submitBtn.disabled = true;
					}
				})
				.catch(error => {
					console.error("Error:", error);
					feedback.innerHTML = "Error connecting to server.";
					feedback.style.color = "orange";
					emailInput.classList.add("is-invalid");
					emailInput.classList.remove("is-valid");
					submitBtn.disabled = true;
				});
		}
	});

// ETHNICITY
function toggleEthnicity() {
		const checkbox = document.getElementById('ip');
		const ethnicityField = document.getElementById('ethnicityField');
		ethnicityField.style.display = checkbox.checked ? 'block' : 'none';
	}

// PHONE NUMBER VALIDATION
document.getElementById('contact_no').addEventListener('input', function() {
		let contactInput = this.value;
		let errorMessage = document.getElementById('contactError');
		let regex = /^[0-9]{10,15}$/;

		if (!regex.test(contactInput)) {
			errorMessage.textContent = "Contact number must be 10-15 digits.";
			this.classList.add('is-invalid');
		} else {
			errorMessage.textContent = "";
			this.classList.remove('is-invalid');
		}
	});

	// MONTHLY INCOME VALIDATION

function formatWithCommas() {
	const display = document.getElementById("monthly_income_display");
	const hidden = document.getElementById("monthly_income");

	// Remove all non-digit characters
	let raw = display.value.replace(/[^0-9]/g, "");

	// Format number with commas
	let formatted = raw.replace(/\B(?=(\d{3})+(?!\d))/g, ",");

	// Update fields
	display.value = raw ? `₱${formatted}` : ""; // Add ₱ if value exists
	hidden.value = raw;
}


// ID CARD NUMBER VALIDATION

function updateIDCardFormat() {
	const icp = document.getElementById("icp").value;
	const icn = document.getElementById("icn");

	// Reset all formatting
	icn.value = "";
	icn.setAttribute("type", "text");
	icn.removeAttribute("pattern");
	icn.removeAttribute("oninput");
	icn.removeAttribute("maxlength");
	icn.placeholder = "Enter ID Card Number";

	switch (icp) {
		case "Philippine National ID":
			icn.placeholder = "e.g., 1234-5678-9012";
			icn.setAttribute("pattern", "\\d{4}-\\d{4}-\\d{4}");
			icn.setAttribute("maxlength", "14");
			icn.setAttribute("oninput", "formatPhilID(this)");
			break;

		case "Passport":
			icn.placeholder = "e.g., P1234567";
			icn.setAttribute("pattern", "[A-Z][0-9]{7}");
			icn.setAttribute("maxlength", "8");
			break;

		case "Driver's License":
			icn.placeholder = "e.g., N01-23-456789";
			icn.setAttribute("pattern", "^[A-Z0-9]{3}-\\d{2}-\\d{6}$");
			icn.setAttribute("maxlength", "14");
			icn.setAttribute("oninput", "formatDriversLicense(this)");
			break;

		case "UMID":
		case "SSS ID":
			icn.placeholder = "e.g., 02-1234567-8";
			icn.setAttribute("pattern", "^\\d{2}-\\d{7}-\\d{1}$");
			icn.setAttribute("maxlength", "12");
			icn.setAttribute("oninput", "formatSSS_UMID(this)");
			break;
		case "TIN ID":
			icn.placeholder = "e.g., 123-456-789";
			icn.setAttribute("pattern", "\\d{3}-\\d{3}-\\d{3}");
			icn.setAttribute("maxlength", "11");
			icn.setAttribute("oninput", "formatTIN(this)");
			break;

		case "PhilHealth ID":
			icn.placeholder = "e.g., 12-345678901-2";
			icn.setAttribute("pattern", "\\d{2}-\\d{9}-\\d{1}");
			icn.setAttribute("maxlength", "14");
			icn.setAttribute("oninput", "formatPhilHealth(this)");
			break;

		case "PRC ID":
			icn.placeholder = "e.g., 1234567";
			icn.setAttribute("pattern", "\\d{7}");
			icn.setAttribute("maxlength", "7");
			break;

		case "Voter's ID":
			icn.placeholder = "e.g., 1234-5678A-9";
			icn.setAttribute("pattern", "\\d{4}-\\d{4}[A-Z]{1}-\\d{1}");
			icn.setAttribute("maxlength", "13");
			icn.setAttribute("oninput", "formatVotersID(this)");
			break;

		default:
			icn.placeholder = "Enter ID Card Number";
			break;
	}
}

// Formatting functions
function formatPhilID(input) {
	let value = input.value.replace(/\D/g, "").slice(0, 12);
	input.value = value.replace(/(\d{4})(\d{0,4})(\d{0,4})/, (_, a, b, c) => {
		let out = a;
		if (b) out += "-" + b;
		if (c) out += "-" + c;
		return out;
	});
}

function formatDriversLicense(input) {
	let value = input.value.replace(/[^A-Za-z0-9]/g, "").toUpperCase().slice(0, 11);

	input.value = value.replace(/^(.{0,3})(.{0,2})(.{0,6})/, (_, a, b, c) => {
		let out = a;
		if (b) out += "-" + b;
		if (c) out += "-" + c;
		return out;
	});
}
function formatSSS_UMID(input) {
	let value = input.value.replace(/\D/g, "").slice(0, 10); // 2 + 7 + 1 = 10 digits

	input.value = value.replace(/^(\d{0,2})(\d{0,7})(\d{0,1})/, (_, a, b, c) => {
		let out = a;
		if (b) out += "-" + b;
		if (c) out += "-" + c;
		return out;
	});
}

function formatTIN(input) {
	let value = input.value.replace(/\D/g, "").slice(0, 9);
	input.value = value.replace(/(\d{3})(\d{0,3})(\d{0,3})/, (_, a, b, c) => {
		let out = a;
		if (b) out += "-" + b;
		if (c) out += "-" + c;
		return out;
	});
}
function formatVotersID(input) {
	let value = input.value.toUpperCase().replace(/[^A-Z0-9]/g, "").slice(0, 10); // 4 + 4 + 1 + 1 = 10 characters

	input.value = value.replace(/^(\d{0,4})(\d{0,4})([A-Z]?)(\d?)$/, (_, a, b, c, d) => {
		let out = a;
		if (b) out += "-" + b;
		if (c) out += c;
		if (d) out += "-" + d;
		return out;
	});
}

function formatPhilHealth(input) {
	let value = input.value.replace(/\D/g, "").slice(0, 12);
	input.value = value.replace(/(\d{2})(\d{0,9})(\d{0,1})/, (_, a, b, c) => {
		let out = a;
		if (b) out += "-" + b;
		if (c) out += "-" + c;
		return out;
	});
}