// ==========================================
// LOGIKA UNTUK MEMUNCULKAN KALENDER (DATE PICKER)
// ==========================================
function openDatePicker() {
    const dateInput = document.getElementById('hiddenDateInput');
    if (!dateInput) return;
    
    try {
        dateInput.showPicker(); 
    } catch (e) {
        dateInput.focus(); 
        dateInput.click();
    }
}