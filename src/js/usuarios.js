// Definir las funciones globalmente FUERA del DOMContentLoaded
window.cambiarEstadoUsuario = async (id, nuevoEstado) => {
    try {
        const accion = nuevoEstado == 1 ? 'activar' : 'desactivar';
        const confirmacion = await Swal.fire({
            title: `¿Estás seguro?`,
            text: `¿Quieres ${accion} este usuario?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar'
        });

        if (confirmacion.isConfirmed) {
            const formData = new FormData();
            formData.append('Accion', 'CambiarEstado');
            formData.append('id', id);
            formData.append('nuevo_estado', nuevoEstado);

            const response = await fetch('../controllers/usuario_controller.php', {
                method: 'POST',
                body: formData
            });

            
            const result = await response.json();
            

            if (result.success) {
                Swal.fire({
                    title: 'Éxito',
                    text: result.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                
                // Recargar la página para mostrar los cambios
                location.reload();
            } else {
                Swal.fire('Error', result.message || 'Error al cambiar el estado', 'error');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'Error de conexión al servidor', 'error');
    }
};

window.editarUsuario = (id) => {
    window.location.href = `form_usuario.php?id=${id}`;
};

window.eliminarUsuario = async (id) => {
    try {
        const confirmacion = await Swal.fire({
            title: '¿Estás seguro?',
            text: '¿Quieres eliminar este usuario? Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });

        if (confirmacion.isConfirmed) {
            const formData = new FormData();
            formData.append('Accion', 'Eliminar');
            formData.append('id', id);

            const response = await fetch('../Controladores/usuario_controller.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                Swal.fire({
                    title: 'Eliminado',
                    text: 'Usuario eliminado correctamente',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                
                location.reload();
            } else {
                Swal.fire('Error', result.message || 'Error al eliminar usuario', 'error');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'Error de conexión al servidor', 'error');
    }
};

// Ahora el DOMContentLoaded solo para funciones internas
document.addEventListener("DOMContentLoaded", () => {
    const tabla = document.getElementById("tablaUsuarios");

    // Funciones internas que no necesitan ser globales
    function actualizarTabla(usuarios) {
        tabla.innerHTML = "";
        usuarios.forEach(u => {
            if (u.id_estado == -1) return;

            tabla.innerHTML += `
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2">${u.id}</td>
                    <td class="px-4 py-2">${u.nombre}</td>
                    <td class="px-4 py-2">${u.correo}</td>
                    <td class="px-4 py-2 capitalize">${u.rol}</td>
                    <td class="px-4 py-2">
                        ${u.id_estado == 1
                            ? `<span class="text-xs px-2 py-1 bg-green-200 text-green-800 rounded-full">Activo</span>`
                            : `<span class="text-xs px-2 py-1 bg-red-200 text-red-800 rounded-full">Inactivo</span>`}
                    </td>
                    <td class="px-4 py-2 flex gap-2">
                        <button onclick="editarUsuario(${u.id})" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">Editar</button>
                        <button onclick="cambiarEstadoUsuario(${u.id}, ${u.id_estado == 1 ? 0 : 1})"
                                class="${u.id_estado == 1 ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-purple-500 hover:bg-purple-600'} text-white px-3 py-1 rounded text-xs">
                            ${u.id_estado == 1 ? 'Desactivar' : 'Activar'}
                        </button>
                        <button onclick="eliminarUsuario(${u.id})" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">Eliminar</button>
                    </td>
                </tr>
            `;
        });
    }

    async function cargarUsuarios() {
        try {
            const res = await fetch("../Controladores/usuario_controller.php?Accion=Listar");
            const usuarios = await res.json();
            actualizarTabla(usuarios);
        } catch (error) {
            Swal.fire("Error", "No se pudieron cargar los usuarios", "error");
        }
    }

    // Si quieres cargar dinámicamente, descomenta esta línea:
    // cargarUsuarios();
});