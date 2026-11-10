(() => {
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const BASE = document.body.dataset.base || "";
  const esc = (s) =>
    String(s ?? "").replace(
      /[&<>"']/g,
      (c) =>
        ({
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          '"': "&quot;",
          "'": "&#39;",
        })[c],
    );
  const fmt = (n) => {
    n = +n || 0;
    return n.toLocaleString("id-ID", { maximumFractionDigits: 1 });
  };
  const ph = (n, fill) => `<i class="${fill ? "ph-fill" : "ph"} ph-${n}"></i>`;

  /* Sidebar, Popovers, Shortcuts */
  $("#burger")?.addEventListener("click", () =>
    $("#sidebar").classList.toggle("open"),
  );
  document.addEventListener("click", (e) => {
    const t = e.target.closest("[data-menu]");
    $$(".menu-pop").forEach((m) => {
      if (t && m.id === t.dataset.menu) m.hidden = !m.hidden;
      else if (!m.contains(e.target)) m.hidden = true;
    });
    if (!e.target.closest(".sidebar") && !e.target.closest("#burger"))
      $("#sidebar")?.classList.remove("open");
    if (!e.target.closest(".dp"))
      $$(".dp-pop").forEach((p) => (p.hidden = true));
  });
  document.addEventListener("keydown", (e) => {
    if ((e.metaKey || e.ctrlKey) && e.shiftKey && e.key.toLowerCase() === "f") {
      e.preventDefault();
      $("#gsearch")?.focus();
    }
    if (e.key === "Escape") $$(".overlay:not([hidden])").forEach(closeOverlay);
  });
  $$("[data-auto-submit]").forEach((el) =>
    el.addEventListener("change", () => el.form.submit()),
  );
  $$("tr[data-href]").forEach((tr) =>
    tr.addEventListener("click", (e) => {
      if (!e.target.closest("a,button,input,select"))
        location.href = tr.dataset.href;
    }),
  );
  $$("[data-toggle-pw]").forEach((b) =>
    b.addEventListener("click", () => {
      const i = $(b.dataset.togglePw);
      const show = i.type === "password";
      i.type = show ? "text" : "password";
      b.innerHTML = ph(show ? "eye-slash" : "eye");
    }),
  );

  $$("[data-dismiss]").forEach((b) =>
    b.addEventListener("click", () => b.closest(".alert").remove()),
  );

  /* Toast */
  $$(".toast").forEach((t) => {
    t.querySelector("button")?.addEventListener("click", () => t.remove());
    setTimeout(() => t.remove(), 6000);
  });

  /* Modals */
  function closeOverlay(o) {
    o.hidden = true;
    if (o.dataset.dynamic) o.remove();
  }
  window.openModal = (id) => {
    const o = document.getElementById(id);
    if (o) {
      o.hidden = false;
      (
        o.querySelector("input:not([type=hidden]),textarea,select") ||
        o.querySelector("button")
      )?.focus();
    }
  };
  document.addEventListener("click", (e) => {
    const op = e.target.closest("[data-open]");
    if (op) {
      e.preventDefault();
      openModal(op.dataset.open);
    }
    if (e.target.closest("[data-close]"))
      closeOverlay(e.target.closest(".overlay"));
    else if (e.target.classList.contains("overlay")) closeOverlay(e.target);
  });
  // Langsung ngebuka modal yang punya atribut data-autoopen (conoth kaya: modal error setelah validasi gagal)
  $$(".overlay[data-autoopen]").forEach((o) => (o.hidden = false));

  // checker form konfirmasi sebelum disubmit
  document.addEventListener("submit", (e) => {
    const f = e.target;
    if (!f.matches("form[data-confirm]") || f.dataset.confirmed) return;
    e.preventDefault();
    const tone = f.dataset.tone || "red";
    const o = document.createElement("div");
    o.className = "overlay";
    o.dataset.dynamic = 1;
    o.innerHTML = `<div class="modal" role="dialog" aria-modal="true"><div class="m-ico ${tone === "red" ? "m-red" : "m-blue"}">${ph(tone === "red" ? "trash" : "info", true)}</div>
      <h3>${esc(f.dataset.title)}</h3><p class="d">${esc(f.dataset.text)}</p>
      <div class="acts"><button type="button" class="btn btn-secondary" data-close>${esc(f.dataset.cancel || "Batal")}</button>
      <button type="button" class="btn ${tone === "red" ? "btn-danger" : "btn-primary"}" data-yes>${esc(f.dataset.ok || "Lanjutkan")}</button></div></div>`;
    document.body.appendChild(o);
    o.querySelector("[data-yes]").focus();
    o.querySelector("[data-yes]").addEventListener("click", () => {
      f.dataset.confirmed = 1;
      if (f._submitter) {
        const h = document.createElement("input");
        h.type = "hidden";
        h.name = f._submitter.name;
        h.value = f._submitter.value;
        f.appendChild(h);
      }
      HTMLFormElement.prototype.submit.call(f);
    });
  });
  document.addEventListener("click", (e) => {
    const b = e.target.closest("button[type=submit][name]");
    if (b?.form) b.form._submitter = b;
  });

  /* Drawer buat riwayat stok*/
  $$("tr[data-drawer]").forEach((tr) =>
    tr.addEventListener("click", async (e) => {
      if (e.target.closest("a,button")) return;
      $$("tr.sel").forEach((r) => r.classList.remove("sel"));
      tr.classList.add("sel");
      const res = await fetch(
        `${BASE}/history.php?drawer=${tr.dataset.drawer}`,
        { credentials: "same-origin" },
      );
      const o = document.createElement("div");
      o.className = "overlay right";
      o.dataset.dynamic = 1;
      o.innerHTML = await res.text();
      document.body.appendChild(o);
      const close = () => {
        tr.classList.remove("sel");
        closeOverlay(o);
      };
      o.addEventListener("click", (ev) => {
        if (ev.target === o || ev.target.closest("[data-close]")) close();
      });
    }),
  );

  /* Role → permission preview (Pengguna form) */
  const sel = $("[data-perm-preview]");
  if (sel) {
    const MAP = JSON.parse($("#perm-map").textContent);
    const render = () => {
      const p = MAP[sel.value];
      const box = $("#perm-list");
      $("#perm-title").textContent = p
        ? `Izin Peran ${p.label}`
        : "Izin Peran (pilih peran)";
      box.innerHTML = Object.entries(
        p
          ? p.perms
          : {
              "Data Barang": "",
              "Barang Masuk": "",
              "Barang Keluar": "",
              Laporan: "",
              Pengguna: "",
            },
      )
        .map(([k, v]) => {
          const b =
            v === "full"
              ? `<span class="badge b-ok">Penuh ${ph("check-circle", 1)}</span>`
              : v === "edit"
                ? `<span class="badge b-info">Tambah & Ubah ${ph("pencil-simple", 1)}</span>`
                : v === "view"
                  ? `<span class="badge b-neu">Lihat ${ph("eye", 1)}</span>`
                  : '<span class="muted">—</span>';
          return `<div class="role-row"><span>${esc(k)}</span>${b}</div>`;
        })
        .join("");
    };
    sel.addEventListener("change", render);
    render();
  }

  /* Barang Masuk / Keluar */
  const wz = $("[data-wizard]");
  if (wz) {
    const mode = wz.dataset.wizard; // in / out
    const ITEMS = JSON.parse($("#items-data").textContent);
    const byId = Object.fromEntries(ITEMS.map((i) => [i.id, i]));
    const body = $("#rows");
    const tpl = $("#row-tpl");
    const recalc = () => {
      let n = 0,
        total = 0,
        worst = 0;
      $$("tr.irow", body).forEach((tr) => {
        const it = byId[tr.querySelector('[name="item_id[]"]').value];
        const q =
          parseFloat(
            tr.querySelector('[name="qty[]"]').value.replace(",", "."),
          ) || 0;
        const after = tr.querySelector(".after");
        const cur = tr.querySelector(".cur");
        const box = tr.querySelector(".row-input");
        const hint = tr.nextElementSibling?.classList.contains("row-hint")
          ? tr.nextElementSibling
          : null;
        if (!it) {
          cur.textContent = "—";
          after.textContent = "—";
          return;
        }
        n++;
        total += q;
        const res = mode === "in" ? it.stock + q : it.stock - q;
        cur.textContent = fmt(it.stock) + " Ton";
        after.textContent = fmt(res) + " Ton";
        const bad =
          (mode === "out" && q > it.stock) || (q <= 0 && tr.dataset.touched);
        box.classList.toggle("bad", !!bad);
        after.classList.toggle("neg", mode === "out" && res < 0);
        if (hint) hint.remove();
        if (mode === "out" && q > it.stock)
          tr.insertAdjacentHTML(
            "afterend",
            `<tr class="row-hint"><td colspan="5" style="height:auto;padding:0 16px 8px"><span class="err-msg" style="color:var(--err-fg)">Maksimal ${fmt(it.stock)} Ton (stok tersedia)</span></td></tr>`,
          );
      });
      $("#sum-count").textContent = n + " barang";
      $("#sum-total").textContent = fmt(total) + " Ton";
    };
    const bindRow = (tr) => {
      const s = tr.querySelector("select.pick");
      s?.addEventListener("change", () => {
        const it = byId[s.value];
        if (!it) return;
        tr.querySelector('[name="item_id[]"]').value = it.id;
        tr.querySelector(".disp").innerHTML =
          `<div class="cell-prod"><span class="thumb">${ph("cube", 1)}</span><span><b>${esc(it.name)}</b><small>${esc(it.sku)}</small></span></div>`;
        recalc();
      });
      tr.querySelector('[name="qty[]"]').addEventListener("input", () => {
        tr.dataset.touched = 1;
        recalc();
      });
      tr.querySelector(".del")?.addEventListener("click", () => {
        tr.nextElementSibling?.classList.contains("row-hint") &&
          tr.nextElementSibling.remove();
        tr.remove();
        recalc();
      });
    };
    $$("tr.irow", body).forEach(bindRow);
    $("#add-row").addEventListener("click", () => {
      const f = tpl.content.cloneNode(true);
      body.appendChild(f);
      const tr = body.lastElementChild;
      bindRow(tr);
      recalc();
    });
    // mirror supplier / destination / date ke card summary
    const mirror = () =>
      $$("[data-mirror]", wz).forEach((m) => {
        const f = wz.elements[m.dataset.mirror];
        if (!f) return;
        let t = f.value;
        if (f.tagName === "SELECT")
          t = f.selectedIndex > 0 ? f.options[f.selectedIndex].text : "";
        if (f.type === "date" && t)
          t = new Date(t + "T00:00:00").toLocaleDateString("id-ID", {
            day: "numeric",
            month: "short",
            year: "numeric",
          });
        m.textContent = t || "—";
      });
    wz.addEventListener("input", mirror);
    wz.addEventListener("change", mirror);
    mirror();
    // kalau field nya udah diubah dan ke disabled, nanti bakal ke enable lagi pas ada input / change
    const rv = $("button[value=review]", wz);
    const reenable = () => {
      if (rv?.disabled) {
        rv.disabled = false;
        rv.classList.remove("disabled");
      }
    };
    wz.addEventListener("input", reenable);
    wz.addEventListener("change", reenable);
    recalc();
  }

  /* Date picker */
  $$(".dp").forEach((dp) => {
    const ref = new Date(dp.dataset.ref + "T00:00:00");
    let from = new Date(dp.dataset.from + "T00:00:00"),
      to = new Date(dp.dataset.to + "T00:00:00");
    let view = new Date(to.getFullYear(), to.getMonth(), 1),
      preset = "p7";
    const pop = $(".dp-pop", dp);
    const iso = (d) =>
      `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
    const MONTHS = [
      "Januari",
      "Februari",
      "Maret",
      "April",
      "Mei",
      "Juni",
      "Juli",
      "Agustus",
      "September",
      "Oktober",
      "November",
      "Desember",
    ];
    const add = (d, n) =>
      new Date(d.getFullYear(), d.getMonth(), d.getDate() + n);
    const presets = {
      p1: ["Hari Ini", () => [ref, ref]],
      p7: ["7 Hari Terakhir", () => [add(ref, -6), ref]],
      p30: ["30 Hari Terakhir", () => [add(ref, -29), ref]],
      pm: [
        "Bulan Ini",
        () => [new Date(ref.getFullYear(), ref.getMonth(), 1), ref],
      ],
      pl: [
        "Bulan Lalu",
        () => [
          new Date(ref.getFullYear(), ref.getMonth() - 1, 1),
          new Date(ref.getFullYear(), ref.getMonth(), 0),
        ],
      ],
      pc: ["Rentang Kustom", null],
    };
    const draw = () => {
      const first = new Date(view.getFullYear(), view.getMonth(), 1),
        days = new Date(view.getFullYear(), view.getMonth() + 1, 0).getDate(),
        lead = (first.getDay() + 6) % 7;
      let cells =
        ["Sen", "Sel", "Rab", "Kam", "Jum", "Sab", "Min"]
          .map((d) => `<span class="h">${d}</span>`)
          .join("") + "<span></span>".repeat(lead);
      for (let d = 1; d <= days; d++) {
        const cur = new Date(view.getFullYear(), view.getMonth(), d);
        const edge = +cur === +from || (to && +cur === +to);
        const inr = to && cur > from && cur < to;
        cells += `<button type="button" data-d="${iso(cur)}" class="${edge ? "edge" : inr ? "in" : ""}">${d}</button>`;
      }
      const n = to ? Math.round((to - from) / 864e5) + 1 : 1;
      pop.innerHTML = `<div class="dp-side">${Object.entries(presets)
        .map(
          ([k, v]) =>
            `<button type="button" data-p="${k}" class="${k === preset ? "on" : ""}">${v[0]}</button>`,
        )
        .join("")}</div>
        <div class="dp-cal"><div class="dp-nav"><button type="button" data-nav="-1" aria-label="Bulan sebelumnya">${ph("caret-left")}</button><span>${MONTHS[view.getMonth()]} ${view.getFullYear()}</span><button type="button" data-nav="1" aria-label="Bulan berikutnya">${ph("caret-right")}</button></div>
        <div class="dp-g">${cells}</div><div class="dp-foot"><span>${n} hari dipilih</span><span style="display:flex;gap:8px"><button type="button" class="btn btn-secondary btn-sm" data-cancel>Batal</button><button type="button" class="btn btn-primary btn-sm" data-apply>Terapkan</button></span></div></div>`;
    };
    $("[data-dp-toggle]", dp).addEventListener("click", (e) => {
      e.stopPropagation();
      pop.hidden = !pop.hidden;
      if (!pop.hidden) draw();
    });
    pop.addEventListener("click", (e) => {
      e.stopPropagation();
      const t = e.target.closest("button");
      if (!t) return;
      if (t.dataset.nav) {
        view = new Date(
          view.getFullYear(),
          view.getMonth() + +t.dataset.nav,
          1,
        );
        draw();
      } else if (t.dataset.p) {
        preset = t.dataset.p;
        if (presets[preset][1]) {
          [from, to] = presets[preset][1]();
          view = new Date(to.getFullYear(), to.getMonth(), 1);
        }
        draw();
      } else if (t.dataset.d) {
        const d = new Date(t.dataset.d + "T00:00:00");
        preset = "pc";
        if (!to || +from !== +to || d < from) {
          from = d;
          to = d;
        } else to = d;
        draw();
      } else if ("cancel" in t.dataset) pop.hidden = true;
      else if ("apply" in t.dataset) {
        const u = new URL(location.href);
        u.searchParams.set("from", iso(from));
        u.searchParams.set("to", iso(to));
        u.searchParams.delete("page");
        location.href = u;
      }
    });
  });
})();
