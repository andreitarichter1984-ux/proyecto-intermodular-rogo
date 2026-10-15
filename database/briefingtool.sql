CREATE DATABASE IF NOT EXISTS briefingtool CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE briefingtool;

-- -------------------------------------------------------
-- Tabla: Plan
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS Plan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio_mensual DECIMAL(8,2) DEFAULT 0.00
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Tabla: Cliente
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS Cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150),
    telefono VARCHAR(20),
    empresa VARCHAR(150)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Tabla: Usuario  (incluye diseñadores: rol = 'disenador')
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS Usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_plan INT DEFAULT 1,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    rol ENUM('usuario','disenador') NOT NULL DEFAULT 'usuario',
    portafolio_url VARCHAR(255),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) DEFAULT 1,
    CONSTRAINT fk_usuario_plan
        FOREIGN KEY (id_plan) REFERENCES Plan(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Tabla: Proyecto
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS Proyecto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_disenador INT,
    id_cliente INT,
    nombre_logo VARCHAR(150) NOT NULL,
    descripcion TEXT,
    colores VARCHAR(255),
    sector VARCHAR(100),
    estilo VARCHAR(100),
    referencias TEXT,
    estado VARCHAR(50) NOT NULL DEFAULT 'pendiente',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_proyecto_usuario
        FOREIGN KEY (id_usuario) REFERENCES Usuario(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_proyecto_disenador
        FOREIGN KEY (id_disenador) REFERENCES Usuario(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_proyecto_cliente
        FOREIGN KEY (id_cliente) REFERENCES Cliente(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_proyecto_usuario (id_usuario)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Tabla: Briefing
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS Briefing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_proyecto INT NOT NULL UNIQUE,
    titulo VARCHAR(150) NOT NULL,
    enlaceUnico VARCHAR(64) NOT NULL UNIQUE,
    estado VARCHAR(50) DEFAULT 'pendiente',
    fechaEnvio TIMESTAMP NULL,
    fechaRespuesta TIMESTAMP NULL,
    CONSTRAINT fk_briefing_proyecto
        FOREIGN KEY (id_proyecto) REFERENCES Proyecto(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Tabla: Pregunta
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS Pregunta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_plan INT,
    texto TEXT NOT NULL,
    tipo VARCHAR(50),
    orden INT DEFAULT 0,
    CONSTRAINT fk_pregunta_plan
        FOREIGN KEY (id_plan) REFERENCES Plan(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Tabla: Respuesta
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS Respuesta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_briefing INT NOT NULL,
    id_pregunta INT NOT NULL,
    valor TEXT,
    imagenRef VARCHAR(255),
    fechaRegistro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_respuesta_briefing
        FOREIGN KEY (id_briefing) REFERENCES Briefing(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_respuesta_pregunta
        FOREIGN KEY (id_pregunta) REFERENCES Pregunta(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Tabla: Recomendacion
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS Recomendacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_briefing INT NOT NULL,
    estiloVisual VARCHAR(150),
    tipografia VARCHAR(150),
    paletaColores VARCHAR(255),
    enfoqueConceptual TEXT,
    CONSTRAINT fk_recomendacion_briefing
        FOREIGN KEY (id_briefing) REFERENCES Briefing(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Datos de prueba
-- -------------------------------------------------------
INSERT IGNORE INTO Plan (nombre, descripcion, precio_mensual) VALUES
('Basic',   'Plan básico con funciones esenciales',  0.00),
('Medium',  'Plan intermedio con más opciones',      9.99),
('Premium', 'Acceso completo a todas las funciones', 19.99);

INSERT IGNORE INTO Pregunta (id, texto, tipo, orden) VALUES
(1,  '¿Cuál es el nombre de tu empresa o proyecto?',                     'texto', 1),
(2,  '¿A qué sector pertenece tu negocio?',                              'texto', 2),
(3,  '¿Cuál es el público objetivo principal?',                          'texto', 3),
(4,  '¿Qué valores o conceptos quieres transmitir con tu marca?',        'texto', 4),
(5,  '¿Tienes preferencias de colores o colores que quieras evitar?',    'texto', 5),
(6,  '¿Tienes referencias visuales de logos que te gusten?',             'texto', 6),
(7,  '¿Qué estilo gráfico prefieres? (Moderno, clásico, minimalista)',   'texto', 7),
(8,  '¿El logo llevará solo símbolo, solo texto, o ambos?',              'texto', 8),
(9,  '¿Dónde se usará principalmente el logo? (Web, redes, impresión)',  'texto', 9),
(10, '¿Tienes algún plazo de entrega o consideración adicional?',        'texto', 10);

INSERT IGNORE INTO Usuario (id_plan, nombre, email, contrasena, rol) VALUES
(3, 'Ana Garcia',   'ana@test.com',     SHA2('test1234', 256), 'usuario'),
(1, 'Luis Torres',  'luis@test.com',    SHA2('test1234', 256), 'usuario'),
(1, 'Carlos Ruiz',  'carlos@rogo.com',  SHA2('rogo1234', 256), 'disenador');

INSERT IGNORE INTO Proyecto (id_usuario, id_disenador, nombre_logo, descripcion, sector, estilo, estado) VALUES
(1, 3, 'Marca NovaTech', 'Identidad visual para startup tecnologica', 'Tecnologia', 'Moderno/Minimalista', 'en_progreso');

SELECT 'Base de datos BriefingTool creada correctamente.' AS resultado;
