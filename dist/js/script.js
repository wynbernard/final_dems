

document.addEventListener("DOMContentLoaded", function () {
	let loginStatus = "<?php echo $loginStatus; ?>";
	if (loginStatus === "success") {
		new bootstrap.Modal(document.getElementById("successModal")).show();
	} else if (loginStatus === "failed") {
		new bootstrap.Modal(document.getElementById("errorModal")).show();
	}
});