# Soluciones ROGO

## Prototipo navegable (Figma)
[Abrir el prototipo](https://www.figma.com/proto/ygfcfIKq2HGcSJ9i9ytJ9V/Pantallas-Soluciones-Rogo?node-id=0-1&t=6ixCrIz7B3LhyuE6-1)

## Documentación de prototipado

- [Documento Tareas 2.1 y 2.2](docs/docPrototipado/Tarea_2_1_y_2_2_Fase_Prototipado.pdf)
- [Mockups de las pantallas](docs/mockups/)
- [Diagrama de navegación](docs/navegacion/diagrama_navegacion.pdf)
  
## Descripción del proyecto

Soluciones ROGO es un proyecto académico del Ciclo Formativo de Grado Superior en Desarrollo de Aplicaciones Multiplataforma (DAM).

El objetivo es desarrollar una aplicación web que permita a diseñadores gráficos crear briefings guiados, compartirlos con sus clientes y obtener recomendaciones para definir la identidad visual de sus proyectos.

## Funcionalidades previstas

- Creación de briefings guiados para clientes.
- Gestión y consulta de proyectos.
- Selección de tres niveles de servicio: Basic, Medium y Premium.
- Generación de recomendaciones de identidad visual a partir de las respuestas del cliente.

## Tecnologías previstas

- Frontend: HTML, CSS y JavaScript.
- Backend: PHP.
- Base de datos: MySQL.
- Entorno de desarrollo: Visual Studio Code y XAMPP.
- Diseño: Figma.
- Control de versiones: Git y GitHub.

## Equipo de trabajo

- Paula Leppanen Socas: Diseño UX/UI.
- Albert Montesinos i Pérez: Desarrollo backend.
- Andrea Katherine Amador Richter: Desarrollo frontend y coordinación general.
- Hugo Alonso Valencia Dávila: Desarrollo backend.

## Estado del proyecto

Fase de prototipado y primeras iteraciones de desarrollo (en desarrollo).

| Módulo | Estado |
|---|---|
| Base de datos (`database/briefingtool.sql`, 8 tablas y datos de prueba) | Implementado y probado |
| Registro, inicio y cierre de sesión | Implementado y probado |
| Panel del diseñador con sus proyectos | Primera versión |
| Formulario de briefing para el cliente (enlace único) | Pendiente |
| Motor de recomendaciones de identidad visual | Pendiente |
| Exportación del briefing a PDF | Pendiente |

## Estructura del repositorio

```
proyecto-intermodular-rogo/
├── backend/
│   ├── auth.php                  # Registro, login, logout y sesión (JSON)
│   ├── config/
│   │   ├── db.php                # Conexión PDO
│   │   └── db_config.example.php # Plantilla de credenciales
│   └── controllers/              # Proyectos, briefings, clientes y recomendaciones
├── database/
│   └── briefingtool.sql          # Script de creación de la base de datos
├── frontend/
│   ├── index.html                # Inicio de sesión y registro
│   ├── dashboard.html            # Panel del diseñador
│   ├── css/styles.css
│   └── js/                       # auth.js, dashboard.js, briefingForm.js, resultViewer.js
├── Analisis.pdf                  # Análisis heurístico de usabilidad (Entregable 4)
└── README.md
```

## Instalación y ejecución en local

Requisitos: XAMPP 8.2 o superior (Apache, PHP 8.x y MySQL/MariaDB) y Git.

1. Clonar el repositorio dentro de la carpeta `htdocs` de XAMPP:
   ```
   cd C:\xampp\htdocs
   git clone https://github.com/andreitarichter1984-ux/proyecto-intermodular-rogo.git
   ```
2. Abrir el XAMPP Control Panel e iniciar **Apache** y **MySQL**.
3. Crear la base de datos: en http://localhost/phpmyadmin, pestaña **Importar**, seleccionar `database/briefingtool.sql` y pulsar **Continuar**.
4. Copiar `backend/config/db_config.example.php` como `backend/config/db_config.php` y revisar los datos de conexión (en XAMPP: usuario `root` y contraseña vacía). Este archivo no se sube al repositorio.
5. Abrir http://localhost/proyecto-intermodular-rogo/frontend/ y crear una cuenta o entrar con un usuario de prueba:

| Usuario | Contraseña | Rol |
|---|---|---|
| ana@test.com | test1234 | usuario (plan Premium) |
| carlos@rogo.com | rogo1234 | diseñador |

## Flujo de trabajo con Git

- Antes de empezar a trabajar: `git pull` para partir de la última versión.
- Commits pequeños y con mensajes descriptivos.
- No subir credenciales: `db_config.php` está en `.gitignore`.
