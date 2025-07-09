<style>
	:root {
		--primary-color: #6366f1;
		--primary-dark: #4f46e5;
		--secondary-color: #f8fafc;
		--accent-color: #f59e0b;
		--text-dark: #1e293b;
		--text-light: #64748b;
		--border-color: #e2e8f0;
		--card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
		--card-shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
	}

	/* Base Card Styles */
	.profile-card,
	.profile-image-card {
		border: none;
		border-radius: 12px;
		box-shadow: var(--card-shadow);
		transition: all 0.3s ease;
		overflow: hidden;
		background: white;
	}

	.profile-card:hover,
	.profile-image-card:hover {
		transform: translateY(-5px);
		box-shadow: var(--card-shadow-hover);
	}

	/* Profile Header */
	.profile-header {
		background: linear-gradient(135deg,
				var(--primary-color) 0%,
				#7c3aed 30%,
				#8b5cf6 70%,
				#a78bfa 100%);
		color: white;
		padding: 1.5rem;
		border-radius: 12px 12px 0 0;
		position: relative;
		overflow: hidden;
	}

	.profile-header::before {
		content: '';
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background: linear-gradient(90deg,
				rgba(255, 255, 255, 0) 0%,
				rgba(255, 255, 255, 0.15) 50%,
				rgba(255, 255, 255, 0) 100%);
		animation: shimmer 8s infinite linear;
		z-index: 1;
	}

	.profile-header>* {
		position: relative;
		z-index: 2;
	}

	.profile-header .title {
		font-weight: 600;
		margin: 0;
		font-size: 1.25rem;
	}

	/* Profile Body */
	.profile-body {
		padding: 1.5rem;
	}

	/* Form Elements */
	.form-group {
		margin-bottom: 1.25rem;
		animation: fadeIn 0.4s ease forwards;
	}

	.form-group label {
		color: var(--text-light);
		font-size: 0.85rem;
		font-weight: 500;
		display: flex;
		align-items: center;
		gap: 0.5rem;
		margin-bottom: 0.25rem;
	}

	.form-control-plaintext {
		font-weight: 500;
		color: var(--text-dark);
		padding: 0.375rem 0;
		border-bottom: 1px solid var(--border-color);
		margin: 0;
	}

	.edit-icon {
		color: var(--primary-color);
		transition: all 0.2s ease;
		cursor: pointer;
		font-size: 0.9rem;
	}

	.edit-icon:hover {
		color: var(--primary-dark);
		transform: scale(1.1);
	}

	/* Profile Image Card */
	/* Profile Image Card - Reduced Heights */
	.cover-image {
		height: 100px;
		/* Reduced from 140px */
		width: 100%;
		background: linear-gradient(135deg,
				var(--primary-color) 0%,
				#6d28d9 25%,
				#8b5cf6 50%,
				#a78bfa 75%,
				#c4b5fd 100%);
		position: relative;
		overflow: hidden;
	}

	.avatar-container {
		margin-top: -40px;
		/* Reduced from -50px to match new height */
		z-index: 3;
		text-align: center;
		position: relative;
	}

	.avatar {
		border: 4px solid white;
		box-shadow: var(--card-shadow);
		width: 75px;
		/* Reduced from 90px */
		height: 75px;
		/* Reduced from 90px */
		object-fit: cover;
		position: relative;
	}

	/* Responsive Adjustments */
	@media (max-width: 768px) {
		.cover-image {
			height: 70px;
			/* Reduced from 120px */
		}

		.avatar {
			width: 65px;
			/* Reduced from 80px */
			height: 65px;
			/* Reduced from 80px */
		}
	}

	@media (max-width: 576px) {
		.cover-image {
			height: 60px;
			/* Reduced from 100px */
		}

		.avatar {
			width: 60px;
			/* Reduced from 70px */
			height: 60px;
			/* Reduced from 70px */
		}
	}

	.user-name {
		font-weight: 700;
		color: var(--text-dark);
		margin-top: 1rem;
		margin-bottom: 0.25rem;
		font-size: 1.1rem;
	}

	.user-email {
		color: var(--text-light);
		font-size: 0.9rem;
		margin-bottom: 1rem;
	}

	.qr-code-container {
		background-color: var(--secondary-color);
		border-radius: 8px;
		padding: 0.75rem;
		display: inline-block;
		margin: 1rem 0;
	}

	/* Animations */
	@keyframes shimmer {
		0% {
			transform: translateX(-100%);
		}

		100% {
			transform: translateX(100%);
		}
	}

	@keyframes fadeIn {
		from {
			opacity: 0;
			transform: translateY(10px);
		}

		to {
			opacity: 1;
			transform: translateY(0);
		}
	}

	/* Animation delays for form groups */
	.form-group:nth-child(1) {
		animation-delay: 0.1s;
	}

	.form-group:nth-child(2) {
		animation-delay: 0.2s;
	}

	.form-group:nth-child(3) {
		animation-delay: 0.3s;
	}

	.form-group:nth-child(4) {
		animation-delay: 0.4s;
	}

	.form-group:nth-child(5) {
		animation-delay: 0.5s;
	}

	.form-group:nth-child(6) {
		animation-delay: 0.6s;
	}

	/* Responsive Adjustments */
	@media (max-width: 768px) {

		.profile-card,
		.profile-image-card {
			margin: 0 10px;
		}

		.cover-image {
			height: 120px;
		}

		.avatar {
			width: 80px;
			height: 80px;
		}

		.profile-header,
		.profile-body {
			padding: 1.25rem;
		}
	}

	@media (max-width: 576px) {
		.cover-image {
			height: 100px;
		}

		.avatar {
			width: 70px;
			height: 70px;
		}

		.profile-header .title {
			font-size: 1.1rem;
		}
	}

	.app-main {
		margin-top: 30px;
	}
</style>