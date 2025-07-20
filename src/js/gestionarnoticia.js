// Función para previsualizar imágenes seleccionadas
function previewImages(input) {
    const container = document.getElementById('preview-container');
    container.innerHTML = '';
    
    if (input.files && input.files.length > 0) {
        Array.from(input.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.createElement('div');
                preview.className = 'preview-image fade-in';
                
                preview.innerHTML = `
                    <img src="${e.target.result}" 
                         alt="Vista previa ${index + 1}" 
                         class="w-full h-40 object-cover transition-transform duration-300 hover:scale-105">
                    <div class="absolute bottom-2 left-2">
                        <span class="bg-gradient-to-r from-gray-800 to-gray-900 text-white text-xs font-medium px-3 py-1 rounded-full shadow-lg">
                            ${file.name.length > 20 ? file.name.substring(0, 20) + '...' : file.name}
                        </span>
                    </div>
                    <div class="absolute top-2 right-2">
                        <span class="bg-gradient-to-r from-green-500 to-green-600 text-white text-xs font-bold px-2 py-1 rounded-full shadow-lg">
                            NUEVO
                        </span>
                    </div>
                `;
                
                container.appendChild(preview);
            };
            reader.readAsDataURL(file);
        });
    }
}

// Función para eliminar imagen existente
function eliminarImagen(imagenId) {
    if (confirm('¿Estás seguro de que quieres eliminar esta imagen?')) {
        fetch('ajax/eliminar_imagen.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ imagen_id: imagenId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Recargar para actualizar la vista
            } else {
                alert('Error al eliminar la imagen: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar la imagen');
        });
    }
}

// Función para establecer imagen principal
function establecerPrincipal(imagenId) {
    fetch('ajax/establecer_imagen_principal.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ imagen_id: imagenId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Recargar para actualizar la vista
        } else {
            alert('Error al establecer imagen principal: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al establecer imagen principal');
    });
}

// Validar número máximo de archivos
document.querySelector('input[name="imagenes[]"]').addEventListener('change', function(e) {
    const maxFiles = 3;
    if (e.target.files.length > maxFiles) {
        alert(`Solo puedes subir un máximo de ${maxFiles} imágenes`);
        e.target.value = '';
        document.getElementById('preview-container').innerHTML = '';
        return;
    }
    previewImages(e.target);
});

// Funciones para Alpine.js
document.addEventListener('alpine:init', () => {
    Alpine.data('formData', () => ({
        previewImages: [],
        dragOver: false,
        publishNow: true,
        
        handleDrop(e) {
            const files = e.dataTransfer.files;
            const input = document.getElementById('imagen-input');
            input.files = files;
            this.previewSelectedImages({ target: { files } });
        },
        
        previewSelectedImages(e) {
            previewImages(e.target);
        }
    }));
});