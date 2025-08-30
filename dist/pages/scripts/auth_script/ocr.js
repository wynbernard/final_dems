document.getElementById('ic_image').addEventListener('change', function() {
    const file = this.files[0];
    const idSelect = document.getElementById('icp');
    if (!file) {
        Swal.fire({
            icon: 'warning',
            title: 'No File',
            text: 'Please upload an ID image.'
        });
        this.value = "";
        return;
    }
    const reader = new FileReader();
    reader.onload = function() {
        const img = new Image();
        img.onload = function() {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            canvas.width = img.width;
            canvas.height = img.height;
            ctx.drawImage(img, 0, 0);
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const qrCode = jsQR(imageData.data, canvas.width, canvas.height);
            if (qrCode) {
                let qrData;
                try {
                    qrData = JSON.parse(qrCode.data);
                } catch (e) {
                    qrData = {};
                }
                // Fill fields from QR if available
                if (qrData.subject) {
                    const fName = (qrData.subject.fName || '').toUpperCase();
                    const mName = (qrData.subject.mName || '').toUpperCase();
                    const lName = (qrData.subject.lName || '').toUpperCase();
                    document.getElementById('f_name').value = fName;
                    document.getElementById('m_name').value = mName;
                    document.getElementById('l_name').value = lName;
                    document.getElementById('icn').value = qrData.subject.PCN || '';
                    idSelect.value = 'Philippine National ID';
                    console.log('Extracted from QR:', { fName, mName, lName });
                    Swal.fire({
                        icon: 'success',
                        title: 'ID Scanned',
                        text: 'Fields auto-filled from QR code.'
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'QR Data Error',
                        text: 'QR code found but no subject data.'
                    });
                }
            } else {
                runTesseractOCR(file, idSelect);
            }
        };
        img.src = reader.result;
    };
    reader.readAsDataURL(file);
});

function runTesseractOCR(file, idSelect) {
    Swal.fire({
        title: 'Checking ID...',
        html: 'Extracting text from the ID image.<br><b>Please wait.</b>',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    Tesseract.recognize(file, 'eng', {
        logger: m => {
            if (m.status === "recognizing text") {
                Swal.update({
                    html: `Extracting text... <b>${Math.round(m.progress * 100)}%</b>`
                });
            }
        }
    }).then(({ data: { text } }) => {
        const normalize = str => str.toLowerCase().replace(/[^\w\s\-\/]/gi, '').replace(/\s+/g, ' ').trim();
        const cleanText = normalize(text);
        console.log('Full OCR text:', cleanText);
        // Extract PhilSys ID number (####-####-####-####)
        const idNumberMatch = cleanText.match(/\b\d{4}-\d{4}-\d{4}-\d{4}\b/);
        const extractedIdNumber = idNumberMatch ? idNumberMatch[0] : "";
        document.getElementById('icn').value = extractedIdNumber;
        // Improved extraction for Philippine National ID
        let fName = '', mName = '', lName = '';
        // Use regex to find the value after the label, allowing for OCR errors and extra spaces
        const lastNameMatch = cleanText.match(/apelyido\/?last name\s*([a-z\- ]{2,})/i);
        if (lastNameMatch) {
            // Only take the first word after the label as the last name
            lName = lastNameMatch[1].split(/\s+/)[0].replace(/[^A-Za-z\-]/g, '').trim().toUpperCase();
        }
        const givenNameMatch = cleanText.match(/mga pangalan\/?given names?\s*[-: ]\s*([a-z\- ,]{2,})/i);
        if (givenNameMatch) {
            // After the dash, take the first word as first name, second as extension if in extList
            const namesRaw = givenNameMatch[1].split(/ gitnang apelyido| middle name| petsa ng kapanganakan| date of birth| tirahan| address|\s{2,}/i)[0]
                .replace(/[^A-Za-z\-\s,]/g, '').trim();
            const names = namesRaw.split(' ').filter(Boolean);
            const extList = ['JR', 'SR', 'I', 'II', 'III', 'IV', 'V'];
            let ext = '';
            if (names.length > 0) {
                fName = names[0].toUpperCase();
                if (names.length > 1 && extList.includes(names[1].toUpperCase())) {
                    ext = names[1].toUpperCase();
                    mName = '';
                } else if (names.length > 1) {
                    mName = names.slice(1).join(' ').toUpperCase();
                }
            }
            document.getElementById('name_extension').value = ext;
        }
        const middleNameMatch = cleanText.match(/gitnang apelyido\/?middle name\s*([a-z\- ]{2,})/i);
        if (middleNameMatch) {
            mName = middleNameMatch[1].split(/ petsa ng kapanganakan| date of birth| tirahan| address|\s{2,}/i)[0]
                .replace(/[^A-Za-z\-\s]/g, '').trim().toUpperCase();
        }
        // Fallback: Try previous logic if not found
        if (!fName || !lName) {
            let nameMatch = cleanText.match(/([a-z]+), ([a-z]+) ([a-z]+)/i); // LAST, FIRST MIDDLE
            if (!nameMatch) {
                nameMatch = cleanText.match(/([a-z]+) ([a-z]+) ([a-z]+)/i); // FIRST MIDDLE LAST
            }
            if (nameMatch) {
                if (cleanText.includes(",")) {
                    lName = lName || nameMatch[1].toUpperCase();
                    fName = fName || nameMatch[2].toUpperCase();
                    mName = mName || nameMatch[3].toUpperCase();
                } else {
                    fName = fName || nameMatch[1].toUpperCase();
                    mName = mName || nameMatch[2].toUpperCase();
                    lName = lName || nameMatch[3].toUpperCase();
                }
            }
        }
        document.getElementById('f_name').value = fName;
        document.getElementById('m_name').value = mName;
        document.getElementById('l_name').value = lName;
        console.log('Extracted from OCR:', { fName, mName, lName });
        // Set ID type if PhilSys detected
        if (extractedIdNumber) {
            idSelect.value = "Philippine National ID";
        }
        if ((fName && lName) && extractedIdNumber) {
            Swal.fire({
                icon: 'success',
                title: 'ID Scanned',
                text: 'National ID details extracted and fields filled!'
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Partial/No Match',
                text: 'Could not extract all details. Please check the fields and try again.'
            });
        }
    }).catch(err => {
        console.error(err);
        document.getElementById('ic_image').value = "";
        Swal.fire({
            icon: 'error',
            title: 'OCR Error',
            text: 'There was an error reading the ID image.',
            confirmButtonColor: '#dc3545'
        });
    });
}