/* =====================================================
   kontak.js - form kontak (hanya dipakai di kontak.html)
===================================================== */

(function () {
    "use strict";

    const form = document.getElementById("contactForm");
    const alertBox = document.getElementById("formAlert");

    if (!form || !alertBox) return;

    function showAlert(message, type) {
        alertBox.textContent = message;
        alertBox.className = "contact-alert is-" + type;
        alertBox.hidden = false;
    }

    form.addEventListener("submit", (event) => {
        event.preventDefault();

        form.classList.add("was-validated");

        // Ada kolom wajib (*) yang kosong / format salah
        if (!form.checkValidity()) {
            const firstInvalid = form.querySelector(":invalid");
            if (firstInvalid) firstInvalid.focus();

            showAlert("Lengkapi dulu kolom yang bertanda *.", "error");
            return;
        }

        // TODO: kirim data ke backend di sini (fetch ke API / form service).
        // Untuk sekarang hanya menampilkan pesan berhasil.
        showAlert("Pesan berhasil dikirim. Tim Alam Indah akan segera menghubungi Anda.", "success");

        form.reset();
        form.classList.remove("was-validated");
    });
})();