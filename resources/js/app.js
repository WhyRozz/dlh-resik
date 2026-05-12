import "./bootstrap";
import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "pusher",
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: false,
    wsHost: window.location.hostname,
    wsPort: 6001,
    enabledTransports: ["ws", "wss"],
});

// Listen untuk notifikasi
window.Echo.channel("admin-notifications").listen(".new-withdrawal", (data) => {
    showNotification(data);
    playSound();
});

function showNotification(data) {
    // Update badge
    const badge = document.getElementById("notifBadge");
    const currentCount = parseInt(badge.textContent) || 0;
    badge.textContent = currentCount + 1;
    badge.style.display = "flex";

    // Tambah ke list
    const list = document.getElementById("notifList");
    const newItem = document.createElement("div");
    newItem.className = "notif-item unread";
    newItem.innerHTML = `
        <div style="font-weight: 600; color: #333; margin-bottom: 4px;">${data.nama}</div>
        <div style="font-size: 13px; color: #2e8b57; font-weight: 600;">Rp ${formatRupiah(data.jumlah)}</div>
        <div style="font-size: 12px; color: #999; margin-top: 4px;">Baru saja</div>
    `;
    list.insertBefore(newItem, list.firstChild);

    // Browser notification
    if (Notification.permission === "granted") {
        new Notification("Pengajuan Penarikan Baru", {
            body: `${data.nama} mengajukan penarikan Rp ${formatRupiah(data.jumlah)}`,
            icon: "/logo.png",
        });
    }
}
// Update Badge dari Backend
function updateNotifBadges() {
    fetch("/admin/notifications/counts")
        .then((res) => res.json())
        .then((data) => {
            setBadge("badge-penarikan", data.penarikan);
            setBadge("badge-laporan", data.laporan);
        });
}

function setBadge(id, count) {
    const el = document.getElementById(id);
    if (count > 0) {
        el.textContent = count > 9 ? "9+" : count;
        el.style.display = "inline-block";
    } else {
        el.style.display = "none";
    }
}

// Buka Modal & Load Data
function openNotifModal(type) {
    const modal = document.getElementById("notifModal");
    const title = document.getElementById("modalTitle");
    const body = document.getElementById("modalBody");
    const seeAll = document.getElementById("modalSeeAll");

    modal.classList.add("active");
    body.innerHTML = '<div class="loading-spinner">Memuat data...</div>';

    if (type === "penarikan") {
        title.textContent = "💸 Pengajuan Penarikan Baru";
        seeAll.href = '{{ route("admin.bank-sampah.penarikan.index") }}';
        fetch("/admin/notifications/recent/penarikan")
            .then((res) => res.json())
            .then((data) => renderList(body, data, "penarikan"));
    } else {
        title.textContent = "🚨 Laporan Sampah Ilegal Baru";
        seeAll.href = '{{ route("admin.laporan.index") }}';
        fetch("/admin/notifications/recent/laporan")
            .then((res) => res.json())
            .then((data) => renderList(body, data, "laporan"));
    }
}

function renderList(container, items, type) {
    if (!items.length) {
        container.innerHTML =
            '<p style="text-align:center;color:#888;padding:20px;">Tidak ada data baru</p>';
        return;
    }
    container.innerHTML = items
        .map(
            (item) => `
        <div class="notif-item">
            <div class="notif-info">
                <strong>${item.nama || item.lokasi || "Data Baru"}</strong>
                <small>${type === "penarikan" ? "Rp " + new Intl.NumberFormat("id-ID").format(item.jumlah) : item.jenis || "-"}</small>
            </div>
            <span class="notif-time">${item.waktu}</span>
        </div>
    `,
        )
        .join("");
}

function closeNotifModal() {
    document.getElementById("notifModal").classList.remove("active");
}

// Tutup modal jika klik di luar
document.getElementById("notifModal").addEventListener("click", function (e) {
    if (e.target === this) closeNotifModal();
});

// Integrasi dengan Pusher (Realtime Penarikan)
if (typeof window.Echo !== "undefined") {
    window.Echo.channel("admin-notifications").listen(
        ".new-withdrawal",
        (data) => {
            const badge = document.getElementById("badge-penarikan");
            const current = parseInt(badge.textContent) || 0;
            setBadge("badge-penarikan", current + 1);
        },
    );
}

// Jalankan saat load
document.addEventListener("DOMContentLoaded", () => {
    updateNotifBadges();
    setInterval(updateNotifBadges, 30000); // Refresh setiap 30 detik
});
