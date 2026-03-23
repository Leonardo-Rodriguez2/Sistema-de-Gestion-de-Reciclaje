// js/charts.js

document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('chartMensual'), {
        type: 'line',
        data: {
            labels: meses, // Usamos la variable JS directamente
            datasets: [{
                label: 'Kg reciclados',
                data: valMensual, // Usamos la variable JS directamente
                borderWidth: 2,
                tension: 0.3
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    new Chart(document.getElementById('chartDistribucion'), {
        type: 'pie',
        data: {
            labels: mat, // Usamos la variable JS directamente
            datasets: [{
                data: valDist, // Usamos la variable JS directamente
                borderWidth: 1
            }]
        },
        options: {
            maintainAspectRatio: false
        }
    });
});

  