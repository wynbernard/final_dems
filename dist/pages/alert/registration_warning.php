<?php
if (isset($_SESSION['success'])) {
	echo "<div class='alert alert-success text-white text-center' role='alert'>" . $_SESSION['success'] . "</div>";
	unset($_SESSION['success']); // Remove success message after displaying
}

if (isset($_SESSION['error'])) {
	echo "<div class='alert alert-danger text-white text-center' role='alert'>" . $_SESSION['error'] . "</div>";
	unset($_SESSION['error']); // Remove error message after displaying
}
?>

<script>
	setTimeout(() => {
		let alerts = document.querySelectorAll('.alert');
		alerts.forEach(alert => alert.style.display = 'none');
	}, 5000); // Alert disappears after 5 seconds
</script>