-- ============================================================
-- COMANDEX POS — Schema MySQL
-- Colombia 🇨🇴 | Moneda: COP | Zona horaria: America/Bogota
-- ============================================================
CREATE DATABASE IF NOT EXISTS comandex CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE comandex;

-- Sucursales
CREATE TABLE sucursales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    direccion VARCHAR(200),
    telefono VARCHAR(20),
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Roles
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    permisos JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    sucursal_id INT NOT NULL,
    avatar VARCHAR(255),
    activo TINYINT(1) DEFAULT 1,
    ultimo_acceso TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id),
    FOREIGN KEY (sucursal_id) REFERENCES sucursales(id)
);

-- Mesas
CREATE TABLE mesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL,
    zona ENUM('terraza','salon','privado') DEFAULT 'salon',
    capacidad INT DEFAULT 4,
    estado ENUM('libre','ocupada','reservada','por_liberar') DEFAULT 'libre',
    pos_x INT DEFAULT 0,
    pos_y INT DEFAULT 0,
    sucursal_id INT NOT NULL,
    FOREIGN KEY (sucursal_id) REFERENCES sucursales(id)
);

-- Categorias de productos
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    icono VARCHAR(50) DEFAULT '🍽️',
    orden INT DEFAULT 0
);

-- Productos
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    categoria_id INT,
    imagen VARCHAR(255),
    disponible TINYINT(1) DEFAULT 1,
    favorito TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

-- Modificadores de productos (término, guarnición, etc.)
CREATE TABLE modificadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('obligatorio','opcional') DEFAULT 'opcional',
    opciones JSON,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);

-- Turnos
CREATE TABLE turnos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    activo TINYINT(1) DEFAULT 1
);

-- Pedidos
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL,
    mesa_id INT NOT NULL,
    mesero_id INT NOT NULL,
    estado ENUM('pendiente','en_cocina','listo','servido','cancelado') DEFAULT 'pendiente',
    personas INT DEFAULT 1,
    notas_cocina TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mesa_id) REFERENCES mesas(id),
    FOREIGN KEY (mesero_id) REFERENCES usuarios(id)
);

-- Items de pedido
CREATE TABLE pedido_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL,
    notas TEXT,
    modificadores_sel JSON,
    estado ENUM('pendiente','en_preparacion','listo') DEFAULT 'pendiente',
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- Pagos
CREATE TABLE pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    metodo ENUM('efectivo','tarjeta','transferencia','qr') NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    recibido DECIMAL(10,2),
    cambio DECIMAL(10,2) DEFAULT 0,
    referencia VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id)
);

-- Actividad reciente (log)
CREATE TABLE actividad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    tipo VARCHAR(50),
    descripcion TEXT,
    entidad VARCHAR(50),
    entidad_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Configuración del sistema (solo Administrador)
CREATE TABLE configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(80) NOT NULL UNIQUE,
    valor TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- DATOS DE PRUEBA
-- ============================================================

INSERT INTO sucursales (nombre, direccion, telefono) VALUES
('Sede Principal',  'Cra 7 # 32-10, Bogotá, Colombia',  '+57 601 234 5678'),
('Sucursal Norte',  'Av. 19 # 120-51, Bogotá, Colombia', '+57 601 765 4321'),
('Sede Medellín',   'El Poblado, Cra 33 # 7-40, Medellín','+57 604 555 4444');

INSERT INTO roles (nombre, permisos) VALUES
('Administrador', '["dashboard","mesero","cocina","pagos","admin","mesas","reportes"]'),
('Gerente', '["dashboard","mesero","cocina","pagos","mesas","reportes"]'),
('Mesero', '["mesero","mesas","pedidos"]'),
('Cocina', '["cocina"]'),
('Cajero', '["pagos","mesas"]'),
('Invitado', '["dashboard"]');

