<style>
	#qrScanner canvas {
		width: 100% !important;
		height: 100% !important;
		object-fit: cover;
		border-radius: 0.5rem;
	}

	.border-dashed {
		border-style: dashed !important;
	}

	#familyList::-webkit-scrollbar {
		width: 6px;
	}

	#familyList::-webkit-scrollbar-track {
		background-color: #f9f9f9;
		border-radius: 10px;
	}

	#familyList::-webkit-scrollbar-thumb {
		background-color: #ccc;
		border-radius: 10px;
	}

	.member-checkbox:disabled+label {
		opacity: 0.6;
	}

	.modal-content {
		max-height: 95vh;
		overflow-y: auto;
	}
</style>