<?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>
	<script>
		document.addEventListener("DOMContentLoaded", function() {
			const Toast = Swal.mixin({
				toast: true,
				position: 'top', // Center of the screen
				showConfirmButton: false,
				timer: 3000, // Auto-close after 3 seconds
				timerProgressBar: true, // Progress bar animation
				customClass: {
					popup: 'custom-toast', // Custom class for styling
					title: 'custom-toast-title',
					icon: 'custom-toast-icon'
				},
				didOpen: (toast) => {
					toast.addEventListener('mouseenter', Swal.stopTimer); // Pause on hover
					toast.addEventListener('mouseleave', Swal.resumeTimer); // Resume on leave
				}
			});

			<?php if (isset($_SESSION['success'])): ?>
				Toast.fire({
					icon: 'success',
					title: '<?php echo addslashes($_SESSION["success"]); ?>',
					background: '#d4edda', // Light green background
					color: '#155724', // Dark green text
					iconColor: '#28a745', // Green icon
					width: 400
				});
				<?php unset($_SESSION['success']); ?>
			<?php endif; ?>

			<?php if (isset($_SESSION['error'])): ?>
				Toast.fire({
					icon: 'error',
					title: '<?php echo addslashes($_SESSION["error"]); ?>',
					background: '#f8d7da', // Light red background
					color: '#721c24', // Dark red text
					iconColor: '#dc3545', // Red icon
					width: 400
				});
				<?php unset($_SESSION['error']); ?>
			<?php endif; ?>
		});
	</script>
	<style>
		.custom-toast-title {
			text-align: center !important;
		}

		.custom-toast {
			text-align: center !important;
		}
	</style>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>