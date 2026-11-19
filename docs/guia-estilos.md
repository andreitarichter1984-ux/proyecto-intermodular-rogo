# Guía de estilos de ROGO

Esta guía recoge los estilos del prototipo de Figma para trasladarlos al código de forma coherente. Todos los valores están definidos como variables CSS en el bloque `:root` del principio de `frontend/css/styles.css`.

**Regla principal:** en el CSS no se escriben colores ni medidas sueltas. Se usa siempre la variable correspondiente, por ejemplo `color: var(--primary);` en lugar de `color: #4338CA;`. Si hace falta un valor nuevo, se añade primero a `:root` y a esta guía.

Los valores se han tomado de las pantallas del prototipo (`docs/mockups`), diseñadas a 1440 px de ancho.

## 1. Colores

### Colores principales

| Variable | Color | Uso |
|---|---|---|
| `--primary` | `#4338CA` | Botones principales, enlaces y elementos activos |
| `--primary-dark` | `#3730A3` | Botón principal al pasar el ratón |
| `--primary-light` | `#EEF2FF` | Fondo del elemento activo del menú lateral y de los avatares |
| `--primary-soft` | `#E9E8FC` | Avisos destacados (por ejemplo, «Café Natura ha completado su briefing») y tarjeta del plan |
| `--accent` | `#6366F1` | Detalles sobre fondo morado |
| `--on-primary` | `#FFFFFF` | Texto sobre fondo morado |

### Fondos y texto

| Variable | Color | Uso |
|---|---|---|
| `--bg` | `#F8F9FC` | Fondo general de la aplicación |
| `--surface` | `#FFFFFF` | Tarjetas, menú lateral, cabecera y formularios |
| `--heading` | `#312999` | Título principal de cada pantalla |
| `--text` | `#1F2937` | Texto normal |
| `--muted` | `#6B7280` | Texto secundario, descripciones y fechas |
| `--border` | `#E5E7EB` | Bordes de tarjetas y campos, separadores |

### Estados

| Variable | Color | Uso |
|---|---|---|
| `--success` / `--success-light` | `#16A34A` / `#DDF7EE` | Briefing completado, acciones correctas |
| `--error` / `--error-light` / `--error-border` | `#DC2626` / `#FEF2F2` / `#FECACA` | Mensajes de error y campos con error |
| `--warning` / `--warning-light` | `#EA580C` / `#FFF7ED` | Pendiente, clientes |
| `--info-light` | `#EFF6FF` | Enviado al cliente, en progreso (con texto `--primary`) |
| `--purple` / `--purple-light` | `#7E22CE` / `#F3E8FF` | Resultados listos |

### Tarjetas de resumen del panel

| Variable | Color | Tarjeta |
|---|---|---|
| `--card-blue` | `#EFF6FF` | Proyectos activos |
| `--card-purple` | `#F5F3FF` | Briefings pendientes |
| `--card-green` | `#ECFDF5` | Resultados listos |
| `--card-orange` | `#FFF7ED` | Clientes |

## 2. Tipografía

Se usa la fuente **Inter** (`--font`) en toda la aplicación. Para cargarla, cada página HTML incluye en el `<head>`:

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
```

| Variable | Tamaño | Peso | Uso |
|---|---|---|---|
| `--fs-h1` | 28 px | Bold (700) | Título de la pantalla («Buenos días, Maria Elena») |
| `--fs-stat` | 30 px | Bold (700) | Números de las tarjetas de resumen |
| `--fs-h2` | 18 px | Semibold (600) | Título de tarjeta o sección («Actividad reciente») |
| `--fs-h3` | 15 px | Semibold (600) | Título de un elemento de lista |
| `--fs-body` | 16 px | Regular (400) | Texto normal y subtítulos |
| `--fs-small` | 14 px | Medium (500) | Botones, campos, etiquetas de formulario y menú |
| `--fs-caption` | 13 px | Regular (400) | Fechas, ayudas y datos secundarios |
| `--fs-badge` | 12 px | Semibold (600) | Etiquetas de estado |

Pesos disponibles: `--fw-regular` (400), `--fw-medium` (500), `--fw-semibold` (600) y `--fw-bold` (700).

## 3. Espaciados

Todos los márgenes y rellenos siguen una escala basada en múltiplos de 4 px:

| Variable | Valor | Uso habitual |
|---|---|---|
| `--space-1` | 4 px | Separación mínima (icono y texto pequeño) |
| `--space-2` | 8 px | Entre elementos muy relacionados |
| `--space-3` | 12 px | Relleno de campos, separación icono-texto en el menú |
| `--space-4` | 16 px | Separación entre tarjetas |
| `--space-5` | 24 px | Relleno del menú lateral, separación entre bloques |
| `--space-6` | 32 px | Relleno del contenido principal |
| `--space-7` | 48 px | Separación entre secciones grandes |

## 4. Bordes y sombras

| Variable | Valor | Uso |
|---|---|---|
| `--radius-sm` | 8 px | Elementos del menú lateral, pestañas y logo |
| `--radius` | 10 px | Botones y campos |
| `--radius-md` | 12 px | Tarjetas |
| `--radius-lg` | 16 px | Tarjeta de acceso y ventanas emergentes |
| `--radius-full` | 999 px | Etiquetas de estado y avatares (forma de píldora o círculo) |
| `--shadow-sm` | `0 1px 3px rgba(17, 24, 39, 0.1)` | Pestaña activa y elementos pequeños elevados |
| `--shadow-md` | `0 10px 30px rgba(17, 24, 39, 0.06)` | Tarjeta de acceso y ventanas emergentes |

Los bordes son siempre de `1px solid var(--border)`. Las tarjetas del panel no llevan sombra, solo borde.

## 5. Componentes

### Botones

Altura de 44 px (`--control-height`), texto de 14 px semibold y esquinas de 10 px.

```html
<button class="btn-primary">+ Nuevo proyecto</button>
<button class="btn-secondary">Cancelar</button>
```

- **Principal** (`.btn-primary`): fondo `--primary`, texto blanco. Al pasar el ratón cambia a `--primary-dark`. Desactivado: 60 % de opacidad.
- **Secundario** (`.btn-secondary`): fondo transparente, borde `--border` y texto `--muted`.

### Campos de formulario

Altura de 44 px, borde `--border`, esquinas de 10 px. Al seleccionarlos se marca un contorno de 2 px de color `--primary`. La etiqueta va encima, a 14 px medium, y la ayuda debajo, a 13 px en `--muted`.

```html
<label for="email">Correo electrónico</label>
<input id="email" type="email">
<small class="hint">Te enviaremos aquí el enlace del briefing.</small>
```

Los errores se muestran con la clase `.alert` (fondo `--error-light`, texto `--error`, borde `--error-border`).

### Tarjetas

Fondo `--surface`, borde `1px solid var(--border)`, esquinas de 12 px y relleno de 20 px. Las tarjetas de resumen del panel usan los fondos `--card-blue`, `--card-purple`, `--card-green` y `--card-orange`.

```html
<article class="project-card">
    <div class="project-head">
        <h3>Identidad Visual — Café Natura</h3>
        <span class="badge badge-completado">Briefing completado</span>
    </div>
    <p class="muted">Briefing respondido · 15 sep 2026</p>
