// resources/js/notifications.js

// 1. Ekspos ke global window agar bisa dipanggil dari HTML onclick
window.openNotifModal = function (type) {
    console.log("Opening modal for:", type);

    const modal = document.getElementById("notifModal");
    const title = document.getElementById("modalTitle");
    const body = document.getElementById("modalBody");
    const seeAll = document.getElementById("modalSeeAll");

    if (!modal || !body) {
        console.error("Modal elements not found!");
        return;
    }

    modal.style.display = "flex";
    body.innerHTML = '<div class="loading-spinner">Memuat data...</div>';

    if (type === "penarikan") {
        title.textContent = "💸 Pengajuan Penarikan Baru";
        seeAll.href = "/admin/bank-sampah/penarikan";

        fetch("/admin/notifications/recent/penarikan")
            .then((res) => res.json())
            .then((data) => {
                if (!data || data.length === 0) {
                    body.innerHTML =
                        '<p style="text-align: center; color: #888; padding: 20px;">Tidak ada data baru</p>';
                } else {
                    body.innerHTML = data
                        .map(
                            (item) => `
                        <div class="notif-item">
                            <div class="notif-info">
                                <strong>${item.nama}</strong>
                                <small>Rp ${new Intl.NumberFormat("id-ID").format(item.jumlah)}</small>
                            </div>
                            <span class="notif-time">${item.waktu}</span>
                        </div>
                    `,
                        )
                        .join("");
                }
            })
            .catch((err) => {
                console.error("Error:", err);
                body.innerHTML =
                    '<p style="text-align: center; color: red;">Gagal memuat data</p>';
            });
    } else {
        title.textContent = " Laporan Sampah Ilegal";
        seeAll.href = "/admin/laporan";
        body.innerHTML =
            '<p style="text-align: center; color: #888; padding: 20px;">Fitur dalam pengembangan</p>';
    }
};

window.closeNotifModal = function () {
    const modal = document.getElementById("notifModal");
    if (modal) modal.style.display = "none";
};

window.updateNotifBadges = function () {
    fetch("/admin/notifications/counts")
        .then((res) => res.json())
        .then((data) => {
            const badgeP = document.getElementById("badge-penarikan");
            const badgeL = document.getElementById("badge-laporan");

            if (badgeP) {
                badgeP.textContent =
                    data.penarikan > 0
                        ? data.penarikan > 9
                            ? "9+"
                            : data.penarikan
                        : "0";
                badgeP.style.display =
                    data.penarikan > 0 ? "inline-block" : "none";
            }
            if (badgeL) {
                if (data.laporan > 0) {
                    badgeL.textContent = data.laporan > 9 ? "9+" : data.laporan;
                    badgeL.style.display = "inline-block";
                } else {
                    badgeL.style.display = "none";
                }
            }
        })
        .catch((err) => console.error("Badge fetch error:", err));
};

// Init saat DOM ready
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () =>
        window.updateNotifBadges(),
    );
} else {
    window.updateNotifBadges();
}

// Auto refresh setiap 30 detik
setInterval(window.updateNotifBadges, 30000);

// Tutup modal saat klik di luar
document.getElementById("notifModal")?.addEventListener("click", function (e) {
    if (e.target === this) window.closeNotifModal();
});
