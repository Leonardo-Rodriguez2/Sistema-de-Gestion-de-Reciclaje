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

function cargarMas() {
    // Obtener la cantidad de filas que ya están cargadas en la tabla
    let offset = document.querySelectorAll('#tablaActividades tr').length;
  
    // Realizar la solicitud AJAX para obtener 10 reportes más
    fetch(`index.php?offset=${offset}`)
      .then(response => response.text())
      .then(data => {
        // Insertar las nuevas filas de la tabla en la parte final de la tabla
        const tabla = document.getElementById('tablaActividades');
        const parser = new DOMParser();
        const doc = parser.parseFromString(data, 'text/html');
        const nuevasFilas = doc.querySelectorAll('#tablaActividades tr');
        
        nuevasFilas.forEach(fila => {
          tabla.appendChild(fila);
        });
  
        // Si no hay más datos, ocultar el botón "Ver más"
        if (nuevasFilas.length === 0) {
          document.getElementById('verMas').style.display = 'none';
        }
      })
      .catch(error => console.error('Error al cargar más reportes:', error));
  }
  