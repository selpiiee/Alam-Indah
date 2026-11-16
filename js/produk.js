(() => {
    const cards = Array.from(document.querySelectorAll(".catalog-card"));
    const search = document.getElementById("catalogSearch");
    const branch = document.getElementById("catalogBranch");
    const category = document.getElementById("catalogCategory");
    const emptyMessage = document.getElementById("catalogEmpty");
    const modalElement = document.getElementById("catalogModal");

    function filterCatalog() {
        const query = (search?.value || "").trim().toLocaleLowerCase("id");
        const selectedBranch = branch?.value || "all";
        const selectedCategory = category?.value || "all";
        let visibleCount = 0;

        cards.forEach((card) => {
            const title = card.querySelector("strong")?.textContent || "";
            const description = card.querySelector(".catalog-card-copy > span")?.textContent || "";
            const matchesText = `${title} ${description}`.toLocaleLowerCase("id").includes(query);
            const matchesBranch = (card.dataset.branches || "").split(",").includes(selectedBranch) || selectedBranch === "all";
            const matchesCategory = selectedCategory === "all" || card.dataset.category === selectedCategory;
            const visible = matchesText && matchesBranch && matchesCategory;

            card.classList.toggle("d-none", !visible);
            if (visible) visibleCount += 1;
        });

        emptyMessage?.classList.toggle("d-none", visibleCount > 0);
    }

    search?.addEventListener("input", filterCatalog);
    branch?.addEventListener("change", filterCatalog);
    category?.addEventListener("change", filterCatalog);

    if (!modalElement) return;

    const modal = new bootstrap.Modal(modalElement);
    const modalTitle = document.getElementById("catalogModalTitle");
    const modalImage = document.getElementById("catalogModalImage");
    const modalDescription = document.getElementById("catalogModalDescription");
    const modalUse = document.getElementById("catalogModalUse");
    const modalPrice = document.getElementById("catalogModalPrice");
    const orderLink = document.getElementById("catalogOrderLink");

    function openProduct(card) {
        const title = card.querySelector("strong")?.textContent.trim() || "Produk";
        const description = card.querySelector(".catalog-card-copy > span")?.textContent.trim() || "";
        const image = card.querySelector("img");

        modalTitle.textContent = title;
        modalDescription.textContent = description;
        modalUse.textContent = card.dataset.use || "Hubungi kami untuk saran pemakaian produk ini.";
        modalPrice.textContent = card.dataset.price || "Hubungi kami untuk informasi harga.";
        if (image) {
            modalImage.src = image.currentSrc || image.src;
            modalImage.alt = image.alt || title;
        }
        orderLink.href = `mailto:alam@gmail.com?subject=${encodeURIComponent(`Pesan ${title} - Alam Indah`)}`;
        modal.show(card);
    }

    cards.forEach((card) => card.addEventListener("click", () => openProduct(card)));
})();
