function toggleSignature() {
						const option = document.querySelector('input[name="option"]:checked').value;
						const drawSection = document.getElementById('draw');
						const uploadSection = document.getElementById('upload');
						if (option === 'draw') {
							drawSection.style.display = 'block';
							uploadSection.style.display = 'none';
						} else {
							drawSection.style.display = 'none';
							uploadSection.style.display = 'block';
						}
					}


					document.addEventListener("DOMContentLoaded", function() {
						let drawing1 = false;
						const canvas1 = document.getElementById("pad");
						const ctx1 = canvas1.getContext("2d");

						// Set canvas size correctly


						// Drawing styles
						ctx1.lineWidth = 1;
						ctx1.lineCap = "round";
						ctx1.strokeStyle = "#000";
						// Mouse events
						canvas1.addEventListener("mousedown", (e) => {
							drawing1 = true;
							const {
								x,
								y
							} = getCoords(e);
							ctx1.beginPath();
							ctx1.moveTo(x, y);
						});

						canvas1.addEventListener("mousemove", (e) => {
							if (!drawing1) return;
							const {
								x,
								y
							} = getCoords(e);
							ctx1.lineTo(x, y);
							ctx1.stroke();
						});

						canvas1.addEventListener("mouseup", () => {
							drawing1 = false;
							ctx1.closePath();
							saveSignature();
						});

						canvas1.addEventListener("mouseleave", () => {
							if (drawing1) {
								drawing1 = false;
								ctx1.closePath();
								saveSignature();
							}
						});

						// Touch events
						canvas1.addEventListener("touchstart", (e) => {
							e.preventDefault();
							drawing1 = true;
							const {
								x,
								y
							} = getCoords(e.touches[0]);
							ctx1.beginPath();
							ctx1.moveTo(x, y);
						});

						canvas1.addEventListener("touchmove", (e) => {
							e.preventDefault();
							if (!drawing1) return;
							const {
								x,
								y
							} = getCoords(e.touches[0]);
							ctx1.lineTo(x, y);
							ctx1.stroke();
						});

						canvas1.addEventListener("touchend", () => {
							drawing1 = false;
							ctx1.closePath();
							saveSignature();
						});

						// Coordinate helper
						function getCoords(e) {
							const rect = canvas1.getBoundingClientRect();
							return {
								x: e.clientX - rect.left,
								y: e.clientY - rect.top,
							};
						}

						// Save canvas1 to hidden input
						function saveSignature() {
							document.getElementById("signature_data").value = canvas1.toDataURL("image/png");
						}

						// Clear canvas1
						window.clearSignature = function() {
							ctx1.clearRect(0, 0, canvas1.width, canvas1.height);
							document.getElementById("signature_data").value = "";
						};
					});