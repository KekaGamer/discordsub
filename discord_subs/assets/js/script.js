// --- Funciones para manejar modales ---
function mostrarModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function cerrarModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// --- Lógica para cerrar modales ---
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.style.display = 'none';
        });
        document.body.style.overflow = 'auto';
    }
});

// --- Lógica para rellenar modales de edición ---

// Editar Usuario (Admin)
function mostrarModalEditar(usuario) {
    document.getElementById('editar-id').value = usuario.id;
    document.getElementById('editar-nombre').value = usuario.nombre;
    document.getElementById('editar-apellido').value = usuario.apellido;
    document.getElementById('editar-discord').value = usuario.discord;
    document.getElementById('editar-rol').value = usuario.rol;
    document.getElementById('editar-estado').value = usuario.estado;
    mostrarModal('modal-editar-usuario');
}

// Eliminar Usuario (Admin)
function confirmarEliminar(id) {
    document.getElementById('eliminar-id').value = id;
    mostrarModal('modal-confirmar-eliminar');
}

// Editar Suscripción (Admin)
function mostrarModalEditarSusc(susc) {
    document.getElementById('editar-susc-id').value = susc.id;
    document.getElementById('editar-susc-tipo').value = susc.tipo;
    document.getElementById('editar-susc-monto').value = susc.monto;
    document.getElementById('editar-susc-inicio').value = susc.fecha_inicio.split(' ')[0];
    document.getElementById('editar-susc-termino').value = susc.fecha_termino.split(' ')[0];
    document.getElementById('editar-susc-estado').value = susc.estado;
    mostrarModal('modal-editar-suscripcion');
}

// Eliminar Suscripción (Admin)
function confirmarEliminarSusc(id) {
    document.getElementById('eliminar-susc-id').value = id;
    mostrarModal('modal-confirmar-eliminar-susc');
}

// Editar Evento (Admin)
function mostrarModalEditarEvento(evento) {
    document.getElementById('editar-evento-id').value = evento.id;
    document.getElementById('editar-evento-titulo').value = evento.titulo;
    document.getElementById('editar-evento-descripcion').value = evento.descripcion;
    const fecha = new Date(evento.fecha_evento);
    const fechaFormateada = fecha.toISOString().slice(0, 16);
    document.getElementById('editar-evento-fecha').value = fechaFormateada;
    mostrarModal('modal-editar-evento');
}

// Eliminar Evento (Admin)
function confirmarEliminarEvento(id) {
    document.getElementById('eliminar-evento-id').value = id;
    mostrarModal('modal-confirmar-eliminar-evento');
}

// --- Validación de formularios en el lado del cliente ---
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('#form-registro, #form-perfil');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const password = form.querySelector('#password');
            const confirmPassword = form.querySelector('#confirm_password');
            if (password && confirmPassword && password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Las contraseñas no coinciden.');
            }
        });
    });
});