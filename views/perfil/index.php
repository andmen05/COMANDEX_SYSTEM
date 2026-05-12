<?php
// views/perfil/index.php
ob_start();

$avatarColor = ($user['avatar'] ?? '') && str_starts_with($user['avatar'] ?? '', '#') ? $user['avatar'] : '#2563eb';
$iniciales   = strtoupper(substr($user['nombre'] ?? 'U', 0, 2));
$miembroDesde = isset($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : '—';

$actIcons = [
    'pedido_nuevo'   => ['icon' => 'chef-hat',      'bg' => '#dbeafe', 'color' => '#2563eb',  'label' => 'Nuevo pedido'],
    'pedido_listo'   => ['icon' => 'check-circle',  'bg' => '#d1fae5', 'color' => '#059669',  'label' => 'Pedido listo'],
    'pago'           => ['icon' => 'credit-card',   'bg' => '#ede9fe', 'color' => '#7c3aed',  'label' => 'Pago'],
    'mesa_asignada'  => ['icon' => 'armchair',      'bg' => '#fef3c7', 'color' => '#d97706',  'label' => 'Mesa'],
    'pedido_cancel'  => ['icon' => 'x-circle',      'bg' => '#fee2e2', 'color' => '#dc2626',  'label' => 'Cancelado'],
    'perfil_update'  => ['icon' => 'user-check',    'bg' => '#f0fdf4', 'color' => '#059669',  'label' => 'Perfil'],
];

$coloresPaleta = [
    '#2563eb','#7c3aed','#059669','#dc2626','#d97706',
    '#0891b2','#be185d','#65a30d','#0f172a','#6366f1',
    '#f97316','#10b981','#ec4899','#14b8a6','#8b5cf6',
];
?>
<style>
.perfil-grid  { display:grid; grid-template-columns:300px 1fr; gap:20px; align-items:start; }
.perfil-card  { background:var(--surface); border:1px solid var(--border); border-radius:18px; padding:22px; }
.pf-label     { font-size:11px; font-weight:700; color:var(--text-4); text-transform:uppercase; letter-spacing:.07em; margin-bottom:6px; display:block; }
.pf-input     {
    width:100%; padding:10px 14px; border:1.5px solid var(--border2);
    border-radius:10px; font-size:14px; color:var(--text-2);
    background:var(--input-bg); box-sizing:border-box; transition:border-color .15s;
}
.pf-input:focus { outline:none; border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.15); }

/* Avatar */
.avatar-ring {
    width:90px; height:90px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    color:#fff; font-weight:800; font-size:32px;
    box-shadow:0 6px 24px rgba(0,0,0,.2);
    transition:transform .2s; cursor:default; flex-shrink:0;
    position:relative;
}
.avatar-ring:hover { transform:scale(1.06); }
.avatar-ring::after {
    content:''; position:absolute; inset:-3px; border-radius:50%;
    border:2px solid transparent;
    background:linear-gradient(var(--surface),var(--surface)) padding-box,
               linear-gradient(135deg,#3b82f6,#8b5cf6) border-box;
}

/* Color dots */
.color-dot {
    width:28px; height:28px; border-radius:50%; cursor:pointer;
    border:2px solid transparent; transition:all .15s; flex-shrink:0;
}
.color-dot:hover, .color-dot.selected {
    border-color:#fff; box-shadow:0 0 0 2px currentColor; transform:scale(1.18);
}

/* Log items */
.log-item { display:flex; align-items:flex-start; gap:12px; padding:11px 0; border-bottom:1px solid var(--border); }
.log-item:last-child { border-bottom:none; }

/* Stat cards */
.stat-box { background:var(--bg); border-radius:14px; padding:14px; text-align:center; border:1px solid var(--border); }

/* Badge */
.pf-badge { display:inline-flex; align-items:center; gap:5px; padding:4px 12px; border-radius:99px; font-size:12px; font-weight:700; }

/* Brand footer card */
.brand-card {
    background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 100%);
    border:1px solid rgba(99,102,241,.3);
    border-radius:18px; padding:20px; text-align:center;
    position:relative; overflow:hidden;
}
.brand-card::before {
    content:''; position:absolute; top:-40px; right:-40px;
    width:120px; height:120px; border-radius:50%;
    background:radial-gradient(circle,rgba(99,102,241,.25) 0%,transparent 70%);
}
</style>

<?php if($msg): ?>
<div style="display:flex;align-items:center;gap:10px;background:#f0fdf4;border:1px solid #86efac;color:#065f46;border-radius:12px;padding:13px 18px;font-size:14px;font-weight:500;margin-bottom:16px;">
    <i data-lucide="check-circle-2" style="width:17px;height:17px;flex-shrink:0;"></i>
    <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>
