/**
 * Finzo Chart.js Configurations
 */

$(document).ready(function() {
    $('.finzo-chart').each(function() {
        const ctx = this.getContext('2d');
        const type = $(this).attr('data-type') || 'line';
        
        // --- Grouped Bar Chart (Institutional Ledger) ---
        if (type === 'grouped-bar') {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [
                        {
                            label: 'Deposits',
                            data: [45, 52, 48, 61, 58, 72],
                            backgroundColor: '#10B981',
                            borderRadius: { topLeft: 6, topRight: 6 },
                            borderSkipped: false,
                            barThickness: 8
                        },
                        {
                            label: 'Withdrawals',
                            data: [28, 31, 25, 35, 29, 38],
                            backgroundColor: '#F87171',
                            borderRadius: { topLeft: 6, topRight: 6 },
                            borderSkipped: false,
                            barThickness: 8
                        },
                        {
                            label: 'Transfers',
                            data: [18, 22, 19, 27, 24, 31],
                            backgroundColor: '#3B82F6',
                            borderRadius: { topLeft: 6, topRight: 6 },
                            borderSkipped: false,
                            barThickness: 8
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 2000,
                        easing: 'easeOutQuart'
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#002645',
                            titleFont: { size: 10, weight: 'bold' },
                            bodyFont: { size: 10 },
                            padding: 10,
                            displayColors: true,
                            callbacks: {
                                label: (context) => ` ₹${context.raw}K`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 75,
                            grid: { color: 'rgba(0, 0, 0, 0.03)', drawTicks: false },
                            ticks: {
                                stepSize: 25,
                                color: 'rgba(0, 38, 69, 0.4)',
                                font: { size: 9, weight: 'bold' },
                                callback: value => '₹' + value + 'K'
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: 'rgba(0, 38, 69, 0.4)',
                                font: { size: 9, weight: 'bold' }
                            }
                        }
                    }
                }
            });
            return;
        }

        // --- Legacy/Generic Chart Logic (Premium Line) ---
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(0, 38, 69, 0.4)');
        gradient.addColorStop(0.4, 'rgba(0, 38, 69, 0.1)');
        gradient.addColorStop(1, 'rgba(0, 38, 69, 0)');

        const dataPoints = [15, 18, 14, 22, 20, 28, 25, 34, 32, 40, 38, 48, 45, 55, 52, 65, 62, 75, 72, 85, 82, 95];

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: Array(dataPoints.length).fill(''),
                datasets: [
                    {
                        label: 'Live Ledger',
                        data: dataPoints,
                        borderColor: '#002645',
                        backgroundColor: gradient,
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 0,
                        z: 10
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 3000, easing: 'easeOutExpo' },
                layout: { padding: { left: -10, right: 0, top: 20, bottom: 0 } },
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: {
                    y: { display: true, position: 'right', grid: { color: 'rgba(0, 38, 69, 0.03)', borderDash: [2, 2], drawTicks: false }, ticks: { display: false } },
                    x: { display: true, grid: { color: 'rgba(0, 38, 69, 0.03)', borderDash: [2, 2], drawTicks: false }, ticks: { display: false } }
                }
            }
        });
    });
});