-- password: Admin1234 (bcrypt)
INSERT INTO usuarios (nombre, email, password, rol_id, sucursal_id) VALUES
('Diego Ramírez',    'diego.ramirez@laterraza.com',   '$2y$12$YQkqKJZfI3lJ3v7DH9NFeOzrVAEsGrHvUW0RRkA7s7VxH/EV.1JWS', 1, 1),
('Ana López',        'ana.lopez@laterraza.com',        '$2y$12$YQkqKJZfI3lJ3v7DH9NFeOzrVAEsGrHvUW0RRkA7s7VxH/EV.1JWS', 2, 1),
('Carlos Méndez',    'carlos.mendez@laterraza.com',    '$2y$12$YQkqKJZfI3lJ3v7DH9NFeOzrVAEsGrHvUW0RRkA7s7VxH/EV.1JWS', 4, 1),
('Luis Vargas',      'luis.vargas@laterraza.com',      '$2y$12$YQkqKJZfI3lJ3v7DH9NFeOzrVAEsGrHvUW0RRkA7s7VxH/EV.1JWS', 3, 1),
('María Gómez',      'maria.gomez@laterraza.com',      '$2y$12$YQkqKJZfI3lJ3v7DH9NFeOzrVAEsGrHvUW0RRkA7s7VxH/EV.1JWS', 3, 2),
('Jorge Ruiz',       'jorge.ruiz@laterraza.com',       '$2y$12$YQkqKJZfI3lJ3v7DH9NFeOzrVAEsGrHvUW0RRkA7s7VxH/EV.1JWS', 5, 2),
('Paula Torres',     'paula.torres@laterraza.com',     '$2y$12$YQkqKJZfI3lJ3v7DH9NFeOzrVAEsGrHvUW0RRkA7s7VxH/EV.1JWS', 4, 2),
('Fernanda Soto',    'fernanda.soto@laterraza.com',    '$2y$12$YQkqKJZfI3lJ3v7DH9NFeOzrVAEsGrHvUW0RRkA7s7VxH/EV.1JWS', 6, 3);

INSERT INTO turnos (nombre, hora_inicio, hora_fin) VALUES
('Matutino', '07:00:00', '15:00:00'),
('Vespertino', '15:00:00', '22:00:00'),
('Nocturno', '22:00:00', '07:00:00');

INSERT INTO mesas (numero, zona, capacidad, estado, pos_x, pos_y, sucursal_id) VALUES
(1,  'salon', 2, 'libre',       60,  60,  1),
(2,  'salon', 4, 'ocupada',     200, 60,  1),
(3,  'salon', 2, 'libre',       340, 60,  1),
(4,  'salon', 4, 'libre',       60,  200, 1),
(5,  'salon', 4, 'ocupada',     200, 200, 1),
(6,  'salon', 6, 'reservada',   340, 200, 1),
(7,  'salon', 2, 'ocupada',     60,  340, 1),
(8,  'salon', 4, 'por_liberar', 200, 340, 1),
(9,  'salon', 2, 'libre',       340, 340, 1),
(10, 'salon', 2, 'ocupada',     60,  480, 1),
(11, 'salon', 2, 'libre',       200, 480, 1),
(12, 'salon', 6, 'ocupada',     340, 480, 1);

INSERT INTO categorias (nombre, icono, orden) VALUES
('Favoritos',      '⭐', 0),
('Entradas',       '🥗', 1),
('Platos fuertes', '🍖', 2),
('Pastas',         '🍝', 3),
('Bebidas',        '🥤', 4),
('Postres',        '🍰', 5);

-- Precios en COP (Pesos colombianos)
INSERT INTO productos (nombre, descripcion, precio, categoria_id, disponible, favorito) VALUES
('Tiradito de atún',      'Atún fresco marinado con leche de tigre y ají amarillo',     42000, 1, 1, 1),
('Pulpo a las brasas',    'Pulpo mediterráneo con papas trufadas y pimentón',           58000, 1, 1, 1),
('Ensalada de la casa',   'Mix de lechugas, cherry, queso de cabra y vinagreta',        28000, 1, 1, 0),
('Ribeye madurado',       'Corte de ribeye 300g, madurado 28 días, con vegetales asados',95000, 3, 1, 1),
('Salmón al grill',       'Salmón atlántico con mantequilla de hierbas y limón',        68000, 3, 1, 1),
('Pasta al burro',        'Tagliatelle artesanal con mantequilla, parmesano y trufa',   38000, 4, 1, 0),
('Risotto de setas',      'Arroz arbóreo cremoso con mezcla de setas silvestres',       42000, 4, 1, 0),
('Agua natural',          'Agua mineral natural 500ml',                                  5000, 5, 1, 0),
('Limonada mineral',      'Limonada fresca con agua mineral y menta',                   9000, 5, 1, 0),
('Bruschetta de tomate',  'Pan artesanal tostado con tomate cherry y albahaca',         18000, 2, 1, 0),
('Tartar de atún',        'Atún rojo con aguacate, soya y semillas de sésamo',           44000, 2, 1, 0),
('Cheesecake de frutos',  'Tarta de queso con coulis de frutos rojos',                  22000, 6, 1, 0),
('Café americano',        'Café de especialidad origen único',                            9000, 5, 1, 0),
('Hamburguesa Especial',  '200g res wagyu, queso brie, cebolla caramelizada',           48000, 3, 1, 0),
('Pollo al limón',        'Pechuga de pollo en salsa de limón y alcáparras',            32000, 3, 1, 0),
('Batido de fresa',       'Batido natural de fresa con leche entera',                  12000, 5, 1, 0);

