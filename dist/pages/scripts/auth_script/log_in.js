
if (!('placeholder' in document.createElement('input'))) {
	document.querySelectorAll('.form-group input').forEach(input => {
		input.addEventListener('input', function() {
			const label = this.nextElementSibling;
				label.style.opacity = this.value.trim() ? '1' : '0';
			});
		});
	}

	