<?php if($err): ?>
<div style="display:flex;align-items:center;gap:10px;background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;border-radius:12px;padding:13px 18px;font-size:14px;font-weight:500;margin-bottom:16px;">
    <i data-lucide="alert-circle" style="width:17px;height:17px;flex-shrink:0;"></i>
    <?= htmlspecialchars($err) ?>
</div>
<?php endif; ?>

<div class="perfil-grid">

<!-- ═══════ COL IZQUIERDA ═══════ -->
<div style="display:flex;flex-direction:column;gap:16px;">

    <!-- Tarjeta de identidad -->
    <div class="perfil-card" style="text-align:center;padding:30px 24px;">
        <div style="display:flex;justify-content:center;margin-bottom:18px;">
            <div id="avatar-preview" class="avatar-ring" style="background:<?= $avatarColor ?>;">
                <?= $iniciales ?>
            </div>
        </div>
        <h2 style="font-size:19px;font-weight:800;color:var(--text-1);margin:0 0 4px;"><?= htmlspecialchars($user['nombre']) ?></h2>
        <p style="font-size:13px;color:var(--text-4);margin:0 0 14px;"><?= htmlspecialchars($user['email']) ?></p>
        <div style="display:flex;justify-content:center;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
            <span class="pf-badge" style="background:#dbeafe;color:#1d4ed8;">
                <i data-lucide="shield" style="width:12px;height:12px;"></i>
                <?= htmlspecialchars($user['rol']) ?>
            </span>
            <span class="pf-badge" style="background:#f0fdf4;color:#065f46;">
                <i data-lucide="map-pin" style="width:12px;height:12px;"></i>
                <?= htmlspecialchars($user['sucursal']) ?>
            </span>
        </div>
        <div style="border-top:1px solid var(--border);padding-top:14px;display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            <div>
                <p style="font-size:11px;color:var(--text-4);margin:0 0 2px;">Miembro desde</p>
                <p style="font-size:13px;font-weight:700;color:var(--text-1);margin:0;"><?= $miembroDesde ?></p>
            </div>
            <div>
                <p style="font-size:11px;color:var(--text-4);margin:0 0 2px;">Último acceso</p>
                <p style="font-size:13px;font-weight:700;color:var(--text-1);margin:0;">
                    <?= $stats['ultimo_acceso'] ? date('d/m H:i', strtotime($stats['ultimo_acceso'])) : '—' ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="perfil-card">
        <h3 style="font-size:13px;font-weight:700;color:var(--text-1);margin:0 0 14px;display:flex;align-items:center;gap:8px;">
            <i data-lucide="bar-chart-2" style="width:15px;height:15px;color:#2563eb;"></i> Mi actividad
        </h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            <div class="stat-box">
                <p style="font-size:28px;font-weight:800;color:var(--text-1);margin:0;"><?= $stats['total_pedidos'] ?></p>
                <p style="font-size:11px;color:var(--text-4);margin:3px 0 0;">Total pedidos</p>
            </div>
            <div class="stat-box">
                <p style="font-size:28px;font-weight:800;color:#2563eb;margin:0;"><?= $stats['pedidos_hoy'] ?></p>
                <p style="font-size:11px;color:var(--text-4);margin:3px 0 0;">Hoy</p>
            </div>
            <div class="stat-box" style="grid-column:span 2;">
                <p style="font-size:22px;font-weight:800;color:#7c3aed;margin:0;"><?= count($actividad) ?></p>
                <p style="font-size:11px;color:var(--text-4);margin:3px 0 0;">Registros en historial</p>
            </div>
        </div>
    </div>

    <!-- Personalización de avatar -->
    <div class="perfil-card">
        <h3 style="font-size:13px;font-weight:700;color:var(--text-1);margin:0 0 6px;display:flex;align-items:center;gap:8px;">
            <i data-lucide="palette" style="width:15px;height:15px;color:#ec4899;"></i> Color de avatar
        </h3>
        <p style="font-size:12px;color:var(--text-4);margin:0 0 14px;">Elige el color que te identifica en el sistema.</p>
        <form method="POST" action="<?= BASE_URL ?>/perfil" id="form-color">
            <input type="hidden" name="accion" value="personalizar">
            <input type="hidden" name="avatar_color" id="avatar_color_input" value="<?= $avatarColor ?>">
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px;">
                <?php foreach($coloresPaleta as $c): ?>
                <div class="color-dot <?= $c === $avatarColor ? 'selected' : '' ?>"
                     style="background:<?= $c ?>;"
                     onclick="selectColor('<?= $c ?>')"
                     title="<?= $c ?>"></div>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:10px;">
                <i data-lucide="save" style="width:14px;height:14px;"></i> Guardar color
            </button>
        </form>
    </div>

    <!-- Info del sistema + Brand -->
    <div class="perfil-card">
        <h3 style="font-size:13px;font-weight:700;color:var(--text-1);margin:0 0 14px;display:flex;align-items:center;gap:8px;">
            <i data-lucide="info" style="width:15px;height:15px;color:#7c3aed;"></i> Info del sistema
        </h3>
        <?php
        $infoRows = [
            ['ID de usuario',   '#'.$user['id']],
            ['Rol',             $user['rol']],
            ['Sucursal',        $user['sucursal']],
            ['Versión',         'Comandex POS v1.0'],
            ['Desarrollado por','andmen05'],
            ['Zona horaria',    'America/Bogota'],
        ];
        foreach($infoRows as $r): ?>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid var(--border);font-size:13px;">
            <span style="color:var(--text-4);"><?= $r[0] ?></span>
            <span style="font-weight:600;color:var(--text-1);"><?= htmlspecialchars($r[1]) ?></span>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<!-- ═══════ COL DERECHA ═══════ -->
