// Formularios de inicio de sesión (login.html) y registro (registro.html).

const API_AUTH = '../backend/auth.php';

const formLogin = document.getElementById('form-login');
const formRegister = document.getElementById('form-register');

// ---------- Mensajes de error ----------

function mostrarAviso(form, mensaje) {
  const aviso = form.querySelector('.alert');
  aviso.textContent = mensaje;
  aviso.hidden = false;
}

// Marca un campo como inválido y enseña su mensaje debajo
function mostrarErrorCampo(input, mensaje) {
  const error = document.getElementById(`${input.id}-error`);
  input.classList.add('invalid');
  input.setAttribute('aria-invalid', 'true');
  error.textContent = mensaje;
  error.hidden = false;
}

function limpiarErrores(form) {
  form.querySelector('.alert').hidden = true;
  form.querySelectorAll('.field-error').forEach((e) => { e.hidden = true; });
  form.querySelectorAll('.invalid').forEach((i) => {
    i.classList.remove('invalid');
    i.removeAttribute('aria-invalid');
  });
}

// Al escribir en un campo se quita su error
document.querySelectorAll('.auth-form input').forEach((input) => {
  input.addEventListener('input', () => {
    const error = document.getElementById(`${input.id}-error`);
    input.classList.remove('invalid');
    input.removeAttribute('aria-invalid');
    if (error) error.hidden = true;
  });
});

// ---------- Mostrar / ocultar contraseña ----------

document.querySelectorAll('.toggle-password').forEach((boton) => {
  boton.addEventListener('click', () => {
    const input = boton.parentElement.querySelector('input');
    const visible = input.type === 'text';
    input.type = visible ? 'password' : 'text';
    boton.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
  });
});

// ---------- Botón de Google (aún sin backend) ----------

document.querySelectorAll('[data-google]').forEach((boton) => {
  boton.addEventListener('click', () => {
    mostrarAviso(boton.closest('form'), 'El acceso con Google estará disponible próximamente.');
  });
});

// ---------- Envío al backend ----------

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
    } else if (respuesta.status === 409) {
      // Correo ya registrado: se marca en el propio campo
      mostrarErrorCampo(form.email, datos.error);
    } else {
      mostrarAviso(form, datos.error);
    }
  } catch (e) {
    mostrarAviso(form, 'No se pudo conectar con el servidor. Comprueba que Apache y MySQL están iniciados.');
  } finally {
    boton.disabled = false;
  }
}

// ---------- Inicio de sesión ----------

if (formLogin) {
  formLogin.addEventListener('submit', (e) => {
    e.preventDefault();
    limpiarErrores(formLogin);
    let valido = true;

    if (!formLogin.email.value.trim()) {
      mostrarErrorCampo(formLogin.email, 'Introduce tu correo electrónico.');
      valido = false;
    } else if (!formLogin.email.checkValidity()) {
      mostrarErrorCampo(formLogin.email, 'El correo electrónico no es válido.');
      valido = false;
    }
    if (!formLogin.contrasena.value) {
      mostrarErrorCampo(formLogin.contrasena, 'Introduce tu contraseña.');
      valido = false;
    }

    if (valido) enviar('login', formLogin);
  });
}

// ---------- Registro ----------

// Reglas de la contraseña (se marcan en verde mientras se escribe)
function reglasContrasena(valor) {
  return {
    length: valor.length >= 8,
    upper: /[A-Z]/.test(valor),
    number: /\d/.test(valor),
  };
}

if (formRegister) {
  const password2 = document.getElementById('register-password2');
  const terminos = document.getElementById('register-terms');

  formRegister.contrasena.addEventListener('input', () => {
    const reglas = reglasContrasena(formRegister.contrasena.value);
    document.querySelectorAll('#password-rules li').forEach((li) => {
      li.classList.toggle('ok', reglas[li.dataset.rule]);
    });
  });

  terminos.addEventListener('change', () => {
    document.getElementById('register-terms-error').hidden = true;
  });

  formRegister.addEventListener('submit', (e) => {
    e.preventDefault();
    limpiarErrores(formRegister);
    let valido = true;

    if (!formRegister.nombre.value.trim()) {
      mostrarErrorCampo(formRegister.nombre, 'Introduce tu nombre completo.');
      valido = false;
    }
    if (!formRegister.email.value.trim()) {
      mostrarErrorCampo(formRegister.email, 'Introduce tu correo electrónico.');
      valido = false;
    } else if (!formRegister.email.checkValidity()) {
      mostrarErrorCampo(formRegister.email, 'El correo electrónico no es válido.');
      valido = false;
    }

    const reglas = reglasContrasena(formRegister.contrasena.value);
    if (!formRegister.contrasena.value) {
      mostrarErrorCampo(formRegister.contrasena, 'Introduce una contraseña.');
      valido = false;
    } else if (!reglas.length || !reglas.upper || !reglas.number) {
      mostrarErrorCampo(formRegister.contrasena, 'La contraseña no cumple los requisitos.');
      valido = false;
    }

    if (formRegister.contrasena.value !== password2.value) {
      mostrarErrorCampo(password2, 'Las contraseñas no coinciden.');
      valido = false;
    }
    if (!terminos.checked) {
      const error = document.getElementById('register-terms-error');
      error.textContent = 'Debes aceptar los términos y la política de privacidad.';
      error.hidden = false;
      valido = false;
    }

    if (valido) enviar('register', formRegister);
  });
}

// Si ya hay sesión iniciada, ir directamente al panel
fetch(`${API_AUTH}?action=session`, { credentials: 'same-origin' })
  .then((r) => r.json())
  .then((datos) => {
    if (datos.ok) window.location.href = 'dashboard.html';
  })
  .catch(() => {});
