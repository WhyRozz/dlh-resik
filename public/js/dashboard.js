document.addEventListener('DOMContentLoaded', function () {
    initResponsiveChart();
});

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

                    suggestedMax: Math.max(...dataValues, 10),

                    ticks: {
                        precision: 0,

                        callback: function (value) {
                            return value;
                        }
                    },

                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
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
window.addEventListener('resize', function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
        if (window.laporanChart) {
            window.laporanChart.destroy();
            initResponsiveChart();
        }
    }, 250);
});
