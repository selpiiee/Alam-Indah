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
    // GLASS NAVBAR: stays visible and strengthens its tint after scrolling.
    const glassNavbars = document.querySelectorAll(".hero-navbar, .catalog-navbar");
    const syncGlassNavbars = () => {
        glassNavbars.forEach((navbar) => {
            const hero = navbar.closest(".hero, .catalog-hero")
                || document.querySelector(".hero, .catalog-hero");
            const hasPassedHero = hero
                ? hero.getBoundingClientRect().bottom <= navbar.getBoundingClientRect().bottom
                : window.scrollY > 24;

            navbar.classList.toggle("is-scrolled", window.scrollY > 24 && hasPassedHero);
        });
    };

    if (glassNavbars.length) {
        syncGlassNavbars();
        window.addEventListener("scroll", syncGlassNavbars, { passive: true });
    }

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
(() => {
    const cards = [...document.querySelectorAll(".catalog-card, .product-item")];
    const searchInput = document.getElementById("catalogSearch");
    const branchSelect = document.getElementById("catalogBranch");
    const categorySelect = document.getElementById("catalogCategory");
    const emptyMessage = document.getElementById("catalogEmpty");
    const modalElement = document.getElementById("catalogModal");

    const modalFields = {
        title: document.getElementById("catalogModalTitle"),
        image: document.getElementById("catalogModalImage"),
        description: document.getElementById("catalogModalDescription"),
        use: document.getElementById("catalogModalUse"),
        price: document.getElementById("catalogModalPrice"),
        orderLink: document.getElementById("catalogOrderLink"),
    };

    const modal = modalElement && window.bootstrap?.Modal
        ? window.bootstrap.Modal.getOrCreateInstance(modalElement)
        : null;

    function getFilteredCards() {
        const query = (searchInput?.value || "").trim().toLocaleLowerCase("id");
        const branch = branchSelect?.value || "all";
        const category = categorySelect?.value || "all";

        return cards.filter((card) => {
            const searchableText = card.textContent.toLocaleLowerCase("id");
            const availableBranches = (card.dataset.branches || "").split(",");

            const matchesSearch = searchableText.includes(query);
            const matchesBranch =
                branch === "all" || availableBranches.includes(branch);
            const matchesCategory =
                category === "all" || card.dataset.category === category;

            return matchesSearch && matchesBranch && matchesCategory;
        });
    }

    function renderPagination(pageCount) {
    if (!pagination || !paginationList) return;

    paginationList.replaceChildren();
    pagination.classList.toggle("d-none", pageCount <= 1);

    if (pageCount <= 1) return;

    function addButton(label, page, options = {}) {
        const item = document.createElement("li");
        item.className = "page-item";

        if (options.active) item.classList.add("active");
        if (options.disabled) item.classList.add("disabled");

        const button = document.createElement("button");
        button.type = "button";
        button.className = "page-link";
        button.textContent = label;
        button.disabled = Boolean(options.disabled);

        if (options.active) {
            button.setAttribute("aria-current", "page");
        }

        button.addEventListener("click", () => {
            currentPage = page;
            renderCatalog();

            document.getElementById("produk-list")?.scrollIntoView({
                behavior: "smooth",
                block: "start",
            });
        });

        item.append(button);
        paginationList.append(item);
    }

    function addEllipsis() {
        const item = document.createElement("li");
        item.className = "page-item pagination-ellipsis";
        item.innerHTML = '<span class="page-link">...</span>';
        paginationList.append(item);
    }

    addButton("‹ Prev", Math.max(1, currentPage - 1), {
        disabled: currentPage === 1,
    });

    let pages;

    if (pageCount <= 5) {
        pages = Array.from({ length: pageCount }, (_, index) => index + 1);
    } else if (currentPage <= 3) {
        pages = [1, 2, 3, "...", pageCount];
    } else if (currentPage >= pageCount - 2) {
        pages = [1, "...", pageCount - 2, pageCount - 1, pageCount];
    } else {
        pages = [1, "...", currentPage - 1, currentPage, currentPage + 1, "...", pageCount];
    }

    pages.forEach((page) => {
        if (page === "...") {
            addEllipsis();
        } else {
            addButton(String(page), page, {
                active: page === currentPage,
            });
        }
    });

    addButton("Next ›", Math.min(pageCount, currentPage + 1), {
        disabled: currentPage === pageCount,
    });
}

    function renderCatalog() {
        const filteredCards = getFilteredCards();
        const pageCount = Math.max(1, Math.ceil(filteredCards.length / pageSize));

        currentPage = Math.min(currentPage, pageCount);

        const startIndex = (currentPage - 1) * pageSize;
        const visibleCards = new Set(
            filteredCards.slice(startIndex, startIndex + pageSize)
        );

        cards.forEach((card) => {
            card.classList.toggle("d-none", !visibleCards.has(card));
        });

        emptyMessage?.classList.toggle("d-none", filteredCards.length > 0);
        renderPagination(pageCount);
    }

    const pagination = document.getElementById("catalogPagination");
    const paginationList = pagination?.querySelector(".pagination");
    const pageSize = 9;
    let currentPage = 1;

    searchInput?.addEventListener("input", () => {
        currentPage = 1;
        renderCatalog();
    });

    branchSelect?.addEventListener("change", () => {
        currentPage = 1;
        renderCatalog();
    });

    categorySelect?.addEventListener("change", () => {
        currentPage = 1;
        renderCatalog();
    });

    renderCatalog();

    function showProduct(card) {
        if (!modal) return;

        const title =
            card.dataset.name ||
            card.querySelector(".catalog-card-copy strong, .product-info h3")?.textContent.trim() ||
            "Produk";

        const cardDescription =
            card.dataset.description ||
            card.querySelector(".catalog-card-copy > span, .product-info p")?.textContent.trim() ||
            "";
        const cardImage = card.querySelector("img");

        modalFields.title.textContent = title;
        modalFields.description.textContent = card.dataset.modalDescription || cardDescription;
        modalFields.use.textContent = card.dataset.modalUse || card.dataset.use || "Hubungi kami untuk saran pemakaian produk ini.";
        modalFields.price.textContent = card.dataset.modalPrice || card.dataset.price || "Hubungi kami untuk informasi harga.";

        if (cardImage) {
            modalFields.image.src = card.dataset.modalImage || card.dataset.image || cardImage.currentSrc || cardImage.src;
            modalFields.image.alt = cardImage.alt || title;
        }

        modalFields.orderLink.href = `mailto:alam@gmail.com?subject=${encodeURIComponent(`Pesan ${title} - Alam Indah`)}`;
        modal.show(card);
    }

    cards.forEach((card) => card.addEventListener("click", () => showProduct(card)));
})();
