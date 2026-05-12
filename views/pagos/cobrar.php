<?php
// views/pagos/cobrar.php
ob_start();
?>

<?php if ($yaFuePagado): ?>
<!-- ══════════════════ RECIBO DE PAGO (ya cobrado) ══════════════════ -->
<div style="max-width:640px;margin:0 auto;display:flex;flex-direction:column;gap:20px;">

    <!-- Éxito banner -->
    <div style="background:linear-gradient(135deg,#059669,#047857);border-radius:20px;padding:32px;text-align:center;color:#fff;">
        <div style="width:64px;height:64px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <i data-lucide="check-circle" style="width:32px;height:32px;color:#fff;"></i>
        </div>
        <h2 style="font-size:22px;font-weight:800;margin:0 0 6px;">¡Pago registrado!</h2>
        <p style="font-size:14px;opacity:0.85;margin:0;">Mesa <?= str_pad($pedido['mesa_numero'],2,'0',STR_PAD_LEFT) ?> — Pedido #<?= $pedido['numero'] ?></p>
    </div>

    <!-- Recibo imprimible -->
    <div class="card" style="font-family:'Courier New',monospace;">
        <div style="text-align:center;padding-bottom:16px;border-bottom:2px dashed #e2e8f0;">
            <div style="width:44px;height:44px;background:linear-gradient(135deg,#f59e0b,#d97706);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
                <span style="color:#fff;font-weight:900;font-size:20px;">C</span>
            </div>
            <p style="font-size:15px;font-weight:800;color:#0f172a;margin:0;">LA TERRAZA</p>
            <p style="font-size:11px;color:#64748b;margin:2px 0 0;">Cocina de Autor</p>
        </div>

        <div style="padding:14px 0;border-bottom:1px dashed #e2e8f0;">
            <?php foreach([
                ['Fecha',   date('d/m/Y H:i')],
                ['Mesa',    str_pad($pedido['mesa_numero'],2,'0',STR_PAD_LEFT)],
                ['Mesero',  $pedido['mesero_nombre']],
                ['Personas',$pedido['personas'].' pax'],
                ['Método',  ucfirst($pagoPrevio['metodo'])],
            ] as $r): ?>
            <div style="display:flex;justify-content:space-between;font-size:12px;color:#374151;margin-bottom:5px;">
                <span><?= $r[0] ?></span><span style="font-weight:600;"><?= htmlspecialchars($r[1]) ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="padding:14px 0;border-bottom:1px dashed #e2e8f0;">
            <?php foreach($pedido['items'] as $it): ?>
            <div style="display:flex;justify-content:space-between;font-size:12px;color:#374151;margin-bottom:4px;">
                <span><?= $it['cantidad'] ?>x <?= htmlspecialchars(substr($it['producto_nombre'],0,22)) ?></span>
                <span style="font-weight:600;"><?= formatMoney($it['cantidad']*$it['precio_unitario']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="padding:14px 0;">
            <?php foreach(['Subtotal'=>formatMoney($subtotal),'IVA 19%'=>formatMoney($iva),'Servicio 10%'=>formatMoney($servicio)] as $k=>$v): ?>
            <div style="display:flex;justify-content:space-between;font-size:11px;color:#64748b;margin-bottom:4px;">
                <span><?= $k ?></span><span><?= $v ?></span>
            </div>
            <?php endforeach; ?>
            <div style="display:flex;justify-content:space-between;margin-top:10px;padding-top:10px;border-top:2px solid #374151;">
                <span style="font-size:14px;font-weight:800;color:#0f172a;">TOTAL</span>
                <span style="font-size:14px;font-weight:800;color:#2563eb;"><?= formatMoney($total) ?></span>
            </div>
            <?php if ($pagoPrevio['metodo'] === 'efectivo'): ?>
            <div style="display:flex;justify-content:space-between;margin-top:6px;">
                <span style="font-size:12px;color:#64748b;">Recibido</span>
                <span style="font-size:12px;color:#374151;font-weight:600;"><?= formatMoney($pagoPrevio['recibido']) ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;">
                <span style="font-size:13px;font-weight:700;color:#059669;">Cambio</span>
                <span style="font-size:13px;font-weight:800;color:#059669;"><?= formatMoney($pagoPrevio['cambio']) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <p style="text-align:center;font-size:11px;color:#94a3b8;margin:0;padding-top:10px;border-top:1px dashed #e2e8f0;">
            ¡Gracias por su visita! · Vuelva pronto 🍽
        </p>
    </div>

    <!-- Botones -->
    <div style="display:flex;gap:12px;">
        <button onclick="window.print()" style="flex:1;padding:14px;background:#2563eb;color:#fff;font-size:15px;font-weight:700;border:none;border-radius:14px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;">
            <i data-lucide="printer" style="width:18px;height:18px;"></i> Imprimir ticket
        </button>
        <a href="<?= BASE_URL ?>/pagos" style="flex:1;padding:14px;background:#f8fafc;border:1px solid #e2e8f0;color:#374151;font-size:15px;font-weight:600;border-radius:14px;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:8px;">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i> Volver a cobros
        </a>
    </div>
</div>

<?php else: ?>
<!-- ══════════════════ FORMULARIO DE COBRO ══════════════════ -->
<div style="display:grid;grid-template-columns:1fr 1.1fr 1fr;gap:20px;align-items:start;">

<!-- COL 1: Info + artículos -->
<div style="display:flex;flex-direction:column;gap:16px;">
    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0;">Información</h2>
            <span style="background:<?= $pedido['estado']==='listo'?'#fef3c7':'#dbeafe' ?>;color:<?= $pedido['estado']==='listo'?'#d97706':'#1d4ed8' ?>;font-size:12px;font-weight:700;padding:4px 12px;border-radius:99px;">
                <?= $pedido['estado'] === 'listo' ? '✓ Listo' : '🍽 Servido' ?>
            </span>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <?php foreach([
                ['Mesa',    str_pad($pedido['mesa_numero'],2,'0',STR_PAD_LEFT)],
                ['Pedido',  '#'.$pedido['numero']],
                ['Mesero',  $pedido['mesero_nombre']],
                ['Personas',$pedido['personas'].' pax'],
                ['Apertura',date('h:i a',strtotime($pedido['created_at']))],
                ['Tiempo',  $pedido['minutos'].' min'],
            ] as $i): ?>
            <div>
                <p style="font-size:11px;color:#94a3b8;margin:0 0 2px;text-transform:uppercase;letter-spacing:0.04em;"><?= $i[0] ?></p>
                <p style="font-size:14px;font-weight:700;color:#0f172a;margin:0;"><?= htmlspecialchars($i[1]) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card">
        <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 14px;">
            Artículos
            <span style="font-size:13px;color:#94a3b8;font-weight:400;margin-left:6px;"><?= count($pedido['items']) ?> productos</span>
        </h2>
        <div>
            <?php foreach($pedido['items'] as $it): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f8fafc;">
                <div>
                    <p style="font-size:14px;font-weight:500;color:#374151;margin:0;"><?= htmlspecialchars($it['producto_nombre']) ?></p>
                    <p style="font-size:12px;color:#94a3b8;margin:2px 0 0;"><?= $it['cantidad'] ?>x <?= formatMoney($it['precio_unitario']) ?></p>
                </div>
                <p style="font-size:14px;font-weight:700;color:#0f172a;margin:0;"><?= formatMoney($it['cantidad']*$it['precio_unitario']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="margin-top:14px;padding-top:14px;border-top:1px solid #e2e8f0;">
            <?php foreach(['Subtotal'=>formatMoney($subtotal),'IVA 16%'=>formatMoney($iva),'Servicio 10%'=>formatMoney($servicio)] as $k=>$v): ?>
            <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <span style="font-size:13px;color:#64748b;"><?= $k ?></span>
                <span style="font-size:13px;color:#374151;font-weight:500;"><?= $v ?></span>
            </div>
            <?php endforeach; ?>
            <div style="display:flex;justify-content:space-between;padding-top:10px;border-top:2px solid #f1f5f9;margin-top:4px;">
                <span style="font-size:16px;font-weight:800;color:#0f172a;">Total a pagar</span>
                <span style="font-size:18px;font-weight:800;color:#2563eb;"><?= formatMoney($total) ?></span>
            </div>
        </div>
    </div>
</div>

<!-- COL 2: Formulario de pago -->
<div class="card">
    <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 4px;">Registrar cobro</h2>
    <p style="font-size:13px;color:#94a3b8;margin:0 0 20px;">Total: <strong style="color:#2563eb;font-size:16px;"><?= formatMoney($total) ?></strong></p>

    <form method="POST" id="form-pago" style="display:flex;flex-direction:column;gap:16px;">
        <!-- Métodos de pago -->
        <div>
            <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:10px;">Método de pago</label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <?php
                $metodos=[
                    ['v'=>'efectivo',     'icon'=>'banknote',    'l'=>'Efectivo'],
                    ['v'=>'tarjeta',      'icon'=>'credit-card', 'l'=>'Tarjeta'],
                    ['v'=>'transferencia','icon'=>'building-2',  'l'=>'Transferencia'],
                    ['v'=>'qr',           'icon'=>'qr-code',     'l'=>'QR / CoDi'],
                ];
                foreach($metodos as $m): ?>
                <label id="lbl-<?= $m['v'] ?>"
                    style="display:flex;flex-direction:column;align-items:center;gap:8px;padding:14px;border:2px solid <?= $m['v']==='efectivo'?'#2563eb':'#e2e8f0' ?>;border-radius:14px;cursor:pointer;background:<?= $m['v']==='efectivo'?'#eff6ff':'' ?>;transition:all 0.15s;"
                    onclick="selectMetodo('<?= $m['v'] ?>')">
                    <input type="radio" name="metodo" value="<?= $m['v'] ?>" style="display:none;" <?= $m['v']==='efectivo'?'checked':'' ?>>
                    <i data-lucide="<?= $m['icon'] ?>" style="width:22px;height:22px;color:<?= $m['v']==='efectivo'?'#2563eb':'#64748b' ?>;"></i>
                    <span style="font-size:13px;font-weight:600;color:#374151;"><?= $m['l'] ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Monto recibido (solo efectivo) -->
        <div id="campo-recibido">
            <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;">Monto recibido</label>
            <div style="position:relative;">
                <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:16px;font-weight:700;color:#64748b;">$</span>
                <input type="number" name="recibido" id="inp-recibido"
                    value="<?= number_format($total,2,'.','') ?>" step="0.01" min="<?= number_format($total,2,'.','') ?>"
                    style="width:100%;padding:14px 16px 14px 32px;border:2px solid #e2e8f0;border-radius:12px;font-size:20px;font-weight:800;color:#0f172a;box-sizing:border-box;background:#f8fafc;outline:none;">
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;padding:10px 14px;background:#f0fdf4;border-radius:10px;border:1px solid #bbf7d0;">
                <span style="font-size:13px;color:#065f46;font-weight:500;">💵 Vuelto</span>
                <span id="cambio-label" style="font-size:16px;font-weight:800;color:#94a3b8;">$ 0</span>
            </div>
            <div style="margin-top:10px;">
                <p style="font-size:11px;color:#94a3b8;margin:0 0 6px;text-transform:uppercase;letter-spacing:0.04em;">Montos rápidos</p>
                <div id="atajos-cobro" style="display:flex;flex-wrap:wrap;gap:6px;"></div>
            </div>
        </div>

        <!-- Referencia (tarjeta/transferencia) -->
        <div id="campo-ref" style="display:none;">
            <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;">Referencia / Autorización</label>
            <input type="text" name="referencia" id="inp-ref" placeholder="Ej. Aprobado **** 4242"
                style="width:100%;padding:12px 14px;border:2px solid #e2e8f0;border-radius:12px;font-size:14px;box-sizing:border-box;outline:none;">
        </div>

        <!-- Botón cobrar -->
        <button type="submit" id="btn-cobrar"
            style="width:100%;padding:16px;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;font-size:16px;font-weight:800;border:none;border-radius:14px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 4px 14px rgba(37,99,235,0.35);transition:all 0.2s;">
            <i data-lucide="check-circle" style="width:20px;height:20px;"></i> Cobrar <?= formatMoney($total) ?>
        </button>
        <a href="<?= BASE_URL ?>/pagos"
            style="width:100%;padding:12px;background:#f8fafc;border:1px solid #e2e8f0;color:#374151;font-size:14px;font-weight:500;border-radius:12px;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:6px;box-sizing:border-box;">
            <i data-lucide="arrow-left" style="width:15px;height:15px;"></i> Cancelar
        </a>
    </form>
</div>

<!-- COL 3: Ticket preview -->
<div class="card">
    <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 16px;">Vista previa del ticket</h2>
    <div style="background:#f8fafc;border-radius:14px;padding:20px;font-family:'Courier New',monospace;">
        <div style="text-align:center;margin-bottom:14px;">
            <div style="width:40px;height:40px;background:linear-gradient(135deg,#f59e0b,#d97706);border-radius:10px;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;">
                <span style="color:#fff;font-weight:800;font-size:18px;">C</span>
            </div>
            <p style="font-size:14px;font-weight:800;color:#0f172a;margin:0;">LA TERRAZA</p>
            <p style="font-size:11px;color:#64748b;margin:2px 0 0;">Cocina de Autor</p>
        </div>
        <div style="border-top:1px dashed #cbd5e1;margin:12px 0;"></div>
        <?php foreach([
            ['Fecha',   date('d/m/Y H:i')],
            ['Mesa',    str_pad($pedido['mesa_numero'],2,'0',STR_PAD_LEFT)],
            ['Mesero',  explode(' ',$pedido['mesero_nombre'])[0]],
            ['Personas',$pedido['personas'].' pax'],
        ] as $row): ?>
        <div style="display:flex;justify-content:space-between;font-size:12px;color:#374151;margin-bottom:5px;">
            <span><?= $row[0] ?></span><span style="font-weight:600;"><?= htmlspecialchars($row[1]) ?></span>
        </div>
        <?php endforeach; ?>
        <div style="border-top:1px dashed #cbd5e1;margin:12px 0;"></div>
        <?php foreach($pedido['items'] as $it): ?>
        <div style="display:flex;justify-content:space-between;font-size:12px;color:#374151;margin-bottom:4px;">
            <span><?= $it['cantidad'] ?>x <?= htmlspecialchars(substr($it['producto_nombre'],0,18)) ?></span>
            <span style="font-weight:600;"><?= formatMoney($it['cantidad']*$it['precio_unitario']) ?></span>
        </div>
        <?php endforeach; ?>
        <div style="border-top:1px dashed #cbd5e1;margin:12px 0;"></div>
        <?php foreach(['Subtotal'=>formatMoney($subtotal),'IVA 16%'=>formatMoney($iva),'Servicio 10%'=>formatMoney($servicio)] as $k=>$v): ?>
        <div style="display:flex;justify-content:space-between;font-size:11px;color:#64748b;margin-bottom:4px;">
            <span><?= $k ?></span><span><?= $v ?></span>
        </div>
        <?php endforeach; ?>
        <div style="border-top:2px solid #374151;margin:10px 0;"></div>
        <div style="display:flex;justify-content:space-between;">
            <span style="font-size:15px;font-weight:800;color:#0f172a;">TOTAL</span>
            <span id="ticket-total" style="font-size:15px;font-weight:800;color:#2563eb;"><?= formatMoney($total) ?></span>
        </div>
        <div id="ticket-cambio-row" style="display:none;margin-top:6px;">
            <div style="display:flex;justify-content:space-between;font-size:12px;color:#64748b;">
                <span>Recibido</span><span id="ticket-recibido">—</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:700;color:#059669;">
                <span>Cambio</span><span id="ticket-cambio">—</span>
            </div>
        </div>
        <p style="text-align:center;font-size:11px;color:#94a3b8;margin:14px 0 0;">¡Gracias por su visita!</p>
    </div>
</div>

</div><!-- fin grid -->
<?php endif; ?>

<script>
const total = <?= number_format($total, 0, '.', '') ?>;   // COP sin decimales

// Formateador COP
function fmtCOP(n) {
    return '$ ' + Math.round(n).toLocaleString('es-CO');
}

function selectMetodo(v) {
    ['efectivo','tarjeta','transferencia','qr'].forEach(m => {
        const l = document.getElementById('lbl-'+m);
        if (!l) return;
        const ico = l.querySelector('i');
        l.style.borderColor = m===v ? '#2563eb' : '#e2e8f0';
        l.style.background  = m===v ? '#eff6ff' : '';
        if (ico) ico.style.color = m===v ? '#2563eb' : '#64748b';
        l.querySelector('input').checked = m===v;
    });
    const mostrarRecibido = v === 'efectivo';
    const mostrarRef = v === 'tarjeta' || v === 'transferencia';
    document.getElementById('campo-recibido').style.display = mostrarRecibido ? '' : 'none';
    document.getElementById('campo-ref').style.display = mostrarRef ? '' : 'none';
    if (mostrarRecibido) calcularCambio();
    else { const r = document.getElementById('ticket-cambio-row'); if(r) r.style.display='none'; }
}

function calcularCambio() {
    const inp = document.getElementById('inp-recibido');
    const cambioLabel = document.getElementById('cambio-label');
    const ticketRow = document.getElementById('ticket-cambio-row');
    if (!inp || !cambioLabel) return;
    const recibido = parseFloat(inp.value) || 0;
    const cambio = Math.max(0, recibido - total);
    cambioLabel.textContent = fmtCOP(cambio);
    cambioLabel.style.color = cambio > 0 ? '#059669' : '#94a3b8';
    if (ticketRow) {
        const tr = document.getElementById('ticket-recibido');
        const tc = document.getElementById('ticket-cambio');
        if (tr) tr.textContent = fmtCOP(recibido);
        if (tc) tc.textContent = fmtCOP(cambio);
        ticketRow.style.display = recibido > 0 ? '' : 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const inp = document.getElementById('inp-recibido');
    if (inp) {
        inp.addEventListener('input',  calcularCambio);
        inp.addEventListener('keyup',  calcularCambio);
        inp.addEventListener('change', calcularCambio);
        calcularCambio();
    }
    // Atajos de monto rápido — redondeo a miles (COP)
    const container = document.getElementById('atajos-cobro');
    if (container && inp) {
        const base = Math.ceil(total / 1000) * 1000;
        [...new Set([total, base, base+1000, base+2000, base+5000, base+10000])].forEach(monto => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = fmtCOP(monto);
            btn.style.cssText = 'padding:8px 12px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;font-weight:600;color:#374151;cursor:pointer;transition:all .15s;white-space:nowrap;';
            btn.onmouseover = () => { btn.style.background='#dbeafe'; btn.style.borderColor='#93c5fd'; btn.style.color='#1d4ed8'; };
            btn.onmouseout  = () => { btn.style.background='#f1f5f9'; btn.style.borderColor='#e2e8f0'; btn.style.color='#374151'; };
            btn.onclick = () => { inp.value = Math.round(monto); calcularCambio(); inp.focus(); };
            container.appendChild(btn);
        });
    }
});

document.getElementById('form-pago')?.addEventListener('submit', function() {
    const btn = document.getElementById('btn-cobrar');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span style="display:inline-block;width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;vertical-align:middle;margin-right:8px;"></span>Procesando...';
    }
});
</script>
<style>@keyframes spin{to{transform:rotate(360deg)}}</style>

<?php
$content=ob_get_clean();
$title='Cobrar #'.$pedido['numero'];
$pageTitle='Cobrar — Mesa '.str_pad($pedido['mesa_numero'],2,'0',STR_PAD_LEFT);
$pageSubtitle='Pedido #'.$pedido['numero'].' · '.$pedido['mesero_nombre'];
$module='pagos';
require __DIR__ . '/../layout/app.php';
