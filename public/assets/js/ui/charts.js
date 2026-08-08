/**
 * Finzo Chart.js Configurations
 */

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.finzo-chart').forEach(function(el) {
        const ctx = el.getContext('2d');
        const type = el.getAttribute('data-type') || 'line';
        
        // --- Grouped Bar Chart (Institutional Ledger) ---
        if (type === 'grouped-bar') {
            fetch('../../../../api/v1/public/stats')
                .then(res => res.json())
                .then(response => {
                    if (!response.success) return;

                    // Update DOM stats if they exist
                    const updateStat = (idPrefix, val) => {
                        const iconEl = document.querySelector('#stat-' + idPrefix + '-icon');
                        const valEl = document.querySelector('#stat-' + idPrefix + '-val');
                        if (iconEl && valEl) {
                            if (val > 0) {
                                iconEl.textContent = '↑';
                                iconEl.classList.remove('text-rose-400', 'text-blue-500');
                                iconEl.classList.add('text-emerald-500');
                                valEl.textContent = '+' + val + '%';
                                valEl.classList.remove('text-rose-400', 'text-blue-500');
                                valEl.classList.add('text-emerald-500');
                            } else if (val < 0) {
                                iconEl.textContent = '↓';
                                iconEl.classList.remove('text-emerald-500', 'text-blue-500');
                                iconEl.classList.add('text-rose-400');
                                valEl.textContent = val + '%';
                                valEl.classList.remove('text-emerald-500', 'text-blue-500');
                                valEl.classList.add('text-rose-400');
                            } else {
                                iconEl.textContent = '→';
                                iconEl.classList.remove('text-emerald-500', 'text-rose-400');
                                iconEl.classList.add('text-blue-500');
                                valEl.textContent = '0%';
                                valEl.classList.remove('text-emerald-500', 'text-rose-400');
                                valEl.classList.add('text-blue-500');
                            }
                        }
                    };

                    updateStat('dep', response.changes.deposits);
                    updateStat('wit', response.changes.withdrawals);
                    updateStat('tra', response.changes.transfers);

                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: response.labels,
                            datasets: [
                                {
                                    label: 'Deposits',
                                    data: response.datasets.deposits,
                                    backgroundColor: '#10B981',
                                    borderRadius: { topLeft: 6, topRight: 6 },
                                    borderSkipped: false,
                                    barThickness: 8
                                },
                                {
                                    label: 'Withdrawals',
                                    data: response.datasets.withdrawals,
                                    backgroundColor: '#F87171',
                                    borderRadius: { topLeft: 6, topRight: 6 },
                                    borderSkipped: false,
                                    barThickness: 8
                                },
                                {
                                    label: 'Transfers',
                                    data: response.datasets.transfers,
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
                                    grid: { color: 'rgba(0, 0, 0, 0.03)', drawTicks: false },
                                    ticks: {
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
                })
                .catch(err => console.error('Error loading chart data:', err));
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
