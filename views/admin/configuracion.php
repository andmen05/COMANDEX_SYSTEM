<?php
// views/admin/configuracion.php
ob_start();
?>
<style>
.cfg-section { display:flex; flex-direction:column; gap:20px; }
.cfg-card { background:var(--surface); border:1px solid var(--border); border-radius:18px; padding:24px; }
.cfg-card h3 { font-size:14px; font-weight:700; color:var(--text-1); margin:0 0 4px; display:flex; align-items:center; gap:8px; }
.cfg-card p.desc { font-size:12px; color:var(--text-4); margin:0 0 20px; }
.cfg-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:14px; }
.cfg-field { display:flex; flex-direction:column; gap:6px; }
.cfg-field label { font-size:12px; font-weight:600; color:var(--text-3); letter-spacing:0.02em; text-transform:uppercase; }
.cfg-input {
    width:100%; padding:10px 14px; border:1.5px solid var(--border2);
    border-radius:10px; font-size:14px; background:var(--input-bg);
    color:var(--text-2); transition:border-color 0.15s, box-shadow 0.15s;
    box-sizing:border-box;
}
.cfg-input:focus { outline:none; border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,0.15); }
.cfg-select { appearance:none; -webkit-appearance:none; }
.cfg-badge-row { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.cfg-badge { display:inline-flex; align-items:center; gap:6px; padding:4px 12px; border-radius:99px; font-size:12px; font-weight:600; }
.cfg-lock { background:#fef3c7; color:#92400e; }
.cfg-ok   { background:#d1fae5; color:#065f46; }

/* Toggle switch */
.cfg-toggle { position:relative; display:inline-block; width:48px; height:26px; }
.cfg-toggle input { opacity:0; width:0; height:0; }
.cfg-slider {
    position:absolute; cursor:pointer; inset:0; background:#e2e8f0;
    border-radius:99px; transition:background 0.2s;
}
.cfg-slider:before {
    content:''; position:absolute; height:20px; width:20px; left:3px; bottom:3px;
    background:#fff; border-radius:50%; transition:transform 0.2s;
    box-shadow:0 1px 4px rgba(0,0,0,0.18);
}
.cfg-toggle input:checked + .cfg-slider { background:#2563eb; }
.cfg-toggle input:checked + .cfg-slider:before { transform:translateX(22px); }

/* Save bar */
.save-bar {
    position:sticky; bottom:0; z-index:50;
    background:var(--surface); border-top:1px solid var(--border);
    padding:16px 0; display:flex; align-items:center; justify-content:space-between;
    gap:12px;
}
</style>

<div class="cfg-section">

<?php if($msg): ?>
<div style="display:flex;align-items:center;gap:10px;background:#f0fdf4;border:1px solid #86efac;color:#065f46;border-radius:12px;padding:13px 18px;font-size:14px;font-weight:500;">
    <i data-lucide="check-circle-2" style="width:17px;height:17px;flex-shrink:0;"></i>
    <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/configuracion" id="cfg-form">

<!-- Header sticky -->
<div class="save-bar" style="top:0;bottom:auto;position:static;margin-bottom:4px;border-top:none;border-bottom:1px solid var(--border);padding-bottom:16px;">
    <div>
        <h2 style="font-size:16px;font-weight:800;color:var(--text-1);margin:0;">Configuración del software</h2>
        <p style="font-size:12px;color:var(--text-4);margin:3px 0 0;">Puede modificar estos parámetros.</p>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
        <span class="cfg-badge cfg-lock">
            <i data-lucide="shield-check" style="width:13px;height:13px;"></i>
            Solo Admin Principal
        </span>
        <button type="submit" class="btn-primary" id="btn-save" style="gap:8px;">
            <i data-lucide="save" style="width:15px;height:15px;"></i>
            Guardar cambios
        </button>
    </div>
</div>

<!-- ═══ 1. INFORMACIÓN DEL NEGOCIO ═══ -->
<div class="cfg-card">
    <h3><i data-lucide="store" style="width:16px;height:16px;color:#2563eb;"></i> Información del negocio</h3>
    <p class="desc">Datos del restaurante que aparecen en recibos y reportes.</p>
    <div class="cfg-grid">
        <div class="cfg-field" style="grid-column:1/-1;">
            <label>Nombre del restaurante</label>
            <input class="cfg-input" type="text" name="nombre_negocio"
                   value="<?= htmlspecialchars($config['nombre_negocio']) ?>" placeholder="Ej: La Terraza">
        </div>
        <div class="cfg-field">
            <label>NIT / RUT</label>
            <input class="cfg-input" type="text" name="nit"
                   value="<?= htmlspecialchars($config['nit']) ?>" placeholder="900.123.456-7">
        </div>
        <div class="cfg-field">
            <label>Teléfono</label>
            <input class="cfg-input" type="text" name="telefono"
                   value="<?= htmlspecialchars($config['telefono']) ?>" placeholder="+57 300 123 4567">
        </div>
        <div class="cfg-field">
            <label>Correo electrónico</label>
            <input class="cfg-input" type="email" name="email_negocio"
                   value="<?= htmlspecialchars($config['email_negocio']) ?>" placeholder="correo@restaurante.com">
        </div>
        <div class="cfg-field" style="grid-column:1/-1;">
            <label>Dirección</label>
            <input class="cfg-input" type="text" name="direccion"
                   value="<?= htmlspecialchars($config['direccion']) ?>" placeholder="Cra 7 # 32-10, Bogotá">
        </div>
    </div>
</div>

<!-- ═══ 2. FISCAL Y PRECIOS ═══ -->
<div class="cfg-card">
    <h3><i data-lucide="receipt" style="width:16px;height:16px;color:#059669;"></i> Fiscal y precios (Colombia)</h3>
    <p class="desc">Configuración de impuestos y moneda para el mercado colombiano.</p>
    <div class="cfg-grid">
        <div class="cfg-field">
            <label>IVA (%)</label>
            <input class="cfg-input" type="number" name="iva_porcentaje" min="0" max="100" step="0.01"
                   value="<?= htmlspecialchars($config['iva_porcentaje']) ?>">
        </div>
        <div class="cfg-field">
            <label>Propina sugerida (%)</label>
            <input class="cfg-input" type="number" name="propina_porcentaje" min="0" max="50" step="1"
                   value="<?= htmlspecialchars($config['propina_porcentaje']) ?>">
        </div>
        <div class="cfg-field">
            <label>Moneda</label>
            <select class="cfg-input cfg-select" name="moneda">
                <option value="COP" <?= $config['moneda']==='COP'?'selected':'' ?>>COP — Peso colombiano</option>
                <option value="USD" <?= $config['moneda']==='USD'?'selected':'' ?>>USD — Dólar americano</option>
                <option value="EUR" <?= $config['moneda']==='EUR'?'selected':'' ?>>EUR — Euro</option>
            </select>
        </div>
        <div class="cfg-field">
            <label>Zona horaria</label>
            <select class="cfg-input cfg-select" name="zona_horaria">
                <option value="America/Bogota"     <?= $config['zona_horaria']==='America/Bogota'    ?'selected':'' ?>>🇨🇴 America/Bogota (UTC-5)</option>
                <option value="America/Lima"        <?= $config['zona_horaria']==='America/Lima'       ?'selected':'' ?>>🇵🇪 America/Lima (UTC-5)</option>
                <option value="America/Guayaquil"   <?= $config['zona_horaria']==='America/Guayaquil'  ?'selected':'' ?>>🇪🇨 America/Guayaquil (UTC-5)</option>
                <option value="America/Caracas"     <?= $config['zona_horaria']==='America/Caracas'    ?'selected':'' ?>>🇻🇪 America/Caracas (UTC-4)</option>
                <option value="America/Mexico_City" <?= $config['zona_horaria']==='America/Mexico_City'?'selected':'' ?>>🇲🇽 America/Mexico_City (UTC-6)</option>
            </select>
        </div>
    </div>
</div>

<!-- ═══ 3. HORARIOS DE OPERACIÓN ═══ -->
<div class="cfg-card">
    <h3><i data-lucide="clock" style="width:16px;height:16px;color:#d97706;"></i> Horarios de operación</h3>
    <p class="desc">Define las horas de apertura, cierre y el turno activo que se muestra en el sistema.</p>
    <div class="cfg-grid">
        <div class="cfg-field">
            <label>Hora de apertura</label>
            <input class="cfg-input" type="time" name="horario_apertura"
                   value="<?= htmlspecialchars($config['horario_apertura']) ?>">
        </div>
        <div class="cfg-field">
            <label>Hora de cierre</label>
            <input class="cfg-input" type="time" name="horario_cierre"
                   value="<?= htmlspecialchars($config['horario_cierre']) ?>">
        </div>
        <div class="cfg-field">
            <label>Días de operación</label>
            <input class="cfg-input" type="text" name="dias_operacion"
                   value="<?= htmlspecialchars($config['dias_operacion']) ?>" placeholder="Lun–Dom">
        </div>
        <div class="cfg-field">
            <label>Nombre del turno activo</label>
            <input class="cfg-input" type="text" name="turno_activo_nombre"
                   value="<?= htmlspecialchars($config['turno_activo_nombre']) ?>" placeholder="Turno Mañana">
        </div>
    </div>

    <!-- Tabla de turnos (lectura) -->
    <?php if(!empty($turnos)): ?>
    <div style="margin-top:20px;">
        <p style="font-size:12px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:10px;">
            Turnos registrados en el sistema
        </p>
        <div style="display:flex;flex-wrap:wrap;gap:10px;">
            <?php foreach($turnos as $t): ?>
            <div style="display:flex;align-items:center;gap:10px;padding:10px 16px;background:var(--bg);border:1px solid var(--border2);border-radius:12px;">
                <i data-lucide="sun" style="width:14px;height:14px;color:#d97706;"></i>
                <div>
                    <p style="font-size:13px;font-weight:700;color:var(--text-1);margin:0;"><?= htmlspecialchars($t['nombre']) ?></p>
                    <p style="font-size:11px;color:var(--text-4);margin:0;">
                        <?= substr($t['hora_inicio'],0,5) ?> – <?= substr($t['hora_fin'],0,5) ?>
                    </p>
                </div>
                <span style="width:8px;height:8px;border-radius:50%;background:<?= $t['activo']?'#22c55e':'#94a3b8' ?>;margin-left:4px;"></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ═══ 4. COMPORTAMIENTO DEL SISTEMA ═══ -->
<div class="cfg-card">
    <h3><i data-lucide="cpu" style="width:16px;height:16px;color:#7c3aed;"></i> Comportamiento del sistema</h3>
    <p class="desc">Parámetros que afectan el flujo de trabajo y las alertas del POS.</p>
    <div class="cfg-grid">
        <div class="cfg-field">
            <label>Modo de operación</label>
            <select class="cfg-input cfg-select" name="modo_operacion">
                <option value="normal"       <?= $config['modo_operacion']==='normal'      ?'selected':'' ?>>Normal</option>
                <option value="alta_demanda" <?= $config['modo_operacion']==='alta_demanda'?'selected':'' ?>>Alta demanda (cocina rápida)</option>
                <option value="mantenimiento"<?= $config['modo_operacion']==='mantenimiento'?'selected':'' ?>>Mantenimiento (solo admin)</option>
            </select>
        </div>
        <div class="cfg-field">
            <label>Alerta cocina (minutos)</label>
            <input class="cfg-input" type="number" name="aviso_cocina_minutos" min="1" max="60"
                   value="<?= htmlspecialchars($config['aviso_cocina_minutos']) ?>">
        </div>
        <div class="cfg-field">
            <label>Ítems por página</label>
            <input class="cfg-input" type="number" name="items_por_pagina" min="5" max="100" step="5"
                   value="<?= htmlspecialchars($config['items_por_pagina']) ?>">
        </div>
    </div>
</div>

<!-- ═══ 5. PERSONALIZACIÓN ═══ -->
<div class="cfg-card">
    <h3><i data-lucide="palette" style="width:16px;height:16px;color:#ec4899;"></i> Personalización</h3>
    <p class="desc">Textos que aparecen en recibos y en la pantalla de bienvenida.</p>
    <div class="cfg-grid">
        <div class="cfg-field" style="grid-column:1/-1;">
            <label>Mensaje de bienvenida</label>
            <input class="cfg-input" type="text" name="mensaje_bienvenida"
                   value="<?= htmlspecialchars($config['mensaje_bienvenida']) ?>"
                   placeholder="Bienvenido a nuestro sistema POS">
        </div>
        <div class="cfg-field" style="grid-column:1/-1;">
            <label>Pie de página del recibo</label>
            <input class="cfg-input" type="text" name="footer_recibo"
                   value="<?= htmlspecialchars($config['footer_recibo']) ?>"
                   placeholder="¡Gracias por su visita!">
        </div>
    </div>
</div>

<!-- ═══ BARRA DE GUARDADO ═══ -->
<div class="save-bar">
    <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text-4);">
        <i data-lucide="info" style="width:15px;height:15px;"></i>
        Los cambios se aplican de inmediato en toda la sesión activa.
    </div>
    <div style="display:flex;gap:10px;align-items:center;">
        <a href="<?= BASE_URL ?>/admin" class="btn-secondary">
            <i data-lucide="arrow-left" style="width:15px;height:15px;"></i>
            Volver a Admin
        </a>
        <button type="submit" class="btn-primary" style="gap:8px;">
            <i data-lucide="save" style="width:15px;height:15px;"></i>
            Guardar configuración
        </button>
    </div>
</div>

</form>
</div>

<script>
// Marcar form como "sucio" al cambiar cualquier campo
let formDirty = false;
document.getElementById('cfg-form').addEventListener('change', () => { formDirty = true; });
window.addEventListener('beforeunload', e => {
    if (formDirty) { e.preventDefault(); e.returnValue = ''; }
});
document.getElementById('cfg-form').addEventListener('submit', () => { formDirty = false; });
</script>

<?php
$content = ob_get_clean();
$title = 'Configuración'; $pageTitle = 'Configuración del software';
$pageSubtitle = 'Parámetros del sistema — Solo Administrador principal'; $module = 'configuracion';
require __DIR__ . '/../layout/app.php';
