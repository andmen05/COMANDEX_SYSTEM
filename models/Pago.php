<?php
// models/Pago.php
class Pago {
    private PDO $db;

    public function __construct() {
        $this->db = getPDO();
    }

    public function findByPedido(int $pedido_id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM pagos WHERE pedido_id=? ORDER BY created_at DESC");
        $stmt->execute([$pedido_id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO pagos (pedido_id, metodo, monto, recibido, cambio, referencia)
            VALUES (?,?,?,?,?,?)
        ");
        $stmt->execute([
            $data['pedido_id'],
            $data['metodo'],
            $data['monto'],
            $data['recibido'] ?? $data['monto'],
            $data['cambio'] ?? 0,
            $data['referencia'] ?? null,
        ]);
        $id = (int)$this->db->lastInsertId();
        // Registrar actividad
        $this->db->prepare("
            INSERT INTO actividad (usuario_id, tipo, descripcion, entidad, entidad_id)
            VALUES (?,?,?,?,?)
        ")->execute([
            $_SESSION['user_id'] ?? null, 'pago',
            "Pago registrado — {$data['metodo']} · " . formatMoney($data['monto']),
            'pago', $id
        ]);
        return $id;
    }

    public function getPendientes(): int {
        $stmt = $this->db->query("
            SELECT COUNT(*) FROM pedidos p
            LEFT JOIN pagos pa ON pa.pedido_id = p.id
            WHERE p.estado IN ('en_cocina','listo','servido') AND pa.id IS NULL
        ");
        return (int)$stmt->fetchColumn();
    }

    public function getTotalPendiente(): float {
        $stmt = $this->db->query("
            SELECT COALESCE(SUM(pi.cantidad * pi.precio_unitario),0)
            FROM pedido_items pi
            JOIN pedidos p ON pi.pedido_id = p.id
            LEFT JOIN pagos pa ON pa.pedido_id = p.id
            WHERE p.estado IN ('en_cocina','listo','servido') AND pa.id IS NULL
        ");
        return (float)$stmt->fetchColumn();
    }
}
