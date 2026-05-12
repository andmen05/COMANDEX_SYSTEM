<?php
// views/dashboard/index.php
ob_start();
$enCocina   = (int)($pedidosEstado['en_cocina'] ?? 0);
$listos     = (int)($pedidosEstado['listo']     ?? 0);
$servidos   = (int)($pedidosEstado['servido']   ?? 0);
$cancelados = (int)($pedidosEstado['cancelado'] ?? 0);
$pendientes = (int)($pedidosEstado['pendiente'] ?? 0);
$totalPeds  = $enCocina + $listos + $servidos + $cancelados + $pendientes;

$actIcons = [
    'pedido_nuevo'  => ['icon'=>'chef-hat',    'bg'=>'#dbeafe','color'=>'#2563eb'],
    'pedido_listo'  => ['icon'=>'check-circle','bg'=>'#d1fae5','color'=>'#059669'],
    'pago'          => ['icon'=>'credit-card', 'bg'=>'#ede9fe','color'=>'#7c3aed'],
    'mesa_asignada' => ['icon'=>'user',        'bg'=>'#fef3c7','color'=>'#d97706'],
    'pedido_cancel' => ['icon'=>'x-circle',    'bg'=>'#fee2e2','color'=>'#dc2626'],
];
?>

<!-- KPI CARDS -->
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:20px; margin-bottom:24px;">

    <!-- Mesas activas -->
    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <div style="width:48px;height:48px;background:#dbeafe;border-radius:14px;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="layout-grid" style="width:22px;height:22px;color:#2563eb;"></i>
            </div>
            <a href="<?= BASE_URL ?>/mesas" style="font-size:12px;color:#2563eb;font-weight:500;text-decoration:none;display:flex;align-items:center;gap:4px;">
                Ver mesas <i data-lucide="arrow-right" style="width:13px;height:13px;"></i>
            </a>
        </div>
        <p style="font-size:32px;font-weight:800;color:#0f172a;margin:0;">
            <?= $mesas['ocupada']??0 ?><span style="font-size:18px;color:#94a3b8;font-weight:400;"> / <?= $mesas['total']??0 ?></span>
        </p>
        <p style="font-size:13px;color:#64748b;margin:4px 0 10px;">Mesas activas</p>
        <?php $pct = ($mesas['total']??0)>0 ? round((($mesas['ocupada']??0)/($mesas['total']??1))*100) : 0; ?>
        <div style="height:5px;background:#f1f5f9;border-radius:99px;overflow:hidden;">
            <div style="height:5px;background:#2563eb;border-radius:99px;width:<?= $pct ?>%;"></div>
        </div>
        <p style="font-size:12px;color:#94a3b8;margin:6px 0 0;"><?= $pct ?>% ocupación</p>
    </div>

    <!-- Pedidos en cocina -->
    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <div style="width:48px;height:48px;background:#fef3c7;border-radius:14px;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="chef-hat" style="width:22px;height:22px;color:#d97706;"></i>
            </div>
            <a href="<?= BASE_URL ?>/cocina" style="font-size:12px;color:#2563eb;font-weight:500;text-decoration:none;display:flex;align-items:center;gap:4px;">
                Ver en cocina <i data-lucide="arrow-right" style="width:13px;height:13px;"></i>
            </a>
        </div>
        <p style="font-size:32px;font-weight:800;color:#0f172a;margin:0;"><?= $enCocina ?></p>
        <p style="font-size:13px;color:#64748b;margin:4px 0 0;">Pedidos en cocina</p>
        <?php if(count($urgentes)>0): ?>
        <p style="font-size:12px;color:#dc2626;font-weight:600;margin:8px 0 0;display:flex;align-items:center;gap:4px;">
            <i data-lucide="alert-triangle" style="width:13px;height:13px;"></i>
            <?= count($urgentes) ?> urgente<?= count($urgentes)>1?'s':'' ?>
        </p>
        <?php endif; ?>
    </div>

    <!-- Ventas del día -->
    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <div style="width:48px;height:48px;background:#d1fae5;border-radius:14px;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="dollar-sign" style="width:22px;height:22px;color:#059669;"></i>
            </div>
            <a href="<?= BASE_URL ?>/reportes" style="font-size:12px;color:#2563eb;font-weight:500;text-decoration:none;display:flex;align-items:center;gap:4px;">
                Ver reportes <i data-lucide="arrow-right" style="width:13px;height:13px;"></i>
            </a>
        </div>
        <p style="font-size:28px;font-weight:800;color:#0f172a;margin:0;"><?= formatMoney($ventasHoy) ?></p>
        <p style="font-size:13px;color:#64748b;margin:4px 0 0;">Ventas del día</p>
    </div>

    <!-- Pagos pendientes -->
    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <div style="width:48px;height:48px;background:#fee2e2;border-radius:14px;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="credit-card" style="width:22px;height:22px;color:#dc2626;"></i>
            </div>
            <a href="<?= BASE_URL ?>/pagos" style="font-size:12px;color:#2563eb;font-weight:500;text-decoration:none;display:flex;align-items:center;gap:4px;">
                Ver pendientes <i data-lucide="arrow-right" style="width:13px;height:13px;"></i>
            </a>
        </div>
        <p style="font-size:32px;font-weight:800;color:#0f172a;margin:0;"><?= $pagosPend ?></p>
        <p style="font-size:13px;color:#64748b;margin:4px 0 0;">Pagos pendientes</p>
        <?php if($totalPend>0): ?>
        <p style="font-size:12px;color:#dc2626;font-weight:600;margin:8px 0 0;"><?= formatMoney($totalPend) ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- ROW 2 -->
