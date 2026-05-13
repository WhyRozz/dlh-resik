// resources/js/notifications.js
const isPenjemputanPage = window.location.href.includes('/admin/bank-sampah/penjemputan');
// 1. Ekspos ke global window agar bisa dipanggil dari HTML onclick
window.openNotifModal = function (type) {
     if (window.disableNotifications) {
        console.log('Notifications disabled on this page');
        return;
    }
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
    } else if (type === 'laporan') {
        title.textContent = '🚨 Laporan Sampah Ilegal';
        seeAll.href = '/admin/laporan';
        
        fetch('/admin/notifications/recent/laporan')
            .then(res => res.json())
            .then(data => {
                if (!data || data.length === 0) {
                    body.innerHTML = '<p style="text-align: center; color: #888; padding: 20px;">Tidak ada data baru</p>';
                } else {
                    body.innerHTML = data.map(item => `
                        <div class="notif-item">
                            <div class="notif-info">
                                <strong>${item.nama || 'Unknown'}</strong>
                                <small>${item.lokasi || item.jenis || '-'}</small>
                            </div>
                            <span class="notif-time">${item.waktu}</span>
                        </div>
                    `).join('');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                body.innerHTML = '<p style="text-align: center; color: red;">Gagal memuat data</p>';
            });
            
    } else if (type === 'penjemputan') {
        // ✅ TAMBAHKAN CASE INI
        title.textContent = '🚚 Penjemputan Baru';
        seeAll.href = '/admin/bank-sampah/penjemputan';
        
        fetch('/admin/notifications/recent/penjemputan')
            .then(res => res.json())
            .then(data => {
                if (!data || data.length === 0) {
                    body.innerHTML = '<p style="text-align: center; color: #888; padding: 20px;">Tidak ada data baru</p>';
                } else {
                    body.innerHTML = data.map(item => `
                        <div class="notif-item">
                            <div class="notif-info">
                                <strong>${item.nama_admin}</strong>
                                <small>${item.berat} • ${item.lokasi}</small>
                            </div>
                            <span class="notif-time">${item.waktu}</span>
                        </div>
                    `).join('');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                body.innerHTML = '<p style="text-align: center; color: red;">Gagal memuat data</p>';
            });
    }
};

document.addEventListener('DOMContentLoaded', function() {
    const badgeWrappers = document.querySelectorAll('.notif-badge-wrapper');
    badgeWrappers.forEach(wrapper => {
        wrapper.addEventListener('click', function(e) {
            // HANYA stop propagation, JANGAN preventDefault
            e.stopPropagation();
            // Biarkan onclick di HTML yang menangani buka modal
        });
    });
});

window.closeNotifModal = function () {
    const modal = document.getElementById("notifModal");
    if (modal) modal.style.display = "none";
};

window.updateNotifBadges = function () {
    
    if (window.disableNotifications) {
        console.log('Notifications disabled on this page');
        return;
    }
    fetch("/admin/notifications/counts")
        .then((res) => res.json())
        .then((data) => {
            const badgeP = document.getElementById("badge-penarikan");
            const badgeL = document.getElementById("badge-laporan");
            const badgeJ = document.getElementById('badge-penjemputan');

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
            if (badgeJ) {
                if (data.penjemputan > 0) {
                    badgeJ.textContent = data.penjemputan > 9 ? '9+' : data.penjemputan;
                    badgeJ.style.display = 'inline-block';
                } else {
                    badgeJ.style.display = 'none';
                }
            }
        })
        .catch((err) => console.error("Badge fetch error:", err));
};

/// Init dengan delay untuk memastikan DOM sudah siap
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 DOM Loaded, initializing notifications...');
    
    // Panggil langsung dengan delay kecil
    setTimeout(() => {
        window.updateNotifBadges();
        console.log('✅ Initial badge update done');
    }, 100); // Delay 100ms
    
    // Auto refresh setiap 30 detik
    setInterval(() => {
        window.updateNotifBadges();
        console.log('🔄 Auto refresh badges');
    }, 30000);
});

// Fallback: jika DOM sudah loaded sebelum script ini jalan
if (document.readyState !== 'loading') {
    setTimeout(() => window.updateNotifBadges(), 100);
}
