<style>
		.profile-card {
			box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
			border: none;
			transition: all 0.3s ease;
		}
		
		.profile-card:hover {
			box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
			transform: translateY(-2px);
		}
		
		.card-header {
			background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
			color: white;
			border-radius: 0.5rem 0.5rem 0 0 !important;
		}
		
		#locationMap {
			border-radius: 0.5rem;
			overflow: hidden;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
		}
		
		.member-avatar {
			width: 45px;
			height: 45px;
			object-fit: cover;
			border: 2px solid #fff;
			box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
		}
		
		.table-responsive {
			border-radius: 0.5rem;
			overflow: hidden;
		}
		
		.table {
			margin-bottom: 0;
		}
		
		.table th {
			background-color: #f8f9fa;
			font-weight: 600;
			color: #495057;
		}
		
		.table-hover tbody tr:hover {
			background-color: rgba(106, 17, 203, 0.05);
		}
		
		.btn-outline-info {
			color: #17a2b8;
			border-color: #17a2b8;
		}
		
		.btn-outline-info:hover {
			background-color: #17a2b8;
			color: white;
		}
		
		.btn-outline-danger {
			color: #dc3545;
			border-color: #dc3545;
		}
		
		.btn-outline-danger:hover {
			background-color: #dc3545;
			color: white;
		}
		
		.empty-state {
			padding: 3rem;
			text-align: center;
			background-color: #f8f9fa;
			border-radius: 0.5rem;
		}
		
		.empty-state i {
			font-size: 3rem;
			color: #adb5bd;
			margin-bottom: 1rem;
		}
		
		.add-member-btn {
			background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
			border: none;
			box-shadow: 0 4px 15px rgba(106, 17, 203, 0.3);
			transition: all 0.3s ease;
		}
		
		.add-member-btn:hover {
			transform: translateY(-2px);
			box-shadow: 0 8px 25px rgba(106, 17, 203, 0.4);
		}
		
		.location-placeholder {
			background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
			display: flex;
			align-items: center;
			justify-content: center;
			height: 250px;
			border-radius: 0.5rem;
		}
		
		.location-placeholder i {
			font-size: 3rem;
			color: #6c757d;
		}
	</style>