</article>
```

### Etiquetas de estado

Texto de 12 px semibold, forma de píldora (`--radius-full`) y relleno de 4 px × 10 px. La clase depende del estado:

| Clase | Fondo / texto | Estado |
|---|---|---|
| `.badge-pendiente` | `--warning-light` / `--warning` | Pendiente |
| `.badge-en_progreso` | `--info-light` / `--primary` | En progreso |
| `.badge-enviado` | `--info-light` / `--primary` | Enviado al cliente |
| `.badge-completado` | `--success-light` / `--success` | Completado o briefing completado |
| `.badge-resultados` | `--purple-light` / `--purple` | Resultados listos |

```html
<span class="badge badge-enviado">Enviado al cliente</span>
```

### Menú lateral

Ancho de 240 px (`--sidebar-width`), fondo `--surface` y borde derecho `--border`. Cada opción lleva un icono de 18 px y el texto a 14 px medium en `--muted`. La opción activa tiene fondo `--primary-light` y texto `--primary`.

```html
<aside class="sidebar">
    <nav>
        <a href="dashboard.html" class="nav-link active">
            <img src="img/icons/inicio.svg" alt=""> Inicio
        </a>
        <a href="proyectos.html" class="nav-link">
            <img src="img/icons/proyectos.svg" alt=""> Proyectos
        </a>
    </nav>
</aside>
```

En pantallas de menos de 760 px, el menú pasa a la parte superior y las opciones se colocan en horizontal.

## 6. Iconos

Los iconos se exportan del prototipo de Figma en formato **SVG** y se guardan en `frontend/img/icons/`. Se nombran en minúsculas, sin tildes y con guiones (por ejemplo, `nuevo-proyecto.svg`).

| Archivo | Dónde se usa |
|---|---|
| `inicio.svg` | Menú lateral: Inicio |
| `proyectos.svg` | Menú lateral: Proyectos |
| `briefings.svg` | Menú lateral: Briefings |
| `clientes.svg` | Menú lateral: Clientes |
| `resultados.svg` | Menú lateral: Resultados |
| `check.svg` | Briefing completado, resultados listos, respuestas guardadas |
| `mas.svg` | Nuevo proyecto, opción «Otro» |
| `flecha-derecha.svg` | Enlaces «Ver proyectos →», botón «Siguiente» |
| `estrella.svg` | Briefing: identidad de marca |
| `rediseno.svg` | Briefing: rediseño de marca |
| `packaging.svg` | Briefing: packaging |
| `enlace.svg` | Briefing: sitio web y enlace del briefing |
| `compartir.svg` | Briefing: redes sociales y compartir enlace |
| `lapiz.svg` | Escribir mi propia respuesta, editar |
| `ayuda.svg` | ¿Dudas? Escríbele a tu diseñador |

Para que cojan el color del texto, los iconos se exportan con `stroke="currentColor"`. Tamaños: 18 px en el menú, 20 px en botones y 24 px dentro de los círculos de las tarjetas.

## 7. Cómo usar esta guía al programar

1. Antes de maquetar una pantalla, ábrela en Figma y localiza aquí cada color, tamaño y componente.
2. Usa las clases que ya existen (`.btn-primary`, `.project-card`, `.badge`, `.nav-link`…) antes de crear otras nuevas.
3. Si una pantalla necesita un estilo nuevo, créalo en `styles.css` usando solo variables de `:root`.
4. Si aparece un color o una medida que no está en la guía, añádelo a `:root` y a este documento, y avisa al equipo.
