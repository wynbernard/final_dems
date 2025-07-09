<!doctype html>
<html lang="en">
<!--begin::Head-->

<?php
include '../../../database/session.php';
include '../layout/head_links.php';

?>
<!--end::Head-->
	
<!--begin::Body-->
ALYSSA
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

	<?php if (isset($_SESSION['error'])): ?>
		<div id="errorAlert" class="alert position-fixed top-0 start-50 translate-middle-x text-center p-3 fw-bold shadow-lg mt-3"
			role="alert" style="z-index: 1050; opacity: 0; transition: opacity 0.5s ease-in-out; font-size: 0.9rem; width: 30%; background-color: red; color: white; border: none;">
			<?php echo $_SESSION['error']; ?>
		</div>
		<?php unset($_SESSION['error']); ?>

		<script>
			document.addEventListener("DOMContentLoaded", function() {
				let errorAlert = document.getElementById("errorAlert");
				if (errorAlert) {
					errorAlert.style.opacity = "1"; // Show alert
					setTimeout(() => {
						errorAlert.style.opacity = "0"; // Hide after 3 seconds
					}, 3000);
				}
			});
		</script>
	<?php endif; ?>
	<div class="app-wrapper">
		<?php
		include '../layout/header.php';
		include '../layout/sidebar.php';
		?>
		<main class="app-main">
			<?php
			include '../layout/main.content.php';
			?>
			<!--end::App Content-->
		</main>
		<?php
		include '../layout/footer.php';
		?>
	</div>
	<?php
	include '../scripts/weather.php';
	?>
</body>
<!--end::Body-->

</html>
