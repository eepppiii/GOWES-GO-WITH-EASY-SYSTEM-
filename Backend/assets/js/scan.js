// ==========================================
// LOGIKA UNTUK SCAN QR CODE (KAMERA)
// ==========================================
let html5QrcodeScanner = null;

async function startCamera() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        stream.getTracks().forEach(track => track.stop());
    } catch (err) {
        if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
            alert("Akses kamera diblokir.\n\nSilakan klik ikon Gembok (🔒) di browser, lalu ubah 'Kamera' menjadi 'Izinkan'.");
        } else {
            alert("Kamera tidak dapat diakses: " + err.message);
        }
        return; 
    }

    document.getElementById('scanner-placeholder').style.display = 'none';
    document.getElementById('reader').style.display = 'block';
    document.getElementById('hint-text').innerText = "Memuat kamera...";
    document.getElementById('btn-close-camera').style.display = 'flex';

    if (typeof Html5Qrcode === 'undefined') {
        alert("Gagal memuat library kamera. Periksa koneksi internet.");
        stopCamera();
        return;
    }

    html5QrcodeScanner = new Html5Qrcode("reader");
    const config = { fps: 10, qrbox: { width: 250, height: 250 } };

    try {
        await html5QrcodeScanner.start({ facingMode: "environment" }, config, onScanSuccess);
        document.getElementById('hint-text').innerText = "Posisikan QR Code di dalam kotak.";
    } catch (err) {
        try {
            await html5QrcodeScanner.start({ facingMode: "user" }, config, onScanSuccess);
            document.getElementById('hint-text').innerText = "Posisikan QR Code di dalam kotak.";
        } catch (err2) {
            alert("Kamera tidak dapat dibuka.");
            stopCamera();
        }
    }
}

function onScanSuccess(decodedText, decodedResult) {
    if (html5QrcodeScanner) {
        html5QrcodeScanner.stop().then(() => {
            window.location.href = "hasil_scan.php?qr=" + encodeURIComponent(decodedText);
        }).catch(err => {
            window.location.href = "hasil_scan.php?qr=" + encodeURIComponent(decodedText);
        });
    }
}

function stopCamera() {
    if (html5QrcodeScanner) {
        html5QrcodeScanner.stop().then(() => {
            html5QrcodeScanner.clear();
            resetUI();
        }).catch(err => {
            resetUI();
        });
    } else {
        resetUI();
    }
}

function resetUI() {
    document.getElementById('scanner-placeholder').style.display = 'flex';
    document.getElementById('reader').style.display = 'none';
    document.getElementById('btn-close-camera').style.display = 'none';
    document.getElementById('hint-text').innerText = "Klik kotak di atas untuk membuka kamera.";
}