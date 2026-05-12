<?php
// controllers/ReportesController.php
class ReportesController {
    public function index(): void {
        requireLogin();
        requireRole(['Administrador', 'Gerente']);

        $db = getPDO();
        $ventasHoy = (float)$db->query("
            SELECT COALESCE(SUM(monto),0) FROM pagos WHERE DATE(created_at)=CURDATE()
        ")->fetchColumn();

        $ventasSemana = $db->query("
            SELECT DATE(created_at) AS dia, SUM(monto) AS total
            FROM pagos
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY dia ORDER BY dia
        ")->fetchAll();

        $topProductos = $db->query("
            SELECT pr.nombre, SUM(pi.cantidad) AS vendidos, SUM(pi.cantidad*pi.precio_unitario) AS total
            FROM pedido_items pi
            JOIN productos pr ON pi.producto_id=pr.id
            JOIN pedidos p ON pi.pedido_id=p.id
            GROUP BY pr.id ORDER BY vendidos DESC LIMIT 10
        ")->fetchAll();

        view('reportes/index', compact('ventasHoy','ventasSemana','topProductos'));
    }
}
