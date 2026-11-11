document.addEventListener("DOMContentLoaded", function () {

    const menuToggle = document.getElementById("menuToggle");
    const sidebar = document.getElementById("sidebar");

    const logoutBtn = document.getElementById("logoutBtn");
    const logoutModal = document.getElementById("logoutModal");
    const logoutCancel = document.getElementById("logoutCancel");

    // ---------- Sidebar (HP) ----------
    if (menuToggle && sidebar) {
        menuToggle.addEventListener("click", function () {
            sidebar.classList.toggle("open");
        });

        sidebar.querySelectorAll(".menu-item").forEach(function (item) {
            item.addEventListener("click", function () {
                sidebar.classList.remove("open");
            });
        });
    }

    // ---------- Modal logout ----------
    function bukaModal() {
        logoutModal.hidden = false;
        document.body.classList.add("modal-open");
    }

    function tutupModal() {
        logoutModal.hidden = true;
        document.body.classList.remove("modal-open");
    }

    if (logoutBtn && logoutModal) {
        logoutBtn.addEventListener("click", function (event) {
            event.preventDefault();
            bukaModal();
        });

        if (logoutCancel) {
            logoutCancel.addEventListener("click", tutupModal);
        }

        logoutModal.addEventListener("click", function (event) {
            if (event.target === logoutModal) tutupModal();
        });
    }

    // ---------- Tombol Escape ----------
    document.addEventListener("keydown", function (event) {
        if (event.key !== "Escape") return;

        if (logoutModal && !logoutModal.hidden) {
            tutupModal();
        } else if (sidebar) {
            sidebar.classList.remove("open");
        }
    });
});