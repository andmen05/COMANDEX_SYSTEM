<?php
// controllers/DashboardController.php
class DashboardController {
    private Mesa $mesaModel;
    private Pedido $pedidoModel;
    private Pago $pagoModel;

    public function __construct() {
        $this->mesaModel   = new Mesa();
        $this->pedidoModel = new Pedido();
        $this->pagoModel   = new Pago();
    }

    public function index(): void {
        requireLogin();
        $mesas        = $this->mesaModel->countByEstado();
        $pedidosEstado = $this->pedidoModel->countByEstado();
        $ventasHoy    = $this->pedidoModel->getVentasHoy();
        $pagosPend    = $this->pagoModel->getPendientes();
        $totalPend    = $this->pagoModel->getTotalPendiente();
        $pedidosCocina = $this->pedidoModel->getAll(['estado' => ['en_cocina','pendiente']]);
        $actividad    = $this->pedidoModel->getActividadReciente(5);
        $todasMesas   = $this->mesaModel->getAll(1);

        // Pedidos urgentes (>15 min en cocina)
        $urgentes = array_filter($pedidosCocina, fn($p) => $p['minutos'] >= 15);

        view('dashboard/index', compact(
            'mesas','pedidosEstado','ventasHoy','pagosPend','totalPend',
            'pedidosCocina','actividad','todasMesas','urgentes'
        ));
    }
}