<div style="display:grid; grid-template-columns:1fr 1fr 1.2fr; gap:20px; margin-bottom:24px;">

    <!-- Donut de pedidos -->
    <div class="card">
        <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 20px;">Pedidos por estado</h2>
        <div style="display:flex;align-items:center;gap:20px;">
            <div style="position:relative;flex-shrink:0;">
                <svg viewBox="0 0 36 36" style="width:120px;height:120px;transform:rotate(-90deg);">
                    <?php
                    $segments = [
                        ['v'=>$enCocina,  'c'=>'#f59e0b'],
                        ['v'=>$listos,    'c'=>'#10b981'],
                        ['v'=>$servidos,  'c'=>'#3b82f6'],
                        ['v'=>$cancelados,'c'=>'#9ca3af'],
                        ['v'=>$pendientes,'c'=>'#f97316'],
                    ];
                    $tot = max($totalPeds,1); $off=0;
                    foreach($segments as $s) {
                        $p=($s['v']/$tot)*100;
                        echo "<circle cx='18' cy='18' r='15.9' fill='none' stroke='{$s['c']}' stroke-width='3.8' stroke-dasharray='$p ".(100-$p)."' stroke-dashoffset='".(100-$off)."'/>";
                        $off+=$p;
                    }
                    ?>
                </svg>
                <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                    <span style="font-size:22px;font-weight:800;color:#0f172a;"><?= $totalPeds ?></span>
                    <span style="font-size:11px;color:#94a3b8;">Total</span>
                </div>
            </div>
            <div style="flex:1;">
                <?php
                $leg=[
                    ['l'=>'En cocina',       'v'=>$enCocina,   'c'=>'#f59e0b'],
                    ['l'=>'Listos p/servir', 'v'=>$listos,     'c'=>'#10b981'],
                    ['l'=>'Servidos',         'v'=>$servidos,   'c'=>'#3b82f6'],
                    ['l'=>'Cancelados',       'v'=>$cancelados, 'c'=>'#9ca3af'],
                    ['l'=>'Pendientes',       'v'=>$pendientes, 'c'=>'#f97316'],
                ];
                foreach($leg as $l): ?>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <div style="display:flex;align-items:center;gap:7px;">
                        <span style="width:10px;height:10px;background:<?= $l['c'] ?>;border-radius:50%;display:inline-block;flex-shrink:0;"></span>
                        <span style="font-size:13px;color:#374151;"><?= $l['l'] ?></span>
                    </div>
                    <span style="font-size:13px;font-weight:700;color:#0f172a;"><?= $l['v'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Actividad reciente -->
    <div class="card">
        <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 20px;">Actividad reciente</h2>
        <div style="display:flex;flex-direction:column;gap:14px;">
            <?php foreach($actividad as $a):
                $ic=$actIcons[$a['tipo']]??['icon'=>'activity','bg'=>'#f1f5f9','color'=>'#64748b'];
            ?>
            <div style="display:flex;align-items:flex-start;gap:12px;">
                <div style="width:34px;height:34px;background:<?= $ic['bg'] ?>;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i data-lucide="<?= $ic['icon'] ?>" style="width:15px;height:15px;color:<?= $ic['color'] ?>;"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <p style="font-size:13px;color:#1e293b;font-weight:500;margin:0;line-height:1.4;"><?= htmlspecialchars($a['descripcion']) ?></p>
                    <p style="font-size:11px;color:#94a3b8;margin:2px 0 0;"><?= date('H:i',strtotime($a['created_at'])) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if(empty($actividad)): ?>
            <p style="font-size:13px;color:#94a3b8;text-align:center;padding:16px 0;">Sin actividad reciente</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Vista del salón mini -->
    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0;">Vista del salón</h2>
            <a href="<?= BASE_URL ?>/mesas" style="font-size:12px;color:#2563eb;text-decoration:none;font-weight:500;">Ver completo →</a>
        </div>
        <div style="position:relative;background:#f8fafc;border-radius:12px;height:200px;overflow:hidden;">
            <?php
            $clsMap=['libre'=>['bg'=>'#e2e8f0','text'=>'#475569'],'ocupada'=>['bg'=>'#bbf7d0','text'=>'#065f46'],'reservada'=>['bg'=>'#fde68a','text'=>'#92400e'],'por_liberar'=>['bg'=>'#fecaca','text'=>'#991b1b']];
            foreach($todasMesas as $m):
                $c=$clsMap[$m['estado']]??$clsMap['libre'];
                $px=round(($m['pos_x']/480)*100);
                $py=round(($m['pos_y']/580)*100);
            ?>
            <div style="position:absolute;left:<?= $px ?>%;top:<?= $py ?>%;transform:translate(-50%,-50%);background:<?= $c['bg'] ?>;color:<?= $c['text'] ?>;border-radius:10px;padding:6px 8px;font-size:11px;font-weight:700;text-align:center;min-width:42px;cursor:pointer;" title="Mesa <?= $m['numero'] ?> — <?= $m['estado'] ?>">
                <?= str_pad($m['numero'],2,'0',STR_PAD_LEFT) ?><br>
                <span style="font-size:9px;font-weight:400;"><?= $m['estado']==='libre'?'Libre':($m['personas']?$m['personas'].' pax':'–') ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="display:flex;gap:14px;margin-top:12px;flex-wrap:wrap;">
            <?php foreach(['#94a3b8'=>'Libre','#22c55e'=>'Ocupada','#f59e0b'=>'Reservada','#ef4444'=>'Por liberar'] as $c=>$l): ?>
            <span style="display:flex;align-items:center;gap:5px;font-size:11px;color:#64748b;">
                <span style="width:9px;height:9px;background:<?= $c ?>;border-radius:50%;"></span><?= $l ?>
            </span>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- TABLA Pedidos en cocina -->
<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0;">Pedidos en cocina</h2>
        <a href="<?= BASE_URL ?>/cocina" style="font-size:13px;color:#2563eb;font-weight:500;text-decoration:none;display:flex;align-items:center;gap:4px;">
            Ver en cocina <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
        </a>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Pedido</th><th>Mesa</th><th>Mesero</th><th>Tiempo en cocina</th><th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach(array_slice($pedidosCocina,0,5) as $p): ?>
            <tr>
                <td><strong>#<?= $p['numero'] ?></strong></td>
                <td><?= str_pad($p['mesa_numero'],2,'0',STR_PAD_LEFT) ?></td>
                <td><?= htmlspecialchars($p['mesero_nombre']) ?></td>
                <td><?= $p['minutos'] ?> min</td>
                <td>
                    <?php $min=(int)$p['minutos']; if($min>=15): ?>
                    <span class="badge badge-urgente">Urgente</span>
                    <?php elseif($p['estado']==='en_cocina'): ?>
                    <span class="badge badge-prep">En preparación</span>
                    <?php else: ?>
                    <span class="badge badge-listo">Por servir</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($pedidosCocina)): ?>
            <tr><td colspan="5" style="text-align:center;padding:32px;color:#94a3b8;">Sin pedidos activos en cocina</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
$title = 'Dashboard'; $pageTitle = 'Dashboard'; $pageSubtitle = 'Resumen general de operaciones'; $module = 'dashboard';
require __DIR__ . '/../layout/app.php';
