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
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                datasets: [{
                    label: 'Activity',
                    data: [1200, 1900, 1500, 2200, 1800, 2500, 2100],
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: type === 'line',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { display: false },
                    x: { display: false }
                }
            }
        });
    });
});
