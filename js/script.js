/* =====================================================
   ALAM INDAH - script.js

   1. Slider testimoni
   2. Newsletter (footer)
   3. FAQ dropdown

   Peta lokasi tidak butuh JS lagi: titik cabang sudah
   berupa link <a> ke Google Maps, kartu alamatnya
   muncul lewat CSS (hover).
===================================================== */

(function () {
    // TESTIMONIAL CAROUSEL

    const testimonialTrack = document.querySelector(".testimonial-track");
    const testimonialInner = document.querySelector(".testimonial-track-inner");
    const testimonialPrev = document.getElementById("testimonialPrev");
    const testimonialNext = document.getElementById("testimonialNext");

    if (testimonialTrack && testimonialInner && testimonialPrev && testimonialNext) {

        const slideDistance = () => {
            const group = testimonialInner.querySelector(".testimonial-group");
            return group ? group.getBoundingClientRect().width + 16 : 0;
        };

        function moveTestimonial(direction) {
            // Hentikan animasi sementara
            testimonialInner.style.animationPlayState = "paused";

            // Geser satu kartu
            const card = testimonialInner.querySelector(".testimonial-card");
            if (!card) return;

            const distance = card.getBoundingClientRect().width + 16;

            testimonialTrack.scrollBy({
                left: direction * distance,
                behavior: "smooth"
            });

            // Jalankan kembali animasi setelah interaksi
            clearTimeout(window.testimonialResumeTimer);

            window.testimonialResumeTimer = setTimeout(() => {
                testimonialInner.style.animationPlayState = "running";
            }, 3000);
        }

        testimonialPrev.addEventListener("click", () => {
            moveTestimonial(-1);
        });

        testimonialNext.addEventListener("click", () => {
            moveTestimonial(1);
        });
    }


    /* =========================
       2. NEWSLETTER
    ========================= */

    const newsletterForm = document.getElementById("newsletterForm");
    const newsletterMessage = document.getElementById("newsletterMessage");

    if (newsletterForm && newsletterMessage) {
        newsletterForm.addEventListener("submit", (event) => {
            event.preventDefault();

            newsletterMessage.textContent =
                "Pendaftaran newsletter belum tersedia. Silakan hubungi tim Alam Indah.";
        });
    }


    /* =========================
       3. FAQ DROPDOWN
       - Klik pertanyaan -> jawaban terbuka, item lain menutup
       - Klik lagi -> menutup
       - Class "active" pada .faq-item yang mengatur
         warna angka (abu -> biru) dan arah panah di CSS
    ========================= */

    const faqItems = document.querySelectorAll(".faq-item");

    function openFaq(item) {
        const question = item.querySelector(".faq-question");
        const answer = item.querySelector(".faq-answer");

        item.classList.add("active");
        question.setAttribute("aria-expanded", "true");
        answer.style.maxHeight = answer.scrollHeight + "px";
    }

    function closeFaq(item) {
        const question = item.querySelector(".faq-question");
        const answer = item.querySelector(".faq-answer");

        item.classList.remove("active");
        question.setAttribute("aria-expanded", "false");
        answer.style.maxHeight = "0px";
    }

    faqItems.forEach((item) => {
        const question = item.querySelector(".faq-question");
        const answer = item.querySelector(".faq-answer");

        if (!question || !answer) return;

        // Kondisi awal
        if (item.classList.contains("active")) {
            openFaq(item);
        } else {
            closeFaq(item);
        }

        question.addEventListener("click", () => {
            const wasActive = item.classList.contains("active");

            // Tutup semua dulu
            faqItems.forEach(closeFaq);

            // Buka yang diklik kalau sebelumnya tertutup
            if (!wasActive) {
                openFaq(item);
            }
        });
    });

    // Sesuaikan tinggi jawaban yang terbuka kalau ukuran layar berubah
    window.addEventListener("resize", () => {
        document.querySelectorAll(".faq-item.active .faq-answer").forEach((answer) => {
            answer.style.maxHeight = answer.scrollHeight + "px";
        });
    });

})();