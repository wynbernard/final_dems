	
// SIGNATURE PAD SCRIPT
	let canvas = document.getElementById("signature-pad");
	let ctx = canvas.getContext("2d");
	let drawing = false;

	canvas.width = canvas.offsetWidth;
	canvas.height = canvas.offsetHeight;

	canvas.addEventListener("mousedown", () => drawing = true);
	canvas.addEventListener("mouseup", () => {
		drawing = false;
		ctx.beginPath();
		saveSignature();
	});
	canvas.addEventListener("mousemove", (e) => {
		draw(e.clientX, e.clientY);
	});

	canvas.addEventListener("touchstart", (e) => {
		e.preventDefault();
		drawing = true;
	});
	canvas.addEventListener("touchend", (e) => {
		e.preventDefault();
		drawing = false;
		ctx.beginPath();
		saveSignature();
	});
	canvas.addEventListener("touchmove", (e) => {
		e.preventDefault();
		const touch = e.touches[0];
		draw(touch.clientX, touch.clientY);
	});

	function draw(x, y) {
		if (!drawing) return;
		const rect = canvas.getBoundingClientRect();
		ctx.lineWidth = 2;
		ctx.lineCap = "round";
		ctx.strokeStyle = "#000";
		ctx.lineTo(x - rect.left, y - rect.top);
		ctx.stroke();
		ctx.beginPath();
		ctx.moveTo(x - rect.left, y - rect.top);
	}

	function clearSignature() {
		ctx.clearRect(0, 0, canvas.width, canvas.height);
		document.getElementById("signature_data").value = "";
	}

	function saveSignature() {
		document.getElementById("signature_data").value = canvas.toDataURL("image/png");
	}

	// Toggle between draw/upload
	function toggleSignatureInput() {
		const option = document.querySelector('input[name="signature_option"]:checked').value;
		document.getElementById("signature-draw").style.display = (option === "draw") ? "block" : "none";
		document.getElementById("signature-upload").style.display = (option === "upload") ? "block" : "none";
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