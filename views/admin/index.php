<?php
// views/admin/index.php
ob_start();
$okMsg = isset($_GET['ok']) ? 'Registro guardado correctamente.' : '';
?>
<div style="display:flex;flex-direction:column;gap:20px;">

<?php if($okMsg): ?>
<div style="display:flex;align-items:center;gap:8px;background:#f0fdf4;border:1px solid #86efac;color:#065f46;border-radius:12px;padding:12px 16px;font-size:14px;font-weight:500;">
    <i data-lucide="check-circle" style="width:16px;height:16px;"></i> <?= $okMsg ?>
</div>
<?php endif; ?>

<!-- Stats -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
    <?php
    $sl=[['l'=>'Usuarios activos','v'=>$stats['usuarios'],'ic'=>'users','bg'=>'#dbeafe','col'=>'#2563eb'],
         ['l'=>'Roles definidos','v'=>$stats['roles'],'ic'=>'shield','bg'=>'#ede9fe','col'=>'#7c3aed'],
         ['l'=>'Productos activos','v'=>$stats['productos'],'ic'=>'package','bg'=>'#d1fae5','col'=>'#059669']];
    foreach($sl as $s): ?>
    <div class="card" style="display:flex;align-items:center;gap:14px;padding:18px;">
        <div style="width:46px;height:46px;background:<?= $s['bg'] ?>;border-radius:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="<?= $s['ic'] ?>" style="width:22px;height:22px;color:<?= $s['col'] ?>;"></i>
        </div>
        <div>
            <p style="font-size:28px;font-weight:800;color:#0f172a;margin:0;"><?= $s['v'] ?></p>
            <p style="font-size:12px;color:#64748b;margin:2px 0 0;"><?= $s['l'] ?></p>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Tabs -->
<div style="border-bottom:2px solid #f1f5f9;display:flex;gap:4px;">
    <?php foreach(['usuarios'=>'Usuarios','productos'=>'Productos','categorias'=>'Categorías'] as $k=>$v):
        $act=$tab===$k; ?>
    <a href="<?= BASE_URL ?>/admin?tab=<?= $k ?>" class="tab-link <?= $act?'active':'' ?>"><?= $v ?></a>
    <?php endforeach; ?>
</div>

<!-- ── TAB USUARIOS ── -->
<?php if($tab==='usuarios'): ?>
<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
        <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0;">Gestión de usuarios</h2>
        <button onclick="toggleModal('m-usuario')" class="btn-primary">
            <i data-lucide="user-plus" style="width:16px;height:16px;"></i> Nuevo usuario
        </button>
    </div>
    <form method="GET" style="display:flex;gap:10px;margin-bottom:18px;">
        <input type="hidden" name="tab" value="usuarios">
        <div style="position:relative;flex:1;max-width:320px;">
            <i data-lucide="search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;"></i>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Buscar usuario, correo o rol..."
                style="width:100%;padding:9px 14px 9px 36px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;box-sizing:border-box;">
        </div>
        <button type="submit" class="btn-secondary">Buscar</button>
    </form>
    <table class="data-table">
        <thead><tr>
            <th>Usuario</th><th>Rol</th><th>Sucursal</th><th>Último acceso</th><th>Estado</th>
        </tr></thead>
        <tbody>
            <?php
            $rolCol=['Administrador'=>['#dbeafe','#1d4ed8'],'Gerente'=>['#ede9fe','#7c3aed'],
                     'Mesero'=>['#d1fae5','#065f46'],'Cocina'=>['#fef3c7','#92400e'],
                     'Cajero'=>['#fce7f3','#9d174d'],'Invitado'=>['#f1f5f9','#475569']];
            foreach($usuarios as $u):
                $rc=$rolCol[$u['rol']]??['#f1f5f9','#475569']; ?>
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#1d4ed8);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0;">
                            <?= strtoupper(substr($u['nombre'],0,1)) ?>
                        </div>
                        <div>
                            <p style="font-size:14px;font-weight:600;color:#0f172a;margin:0;"><?= htmlspecialchars($u['nombre']) ?></p>
                            <p style="font-size:12px;color:#94a3b8;margin:0;"><?= htmlspecialchars($u['email']) ?></p>
                        </div>
                    </div>
                </td>
                <td><span style="background:<?= $rc[0] ?>;color:<?= $rc[1] ?>;font-size:12px;font-weight:700;padding:4px 10px;border-radius:99px;"><?= $u['rol'] ?></span></td>
                <td style="font-size:13px;color:#64748b;"><?= htmlspecialchars($u['sucursal']) ?></td>
                <td style="font-size:12px;color:#94a3b8;"><?= $u['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($u['ultimo_acceso'])) : '—' ?></td>
                <td>
                    <span style="display:flex;align-items:center;gap:5px;font-size:12px;font-weight:600;color:<?= $u['activo']?'#059669':'#dc2626' ?>;">
                        <span style="width:7px;height:7px;border-radius:50%;background:<?= $u['activo']?'#22c55e':'#ef4444' ?>;"></span>
                        <?= $u['activo']?'Activo':'Inactivo' ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($usuarios)): ?>
            <tr><td colspan="5" style="text-align:center;padding:32px;color:#94a3b8;">Sin resultados</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ── TAB PRODUCTOS ── -->