<div style="display:flex;flex-direction:column;gap:16px;">

    <!-- Editar información -->
    <div class="perfil-card">
        <h3 style="font-size:14px;font-weight:700;color:var(--text-1);margin:0 0 4px;display:flex;align-items:center;gap:8px;">
            <i data-lucide="user-pen" style="width:16px;height:16px;color:#2563eb;"></i> Información personal
        </h3>
        <p style="font-size:12px;color:var(--text-4);margin:0 0 18px;">Actualiza tu nombre y correo de acceso.</p>
        <form method="POST" action="<?= BASE_URL ?>/perfil" style="display:flex;flex-direction:column;gap:14px;">
            <input type="hidden" name="accion" value="actualizar_info">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div>
                    <label class="pf-label">Nombre completo</label>
                    <input class="pf-input" type="text" name="nombre"
                           value="<?= htmlspecialchars($user['nombre']) ?>" required>
                </div>
                <div>
                    <label class="pf-label">Correo electrónico</label>
                    <input class="pf-input" type="email" name="email"
                           value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;">
                <button type="submit" class="btn-primary" style="gap:8px;">
                    <i data-lucide="save" style="width:14px;height:14px;"></i> Guardar información
                </button>
            </div>
        </form>
    </div>

    <!-- Cambiar contraseña -->
    <div class="perfil-card">
        <h3 style="font-size:14px;font-weight:700;color:var(--text-1);margin:0 0 4px;display:flex;align-items:center;gap:8px;">
            <i data-lucide="lock-keyhole" style="width:16px;height:16px;color:#dc2626;"></i> Cambiar contraseña
        </h3>
        <p style="font-size:12px;color:var(--text-4);margin:0 0 18px;">Mínimo 8 caracteres. Se aplica al próximo inicio de sesión.</p>
        <form method="POST" action="<?= BASE_URL ?>/perfil" style="display:flex;flex-direction:column;gap:14px;">
            <input type="hidden" name="accion" value="cambiar_password">
            <div>
                <label class="pf-label">Contraseña actual</label>
                <input class="pf-input" type="password" name="password_actual" required placeholder="••••••••">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div>
                    <label class="pf-label">Nueva contraseña</label>
                    <input class="pf-input" type="password" name="password_nueva" id="pwd-new" required placeholder="••••••••" minlength="8">
                </div>
                <div>
                    <label class="pf-label">Confirmar contraseña</label>
                    <input class="pf-input" type="password" name="password_confirm" id="pwd-conf" required placeholder="••••••••">
                </div>
            </div>
            <!-- Indicador de fuerza -->
            <div id="pwd-strength" style="display:none;margin-top:-6px;">
                <div style="height:5px;background:var(--border);border-radius:99px;overflow:hidden;">
                    <div id="strength-bar" style="height:5px;border-radius:99px;width:0;transition:width .35s,background .35s;"></div>
                </div>
                <p id="strength-txt" style="font-size:11px;color:var(--text-4);margin:4px 0 0;"></p>
            </div>
            <div style="display:flex;justify-content:flex-end;">
                <button type="submit" class="btn-danger" style="gap:8px;">
                    <i data-lucide="key-round" style="width:14px;height:14px;"></i> Actualizar contraseña
                </button>
            </div>
        </form>
    </div>

    <!-- Registro de actividad -->
    <div class="perfil-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
            <h3 style="font-size:14px;font-weight:700;color:var(--text-1);margin:0;display:flex;align-items:center;gap:8px;">
                <i data-lucide="history" style="width:16px;height:16px;color:#d97706;"></i> Registro de actividad
            </h3>
            <span style="background:#fef3c7;color:#92400e;font-size:11px;font-weight:700;padding:3px 10px;border-radius:99px;">
                <?= count($actividad) ?> registros
            </span>
        </div>
        <p style="font-size:12px;color:var(--text-4);margin:0 0 18px;">Tus últimas acciones registradas en el sistema.</p>

        <?php if(empty($actividad)): ?>
        <div style="text-align:center;padding:40px;color:var(--text-4);">
            <i data-lucide="inbox" style="width:36px;height:36px;margin:0 auto 10px;display:block;"></i>
            <p style="font-size:14px;">Sin actividad registrada todavía</p>
        </div>
        <?php else: ?>
        <div>
            <?php foreach($actividad as $a):
                $ic = $actIcons[$a['tipo']] ?? ['icon'=>'activity','bg'=>'#f1f5f9','color'=>'#64748b','label'=>'Evento'];
            ?>
            <div class="log-item">
                <div style="width:38px;height:38px;background:<?= $ic['bg'] ?>;border-radius:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i data-lucide="<?= $ic['icon'] ?>" style="width:17px;height:17px;color:<?= $ic['color'] ?>;"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <p style="font-size:13px;color:var(--text-2);font-weight:500;margin:0 0 2px;line-height:1.4;"><?= htmlspecialchars($a['descripcion']) ?></p>
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <span style="font-size:11px;color:var(--text-4);"><?= date('d/m/Y H:i', strtotime($a['created_at'])) ?></span>
                        <?php if($a['entidad']): ?>
                        <span style="background:var(--bg);border:1px solid var(--border2);padding:1px 7px;border-radius:5px;font-size:11px;font-weight:600;color:var(--text-3);"><?= htmlspecialchars($a['entidad']) ?></span>
                        <?php endif; ?>
                        <span style="background:<?= $ic['bg'] ?>;color:<?= $ic['color'] ?>;font-size:10px;font-weight:700;padding:2px 7px;border-radius:5px;"><?= $ic['label'] ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Footer de derechos en la columna derecha -->
    <div style="text-align:center;padding:12px 0 4px;">
        <p style="font-size:12px;color:var(--text-4);margin:0;">
            <strong style="color:var(--text-3);">Comandex POS v1.0</strong> &nbsp;·&nbsp;
            © <?= date('Y') ?> <span style="color:#6366f1;font-weight:700;">andmen05</span>
            &nbsp;·&nbsp; Todos los derechos reservados
        </p>
    </div>

