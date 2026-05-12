<?php
// views/mesero/pedido.php — Tomar pedido
ob_start();
$pedido_id = $pedidoActivo['id'] ?? 0;
$items     = $pedidoActivo['items'] ?? [];
$subtotal  = array_sum(array_map(fn($i)=>$i['cantidad']*$i['precio_unitario'], $items));
$iva       = round($subtotal * 0.16, 2);
$total     = $subtotal + $iva;
$catActiva = $_GET['cat'] ?? 'favoritos';

// Filtrar productos
if($catActiva === 'favoritos') {
    $prodMostrar = array_filter($productos, fn($p)=>$p['favorito']);
    if(empty($prodMostrar)) $prodMostrar = $favoritos;
} else {
    $prodMostrar = array_filter($productos, fn($p)=>(string)$p['categoria_id']===$catActiva);
}
?>

<!-- Layout especial 2 columnas sin el padding del main -->
<style>
.main-override { margin: -32px; height: calc(100vh - 80px); display: flex; overflow: hidden; }
.left-col { flex:1; display:flex; flex-direction:column; overflow:hidden; }
.right-col { width: 320px; flex-shrink:0; background:#fff; border-left:1px solid #f1f5f9; display:flex; flex-direction:column; overflow:hidden; }
.prod-card { background:#fff; border:1.5px solid #f1f5f9; border-radius:14px; overflow:hidden; cursor:pointer; transition:all 0.15s; }
.prod-card:hover { border-color:#3b82f6; box-shadow:0 6px 20px rgba(37,99,235,0.12); transform:translateY(-2px); }
.cat-btn { padding:8px 16px; border-radius:10px; font-size:13px; font-weight:600; cursor:pointer; border:1.5px solid #e2e8f0; background:#fff; color:#374151; white-space:nowrap; flex-shrink:0; transition:all 0.15s; }
.cat-btn.active { background:#2563eb; color:#fff; border-color:#2563eb; }
.cat-btn:hover:not(.active) { border-color:#93c5fd; }
</style>

<div class="main-override">

<!-- ═══ COLUMNA IZQUIERDA: Catálogo ═══ -->
<div class="left-col">

    <!-- Barra de búsqueda y filtros -->
    <div style="padding:20px 24px 16px;background:#f8fafc;border-bottom:1px solid #f1f5f9;flex-shrink:0;">
        <!-- Mesa info + búsqueda -->
        <div style="display:flex;gap:12px;margin-bottom:14px;">
            <div style="position:relative;flex:1;">
                <i data-lucide="search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:#94a3b8;"></i>
                <input type="text" id="search-prod" placeholder="Buscar en el menú..."
                    style="width:100%;padding:10px 14px 10px 38px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;background:#fff;box-sizing:border-box;">
            </div>
            <div style="display:flex;align-items:center;gap:8px;background:#fff;border:1.5px solid #e2e8f0;border-radius:12px;padding:10px 16px;font-size:13px;color:#374151;flex-shrink:0;">
                <i data-lucide="layout-grid" style="width:15px;height:15px;color:#94a3b8;"></i>
                Mesa <strong style="margin:0 4px;"><?= str_pad($mesa['numero'],2,'0',STR_PAD_LEFT) ?></strong>
                <span style="background:<?= $mesa['estado']==='ocupada'?'#d1fae5':'#f1f5f9' ?>;color:<?= $mesa['estado']==='ocupada'?'#065f46':'#475569' ?>;font-size:11px;font-weight:700;padding:2px 8px;border-radius:99px;">
                    <?= ucfirst($mesa['estado']) ?>
                </span>
            </div>
        </div>

        <!-- Tabs categorías -->
        <div style="display:flex;gap:8px;overflow-x:auto;padding-bottom:2px;">
            <button class="cat-btn <?= $catActiva==='favoritos'?'active':'' ?>" onclick="filtrar('favoritos',this)">
                ★ Favoritos
            </button>
            <?php foreach($categorias as $cat): ?>
            <button class="cat-btn <?= (string)$cat['id']===$catActiva?'active':'' ?>" onclick="filtrar('<?= $cat['id'] ?>',this)">
                <?= htmlspecialchars($cat['nombre']) ?>
            </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Grid productos -->
    <div style="flex:1;overflow-y:auto;padding:20px 24px;">
        <div id="prod-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;">
            <?php foreach($prodMostrar as $prod): ?>
            <div class="prod-card" id="prod-<?= $prod['id'] ?>"
                 data-nombre="<?= htmlspecialchars($prod['nombre']) ?>"
                 data-cat="<?= $prod['categoria_id'] ?>"
                 data-fav="<?= $prod['favorito'] ?>"
                 onclick="abrirModal(<?= $prod['id'] ?>,'<?= addslashes($prod['nombre']) ?>',<?= $prod['precio'] ?>,'<?= addslashes($prod['descripcion']??'') ?>')">
                <div style="height:110px;background:linear-gradient(135deg,<?= ['#fef3c7','#dbeafe','#d1fae5','#fce7f3','#ede9fe','#f0fdf4'][($prod['id']-1)%6] ?>,<?= ['#fde68a','#bfdbfe','#a7f3d0','#fbcfe8','#ddd6fe','#bbf7d0'][($prod['id']-1)%6] ?>);display:flex;align-items:center;justify-content:center;position:relative;">
                    <i data-lucide="utensils" style="width:36px;height:36px;color:rgba(0,0,0,0.1);"></i>
                    <?php if($prod['favorito']): ?>
                    <span style="position:absolute;top:8px;right:8px;color:#f59e0b;font-size:16px;">★</span>
                    <?php endif; ?>
                </div>
                <div style="padding:12px;">
                    <p style="font-size:13px;font-weight:600;color:#0f172a;margin:0 0 4px;line-height:1.3;"><?= htmlspecialchars($prod['nombre']) ?></p>
                    <p style="font-size:15px;font-weight:800;color:#2563eb;margin:0;"><?= formatMoney($prod['precio']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if(empty($prodMostrar)): ?>
        <div style="text-align:center;padding:48px 0;color:#cbd5e1;">
            <i data-lucide="search-x" style="width:48px;height:48px;margin:0 auto 12px;"></i>
            <p style="font-size:14px;">No hay productos en esta categoría</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Mini barra de mesas -->
    <div style="border-top:1px solid #f1f5f9;background:#fff;padding:10px 24px;overflow-x:auto;flex-shrink:0;">
        <div style="display:flex;gap:6px;min-width:max-content;">
            <?php
            $dotCol=['libre'=>'#94a3b8','ocupada'=>'#22c55e','reservada'=>'#f59e0b','por_liberar'=>'#ef4444'];
            foreach($todasMesas as $m):
                $isCur=$m['id']===$mesa_id;
            ?>
            <a href="<?= BASE_URL ?>/mesero/pedido/<?= $m['id'] ?>"
               style="display:flex;align-items:center;gap:5px;padding:5px 10px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;transition:all 0.15s;
               background:<?= $isCur?'#2563eb':'#f8fafc' ?>;color:<?= $isCur?'#fff':'#374151' ?>;border:1px solid <?= $isCur?'#2563eb':'#e2e8f0' ?>;">
                <span style="width:6px;height:6px;border-radius:50%;background:<?= $dotCol[$m['estado']] ?>;flex-shrink:0;"></span>
                M<?= str_pad($m['numero'],2,'0',STR_PAD_LEFT) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ═══ COLUMNA DERECHA: Resumen pedido ═══ -->
<div class="right-col">
    <!-- Header -->
    <div style="padding:20px 20px 16px;border-bottom:1px solid #f1f5f9;flex-shrink:0;">
        <h2 style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 2px;">Resumen del pedido</h2>
        <p style="font-size:12px;color:#94a3b8;margin:0;">Mesa <?= str_pad($mesa['numero'],2,'0',STR_PAD_LEFT) ?> · <?= $mesa['capacidad'] ?> pax</p>
    </div>

    <!-- Items -->
    <div style="flex:1;overflow-y:auto;padding:16px 20px;" id="items-container">
        <?php if(empty($items)): ?>
        <div id="empty-state" style="text-align:center;padding:32px 0;color:#cbd5e1;">
            <i data-lucide="shopping-cart" style="width:40px;height:40px;margin:0 auto 10px;"></i>
            <p style="font-size:13px;">Agrega platillos al pedido</p>
        </div>
        <?php else: ?>
        <div id="empty-state" style="display:none;text-align:center;padding:32px 0;color:#cbd5e1;">
            <i data-lucide="shopping-cart" style="width:40px;height:40px;margin:0 auto 10px;"></i>
            <p style="font-size:13px;">Agrega platillos al pedido</p>
        </div>
        <?php foreach($items as $item): ?>
        <div style="display:flex;gap:10px;padding:12px 0;border-bottom:1px solid #f8fafc;" id="item-<?= $item['id'] ?>">
            <div style="flex:1;min-width:0;">
                <p style="font-size:13px;font-weight:600;color:#0f172a;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($item['producto_nombre']) ?></p>
                <?php if($item['notas']): ?>
                <p style="font-size:11px;color:#94a3b8;margin:2px 0 0;">📝 <?= htmlspecialchars($item['notas']) ?></p>
                <?php endif; ?>
                <p style="font-size:12px;color:#2563eb;font-weight:600;margin:3px 0 0;"><?= formatMoney($item['precio_unitario']) ?></p>
            </div>
            <div style="display:flex;align-items:center;gap:4px;flex-shrink:0;">
                <button onclick="cambiarQty(<?= $item['id'] ?>,<?= $item['cantidad']-1 ?>)"
                    style="width:26px;height:26px;border-radius:8px;background:#f1f5f9;border:none;cursor:pointer;font-size:15px;font-weight:700;color:#374151;display:flex;align-items:center;justify-content:center;">−</button>
                <span style="width:22px;text-align:center;font-size:14px;font-weight:700;color:#0f172a;"><?= $item['cantidad'] ?></span>
                <button onclick="cambiarQty(<?= $item['id'] ?>,<?= $item['cantidad']+1 ?>)"
                    style="width:26px;height:26px;border-radius:8px;background:#f1f5f9;border:none;cursor:pointer;font-size:15px;font-weight:700;color:#374151;display:flex;align-items:center;justify-content:center;">+</button>
                <button onclick="eliminarItem(<?= $item['id'] ?>)"
                    style="width:26px;height:26px;border-radius:8px;background:none;border:none;cursor:pointer;color:#cbd5e1;display:flex;align-items:center;justify-content:center;margin-left:2px;"
                    onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#cbd5e1'">
                    <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Totales + acciones -->
    <div style="padding:16px 20px;border-top:1px solid #f1f5f9;flex-shrink:0;">
        <div style="margin-bottom:14px;">
            <div style="display:flex;justify-content:space-between;font-size:13px;color:#64748b;margin-bottom:5px;">
                <span>Subtotal</span><span id="disp-subtotal"><?= formatMoney($subtotal) ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:13px;color:#64748b;margin-bottom:8px;">
                <span>IVA (16%)</span><span id="disp-iva"><?= formatMoney($iva) ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:800;color:#0f172a;padding-top:8px;border-top:1px solid #f1f5f9;">
                <span>Total</span><span id="disp-total"><?= formatMoney($total) ?></span>
            </div>
        </div>

        <form method="POST" id="form-enviar">
            <input type="hidden" name="accion" value="enviar_cocina">
            <input type="hidden" name="pedido_id" id="pid" value="<?= $pedido_id ?>">
            <textarea name="notas_cocina" rows="2" placeholder="+ Agregar comentario para cocina..."
                style="width:100%;padding:10px 12px;border:1.5px dashed #e2e8f0;border-radius:12px;font-size:12px;color:#374151;resize:none;box-sizing:border-box;margin-bottom:10px;"></textarea>

            <?php if($pedido_id): ?>
            <button type="submit"
                style="width:100%;padding:13px;background:#2563eb;color:#fff;font-size:14px;font-weight:700;border:none;border-radius:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 3px 12px rgba(37,99,235,0.25);margin-bottom:8px;">
                <i data-lucide="chef-hat" style="width:16px;height:16px;"></i> Enviar a cocina
            </button>
            <a href="<?= BASE_URL ?>/pagos/cobrar/<?= $pedido_id ?>"
               style="width:100%;padding:11px;background:#f8fafc;border:1px solid #e2e8f0;color:#374151;font-size:13px;font-weight:500;border-radius:12px;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:6px;box-sizing:border-box;">
                <i data-lucide="credit-card" style="width:14px;height:14px;"></i> Cobrar ahora
            </a>
            <?php else: ?>
            <div style="width:100%;padding:13px;background:#f1f5f9;color:#94a3b8;font-size:14px;font-weight:600;border-radius:12px;text-align:center;">
                Agrega platillos primero
            </div>
            <?php endif; ?>
        </form>

        <?php if($pedidoActivo): ?>
        <div style="margin-top:10px;text-align:center;">
            <span style="font-size:11px;padding:4px 12px;border-radius:99px;<?= $pedidoActivo['estado']==='en_cocina'?'background:#dbeafe;color:#1d4ed8;':'background:#fef3c7;color:#92400e;' ?>">
                Estado: <?= ucfirst(str_replace('_',' ',$pedidoActivo['estado'])) ?>
            </span>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>

<!-- ═══ MODAL AGREGAR PRODUCTO ═══ -->
<div id="modal-prod" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);backdrop-filter:blur(4px);z-index:1000;align-items:center;justify-content:center;padding:20px;">
    <div style="background:#fff;border-radius:24px;width:100%;max-width:440px;box-shadow:0 24px 60px rgba(0,0,0,0.2);">
        <div style="padding:24px;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px;">
                <div>
                    <h3 id="m-nombre" style="font-size:17px;font-weight:800;color:#0f172a;margin:0 0 4px;"></h3>
                    <p id="m-desc" style="font-size:13px;color:#64748b;margin:0;"></p>
                    <p id="m-precio" style="font-size:20px;font-weight:800;color:#2563eb;margin:8px 0 0;"></p>
                </div>
                <button onclick="cerrarModal()" style="width:34px;height:34px;background:#f1f5f9;border:none;border-radius:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="x" style="width:16px;height:16px;color:#64748b;"></i>
                </button>
            </div>

            <form method="POST" id="form-agregar">
                <input type="hidden" name="accion" value="agregar_item">
                <input type="hidden" name="pedido_id" id="f-pid" value="<?= $pedido_id ?>">
                <input type="hidden" name="producto_id" id="f-prod-id">
                <input type="hidden" name="precio" id="f-precio">
                <input type="hidden" name="cantidad" id="f-cant" value="1">
                <input type="hidden" name="modificadores" value="{}">

                <!-- Cantidad -->
                <div style="display:flex;align-items:center;justify-content:center;gap:20px;margin:20px 0;">
                    <button type="button" onclick="modalQty(-1)" style="width:44px;height:44px;border-radius:12px;background:#f1f5f9;border:none;cursor:pointer;font-size:22px;font-weight:700;color:#374151;display:flex;align-items:center;justify-content:center;">−</button>
                    <span id="m-qty" style="font-size:30px;font-weight:900;color:#0f172a;min-width:40px;text-align:center;">1</span>
                    <button type="button" onclick="modalQty(1)" style="width:44px;height:44px;border-radius:12px;background:#dbeafe;border:none;cursor:pointer;font-size:22px;font-weight:700;color:#1d4ed8;display:flex;align-items:center;justify-content:center;">+</button>
                </div>

                <!-- Notas -->
                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">Notas especiales</label>
                    <textarea name="notas" id="f-notas" rows="2" maxlength="120"
                        placeholder="Ej. Sin cebolla, extra picante..."
                        style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:13px;resize:none;box-sizing:border-box;"></textarea>
                </div>

                <button type="submit" style="width:100%;padding:14px;background:#2563eb;color:#fff;font-size:15px;font-weight:700;border:none;border-radius:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 3px 12px rgba(37,99,235,0.25);">
                    <i data-lucide="plus" style="width:17px;height:17px;"></i>
                    <span id="m-btn-txt">Agregar</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
let qty = 1, precioModal = 0;
const BASE = '<?= BASE_URL ?>';

function abrirModal(id, nombre, precio, desc) {
    qty = 1; precioModal = precio;
    document.getElementById('m-nombre').textContent = nombre;
    document.getElementById('m-desc').textContent   = desc || '';
    document.getElementById('m-precio').textContent = '$' + precio.toFixed(2);
    document.getElementById('f-prod-id').value = id;
    document.getElementById('f-precio').value  = precio;
    document.getElementById('f-cant').value    = 1;
    document.getElementById('m-qty').textContent = 1;
    document.getElementById('m-btn-txt').textContent = 'Agregar — $' + precio.toFixed(2);
    document.getElementById('f-notas').value = '';
    const m = document.getElementById('modal-prod');
    m.style.display = 'flex';
    lucide.createIcons();
}

function cerrarModal() {
    document.getElementById('modal-prod').style.display = 'none';
}

function modalQty(d) {
    qty = Math.max(1, qty + d);
    document.getElementById('m-qty').textContent = qty;
    document.getElementById('f-cant').value = qty;
    document.getElementById('m-btn-txt').textContent = 'Agregar — $' + (precioModal * qty).toFixed(2);
}

document.getElementById('form-agregar').addEventListener('submit', async e => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const r  = await fetch(window.location.href, { method:'POST', body:fd });
    const d  = await r.json().catch(()=>({ok:false}));
    if(d.ok) { cerrarModal(); location.reload(); }
});

function eliminarItem(id) {
    if(!confirm('¿Eliminar este artículo?')) return;
    const fd = new FormData();
    fd.append('accion','eliminar_item');
    fd.append('item_id', id);
    fetch(window.location.href, {method:'POST',body:fd})
        .then(r=>r.json()).then(d=>{ if(d.ok) location.reload(); });
}

function cambiarQty(id, q) {
    if(q < 1) { eliminarItem(id); return; }
    // En producción se haría con AJAX; por ahora recarga
    location.reload();
}

function filtrar(cat, btn) {
    document.querySelectorAll('.cat-btn').forEach(b => {
        b.classList.remove('active');
    });
    btn.classList.add('active');
    window.location.href = window.location.pathname + '?cat=' + cat;
}

// Búsqueda en vivo
document.getElementById('search-prod')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('[id^="prod-"]').forEach(el => {
        el.style.display = (el.dataset.nombre||'').toLowerCase().includes(q) ? '' : 'none';
    });
});

// Cerrar modal al hacer click fuera
document.getElementById('modal-prod').addEventListener('click', function(e) {
    if(e.target === this) cerrarModal();
});
</script>

<?php
$content=ob_get_clean();
$title='Tomar pedido'; $pageTitle='Tomar pedido'; $pageSubtitle=''; $module='mesero';
require __DIR__ . '/../layout/app.php';
