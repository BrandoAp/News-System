const categorias = window.categorias || [];
const autores = window.autores || [];

function actualizarCampoBusqueda() {
    const tipoBusqueda = document.getElementById('tipo_busqueda').value;
    const campoBusqueda = document.getElementById('busqueda');
    const filtroCategoria = document.getElementById('filtro_categoria');
    const sugerencias = document.getElementById('sugerencias');

    sugerencias.innerHTML = '';

    switch (tipoBusqueda) {
        case 'categoria':
            campoBusqueda.placeholder = 'Escriba el nombre de la categoría (ej: deportes, farándula)';
            filtroCategoria.style.display = 'none';
            categorias.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.nombre;
                sugerencias.appendChild(option);
            });
            break;
        case 'autor':
            campoBusqueda.placeholder = 'Escriba el nombre del autor';
            filtroCategoria.style.display = 'block';
            autores.forEach(autor => {
                const option = document.createElement('option');
                option.value = autor.autor;
                sugerencias.appendChild(option);
            });
            break;
        default:
            campoBusqueda.placeholder = 'Buscar por título, contenido o autor...';
            filtroCategoria.style.display = 'block';
            break;
    }
}

document.addEventListener('DOMContentLoaded', actualizarCampoBusqueda);
