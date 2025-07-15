<script src="tesseract.js"></script>
<script>
  document.getElementById('ic_image').addEventListener('change', function() {
    const file = this.files[0];
    const fname = document.getElementById('f_name').value.trim().toLowerCase();
    const lname = document.getElementById('l_name').value.trim().toLowerCase();

    if (!file || !fname || !lname) {
      Swal.fire({
        icon: 'warning',
        title: 'Missing Input',
        text: 'Please enter both first and last name before uploading the ID.'
      });
      this.value = "";
      return;
    }

    // Show loading modal
    Swal.fire({
      title: 'Scanning ID...',
      html: 'Please wait while we extract text from the image.<br><b>This may take a few seconds.</b>',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    Tesseract.recognize(file, 'eng', {
      logger: m => {
        if (m.status === "recognizing text") {
          Swal.update({
            html: `Recognizing text... <b>${Math.round(m.progress * 100)}%</b>`
          });
        }
      }
    }).then(({
      data: {
        text
      }
    }) => {
      const lowerText = text.toLowerCase();
      const fnameMatch = lowerText.includes(fname);
      const lnameMatch = lowerText.includes(lname);

      if (fnameMatch && lnameMatch) {
        Swal.fire({
          icon: 'success',
          title: 'Match Found',
          text: '✅ Name matched successfully!',
          confirmButtonColor: '#198754'
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Name Mismatch',
          text: '❌ Name on the ID does not match the input.',
          confirmButtonColor: '#dc3545'
        });
        document.getElementById('ic_image').value = "";
      }
    }).catch(err => {
      console.error(err);
      Swal.fire({
        icon: 'error',
        title: 'OCR Error',
        text: '⚠️ Error processing the image. Please try again.',
        confirmButtonColor: '#dc3545'
      });
    });
  });
</script>