document.addEventListener('DOMContentLoaded', function() {
    // Manejar Publicar/Despublicar
    document.querySelectorAll('.btn-publicar, .btn-despublicar').forEach(button => {
        button.addEventListener('click', function() {
            const noticiaId = this.dataset.id;
            const nuevoEstado = this.classList.contains('btn-publicar') ? 'publicada' : 'borrador';

            if (confirm(`¿Estás seguro de ${nuevoEstado === 'publicada' ? 'publicar' : 'despublicar'} esta noticia?`)) {
                fetch('/News-System/public/noticias_acciones.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `accion=cambiar_estado&id=${noticiaId}&estado=${nuevoEstado}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload(); // Recargar la página para ver los cambios
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ocurrió un error al procesar la solicitud.');
                });
            }
        });
    });

    // Manejar Eliminación
    document.querySelectorAll('.btn-eliminar').forEach(button => {
        button.addEventListener('click', function() {
            const noticiaId = this.dataset.id;

            if (confirm('¿Estás seguro de eliminar esta noticia? Esta acción es irreversible.')) {
                fetch('/News-System/public/noticias_acciones.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `accion=eliminar&id=${noticiaId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload(); // Recargar la página para ver los cambios
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ocurrió un error al procesar la solicitud de eliminación.');
                });
            }
        });
    });
});