<?php
// views/pagos/index.php
ob_start();
?>
<div style="display:flex;flex-direction:column;gap:20px;">
    <?php if(empty($pendientes)): ?>
    <div class="card" style="text-align:center;padding:64px 32px;">
        <div style="width:64px;height:64px;background:#d1fae5;border-radius:20px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <i data-lucide="check-circle" style="width:30px;height:30px;color:#059669;"></i>
        </div>
        <h3 style="font-size:18px;font-weight:700;color:#0f172a;margin:0 0 6px;">¡Todo al día!</h3>
        <p style="font-size:14px;color:#64748b;margin:0;">No hay cuentas pendientes de cobro.</p>
    </div>
    <?php else: ?>
    <p style="font-size:14px;color:#64748b;margin:0;">Selecciona una mesa para registrar el cobro</p>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">
        <?php foreach($pendientes as $p):
            $min=(int)$p['minutos'];
            $timeBg=$min<=10?'#d1fae5':($min<=20?'#fef3c7':'#fee2e2');
            $timeCol=$min<=10?'#059669':($min<=20?'#d97706':'#dc2626');
        ?>
        <div class="card" style="display:flex;flex-direction:column;gap:14px;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;">
                <div>
                    <p style="font-size:18px;font-weight:800;color:#0f172a;margin:0;">#<?= $p['numero'] ?></p>
                    <p style="font-size:13px;color:#64748b;margin:2px 0 0;">Mesa <?= str_pad($p['mesa_numero'],2,'0',STR_PAD_LEFT) ?></p>
                </div>
                <span style="background:<?= $timeBg ?>;color:<?= $timeCol ?>;font-size:12px;font-weight:600;padding:4px 10px;border-radius:99px;display:flex;align-items:center;gap:4px;">
                    <i data-lucide="clock" style="width:12px;height:12px;"></i> <?= $min ?> min
                </span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:#64748b;">
                <i data-lucide="user" style="width:15px;height:15px;"></i>
                <?= htmlspecialchars($p['mesero_nombre']) ?>
                <span style="margin-left:auto;font-size:12px;background:#f1f5f9;color:#475569;padding:3px 10px;border-radius:99px;"><?= $p['total_items'] ?> artículos</span>
            </div>
            <div style="display:flex;gap:8px;">
                <a href="<?= BASE_URL ?>/pagos/cobrar/<?= $p['id'] ?>"
                   style="flex:1;padding:11px;background:#2563eb;color:#fff;font-size:14px;font-weight:700;border-radius:12px;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:6px;">
                    <i data-lucide="credit-card" style="width:15px;height:15px;"></i> Cobrar
                </a>
                <a href="<?= BASE_URL ?>/mesero/pedido/<?= $p['mesa_id'] ?>"
                   style="padding:11px 18px;background:#f8fafc;border:1px solid #e2e8f0;color:#374151;font-size:14px;font-weight:500;border-radius:12px;text-decoration:none;display:flex;align-items:center;justify-content:center;">
                    Ver
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php
$content=ob_get_clean();
$title='Pagos'; $pageTitle='Pagos'; $pageSubtitle='Cobro y cierre de cuenta'; $module='pagos';
require __DIR__ . '/../layout/app.php';
