<?php
// views/cocina/index.php — Kanban rediseñado
ob_start();
$totalActivos = count($nuevo) + count($en_preparacion) + count($listo);

function renderCard(array $p, string $col): string {
    $min = (int)$p['minutos'];
    $timeBg    = $min<=10 ? '#d1fae5' : ($min<=20 ? '#fef3c7' : '#fee2e2');
    $timeColor = $min<=10 ? '#059669' : ($min<=20 ? '#d97706' : '#dc2626');
    $borderLeft= $col==='listo' ? '3px solid #10b981' : ($min>15 ? '3px solid #ef4444' : '3px solid transparent');
    ob_start(); ?>
    <div class="kanban-card" style="border-left:<?= $borderLeft ?>;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:10px;">
            <div>
                <span style="font-size:15px;font-weight:800;color:#0f172a;">#<?= $p['numero'] ?></span>
                <span style="font-size:12px;color:#64748b;margin-left:8px;">Mesa <?= str_pad($p['mesa_numero'],2,'0',STR_PAD_LEFT) ?></span>
            </div>
            <span style="background:<?= $timeBg ?>;color:<?= $timeColor ?>;font-size:12px;font-weight:600;padding:3px 10px;border-radius:99px;display:flex;align-items:center;gap:4px;">
                <i data-lucide="clock" style="width:12px;height:12px;"></i> <?= $min ?> min
            </span>
        </div>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
            <p style="font-size:12px;color:#64748b;margin:0;"><?= htmlspecialchars($p['mesero_nombre']) ?></p>
            <?php if($min>15): ?>
            <span style="background:#fee2e2;color:#dc2626;font-size:11px;font-weight:700;padding:2px 8px;border-radius:99px;">Urgente</span>
            <?php elseif($col==='en_cocina'): ?>
            <span style="background:#fef3c7;color:#d97706;font-size:11px;font-weight:600;padding:2px 8px;border-radius:99px;">En preparación</span>
            <?php endif; ?>
        </div>
        <div style="margin-bottom:14px;display:flex;flex-direction:column;gap:5px;">
            <?php foreach($p['items'] as $item): ?>
            <div>
                <span style="font-size:13px;color:#374151;"><strong><?= $item['cantidad'] ?>x</strong> <?= htmlspecialchars($item['producto_nombre']) ?></span>
                <?php if($item['notas']): ?>
                <p style="font-size:11px;color:#94a3b8;margin:1px 0 0 16px;">↳ <?= htmlspecialchars($item['notas']) ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <form method="POST" style="display:flex;gap:8px;">
            <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
            <?php if($col==='nuevo'): ?>
                <input type="hidden" name="estado" value="en_cocina">
                <button type="submit" style="flex:1;padding:9px;background:#2563eb;color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">
                    <i data-lucide="play" style="width:14px;height:14px;"></i> Iniciar
                </button>
            <?php elseif($col==='en_cocina'): ?>
                <input type="hidden" name="estado" value="listo">
                <button type="submit" style="flex:1;padding:9px;background:#d97706;color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">
                    <i data-lucide="check" style="width:14px;height:14px;"></i> Marcar listo
                </button>
            <?php elseif($col==='listo'): ?>
                <input type="hidden" name="estado" value="servido">
                <button type="submit" style="flex:1;padding:9px;background:#059669;color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">
                    <i data-lucide="check-check" style="width:14px;height:14px;"></i> Entregar
                </button>
            <?php endif; ?>
            <button type="button" style="padding:9px 12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;font-size:12px;color:#64748b;cursor:pointer;display:flex;align-items:center;gap:4px;">
                <i data-lucide="bell" style="width:13px;height:13px;"></i> Llamar
            </button>
        </form>
    </div>
    <?php return ob_get_clean();
}
?>

