<?php
// views/layout/app.php — Layout maestro con dark mode + control de acceso por rol
$user    = currentUser();
$baseUrl = BASE_URL;

// Permisos del usuario actual (desde la sesión, guardados al login)
$permisos = $_SESSION['permisos'] ?? ['dashboard'];

// Mapa completo de módulos con icono, label y clave de permiso
$allModules = [
    'dashboard'     => ['icon' => 'layout-dashboard', 'label' => 'Main',          'url' => '/dashboard'],
    'mesero'        => ['icon' => 'user',              'label' => 'Mesero',        'url' => '/mesero'],
    'cocina'        => ['icon' => 'chef-hat',          'label' => 'Cocina',        'url' => '/cocina'],
    'pagos'         => ['icon' => 'credit-card',       'label' => 'Pagos',         'url' => '/pagos'],
    'mesas'         => ['icon' => 'layout-grid',       'label' => 'Mesas',         'url' => '/mesas'],
    'reportes'      => ['icon' => 'bar-chart-2',       'label' => 'Reportes',      'url' => '/reportes'],
    'admin'         => ['icon' => 'settings-2',        'label' => 'Admin',         'url' => '/admin'],
    'configuracion' => ['icon' => 'sliders-horizontal','label' => 'Configuración', 'url' => '/configuracion'],
];
$esAdmin = ($_SESSION['user_rol'] ?? '') === 'Administrador';
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Comandex') ?> — Comandex POS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>
        /* ═══════════════════════ VARIABLES ═══════════════════════ */
        :root {
            --bg:        #f8fafc;
            --surface:   #ffffff;
            --border:    #f1f5f9;
            --border2:   #e2e8f0;
            --text-1:    #0f172a;
            --text-2:    #374151;
            --text-3:    #64748b;
            --text-4:    #94a3b8;
            --sidebar-bg:#0f172a;
            --sidebar-link:#94a3b8;
            --sidebar-border: rgba(255,255,255,0.07);
            --topbar-bg: #ffffff;
            --card-shadow: 0 1px 3px rgba(0,0,0,0.04);
            --card-hover:  0 4px 12px rgba(0,0,0,0.08);
            --input-bg:  #ffffff;
            --table-hover:#f8fafc;
        }
        [data-theme="dark"] {
            --bg:        #0d1117;
            --surface:   #161b22;
            --border:    #21262d;
            --border2:   #30363d;
            --text-1:    #f0f6fc;
            --text-2:    #c9d1d9;
            --text-3:    #8b949e;
            --text-4:    #6e7681;
            --sidebar-bg:#010409;
            --sidebar-link:#8b949e;
            --sidebar-border: rgba(255,255,255,0.06);
            --topbar-bg: #161b22;
            --card-shadow: 0 1px 3px rgba(0,0,0,0.3);
            --card-hover:  0 4px 16px rgba(0,0,0,0.4);
            --input-bg:  #0d1117;
            --table-hover:#1c2128;
        }

        /* ═══════════════════════ BASE ═══════════════════════ */
        * { font-family: 'Inter', system-ui, sans-serif; box-sizing: border-box; margin: 0; padding: 0; }
        body { background: var(--bg); color: var(--text-2); transition: background 0.25s, color 0.25s; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 99px; }

        /* ═══════════════════════ SIDEBAR ═══════════════════════ */
        .sidebar {
            width: 220px; flex-shrink: 0;
            background: var(--sidebar-bg);
            display: flex; flex-direction: column;
            height: 100vh; overflow-y: auto;
            transition: background 0.25s;
        }
        .sidebar-link {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 16px; border-radius: 12px;
            color: var(--sidebar-link); font-size: 14px; font-weight: 500;
            text-decoration: none; transition: all 0.15s ease; margin-bottom: 2px;
        }
        .sidebar-link:hover  { color: #fff; background: rgba(255,255,255,0.08); }
        .sidebar-link.active { color: #fff; background: #2563eb; box-shadow: 0 4px 14px rgba(37,99,235,0.35); }
        .sidebar-link svg    { width: 17px; height: 17px; flex-shrink: 0; }

        /* ═══════════════════════ CARDS ═══════════════════════ */
        .card {
            background: var(--surface); border-radius: 16px; padding: 24px;
            border: 1px solid var(--border); box-shadow: var(--card-shadow);
            transition: box-shadow 0.2s, background 0.25s, border-color 0.25s;
        }
        .card:hover { box-shadow: var(--card-hover); }

        /* ═══════════════════════ BUTTONS ═══════════════════════ */
        .btn-primary {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 10px 18px; background: #2563eb; color: #fff;
            border-radius: 12px; font-size: 14px; font-weight: 600;
            border: none; cursor: pointer; text-decoration: none;
            transition: background 0.15s; white-space: nowrap;
        }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-secondary {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 10px 18px; background: var(--surface); color: var(--text-2);
            border-radius: 12px; font-size: 14px; font-weight: 500;
            border: 1px solid var(--border2); cursor: pointer; text-decoration: none;
            transition: background 0.15s; white-space: nowrap;
        }
        .btn-secondary:hover { background: var(--bg); }
        .btn-success {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 10px 18px; background: #059669; color: #fff;
            border-radius: 12px; font-size: 14px; font-weight: 600;
            border: none; cursor: pointer; text-decoration: none; transition: background 0.15s;
        }
        .btn-success:hover { background: #047857; }
        .btn-danger {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 10px 18px; background: #dc2626; color: #fff;
            border-radius: 12px; font-size: 14px; font-weight: 600;
            border: none; cursor: pointer; text-decoration: none; transition: background 0.15s;
        }
        .btn-danger:hover { background: #b91c1c; }

        /* ═══════════════════════ BADGES ═══════════════════════ */
        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 99px; font-size: 12px; font-weight: 600; }
        .badge-libre      { background: #f1f5f9; color: #64748b; }
        .badge-ocupada    { background: #d1fae5; color: #065f46; }
        .badge-reservada  { background: #fef3c7; color: #92400e; }
        .badge-por_liberar{ background: #fee2e2; color: #991b1b; }
        .badge-urgente    { background: #fee2e2; color: #dc2626; }
        .badge-prep       { background: #fef3c7; color: #d97706; }
        .badge-listo      { background: #d1fae5; color: #059669; }
        .badge-blue       { background: #dbeafe; color: #1d4ed8; }

        /* ═══════════════════════ FORMS ═══════════════════════ */
        input, select, textarea {
            background: var(--input-bg) !important;
            color: var(--text-2) !important;
            border-color: var(--border2) !important;
            transition: background 0.25s, border-color 0.25s, color 0.25s;
        }
        input:focus, select:focus, textarea:focus {
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.18) !important;
            border-color: #3b82f6 !important;
        }
        input::placeholder, textarea::placeholder { color: var(--text-4) !important; }

        /* ═══════════════════════ TABLES ═══════════════════════ */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-4); font-weight: 600; padding: 0 12px 12px; text-align: left; }
        .data-table td { padding: 14px 12px; border-top: 1px solid var(--border); font-size: 14px; color: var(--text-2); }
        .data-table tr:hover td { background: var(--table-hover); }

        /* ═══════════════════════ TABS ═══════════════════════ */
        .tab-link { padding: 12px 20px; font-size: 14px; font-weight: 500; border-bottom: 2px solid transparent; color: var(--text-3); text-decoration: none; transition: all 0.15s; }
        .tab-link:hover  { color: var(--text-2); }
        .tab-link.active { border-bottom-color: #2563eb; color: #2563eb; }

        /* ═══════════════════════ KANBAN ═══════════════════════ */
        .kanban-card {
            background: var(--surface); border-radius: 16px; padding: 16px;
            border: 1px solid var(--border); box-shadow: var(--card-shadow);
            transition: box-shadow 0.2s, background 0.25s;
        }
        .kanban-card:hover { box-shadow: var(--card-hover); }

        /* ═══════════════════════ TOPBAR ═══════════════════════ */
        .topbar {
            background: var(--topbar-bg);
            border-bottom: 1px solid var(--border);
            transition: background 0.25s, border-color 0.25s;
        }

        /* ═══════════════════════ DARK MODE TOGGLE ═══════════════════════ */
        .theme-toggle {
            width: 38px; height: 38px; border-radius: 12px;
            border: 1px solid var(--border2);
            background: var(--surface);
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s; flex-shrink: 0;
            position: relative; overflow: hidden;
        }
        .theme-toggle:hover { background: var(--bg); transform: scale(1.05); }
        .theme-toggle svg   { width: 18px; height: 18px; color: var(--text-3); transition: all 0.2s; }

        /* ═══════════════════════ MODALS ═══════════════════════ */
        [id^="m-"] .modal-box {
            background: var(--surface);
            border: 1px solid var(--border2);
        }

        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
        @keyframes spin   { to{transform:rotate(360deg)} }
    </style>
</head>
<body style="display:flex; height:100vh; overflow:hidden;">

<!-- ═══════════════════════════ SIDEBAR ═══════════════════════════ -->
<aside class="sidebar">
    <!-- Logo Comandex -->
    <div style="padding:20px 16px 18px; border-bottom:1px solid var(--sidebar-border);">
        <div style="display:flex; align-items:center; gap:11px;">
            <!-- Ícono del logo (PNG con fondo transparente) -->
            <div style="width:46px;height:46px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <img src="<?= BASE_URL ?>/assets/img/logo-icon.png"
                     alt="Comandex"
                     style="width:46px;height:46px;object-fit:contain;display:block;"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                <!-- Fallback si no carga la imagen -->
                <span style="display:none;width:44px;height:44px;border-radius:10px;background:linear-gradient(135deg,#1a3a6b,#2DB374);align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:22px;">C</span>
            </div>
            <!-- Textos -->
            <div style="overflow:hidden;">
                <p style="color:#ffffff;font-weight:800;font-size:15px;line-height:1.1;letter-spacing:-.01em;margin:0;">Comandex</p>
                <p style="color:#2DB374;font-size:9.5px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;margin:2px 0 0;">Software de Comandas</p>
            </div>
        </div>
    </div>

    <!-- Nav dinámico según permisos del rol -->
    <nav style="flex:1; padding:16px 12px; display:flex; flex-direction:column; gap:2px;">
        <?php foreach ($allModules as $key => $mod):
            if ($key === 'configuracion') continue;          // se renderiza aparte abajo
            if (!in_array($key, $permisos)) continue;
        ?>
        <a href="<?= $baseUrl . $mod['url'] ?>"
           class="sidebar-link <?= ($module ?? '') === $key ? 'active' : '' ?>">
            <i data-lucide="<?= $mod['icon'] ?>"></i> <?= $mod['label'] ?>
        </a>
        <?php endforeach; ?>

        <?php if ($esAdmin): ?>
        <!-- Separador + link de Configuración exclusivo para Administrador -->
        <div style="margin:10px 4px 8px;border-top:1px solid var(--sidebar-border);"></div>
        <a href="<?= $baseUrl ?>/configuracion"
           class="sidebar-link <?= ($module ?? '') === 'configuracion' ? 'active' : '' ?>"
           style="color:<?= ($module ?? '') === 'configuracion' ? '' : '#a78bfa' ?>;">
            <i data-lucide="sliders-horizontal"></i> Configuración
        </a>
        <?php endif; ?>
    </nav>



    <!-- User + logout -->
    <div style="padding:16px 12px; border-top:1px solid var(--sidebar-border);">
        <!-- Perfil link -->
        <a href="<?= $baseUrl ?>/perfil" class="sidebar-link <?= ($module ?? '') === 'perfil' ? 'active' : '' ?>" style="margin-bottom:4px;">
            <i data-lucide="user-circle"></i> Mi Perfil
        </a>
        <a href="<?= $baseUrl ?>/logout" class="sidebar-link" style="color:#f87171;">
            <i data-lucide="log-out"></i> Cerrar sesión
        </a>
        <a href="<?= $baseUrl ?>/perfil" style="display:flex;align-items:center;gap:10px;padding:10px 8px 4px;text-decoration:none;border-radius:12px;transition:background .15s;" onmouseover="this.style.background='rgba(255,255,255,0.06)'" onmouseout="this.style.background='transparent'">
            <?php
            $sidebarAvatarColor = ($user['avatar'] ?? '') && str_starts_with($user['avatar'] ?? '', '#') ? $user['avatar'] : '#2563eb';
            $sidebarIniciales   = strtoupper(substr($user['nombre'] ?? 'U', 0, 2));
            ?>
            <div style="width:32px;height:32px;border-radius:50%;background:<?= $sidebarAvatarColor ?>;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:12px;flex-shrink:0;">
                <?= $sidebarIniciales ?>
            </div>
            <div style="overflow:hidden;">
                <p style="color:#fff;font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($user['nombre'] ?? '') ?></p>
                <p style="color:#64748b;font-size:11px;"><?= htmlspecialchars($user['rol'] ?? '') ?></p>
            </div>
        </a>
        <!-- Copyright -->
        <div style="margin-top:12px;padding:10px 8px 2px;border-top:1px solid var(--sidebar-border);">
            <p style="font-size:10px;color:#334155;text-align:center;line-height:1.6;">
                <span style="color:#475569;font-weight:600;">Comandex POS</span> v1.0<br>
                © <?= date('Y') ?> <span style="color:#3b82f6;font-weight:700;">andmen05</span><br>
                <span style="color:#1e293b;">Todos los derechos reservados</span>
            </p>
        </div>
    </div>
</aside>

<!-- ═══════════════════════════ MAIN ═══════════════════════════ -->
<div style="flex:1; display:flex; flex-direction:column; overflow:hidden;">

    <!-- Topbar -->
    <header class="topbar" style="padding:16px 32px; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
        <div>
            <h1 style="font-size:20px; font-weight:700; color:var(--text-1); margin:0;"><?= htmlspecialchars($pageTitle ?? $title ?? '') ?></h1>
            <?php if (!empty($pageSubtitle)): ?>
            <p style="font-size:13px; color:var(--text-4); margin:2px 0 0;"><?= htmlspecialchars($pageSubtitle) ?></p>
            <?php endif; ?>
        </div>
        <div style="display:flex; align-items:center; gap:12px;">
            <!-- Turno activo + reloj Colombia -->
            <div style="display:flex;align-items:center;gap:8px;background:#f0fdf4;color:#16a34a;padding:7px 14px;border-radius:99px;font-size:13px;font-weight:500;">
                <span style="width:8px;height:8px;background:#22c55e;border-radius:50%;display:inline-block;animation:pulse 2s infinite;"></span>
                <?= htmlspecialchars(getConfig('turno_activo_nombre', 'Turno Activo')) ?>
                &nbsp;·&nbsp;
                <span id="reloj-col" style="font-variant-numeric:tabular-nums;"></span>
            </div>

            <!-- 🌙 Toggle Dark / Light Mode -->
            <button id="theme-toggle-btn" class="theme-toggle" title="Cambiar tema" onclick="toggleTheme()">
                <i data-lucide="moon" id="icon-dark"  style="position:absolute;transition:all 0.25s;"></i>
                <i data-lucide="sun"  id="icon-light" style="position:absolute;transition:all 0.25s;display:none;"></i>
            </button>

            <!-- Notificaciones -->
            <button style="position:relative;width:38px;height:38px;border-radius:12px;border:1px solid var(--border2);background:var(--surface);cursor:pointer;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="bell" style="width:18px;height:18px;color:var(--text-3);"></i>
            </button>

            <!-- Avatar usuario → link a perfil -->
            <a href="<?= $baseUrl ?>/perfil" style="display:flex;align-items:center;gap:10px;text-decoration:none;padding:6px 10px;border-radius:14px;transition:background .15s;" onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background='transparent'" title="Ver mi perfil">
                <?php
                $topAvatarColor = ($user['avatar'] ?? '') && str_starts_with($user['avatar'] ?? '', '#') ? $user['avatar'] : null;
                ?>
                <?php if ($topAvatarColor): ?>
                <div style="width:38px;height:38px;border-radius:50%;background:<?= $topAvatarColor ?>;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                    <?= strtoupper(substr($user['nombre'] ?? 'U', 0, 2)) ?>
                </div>
                <?php else: ?>
                <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#1d4ed8);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:15px;box-shadow:0 2px 8px rgba(59,130,246,0.25);">
                    <?= strtoupper(substr($user['nombre'] ?? 'U', 0, 1)) ?>
                </div>
                <?php endif; ?>
                <div>
                    <p style="font-size:14px;font-weight:600;color:var(--text-1);margin:0;"><?= htmlspecialchars($user['nombre'] ?? '') ?></p>
                    <p style="font-size:12px;color:var(--text-4);margin:0;"><?= htmlspecialchars($user['rol'] ?? '') ?></p>
                </div>
            </a>
        </div>
    </header>

    <!-- Content -->
    <main style="flex:1; overflow-y:auto; padding:32px; background:var(--bg); transition:background 0.25s;">
        <?= $content ?? '' ?>
    </main>
</div>

<script>
// ─── RELOJ COLOMBIA ──────────────────────────────────────────────────────────
(function tickColombia() {
    const el = document.getElementById('reloj-col');
    if (el) {
        const now = new Date();
        el.textContent = now.toLocaleTimeString('es-CO', {
            timeZone: 'America/Bogota',
            hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
        });
    }
    setTimeout(tickColombia, 1000);
})();
// ─────────────────────────────────────────────────────────────────────────────

// ─── DARK MODE ───────────────────────────────────────────────────────────────
function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('cmx-theme', theme);
    const isDark = theme === 'dark';
    const iconDark  = document.getElementById('icon-dark');
    const iconLight = document.getElementById('icon-light');
    if (iconDark && iconLight) {
        iconDark.style.display  = isDark  ? 'none' : '';
        iconLight.style.display = isDark  ? ''     : 'none';
    }
}

function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme') || 'light';
    applyTheme(current === 'dark' ? 'light' : 'dark');
}

// Aplicar al cargar (antes de renderizar para evitar flash)
(function() {
    const saved = localStorage.getItem('cmx-theme') || 'light';
    applyTheme(saved);
})();
// ─────────────────────────────────────────────────────────────────────────────

lucide.createIcons();
</script>
<?= $scripts ?? '' ?>
</body>
</html>
