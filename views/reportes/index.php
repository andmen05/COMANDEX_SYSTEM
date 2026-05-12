<?php
// views/reportes/index.php
ob_start();
$maxV = !empty($ventasSemana) ? max(array_column($ventasSemana,'total')) : 1;
$totalSemana = array_sum(array_column($ventasSemana,'total'));
$totalVendidos = array_sum(array_column($topProductos,'vendidos'));
?>
<div style="display:flex;flex-direction:column;gap:20px;">

<!-- KPIs -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
    <?php
    $kpis=[
        ['ic'=>'trending-up','bg'=>'#d1fae5','col'=>'#059669','val'=>formatMoney($ventasHoy),'label'=>'Ventas hoy'],
        ['ic'=>'bar-chart-2','bg'=>'#dbeafe','col'=>'#2563eb','val'=>formatMoney($totalSemana),'label'=>'Ventas esta semana'],
        ['ic'=>'package','bg'=>'#ede9fe','col'=>'#7c3aed','val'=>$totalVendidos,'label'=>'Productos vendidos (total)'],
    ];
    foreach($kpis as $k): ?>
    <div class="card">
        <div style="width:46px;height:46px;background:<?= $k['bg'] ?>;border-radius:13px;display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
            <i data-lucide="<?= $k['ic'] ?>" style="width:22px;height:22px;color:<?= $k['col'] ?>;"></i>
        </div>
        <p style="font-size:28px;font-weight:800;color:#0f172a;margin:0;"><?= $k['val'] ?></p>
        <p style="font-size:13px;color:#64748b;margin:4px 0 0;"><?= $k['label'] ?></p>
    </div>
    <?php endforeach; ?>
</div>

<!-- Gráfico de barras: Ventas 7 días -->
<div class="card">
    <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 24px;">Ventas últimos 7 días</h2>
    <?php if(empty($ventasSemana)): ?>
    <div style="text-align:center;padding:32px;color:#cbd5e1;">
        <i data-lucide="bar-chart-2" style="width:40px;height:40px;margin:0 auto 10px;"></i>
        <p>Sin datos de ventas registrados</p>
    </div>
    <?php else: ?>
    <div style="display:flex;align-items:flex-end;gap:10px;height:160px;padding-bottom:8px;">
        <?php foreach($ventasSemana as $v):
            $h=max(4, round(($v['total']/$maxV)*100));
            $dias=['Mon'=>'Lun','Tue'=>'Mar','Wed'=>'Mié','Thu'=>'Jue','Fri'=>'Vie','Sat'=>'Sáb','Sun'=>'Dom'];
            $dia=$dias[date('D',strtotime($v['dia']))]??date('D',strtotime($v['dia']));
        ?>
        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;height:100%;justify-content:flex-end;">
            <span style="font-size:10px;color:#64748b;font-weight:500;"><?= formatMoney($v['total']) ?></span>
            <div style="width:100%;background:linear-gradient(180deg,#3b82f6,#1d4ed8);border-radius:8px 8px 4px 4px;height:<?= $h ?>%;transition:height 0.3s;min-height:4px;"
                 title="<?= $dia ?>: <?= formatMoney($v['total']) ?>"></div>
            <span style="font-size:11px;color:#94a3b8;"><?= $dia ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Top productos -->
<div class="card">
    <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 20px;">Top productos más vendidos</h2>
    <?php if(empty($topProductos)): ?>
    <p style="color:#94a3b8;font-size:14px;text-align:center;padding:24px 0;">Sin datos de ventas</p>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:14px;">
        <?php foreach(array_slice($topProductos,0,8) as $i=>$p):
            $bar=$topProductos[0]['vendidos']>0?round(($p['vendidos']/$topProductos[0]['vendidos'])*100):0;
        ?>
        <div style="display:flex;align-items:center;gap:12px;">
            <span style="font-size:13px;font-weight:800;color:#cbd5e1;width:18px;text-align:right;flex-shrink:0;"><?= $i+1 ?></span>
            <div style="flex:1;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">
                    <span style="font-size:14px;font-weight:600;color:#0f172a;"><?= htmlspecialchars($p['nombre']) ?></span>
                    <span style="font-size:12px;color:#64748b;"><?= $p['vendidos'] ?> uds &nbsp;·&nbsp; <?= formatMoney($p['total']) ?></span>
                </div>
                <div style="height:6px;background:#f1f5f9;border-radius:99px;overflow:hidden;">
                    <div style="height:6px;background:linear-gradient(90deg,#3b82f6,#2563eb);border-radius:99px;width:<?= $bar ?>%;"></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
</div>
<?php
$content=ob_get_clean();
$title='Reportes'; $pageTitle='Reportes'; $pageSubtitle='Análisis de ventas y desempeño'; $module='reportes';
require __DIR__ . '/../layout/app.php';