<?php elseif($tab==='productos'): ?>
<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
        <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0;">Catálogo de productos</h2>
        <button onclick="toggleModal('m-producto')" class="btn-primary">
            <i data-lucide="plus" style="width:16px;height:16px;"></i> Nuevo producto
        </button>
    </div>
    <table class="data-table">
        <thead><tr><th>Producto</th><th>Categoría</th><th style="text-align:right;">Precio</th><th>Estado</th></tr></thead>
        <tbody>
            <?php foreach($productos as $p): ?>
            <tr>
                <td>
                    <p style="font-size:14px;font-weight:600;color:#0f172a;margin:0;"><?= htmlspecialchars($p['nombre']) ?></p>
                    <?php if($p['descripcion']): ?>
                    <p style="font-size:12px;color:#94a3b8;margin:2px 0 0;max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($p['descripcion']) ?></p>
                    <?php endif; ?>
                </td>
                <td style="font-size:13px;color:#64748b;"><?= htmlspecialchars($p['categoria_nombre']??'—') ?></td>
                <td style="text-align:right;font-size:14px;font-weight:700;color:#0f172a;"><?= formatMoney($p['precio']) ?></td>
                <td>
                    <span style="font-size:12px;font-weight:600;padding:4px 10px;border-radius:99px;background:<?= $p['disponible']?'#d1fae5':'#f1f5f9' ?>;color:<?= $p['disponible']?'#065f46':'#64748b' ?>;">
                        <?= $p['disponible']?'Disponible':'No disponible' ?>
                    </span>
                    <?php if($p['favorito']): ?><span style="color:#f59e0b;margin-left:4px;">★</span><?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- ── TAB CATEGORÍAS ── -->
<?php else: ?>
<div class="card">
    <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 18px;">Categorías del menú</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:12px;">
        <?php foreach($categorias as $c): ?>
        <div style="display:flex;align-items:center;gap:12px;padding:14px;background:#f8fafc;border:1px solid #f1f5f9;border-radius:12px;">
            <span style="font-size:26px;"><?= htmlspecialchars($c['icono']) ?></span>
            <p style="font-size:14px;font-weight:600;color:#0f172a;margin:0;"><?= htmlspecialchars($c['nombre']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
</div>

<!-- Modal: Nuevo usuario -->
<div id="m-usuario" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);backdrop-filter:blur(4px);z-index:1000;align-items:center;justify-content:center;padding:20px;">
    <div style="background:#fff;border-radius:24px;width:100%;max-width:460px;box-shadow:0 24px 60px rgba(0,0,0,0.2);">
        <div style="padding:24px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                <h3 style="font-size:17px;font-weight:800;color:#0f172a;margin:0;">Nuevo usuario</h3>
                <button onclick="toggleModal('m-usuario')" style="width:34px;height:34px;background:#f1f5f9;border:none;border-radius:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="x" style="width:16px;height:16px;color:#64748b;"></i>
                </button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin?tab=usuarios" style="display:flex;flex-direction:column;gap:14px;">
                <input type="hidden" name="accion" value="crear_usuario">
                <?php foreach([['Nombre completo','text','nombre'],['Correo electrónico','email','email'],['Contraseña','password','password']] as $f): ?>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;"><?= $f[0] ?></label>
                    <input type="<?= $f[1] ?>" name="<?= $f[2] ?>" required style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;box-sizing:border-box;">
                </div>
                <?php endforeach; ?>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">Rol</label>
                        <select name="rol_id" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;background:#fff;box-sizing:border-box;">
                            <?php foreach($roles as $r): ?><option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">Sucursal</label>
                        <select name="sucursal_id" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;background:#fff;box-sizing:border-box;">
                            <?php foreach($sucursales as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nombre']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn-primary" style="width:100%;padding:13px;justify-content:center;font-size:14px;">Crear usuario</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Nuevo producto -->
<div id="m-producto" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);backdrop-filter:blur(4px);z-index:1000;align-items:center;justify-content:center;padding:20px;">
    <div style="background:#fff;border-radius:24px;width:100%;max-width:460px;box-shadow:0 24px 60px rgba(0,0,0,0.2);">
        <div style="padding:24px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                <h3 style="font-size:17px;font-weight:800;color:#0f172a;margin:0;">Nuevo producto</h3>
                <button onclick="toggleModal('m-producto')" style="width:34px;height:34px;background:#f1f5f9;border:none;border-radius:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="x" style="width:16px;height:16px;color:#64748b;"></i>
                </button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin?tab=productos" style="display:flex;flex-direction:column;gap:14px;">
                <input type="hidden" name="accion" value="crear_producto">
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">Nombre</label>
                    <input type="text" name="nombre" required style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">Descripción</label>
                    <textarea name="descripcion" rows="2" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;resize:none;box-sizing:border-box;"></textarea>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">Precio (COP $)</label>
                        <input type="number" name="precio" step="0.01" min="0" required style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">Categoría</label>
                        <select name="categoria_id" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;background:#fff;box-sizing:border-box;">
                            <?php foreach($categorias as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div style="display:flex;gap:20px;">
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#374151;cursor:pointer;">
                        <input type="checkbox" name="disponible" checked style="width:16px;height:16px;accent-color:#2563eb;"> Disponible
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#374151;cursor:pointer;">
                        <input type="checkbox" name="favorito" style="width:16px;height:16px;accent-color:#f59e0b;"> Favorito ★
                    </label>
                </div>
                <button type="submit" class="btn-primary" style="width:100%;padding:13px;justify-content:center;font-size:14px;">Crear producto</button>
            </form>
        </div>
    </div>
</div>

<script>
function toggleModal(id) {
    const m = document.getElementById(id);
    m.style.display = m.style.display==='flex' ? 'none' : 'flex';
    if(m.style.display==='flex') lucide.createIcons();
}
document.querySelectorAll('[id^="m-"]').forEach(m => {
    m.addEventListener('click', e => { if(e.target===m) m.style.display='none'; });
});
</script>
<?php
$content=ob_get_clean();
$title='Admin'; $pageTitle='Admin'; $pageSubtitle='Gestiona usuarios, productos e inventario'; $module='admin';
require __DIR__ . '/../layout/app.php';
