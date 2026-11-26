// Panel del diseñador (dashboard.html). Requiere sesión iniciada.
// Pantallas del Figma: 03A (primer acceso, sin proyectos) y 03 (con proyectos).
// Para revisar cada versión sin cambiar la base de datos:
//   dashboard.html?demo=vacio   |   dashboard.html?demo=con

const API_AUTH = '../backend/auth.php';
const API_PROYECTOS = '../backend/controllers/ProjectController.php';

const ESTADOS = {
    pendiente: { texto: 'Enviado al cliente', clase: 'enviado', icono: 'briefings' },
    en_progreso: { texto: 'En progreso', clase: 'en_progreso', icono: 'lapiz' },
    completado: { texto: 'Briefing completado', clase: 'completado', icono: 'check' },
};

const PROYECTOS_DEMO = [
    { nombre_logo: 'Identidad Visual', cliente: 'Café Natura', estado: 'completado', fecha_creacion: '2026-09-15' },
    { nombre_logo: 'Diseño de Packaging', cliente: 'EcoLife', estado: 'pendiente', fecha_creacion: '2026-09-12' },
    { nombre_logo: 'Branding Completo', cliente: 'Natura', estado: 'en_progreso', fecha_creacion: '2026-09-10' },
];

function escapar(texto) {
    const div = document.createElement('div');
    div.textContent = texto ?? '';
    return div.innerHTML;
}

function iniciales(nombre) {
    return (nombre || '?').trim().split(/\s+/).slice(0, 2).map((p) => p[0].toUpperCase()).join('');
}

function formatearFecha(texto) {
    const fecha = new Date(texto);
    if (Number.isNaN(fecha.getTime())) return '';
    return fecha.toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric' });
}

function haceCuanto(texto) {
    const fecha = new Date(texto);
    if (Number.isNaN(fecha.getTime())) return '';
    const dias = Math.floor((Date.now() - fecha.getTime()) / 86400000);
    if (dias <= 0) return 'Hoy';
    if (dias === 1) return 'Ayer';
    return `Hace ${dias} días`;
}

function pintarUsuario(usuario) {
    const nombre = usuario.nombre;
    const primerNombre = nombre.split(' ')[0];
    document.querySelectorAll('.js-name').forEach((el) => { el.textContent = nombre; });
    document.getElementById('user-name-side').textContent = nombre;
    document.getElementById('user-name-top').textContent = nombre;
    document.getElementById('user-initials').textContent = iniciales(nombre);
    document.getElementById('user-initials-top').textContent = iniciales(nombre);
    document.getElementById('user-plan').textContent = usuario.plan ?? '—';
    document.title = `ROGO · Panel de ${primerNombre}`;
}

function pintarSinProyectos() {
    document.getElementById('view-empty').hidden = false;
}

function pintarConProyectos(proyectos) {
    document.getElementById('view-projects').hidden = false;

    const completados = proyectos.filter((p) => p.estado === 'completado');
    const pendientes = proyectos.filter((p) => p.estado === 'pendiente');
    const clientes = new Set(proyectos.map((p) => p.cliente).filter(Boolean));

    document.getElementById('stat-activos').textContent = proyectos.length - completados.length;
    document.getElementById('stat-pendientes').textContent = pendientes.length;
    document.getElementById('stat-listos').textContent = completados.length;
    document.getElementById('stat-clientes').textContent = clientes.size;

    if (completados.length > 0) {
        const ultimo = completados[0];
        document.getElementById('notice-title').textContent =
            `${ultimo.cliente || ultimo.nombre_logo} ha completado su briefing`;
        document.getElementById('notice').hidden = false;
    }

    // Actividad reciente: se deduce de los proyectos más recientes.
    document.getElementById('activity').innerHTML = proyectos.slice(0, 4).map((p) => {
        const info = ESTADOS[p.estado] ?? { texto: p.estado, clase: 'en_progreso', icono: 'lapiz' };
        return `
        <li>
            <span class="dot dot-${escapar(info.clase)}"><span class="icon" style="--icon:url('../img/icons/${info.icono ?? 'check'}.svg')"></span></span>
            <div><strong>${escapar(info.texto)}</strong><span>${escapar(p.nombre_logo)}${p.cliente ? ' — ' + escapar(p.cliente) : ''}</span></div>
            <time>${escapar(haceCuanto(p.fecha_creacion))}</time>
        </li>`;
    }).join('');

    document.getElementById('recent').innerHTML = proyectos.slice(0, 3).map((p) => {
        const info = ESTADOS[p.estado] ?? { texto: p.estado, clase: 'en_progreso', icono: 'lapiz' };
        return `
        <li>
            <span class="avatar avatar-soft">${escapar(iniciales(p.nombre_logo))}</span>
            <div><strong>${escapar(p.nombre_logo)}${p.cliente ? ' — ' + escapar(p.cliente) : ''}</strong><span>${escapar(formatearFecha(p.fecha_creacion))}</span></div>
            <span class="badge badge-${escapar(info.clase)}">${escapar(info.texto)}</span>
        </li>`;
    }).join('');
}

async function iniciar() {
    const demo = new URLSearchParams(window.location.search).get('demo');

    const sesion = await fetch(`${API_AUTH}?action=session`, { credentials: 'same-origin' });
    const datosSesion = await sesion.json();
    if (!datosSesion.ok) {
        window.location.href = 'index.html';
        return;
    }
    pintarUsuario(datosSesion.usuario);

    if (demo === 'vacio') return pintarSinProyectos();
    if (demo === 'con') return pintarConProyectos(PROYECTOS_DEMO);

    const respuesta = await fetch(`${API_PROYECTOS}?action=list`, { credentials: 'same-origin' });
    const datos = await respuesta.json();
    if (!datos.ok) {
        mostrarError(datos.error);
        return;
    }
    if (datos.proyectos.length === 0) {
        pintarSinProyectos();
    } else {
        pintarConProyectos(datos.proyectos);
    }
}

function mostrarError(mensaje) {
    const el = document.getElementById('dash-error');
    el.textContent = mensaje;
    el.hidden = false;
}

document.getElementById('logout').addEventListener('click', async () => {
    await fetch(`${API_AUTH}?action=logout`, { method: 'POST', credentials: 'same-origin' });
    window.location.href = 'index.html';
});

iniciar().catch(() => mostrarError('No se pudo conectar con el servidor.'));
