// Panel del diseñador (dashboard.html). Requiere sesión iniciada.

const API_AUTH = '../backend/auth.php';
const API_PROYECTOS = '../backend/controllers/ProjectController.php';

const ESTADOS = {
    pendiente: 'Pendiente',
    en_progreso: 'En progreso',
    completado: 'Completado',
};

function escapar(texto) {
    const div = document.createElement('div');
    div.textContent = texto ?? '';
    return div.innerHTML;
}

async function cargarSesion() {
    const respuesta = await fetch(`${API_AUTH}?action=session`, { credentials: 'same-origin' });
    const datos = await respuesta.json();
    if (!datos.ok) {
        window.location.href = 'index.html';
        return false;
    }
    document.getElementById('user-name').textContent = datos.usuario.nombre;
    document.getElementById('user-email').textContent = datos.usuario.email;
    document.getElementById('user-plan').textContent = datos.usuario.plan ?? '—';
    return true;
}

async function cargarProyectos() {
    const contenedor = document.getElementById('projects');
    const respuesta = await fetch(`${API_PROYECTOS}?action=list`, { credentials: 'same-origin' });
    const datos = await respuesta.json();

    if (!datos.ok) {
        contenedor.innerHTML = `<p class="muted">${escapar(datos.error)}</p>`;
        return;
    }
    if (datos.proyectos.length === 0) {
        contenedor.innerHTML = '<p class="muted">Todavía no tienes proyectos.</p>';
        return;
    }
    contenedor.innerHTML = datos.proyectos.map((p) => `
        <article class="project-card">
            <div class="project-head">
                <h3>${escapar(p.nombre_logo)}</h3>
                <span class="badge badge-${escapar(p.estado)}">${escapar(ESTADOS[p.estado] ?? p.estado)}</span>
            </div>
            <p>${escapar(p.descripcion)}</p>
            <p class="muted">${[p.sector, p.estilo, p.cliente].filter(Boolean).map(escapar).join(' · ')}</p>
        </article>
    `).join('');
}

document.getElementById('logout').addEventListener('click', async () => {
    await fetch(`${API_AUTH}?action=logout`, { method: 'POST', credentials: 'same-origin' });
    window.location.href = 'index.html';
});

cargarSesion()
    .then((ok) => ok && cargarProyectos())
    .catch(() => {
        document.getElementById('projects').innerHTML =
            '<p class="muted">No se pudo conectar con el servidor.</p>';
    });
