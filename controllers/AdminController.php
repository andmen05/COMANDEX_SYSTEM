<?php
// controllers/AdminController.php
class AdminController {
    private User $userModel;
    private Producto $productoModel;

    public function __construct() {
        $this->userModel     = new User();
        $this->productoModel = new Producto();
    }

    public function index(): void {
        requireLogin();
        requireRole(['Administrador', 'Gerente']);

        $tab      = $_GET['tab'] ?? 'usuarios';
        $search   = $_GET['search'] ?? '';
        $usuarios = $this->userModel->getAll(['search' => $search]);
        $productos = $this->productoModel->getAll(0, false);
        $categorias = $this->productoModel->getCategorias();

        // POST: nuevo usuario
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accion = $_POST['accion'] ?? '';

            if ($accion === 'crear_usuario') {
                $nuevoId = $this->userModel->create([
                    'nombre'      => $_POST['nombre'],
                    'email'       => $_POST['email'],
                    'password'    => $_POST['password'],
                    'rol_id'      => (int)$_POST['rol_id'],
                    'sucursal_id' => (int)($_POST['sucursal_id'] ?? 1),
                ]);
                // Auditoría: inserción de nuevo usuario
                $admin = $_SESSION['user_id'] ?? 'desconocido';
                logger()->info("USUARIO_CREADO — nuevo_id:{$nuevoId} email:{$_POST['email']} por_admin:{$admin}");
                redirect('admin?tab=usuarios&ok=1');
            }

            if ($accion === 'crear_producto') {
                $prodId = $this->productoModel->create([
                    'nombre'       => $_POST['nombre'],
                    'descripcion'  => $_POST['descripcion'] ?? '',
                    'precio'       => (float)$_POST['precio'],
                    'categoria_id' => (int)$_POST['categoria_id'],
                    'disponible'   => isset($_POST['disponible']) ? 1 : 0,
                    'favorito'     => isset($_POST['favorito']) ? 1 : 0,
                ]);
                // Auditoría: inserción de nuevo producto
                $admin = $_SESSION['user_id'] ?? 'desconocido';
                logger()->info("PRODUCTO_CREADO — nuevo_id:{$prodId} nombre:{$_POST['nombre']} precio:{$_POST['precio']} por_admin:{$admin}");
                redirect('admin?tab=productos&ok=1');
            }
        }

        $db   = getPDO();
        $roles = $db->query("SELECT * FROM roles ORDER BY nombre")->fetchAll();
        $sucursales = $db->query("SELECT * FROM sucursales")->fetchAll();
        $stats = [
            'usuarios'  => $this->userModel->count(),
            'roles'     => count($roles),
            'productos' => $this->productoModel->count(),
        ];

        view('admin/index', compact('tab','usuarios','productos','categorias','roles','sucursales','stats','search'));
    }

    // ─── CONFIGURACIÓN DEL SOFTWARE ────────────────────────────────────────────
    public function configuracion(): void {
        requireLogin();
        requireRole(['Administrador']);   // ← SOLO el admin principal

        $db  = getPDO();
        $msg = '';
        $err = '';

        // Guardar configuración
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $campos = [
                'nombre_negocio', 'nit', 'direccion', 'telefono', 'email_negocio',
                'iva_porcentaje', 'propina_porcentaje', 'turno_activo_nombre',
                'moneda', 'zona_horaria', 'modo_operacion',
                'horario_apertura', 'horario_cierre', 'dias_operacion',
                'aviso_cocina_minutos', 'items_por_pagina',
                'footer_recibo', 'mensaje_bienvenida',
            ];
            foreach ($campos as $clave) {
                if (!isset($_POST[$clave])) continue;
                $valor = trim($_POST[$clave]);
                // upsert
                $db->prepare("
                    INSERT INTO configuracion (clave, valor)
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE valor = VALUES(valor), updated_at = NOW()
                ")->execute([$clave, $valor]);
            }
            // Limpiar caché de config en sesión
            unset($_SESSION['_config']);
            $msg = 'Configuración guardada correctamente.';
        }

        // Leer config actual
        $config = $db->query("SELECT clave, valor FROM configuracion")->fetchAll(PDO::FETCH_KEY_PAIR);
        // Defaults Colombia
        $defaults = [
            'nombre_negocio'       => 'Mi Restaurante',
            'nit'                  => '',
            'direccion'            => '',
            'telefono'             => '',
            'email_negocio'        => '',
            'iva_porcentaje'       => '19',
            'propina_porcentaje'   => '10',
            'turno_activo_nombre'  => 'Turno Activo',
            'moneda'               => 'COP',
            'zona_horaria'         => 'America/Bogota',
            'modo_operacion'       => 'normal',
            'horario_apertura'     => '07:00',
            'horario_cierre'       => '22:00',
            'dias_operacion'       => 'Lun–Dom',
            'aviso_cocina_minutos' => '15',
            'items_por_pagina'     => '20',
            'footer_recibo'        => '¡Gracias por su visita!',
            'mensaje_bienvenida'   => 'Bienvenido a nuestro sistema POS',
        ];
        foreach ($defaults as $k => $v) {
            if (!array_key_exists($k, $config)) $config[$k] = $v;
        }

        // Leer turnos
        $turnos = $db->query("SELECT * FROM turnos ORDER BY id")->fetchAll();

        view('admin/configuracion', compact('config','turnos','msg','err'));
    }
}
