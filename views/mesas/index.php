<?php
// views/mesas/index.php
ob_start();
$colorMap = [
    'libre'       => ['bg'=>'#fff','border'=>'#e2e8f0','text'=>'#374151','dot'=>'#94a3b8'],
    'ocupada'     => ['bg'=>'#f0fdf4','border'=>'#86efac','text'=>'#065f46','dot'=>'#22c55e'],
    'reservada'   => ['bg'=>'#fffbeb','border'=>'#fcd34d','text'=>'#92400e','dot'=>'#f59e0b'],
    'por_liberar' => ['bg'=>'#fff1f2','border'=>'#fca5a5','text'=>'#991b1b','dot'=>'#ef4444'],
];
?>

<div style="display:flex;flex-direction:column;gap:20px;">

<!-- Stats rápidos -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;">
    <?php
    $sl=[['k'=>'total','l'=>'Total mesas','ic'=>'layout-grid','bg'=>'#f1f5f9','col'=>'#475569'],
         ['k'=>'ocupada','l'=>'Ocupadas','ic'=>'users','bg'=>'#d1fae5','col'=>'#059669'],
         ['k'=>'libre','l'=>'Libres','ic'=>'check-circle','bg'=>'#f1f5f9','col'=>'#64748b'],
         ['k'=>'reservada','l'=>'Reservadas','ic'=>'calendar','bg'=>'#fef3c7','col'=>'#d97706']];
    foreach($sl as $s): ?>
    <div class="card" style="display:flex;align-items:center;gap:14px;padding:18px;">
        <div style="width:44px;height:44px;background:<?= $s['bg'] ?>;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="<?= $s['ic'] ?>" style="width:20px;height:20px;color:<?= $s['col'] ?>;"></i>
        </div>
        <div>
            <p style="font-size:26px;font-weight:800;color:#0f172a;margin:0;"><?= $stats[$s['k']]??0 ?></p>
            <p style="font-size:12px;color:#64748b;margin:2px 0 0;"><?= $s['l'] ?></p>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Filtros zona -->
<div style="display:flex;align-items:center;gap:8px;">
    <?php foreach([''=>'Todas','salon'=>'Salón','terraza'=>'Terraza','privado'=>'Privado'] as $k=>$v):
        $act=$zonaFiltro===$k; ?>
    <a href="<?= BASE_URL ?>/mesas<?= $k?"?zona=$k":'' ?>"
       style="padding:8px 18px;border-radius:10px;font-size:14px;font-weight:600;text-decoration:none;border:1px solid <?= $act?'#2563eb':'#e2e8f0' ?>;background:<?= $act?'#2563eb':'#fff' ?>;color:<?= $act?'#fff':'#374151' ?>;transition:all 0.15s;">
        <?= $v ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- Grid de mesas -->
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:14px;">
    <?php foreach($mesas as $m):
        $c=$colorMap[$m['estado']]??$colorMap['libre'];
    ?>
    <div style="background:<?= $c['bg'] ?>;border:2px solid <?= $c['border'] ?>;border-radius:18px;padding:20px 14px;text-align:center;cursor:pointer;transition:all 0.15s;position:relative;"
         onmouseover="this.style.transform='scale(1.03)';this.style.boxShadow='0 8px 20px rgba(0,0,0,0.1)';"
         onmouseout="this.style.transform='';this.style.boxShadow='';"
         onclick="window.location='<?= BASE_URL ?>/mesero/pedido/<?= $m['id'] ?>'">
        <p style="font-size:34px;font-weight:800;color:<?= $c['text'] ?>;margin:0 0 4px;"><?= str_pad($m['numero'],2,'0',STR_PAD_LEFT) ?></p>
        <div style="display:flex;align-items:center;justify-content:center;gap:5px;">
            <span style="width:8px;height:8px;background:<?= $c['dot'] ?>;border-radius:50%;"></span>
            <span style="font-size:12px;font-weight:600;color:<?= $c['text'] ?>;"><?= ucfirst(str_replace('_',' ',$m['estado'])) ?></span>
        </div>
        <?php if($m['personas']): ?>
        <p style="font-size:11px;color:<?= $c['dot'] ?>;margin:4px 0 0;"><?= $m['personas'] ?> pax</p>
        <?php endif; ?>
        <?php if($m['pedido_inicio']): ?>
        <p style="font-size:10px;color:<?= $c['dot'] ?>;margin:2px 0 0;"><?= date('H:i',strtotime($m['pedido_inicio'])) ?></p>
        <?php endif; ?>
        <!-- Hover actions -->
        <div style="position:absolute;inset:0;border-radius:16px;background:rgba(0,0,0,0.03);display:flex;align-items:flex-end;justify-content:center;padding-bottom:10px;opacity:0;transition:opacity 0.15s;" class="mesa-actions-<?= $m['id'] ?>">
            <?php if($m['pedido_id']): ?>
            <a href="<?= BASE_URL ?>/pagos/cobrar/<?= $m['pedido_id'] ?>" style="background:#2563eb;color:#fff;font-size:11px;font-weight:700;padding:4px 10px;border-radius:7px;text-decoration:none;" onclick="event.stopPropagation();">Cobrar</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Leyenda -->
<div style="display:flex;gap:20px;flex-wrap:wrap;">
    <?php foreach(['#94a3b8'=>'Libre','#22c55e'=>'Ocupada','#f59e0b'=>'Reservada','#ef4444'=>'Por liberar'] as $c=>$l): ?>
    <span style="display:flex;align-items:center;gap:6px;font-size:12px;color:#64748b;">
        <span style="width:10px;height:10px;background:<?= $c ?>;border-radius:50%;"></span><?= $l ?>
    </span>
    <?php endforeach; ?>
</div>

</div>
<?php
$content = ob_get_clean();
$title='Mesas'; $pageTitle='Mesas'; $pageSubtitle='Vista y gestión del salón'; $module='mesas';
require __DIR__ . '/../layout/app.php';
