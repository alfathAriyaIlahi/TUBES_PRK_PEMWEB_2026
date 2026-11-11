document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("modal-detail");
  const btnTutup = document.getElementById("modal-tutup");

  if (!modal) return;

  function tutupModal() {
    modal.hidden = true;
    document.body.style.overflow = "";
  }

  if (btnTutup) {
    btnTutup.addEventListener("click", tutupModal);
  }

  modal.addEventListener("click", (e) => {
    if (e.target === modal) tutupModal();
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && !modal.hidden) tutupModal();
  });

  window.bukaModalDetail = async function (id) {
    try {
      const res = await fetch(`../api/obat_detail.php?id=${id}`);
      const result = await res.json();

      if (result.status === "success" && result.data) {
        const d = result.data;

        document.getElementById("modal-nama").textContent = d.nama_obat || "-";
        document.getElementById("modal-indikasi").textContent = d.indikasi || "-";
        document.getElementById("modal-aturan").textContent = d.aturan_pakai || "-";
        document.getElementById("modal-peringatan").textContent = d.peringatan || "-";

        const elStok = document.getElementById("modal-stok");
        if (elStok) {
          const stok = parseInt(d.stok || 0);
          if (stok > 5) {
            elStok.className = "stok st-ok";
            elStok.textContent = `Tersedia (${stok})`;
          } else if (stok > 0) {
            elStok.className = "stok st-low";
            elStok.textContent = `Menipis (${stok})`;
          } else {
            elStok.className = "stok st-out";
            elStok.textContent = "Habis (0)";
          }
        }

        const elTags = document.getElementById("modal-tags");
        if (elTags) {
          elTags.innerHTML = `
            ${d.kategori ? `<span class="tag">${d.kategori}</span>` : ""}
            ${d.bentuk ? `<span class="tag tag-sub">${d.bentuk}</span>` : ""}
          `;
        }

        modal.hidden = false;
        document.body.style.overflow = "hidden";
      }
    } catch (err) {
      console.error("Gagal memuat detail obat:", err);
    }
  };

  document.addEventListener("click", (e) => {
    const btn = e.target.closest("[data-detail-obat]");
    if (btn) {
      const id = btn.getAttribute("data-id");
      if (id) window.bukaModalDetail(id);
    }
  });
});