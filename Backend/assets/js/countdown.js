// ==========================================
// LOGIKA HITUNG MUNDUR (COUNTDOWN SISA WAKTU)
// ==========================================
function updateCountdowns() {
    const countdowns = document.querySelectorAll('.countdown');
    const now = new Date().getTime();

    countdowns.forEach(el => {
        const targetStr = el.getAttribute('data-target');
        if (!targetStr) return;
        
        const target = new Date(targetStr).getTime();
        const distance = target - now;

        if (distance < 0) {
            const absDist = Math.abs(distance);
            const hours = Math.floor(absDist / (1000 * 60 * 60));
            const minutes = Math.floor((absDist % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((absDist % (1000 * 60)) / 1000);
            el.innerHTML = `Lewat ${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            el.style.color = '#c62828'; // Merah
        } else {
            const hours = Math.floor(distance / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            el.innerHTML = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            el.style.color = '#3A32A5'; // Ungu
        }
    });
}

// Jalankan setiap 1 detik
setInterval(updateCountdowns, 1000);
document.addEventListener("DOMContentLoaded", updateCountdowns);