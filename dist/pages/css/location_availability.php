<style>
	/* GENERAL TWEAKS */
	body {
		background-color: #f8f9fa;
		font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
	}

	.card {
		border-radius: 0.5rem;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
	}

	.card-header {
		background-color: rgb(255, 255, 255);
		color: #fff;
		border-radius: 0.5rem 0.5rem 0 0;
	}

	.card-header input {
		border-radius: 0.375rem;
	}

	/* ACCORDION STYLING */
	.accordion-button {
		background-color: #e7f1ff;
		font-weight: 600;
		color: #0d6efd;
		transition: background-color 0.3s ease, color 0.3s ease;
	}

	.accordion-button:not(.collapsed) {
		background-color: #0d6efd;
		color: #fff;
		box-shadow: inset 0 -2px 0 rgba(0, 0, 0, 0.15);
	}

	.accordion-item {
		border: none;
		background-color: #fff;
		border-radius: 0.5rem;
		margin-bottom: 1rem;
	}

	.accordion-body {
		background-color: #ffffff;
	}

	/* TABLE STYLING */
	.table thead {
		background-color: #f1f1f1;
	}

	.table th,
	.table td {
		vertical-align: middle;
	}

	/* BUTTONS */
	.btn-sm {
		border-radius: 0.375rem;
		padding: 0.375rem 0.75rem;
	}

	.btn-success,
	.btn-danger,
	.btn-primary {
		transition: background-color 0.2s ease-in-out, transform 0.1s ease;
	}

	.btn:hover {
		transform: scale(1.02);
	}

	.badge {
		font-size: 0.75rem;
	}

	/* BREADCRUMB */
	.breadcrumb-item+.breadcrumb-item::before {
		color: #6c757d;
	}

	.breadcrumb-item a {
		text-decoration: none;
		color: #0d6efd;
	}

	.breadcrumb-item.active {
		font-weight: 600;
		color: #212529;
	}

	/* RESPONSIVE INPUT AND HEADER */
	@media (max-width: 576px) {
		.card-header {
			flex-direction: column;
			align-items: stretch;
			gap: 0.75rem;
		}
	}
</style>