</div>
</div><!-- fin perfil-grid -->

<script>
// ── Selector de color de avatar ─────────────────────────────────
function selectColor(hex) {
    document.querySelectorAll('.color-dot').forEach(d => d.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
    document.getElementById('avatar_color_input').value = hex;
    document.getElementById('avatar-preview').style.background = hex;
}

// ── Indicador de fuerza de contraseña ───────────────────────────
document.getElementById('pwd-new')?.addEventListener('input', function() {
    const val = this.value;
    const wrap = document.getElementById('pwd-strength');
    const bar  = document.getElementById('strength-bar');
    const txt  = document.getElementById('strength-txt');
    if (!val) { wrap.style.display='none'; return; }
    wrap.style.display='block';
    let score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const levels = [
        {w:'20%',  bg:'#ef4444', t:'Muy débil'},
        {w:'40%',  bg:'#f97316', t:'Débil'},
        {w:'60%',  bg:'#eab308', t:'Regular'},
        {w:'80%',  bg:'#22c55e', t:'Fuerte ✓'},
        {w:'100%', bg:'#15803d', t:'Muy fuerte 🔐'},
    ];
    const lv = levels[Math.min(score-1, 4)] || levels[0];
    bar.style.width      = lv.w;
    bar.style.background = lv.bg;
    txt.textContent      = lv.t;
    txt.style.color      = lv.bg;
});
</script>

<?php
$content = ob_get_clean();
$title = 'Mi perfil';
$pageTitle = 'Mi perfil';
$pageSubtitle = 'Personalización · Seguridad · Registro de actividad';
$module = 'perfil';
require __DIR__ . '/../layout/app.php';
