<?php
// views/mesero/index.php
ob_start();
$zonaF = $_GET['zona'] ?? '';
$colorMap = [
    'libre'       => ['bg'=>'#fff','border'=>'#e2e8f0','text'=>'#374151','dot'=>'#94a3b8','label'=>'Libre'],
    'ocupada'     => ['bg'=>'#f0fdf4','border'=>'#86efac','text'=>'#065f46','dot'=>'#22c55e','label'=>'Ocupada'],
    'reservada'   => ['bg'=>'#fffbeb','border'=>'#fcd34d','text'=>'#92400e','dot'=>'#f59e0b','label'=>'Reservada'],
    'por_liberar' => ['bg'=>'#fff1f2','border'=>'#fca5a5','text'=>'#991b1b','dot'=>'#ef4444','label'=>'Por liberar'],
];
?>
<div style="display:flex;flex-direction:column;gap:20px;">

    <!-- Filtros de zona -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <?php foreach([''=>'Todas','salon'=>'Salón','terraza'=>'Terraza','privado'=>'Privado'] as $k=>$v):
                $act=$zonaF===$k; ?>
            <a href="<?= BASE_URL ?>/mesero<?= $k?"?zona=$k":'' ?>"
               style="padding:8px 18px;border-radius:10px;font-size:14px;font-weight:600;text-decoration:none;border:1px solid <?= $act?'#2563eb':'#e2e8f0' ?>;background:<?= $act?'#2563eb':'#fff' ?>;color:<?= $act?'#fff':'#374151' ?>;">
                <?= $v ?>
            </a>
            <?php endforeach; ?>
        </div>
        <span style="font-size:13px;color:#94a3b8;"><?= count($mesas) ?> mesas</span>
    </div>

    <!-- Grid de mesas -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:14px;">
        <?php foreach($mesas as $m):
            if($zonaF && $m['zona']!==$zonaF) continue;
            $c=$colorMap[$m['estado']]??$colorMap['libre'];
        ?>
        <a href="<?= BASE_URL ?>/mesero/pedido/<?= $m['id'] ?>"
           style="background:<?= $c['bg'] ?>;border:2px solid <?= $c['border'] ?>;border-radius:18px;padding:24px 16px;text-align:center;text-decoration:none;display:block;transition:all 0.15s;position:relative;overflow:hidden;"
           onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.1)';"
           onmouseout="this.style.transform='';this.style.boxShadow='';">
            <p style="font-size:38px;font-weight:900;color:<?= $c['text'] ?>;margin:0 0 6px;line-height:1;"><?= str_pad($m['numero'],2,'0',STR_PAD_LEFT) ?></p>
            <div style="display:flex;align-items:center;justify-content:center;gap:5px;margin-bottom:6px;">
                <span style="width:8px;height:8px;background:<?= $c['dot'] ?>;border-radius:50%;flex-shrink:0;"></span>
                <span style="font-size:12px;font-weight:600;color:<?= $c['text'] ?>;"><?= $c['label'] ?></span>
            </div>
            <?php if($m['personas']): ?>
            <p style="font-size:11px;color:<?= $c['dot'] ?>;margin:0;"><?= $m['personas'] ?> pax</p>
            <?php endif; ?>
            <!-- Efecto hover -->
            <div style="position:absolute;inset:0;background:rgba(37,99,235,0.04);opacity:0;transition:opacity 0.15s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0"></div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Leyenda -->
    <div style="display:flex;gap:20px;flex-wrap:wrap;">
        <?php foreach(['#94a3b8'=>'Libre','#22c55e'=>'Ocupada — toca para ver pedido','#f59e0b'=>'Reservada','#ef4444'=>'Por liberar'] as $col=>$lbl): ?>
        <span style="display:flex;align-items:center;gap:6px;font-size:12px;color:#64748b;">
            <span style="width:10px;height:10px;background:<?= $col ?>;border-radius:50%;flex-shrink:0;"></span><?= $lbl ?>
        </span>
        <?php endforeach; ?>
    </div>
</div>
<?php
$content=ob_get_clean();
$title='Mesero'; $pageTitle='Seleccionar mesa'; $pageSubtitle='Elige una mesa para gestionar su pedido'; $module='mesero';
require __DIR__ . '/../layout/app.php';