INSERT INTO modificadores (producto_id, nombre, tipo, opciones) VALUES
(4, 'Término de cocción', 'obligatorio', '["Azul","Rojo","3/4","Medio","Bien cocido"]'),
(4, 'Guarnición', 'opcional', '["Papas a la francesa","Puré de papa","Ensalada verde","Vegetales asados"]'),
(5, 'Término', 'obligatorio', '["Término medio","Bien cocido"]'),
(14,'Término', 'obligatorio', '["3/4","Bien cocido"]');

-- Pedidos de ejemplo
INSERT INTO pedidos (numero, mesa_id, mesero_id, estado, personas) VALUES
(1022, 7,  4, 'en_cocina', 2),
(1023, 11, 2, 'listo',     3),
(1024, 2,  2, 'listo',     4),
(1025, 5,  4, 'en_cocina', 2),
(1026, 8,  3, 'en_cocina', 3),
(1027, 5,  2, 'en_cocina', 4),
(1028, 12, 3, 'en_cocina', 6);

INSERT INTO pedido_items (pedido_id, producto_id, cantidad, precio_unitario, notas) VALUES
(1, 8,  2, 45.00,  NULL),
(1, 9,  1, 60.00,  NULL),
(2, 12, 1, 120.00, NULL),
(2, 13, 2, 55.00,  NULL),
(3, 6,  1, 220.00, NULL),
(3, 10, 1, 95.00,  NULL),
(4, 1,  1, 210.00, 'Sin cilantro'),
(4, 4,  1, 560.00, NULL),
(5, 4,  2, 560.00, NULL),
(6, 5,  1, 340.00, NULL),
(7, 1,  1, 210.00, NULL),
(7, 4,  1, 560.00, NULL),
(7, 9,  1, 60.00,  NULL),
(7, 8,  1, 45.00,  NULL);

INSERT INTO actividad (usuario_id, tipo, descripcion, entidad, entidad_id) VALUES
(3, 'pedido_nuevo',    'Nuevo pedido #1028 enviado a cocina',             'pedido', 7),
(4, 'pedido_listo',    'Pedido #1025 marcado como servido',               'pedido', 4),
(5, 'pago',            'Pago registrado — Mesa 03 · $ 280.000',           'pago',   1),
(2, 'mesa_asignada',   'Mesa 15 asignada a Ana López · 2 comensales',     'mesa',   5),
(3, 'pedido_cancel',   'Pedido #1023 cancelado · Mesa 09 · 1 platillo',   'pedido', 2));

-- Configuración inicial Colombia
INSERT IGNORE INTO configuracion (clave, valor) VALUES
('nombre_negocio',      'Mi Restaurante'),
('nit',                 ''),
('direccion',           ''),
('telefono',            ''),
('email_negocio',       ''),
('iva_porcentaje',      '19'),
('propina_porcentaje',  '10'),
('turno_activo_nombre', 'Turno Activo'),
('moneda',              'COP'),
('zona_horaria',        'America/Bogota'),
('modo_operacion',      'normal'),
('horario_apertura',    '07:00'),
('horario_cierre',      '22:00'),
('dias_operacion',      'Lun–Dom'),
('aviso_cocina_minutos','15'),
('items_por_pagina',    '20'),
('footer_recibo',       '¡Gracias por su visita!'),
('mensaje_bienvenida',  'Bienvenido a nuestro sistema POS');
