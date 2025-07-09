<style>
	.modal-container {
		background-color: white;
		border-radius: 20px;
		box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
		width: 100%;
		max-width: 1200px;
		overflow: hidden;
		animation: slideUp 0.5s ease-out;
	}

	@keyframes slideUp {
		from {
			transform: translateY(50px);
			opacity: 0;
		}

		to {
			transform: translateY(0);
			opacity: 1;
		}
	}

	.modal-header {
		background: linear-gradient(90deg, #2575fc, #6a11cb);
		color: white;
		padding: 25px 30px;
		display: flex;
		justify-content: space-between;
		align-items: center;
	}

	.modal-header h2 {
		font-weight: 600;
		font-size: 1.8rem;
		display: flex;
		align-items: center;
		gap: 15px;
	}

	.modal-header h2 i {
		font-size: 1.5rem;
	}

	.close-btn {
		background: rgba(255, 255, 255, 0.2);
		border: none;
		width: 40px;
		height: 40px;
		border-radius: 50%;
		color: white;
		font-size: 1.3rem;
		cursor: pointer;
		transition: all 0.3s ease;
	}

	.close-btn:hover {
		background: rgba(255, 255, 255, 0.3);
		transform: rotate(90deg);
	}

	.modal-body {
		padding: 30px;
		max-height: 70vh;
		overflow-y: auto;
	}

	.info-grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
		gap: 25px;
	}

	.info-section {
		background: #f8f9ff;
		border-radius: 15px;
		padding: 25px;
		box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
		border: 1px solid #eef2f7;
	}

	.section-header {
		display: flex;
		align-items: center;
		gap: 12px;
		margin-bottom: 20px;
		padding-bottom: 15px;
		border-bottom: 2px solid #eef2f7;
	}

	.section-header i {
		font-size: 1.3rem;
		color: #2575fc;
		background: #e6f0ff;
		width: 40px;
		height: 40px;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.section-header h3 {
		color: #2c3e50;
		font-size: 1.3rem;
	}

	.info-item {
		display: flex;
		margin-bottom: 18px;
		line-height: 1.5;
	}

	.info-item strong {
		flex: 0 0 40%;
		color: #5a6d80;
		font-weight: 600;
		font-size: 0.95rem;
	}

	.info-item span {
		flex: 0 0 60%;
		color: #2c3e50;
		font-size: 1.05rem;
		word-break: break-word;
	}

	.id-image-container {
		margin-top: 15px;
		text-align: center;
	}

	#review_id_image {
		max-width: 100%;
		max-height: 200px;
		border-radius: 10px;
		box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
		border: 1px solid #e0e6ed;
		display: none;
		margin: 10px auto;
	}

	.id-image-container p {
		font-style: italic;
		color: #95a5a6;
		margin-top: 5px;
	}

	.password-container {
		display: flex;
		align-items: center;
		gap: 10px;
	}

	.toggle-password {
		background: #eef2f7;
		border: none;
		border-radius: 5px;
		padding: 5px 10px;
		font-size: 0.85rem;
		cursor: pointer;
		color: #2575fc;
		transition: background 0.2s;
	}

	.toggle-password:hover {
		background: #e0e6ed;
	}

	.modal-footer {
		padding: 20px 30px;
		background: #f8f9ff;
		display: flex;
		justify-content: flex-end;
		gap: 15px;
		border-top: 1px solid #eef2f7;
	}


	.btn-edit {
		background: #f1f2f6;
		color: #5a6d80;
	}

	.btn-submit {
		background: linear-gradient(90deg, #2575fc, #6a11cb);
		color: white;
		box-shadow: 0 5px 15px rgba(37, 117, 252, 0.4);
	}

	.btn:hover {
		transform: translateY(-3px);
		box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
	}

	.btn:active {
		transform: translateY(1px);
	}

	@media (max-width: 768px) {
		.info-grid {
			grid-template-columns: 1fr;
		}

		.modal-header {
			padding: 20px;
		}

		.modal-body {
			padding: 20px;
		}

		.info-section {
			padding: 20px;
		}

		.btn {
			padding: 10px 20px;
			font-size: 0.95rem;
		}

		.info-item {
			flex-direction: column;
			gap: 5px;
		}

		.info-item strong {
			flex: 1;
		}

		.info-item span {
			flex: 1;
		}
	}
</style>