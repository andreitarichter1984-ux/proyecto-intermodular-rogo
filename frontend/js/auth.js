// Formularios de inicio de sesión y registro (index.html).

const API_AUTH = '../backend/auth.php';

const alertBox = document.getElementById('alert');
const formLogin = document.getElementById('form-login');
const formRegister = document.getElementById('form-register');

function mostrarError(mensaje) {
    alertBox.textContent = mensaje;
    alertBox.hidden = false;
}

function ocultarError() {
    alertBox.hidden = true;
}

// Cambio entre pestañas "Iniciar sesión" y "Crear cuenta"
document.querySelectorAll('.tab').forEach((tab) => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.tab').forEach((t) => {
            t.classList.toggle('active', t === tab);
            t.setAttribute('aria-selected', t === tab);
        });
        formLogin.hidden = tab.dataset.target !== 'form-login';
        formRegister.hidden = tab.dataset.target !== 'form-register';
        ocultarError();
    });
});

async function enviar(accion, form) {
    const boton = form.querySelector('button[type="submit"]');
    boton.disabled = true;
    try {
        const respuesta = await fetch(`${API_AUTH}?action=${accion}`, {
            method: 'POST',
            body: new FormData(form),
            credentials: 'same-origin',
        });
        const datos = await respuesta.json();
        if (datos.ok) {
            window.location.href = datos.redirect;
        } else {
            mostrarError(datos.error);
        }
    } catch (e) {
        mostrarError('No se pudo conectar con el servidor. Comprueba que Apache y MySQL están iniciados.');
    } finally {
        boton.disabled = false;
    }
}

formLogin.addEventListener('submit', (e) => {
    e.preventDefault();
    ocultarError();
    const email = formLogin.email.value.trim();
    if (!email || !formLogin.contrasena.value) {
        mostrarError('Introduce tu correo y tu contraseña.');
        return;
    }
    enviar('login', formLogin);
});

formRegister.addEventListener('submit', (e) => {
    e.preventDefault();
    ocultarError();
    const nombre = formRegister.nombre.value.trim();
    const email = formRegister.email.value.trim();
    const pass = formRegister.contrasena.value;
    const pass2 = document.getElementById('register-password2').value;

    if (!nombre || !email || !pass) {
        mostrarError('Todos los campos son obligatorios.');
        return;
    }
    if (!formRegister.email.checkValidity()) {
        mostrarError('El correo electrónico no es válido.');
        return;
    }
    if (pass.length < 8) {
        mostrarError('La contraseña debe tener al menos 8 caracteres.');
        return;
    }
    if (pass !== pass2) {
        mostrarError('Las contraseñas no coinciden.');
        return;
    }
    enviar('register', formRegister);
});

// Si ya hay sesión iniciada, ir directamente al panel
fetch(`${API_AUTH}?action=session`, { credentials: 'same-origin' })
    .then((r) => r.json())
    .then((datos) => {
        if (datos.ok) window.location.href = 'dashboard.html';
    })
    .catch(() => {});
