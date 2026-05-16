document.addEventListener('DOMContentLoaded', function() {
    initLaporanChart();
});

/**
 * FUNGSI: Buat instance Chart.js dengan config dari bridge
 */
function initLaporanChart() {
    const ctx = document.getElementById('laporanChart')?.getContext('2d');
    
    if (!ctx) return;
    
    // Baca data chart dari bridge config (di-render Blade)
    const labels = window.DashboardConfig?.chartLabels || [];
    const dataValues = window.DashboardConfig?.chartData || [];
    
    new Chart(ctx, {
        type: 'line', // Bisa juga 'bar' jika mau bentuk batang
        data: {
            labels: labels, // Ini akan jadi "Minggu 1", "Minggu 2", dst
            datasets: [{
                label: 'Jumlah Laporan',
                data: dataValues, // Ini angka 10, 20, 30, dst
                borderColor: '#22c55e', // Warna Garis Hijau
                backgroundColor: 'rgba(34, 197, 94, 0.1)', // Warna Area Transparan
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#22c55e',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false } // Sembunyikan legend agar bersih
            },
            scales: {
                y: {
                    beginAtZero: true,
                    // Biarkan Chart.js mengatur sendiri step (10, 20, 30) 
                    // agar muat sampai 50+
                    grid: { color: 'rgba(0,0,0,0.05)' },
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
}

// ===== EXPORT FUNCTIONS (Opsional) =====
window.DashboardJS = {
    initLaporanChart: initLaporanChart
};




/**
 * Responsive Chart Initialization
 */
function initResponsiveChart() {
    const ctx = document.getElementById('laporanChart')?.getContext('2d');
    if (!ctx) return;
    
    const labels = window.DashboardConfig?.chartLabels || [];
    const dataValues = window.DashboardConfig?.chartData || [];
    
    // Detect mobile for simpler chart
    const isMobile = window.innerWidth <= 768;
    
    window.laporanChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Laporan',
                data: dataValues,
                borderColor: '#22c55e',
                backgroundColor: isMobile 
                    ? 'transparent' 
                    : 'rgba(34, 197, 94, 0.1)',
                borderWidth: isMobile ? 2 : 3,
                fill: !isMobile,
                tension: isMobile ? 0.2 : 0.4,
                pointRadius: isMobile ? 3 : 5,
                pointHoverRadius: isMobile ? 4 : 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    enabled: true,
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: isMobile ? undefined : 10,
                        font: { size: isMobile ? 10 : 12 }
                    },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: isMobile ? 10 : 11 },
                        maxRotation: isMobile ? 45 : 0,
                        minRotation: isMobile ? 45 : 0
                    }
                }
            },
            interaction: {
                mode: isMobile ? 'nearest' : 'index',
                intersect: false
            }
        }
    });
}

// Re-init chart on resize (debounced)
let resizeTimer;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
        if (window.laporanChart) {
            window.laporanChart.destroy();
            initResponsiveChart();
        }
    }, 250);
});

// Init on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initResponsiveChart();
});

