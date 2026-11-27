/* =====================================================
   kontak.js - form kontak -> WhatsApp (hanya dipakai di kontak.html)

   Saat tombol "Kirim Pesan" diklik, isi form disusun menjadi satu pesan
   dan WhatsApp dibuka dengan pesan yang sudah terisi. Pengunjung tinggal
   menekan tombol kirim di WhatsApp.
===================================================== */

(function () {
    "use strict";

    /* ---------- pengaturan ---------- */

    // Nomor WhatsApp tujuan. Format internasional: tanpa "+" dan tanpa 0 di depan.
    // Contoh: 081234567890 -> "6281234567890"
    const WA_NUMBER = "6281358734165";

    /* ---------- elemen ---------- */

    const form = document.getElementById("contactForm");
    const alertBox = document.getElementById("formAlert");

    if (!form || !alertBox) return;

    /* ---------- fungsi bantu ---------- */

    function showAlert(message, type) {
        alertBox.textContent = message;
        alertBox.className = "contact-alert is-" + type;
        alertBox.hidden = false;
    }

    function getValue(id) {
        return document.getElementById(id).value.trim();
    }

    // Susun isi form menjadi teks pesan WhatsApp
    function buildMessage() {
        const service = getValue("service");

        return [
            "Halo Alam Indah, saya ingin konsultasi.",
            "",
            "Nama: " + getValue("firstName") + " " + getValue("lastName"),
            "Email: " + getValue("email"),
            "Telepon: " + getValue("phone"),
            service ? "Layanan: " + service : null,
            "",
            "Pesan:",
            getValue("message")
        ].filter((line) => line !== null).join("\n");
    }

    /* ---------- kirim ---------- */

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

        const url = "https://wa.me/" + WA_NUMBER + "?text=" + encodeURIComponent(buildMessage());

        // Buka WhatsApp di tab baru. Kalau diblokir browser, pindah di tab yang sama.
        const waWindow = window.open(url, "_blank");

        if (waWindow) {
            waWindow.opener = null;
        } else {
            window.location.href = url;
        }

        showAlert("Mengarahkan ke WhatsApp. Tekan tombol kirim di WhatsApp untuk mengirim pesan Anda.", "success");
    });
})();