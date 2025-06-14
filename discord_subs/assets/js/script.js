// Funciones para manejar modales
function mostrarModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function cerrarModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = 'auto';
}

// Cerrar modal al hacer clic fuera del contenido
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }
    });
}

// Aprobar suscripción
function aprobarSuscripcion(id) {
    if (confirm('¿Estás seguro de aprobar esta suscripción?')) {
        fetch('../procesar/admin_suscripciones.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `accion=aprobar&id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Suscripción aprobada correctamente');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrió un error al aprobar la suscripción');
        });
    }
}

// Mostrar modal de edición de suscripción
function mostrarModalEditar(suscripcion) {
    document.getElementById('editar-suscripcion-id').value = suscripcion.id;
    document.getElementById('editar-tipo').value = suscripcion.tipo;
    document.getElementById('editar-monto').value = suscripcion.monto;
    document.getElementById('editar-fecha-inicio').value = suscripcion.fecha_inicio.split(' ')[0];
    document.getElementById('editar-fecha-termino').value = suscripcion.fecha_termino.split(' ')[0];
    document.getElementById('editar-estado').value = suscripcion.estado;
    
    mostrarModal('modal-editar-suscripcion');
}

// Confirmar eliminación
function confirmarEliminar(id) {
    document.getElementById('eliminar-id').value = id;
    mostrarModal('modal-confirmar-eliminar');
}

// Validación de formularios
document.addEventListener('DOMContentLoaded', function() {
    // Validar formulario de registro
    const registroForm = document.getElementById('form-registro');
    if (registroForm) {
        registroForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
            }
        });
    }
    
    // Validar formulario de perfil
    const perfilForm = document.getElementById('form-perfil');
    if (perfilForm) {
        perfilForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password && password !== confirmPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
            }
        });
    }
});

// Manejar subida de comprobante
function handleComprobanteUpload(input) {
    const file = input.files[0];
    if (file) {
        const fileName = document.getElementById('file-name');
        fileName.textContent = file.name;
    }
}