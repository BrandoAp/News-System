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

// Función para previsualizar imagen individual
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const isLarge = previewId === 'preview_grande';
            const maxHeight = isLarge ? 'max-h-48' : 'max-h-24';
            
            preview.innerHTML = `
                <img src="${e.target.result}" 
                     alt="Vista previa" 
                     class="max-w-full ${maxHeight} mx-auto rounded-lg shadow-md">
                <p class="text-xs text-gray-600 mt-2">Nueva imagen seleccionada</p>
            `;
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Validar archivos individuales
function validarImagen(input) {
    const file = input.files[0];
    if (!file) return true;

    // Validar tipo de archivo
    const tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!tiposPermitidos.includes(file.type)) {
        alert('Por favor selecciona solo archivos de imagen (JPG, PNG, GIF, WebP)');
        input.value = '';
        return false;
    }

    // Validar tamaño (5MB máximo)
    const maxSize = 5 * 1024 * 1024; // 5MB
    if (file.size > maxSize) {
        alert('El archivo es demasiado grande. El tamaño máximo es 5MB.');
        input.value = '';
        return false;
    }

    return true;
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

// Agregar validación a todos los inputs de imagen
document.addEventListener('DOMContentLoaded', function() {
    const imageInputs = [
        'imagen_grande',
        'imagen_thumb_original', 
        'imagen_thumb1',
        'imagen_thumb2'
    ];

    imageInputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('change', function() {
                if (validarImagen(this)) {
                    previewImage(this, 'preview_' + inputId.replace('imagen_', ''));
                }
            });
        }
    });
});

// Funciones para Alpine.js
document.addEventListener('alpine:init', () => {
    Alpine.data('formData', () => ({
        previewImages: [],
        dragOver: false,
        publishNow: true,
        resumenCount: 0,
        maxResumen: 250,
        
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