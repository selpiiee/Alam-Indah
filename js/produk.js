(() => {
    const cards = [...document.querySelectorAll(".catalog-card")];
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

        const title = card.querySelector(".catalog-card-copy strong")?.textContent.trim() || "Produk";
        const cardDescription = card.querySelector(".catalog-card-copy > span")?.textContent.trim() || "";
        const cardImage = card.querySelector("img");

        modalFields.title.textContent = title;
        modalFields.description.textContent = card.dataset.modalDescription || cardDescription;
        modalFields.use.textContent = card.dataset.modalUse || card.dataset.use || "Hubungi kami untuk saran pemakaian produk ini.";
        modalFields.price.textContent = card.dataset.modalPrice || card.dataset.price || "Hubungi kami untuk informasi harga.";

        if (cardImage) {
            modalFields.image.src = card.dataset.modalImage || cardImage.currentSrc || cardImage.src;
            modalFields.image.alt = cardImage.alt || title;
        }

        modalFields.orderLink.href = `mailto:alam@gmail.com?subject=${encodeURIComponent(`Pesan ${title} - Alam Indah`)}`;
        modal.show(card);
    }

    cards.forEach((card) => card.addEventListener("click", () => showProduct(card)));
})();