<div style="display:flex;flex-direction:column;gap:20px;">
    <!-- Header bar -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div style="display:flex;align-items:center;gap:8px;">
            <span style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:8px 16px;font-size:14px;font-weight:700;color:#0f172a;">
                Todos <span style="background:#dbeafe;color:#1d4ed8;font-size:12px;padding:2px 8px;border-radius:99px;margin-left:6px;"><?= $totalActivos ?></span>
            </span>
        </div>
        <div style="display:flex;align-items:center;gap:16px;font-size:12px;color:#64748b;">
            <span style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;background:#22c55e;border-radius:50%;"></span>0-10 min</span>
            <span style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;background:#f59e0b;border-radius:50%;"></span>11-20 min</span>
            <span style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;background:#ef4444;border-radius:50%;"></span>>20 min</span>
            <button onclick="location.reload()" style="display:flex;align-items:center;gap:6px;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:7px 12px;cursor:pointer;font-size:12px;color:#374151;">
                <i data-lucide="refresh-cw" style="width:13px;height:13px;"></i> Actualizar
            </button>
        </div>
    </div>

    <!-- Kanban 3 cols -->
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;align-items:start;">

        <!-- Nuevo -->
        <div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
                <span style="width:12px;height:12px;background:#3b82f6;border-radius:50%;"></span>
                <h3 style="font-size:14px;font-weight:700;color:#0f172a;margin:0;">Nuevo</h3>
                <span style="background:#dbeafe;color:#1d4ed8;font-size:12px;font-weight:600;padding:2px 8px;border-radius:99px;"><?= count($nuevo) ?></span>
            </div>
            <div style="display:flex;flex-direction:column;gap:12px;">
                <?php foreach($nuevo as $p): echo renderCard($p,'nuevo'); endforeach; ?>
                <?php if(empty($nuevo)): ?>
                <div style="border:2px dashed #e2e8f0;border-radius:16px;padding:32px;text-align:center;color:#cbd5e1;">
                    <i data-lucide="inbox" style="width:28px;height:28px;margin:0 auto 8px;"></i>
                    <p style="font-size:13px;margin:0;">Sin pedidos nuevos</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- En preparación -->
        <div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
                <span style="width:12px;height:12px;background:#f59e0b;border-radius:50%;"></span>
                <h3 style="font-size:14px;font-weight:700;color:#0f172a;margin:0;">En preparación</h3>
                <span style="background:#fef3c7;color:#d97706;font-size:12px;font-weight:600;padding:2px 8px;border-radius:99px;"><?= count($en_preparacion) ?></span>
            </div>
            <div style="display:flex;flex-direction:column;gap:12px;">
                <?php foreach($en_preparacion as $p): echo renderCard($p,'en_cocina'); endforeach; ?>
                <?php if(empty($en_preparacion)): ?>
                <div style="border:2px dashed #e2e8f0;border-radius:16px;padding:32px;text-align:center;color:#cbd5e1;">
                    <i data-lucide="chef-hat" style="width:28px;height:28px;margin:0 auto 8px;"></i>
                    <p style="font-size:13px;margin:0;">Sin pedidos en preparación</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Listo -->
        <div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
                <span style="width:12px;height:12px;background:#10b981;border-radius:50%;"></span>
                <h3 style="font-size:14px;font-weight:700;color:#0f172a;margin:0;">Listo para servir</h3>
                <span style="background:#d1fae5;color:#059669;font-size:12px;font-weight:600;padding:2px 8px;border-radius:99px;"><?= count($listo) ?></span>
            </div>
            <div style="display:flex;flex-direction:column;gap:12px;">
                <?php foreach($listo as $p): echo renderCard($p,'listo'); endforeach; ?>
                <?php if(empty($listo)): ?>
                <div style="border:2px dashed #e2e8f0;border-radius:16px;padding:32px;text-align:center;color:#cbd5e1;">
                    <i data-lucide="check-circle" style="width:28px;height:28px;margin:0 auto 8px;"></i>
                    <p style="font-size:13px;margin:0;">Sin pedidos listos</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div style="display:flex;justify-content:space-between;font-size:12px;color:#94a3b8;">
        <span>Color de indicador = tiempo transcurrido</span>
        <span>Última actualización: <?= date('H:i:s') ?> <span style="color:#22c55e;">●</span></span>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Cocina'; $pageTitle = 'Cocina'; $pageSubtitle = 'Gestión de pedidos en tiempo real'; $module = 'cocina';
require __DIR__ . '/../layout/app.php';
