/**
 * Finzo Chart.js Configurations
 */

$(document).ready(function() {
    $('.finzo-chart').each(function() {
        const ctx = this.getContext('2d');
        const type = $(this).attr('data-type') || 'line';
        
        new Chart(ctx, {
            type: type,
            data: {
                labels: ['', '', '', '', '', '', ''],
                datasets: [{
                    label: 'Activity',
                    data: [12, 19, 15, 22, 18, 25, 21],
                    borderColor: '#002645',
                    backgroundColor: 'rgba(0, 38, 69, 0.03)',
                    borderWidth: 3,
                    tension: 0.45,
                    fill: true,
                    pointRadius: 0,
                    pointHoverRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { left: -1, right: -1, top: 10, bottom: -1 } },
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: {
                    y: { display: false, beginAtZero: false },
                    x: { display: false }
                },
                hover: { mode: null }
            }
        });
    });
});
