<?php
// models/Pedido.php
class Pedido {
    private PDO $db;

    public function __construct() {
        $this->db = getPDO();
    }

    public function getAll(array $filters = []): array {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['estado'])) {
            $placeholders = implode(',', array_fill(0, count($filters['estado']), '?'));
            $where[] = "p.estado IN ($placeholders)";
            $params = array_merge($params, $filters['estado']);
        }
        if (!empty($filters['mesa_id'])) {
            $where[] = "p.mesa_id=?";
            $params[] = $filters['mesa_id'];
        }
        if (!empty($filters['mesero_id'])) {
            $where[] = "p.mesero_id=?";
            $params[] = $filters['mesero_id'];
        }
        $sql = "SELECT p.*, m.numero AS mesa_numero, u.nombre AS mesero_nombre,
                    TIMESTAMPDIFF(MINUTE, p.created_at, NOW()) AS minutos,
                    COUNT(pi.id) AS total_items
                FROM pedidos p
                JOIN mesas m ON p.mesa_id = m.id
                JOIN usuarios u ON p.mesero_id = u.id
                LEFT JOIN pedido_items pi ON pi.pedido_id = p.id
                WHERE " . implode(' AND ', $where) . "
                GROUP BY p.id
                ORDER BY p.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT p.*, m.numero AS mesa_numero, u.nombre AS mesero_nombre,
                TIMESTAMPDIFF(MINUTE, p.created_at, NOW()) AS minutos
            FROM pedidos p
            JOIN mesas m ON p.mesa_id = m.id
            JOIN usuarios u ON p.mesero_id = u.id
            WHERE p.id=?
        ");
        $stmt->execute([$id]);
        $pedido = $stmt->fetch() ?: null;
        if ($pedido) {
            $pedido['items'] = $this->getItems($id);
        }
        return $pedido;
    }

    public function getItems(int $pedido_id): array {
        $stmt = $this->db->prepare("
            SELECT pi.*, pr.nombre AS producto_nombre, pr.precio AS precio_original
            FROM pedido_items pi
            JOIN productos pr ON pi.producto_id = pr.id
            WHERE pi.pedido_id=?
            ORDER BY pi.id
        ");
        $stmt->execute([$pedido_id]);
        $items = $stmt->fetchAll();
        foreach ($items as &$item) {
            $item['modificadores_sel'] = json_decode($item['modificadores_sel'] ?? '[]', true);
        }
        return $items;
    }

    public function getActivoByMesa(int $mesa_id): ?array {
        $stmt = $this->db->prepare("
            SELECT * FROM pedidos
            WHERE mesa_id=? AND estado NOT IN ('pagado','cancelado')
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$mesa_id]);
        $pedido = $stmt->fetch() ?: null;
        if ($pedido) {
            $pedido['items'] = $this->getItems($pedido['id']);
        }
        return $pedido;
    }

    public function create(array $data): int {
        // Número secuencial
        $num = (int)$this->db->query("SELECT COALESCE(MAX(numero),1000)+1 FROM pedidos")->fetchColumn();
        $stmt = $this->db->prepare("
            INSERT INTO pedidos (numero, mesa_id, mesero_id, estado, personas, notas_cocina)
            VALUES (?,?,?,?,?,?)
        ");
        $stmt->execute([
            $num, $data['mesa_id'], $data['mesero_id'],
            $data['estado'] ?? 'pendiente',
            $data['personas'] ?? 1,
            $data['notas_cocina'] ?? null,
        ]);
        $id = (int)$this->db->lastInsertId();
        // Actualizar mesa
        $this->db->prepare("UPDATE mesas SET estado='ocupada' WHERE id=?")->execute([$data['mesa_id']]);
        return $id;
    }

    public function addItem(int $pedido_id, array $item): int {
        // Precio actual del producto
        $stmt = $this->db->prepare("SELECT precio FROM productos WHERE id=?");
        $stmt->execute([$item['producto_id']]);
        $precioActual = (float)($stmt->fetchColumn() ?: ($item['precio_unitario'] ?? 0));

        $stmt = $this->db->prepare("
            INSERT INTO pedido_items (pedido_id, producto_id, cantidad, precio_unitario, notas, modificadores_sel)
            VALUES (?,?,?,?,?,?)
        ");
        $stmt->execute([
            $pedido_id, $item['producto_id'], $item['cantidad'],
            $precioActual,
            $item['notas'] ?? null,
            json_encode($item['modificadores_sel'] ?? [])
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updateItem(int $item_id, array $data): void {
        $this->db->prepare("
            UPDATE pedido_items SET cantidad=?, notas=?, modificadores_sel=? WHERE id=?
        ")->execute([$data['cantidad'], $data['notas'] ?? null, json_encode($data['modificadores_sel'] ?? []), $item_id]);
    }

    public function removeItem(int $item_id): void {
        $this->db->prepare("DELETE FROM pedido_items WHERE id=?")->execute([$item_id]);
    }

    public function updateEstado(int $id, string $estado): void {
        $this->db->prepare("UPDATE pedidos SET estado=? WHERE id=?")->execute([$estado, $id]);
        if (in_array($estado, ['servido', 'cancelado', 'pagado'])) {
            $mesa = $this->db->prepare("SELECT mesa_id FROM pedidos WHERE id=?");
            $mesa->execute([$id]);
            $mesa_id = $mesa->fetchColumn();
            if ($mesa_id) {
                $this->db->prepare("UPDATE mesas SET estado='libre' WHERE id=?")->execute([$mesa_id]);
            }
        }
    }

    /**
     * Pedidos que deben aparecer en el módulo de cobro:
     * - Estado 'listo' o 'servido' (ya salieron de cocina)
     * - SIN pago registrado en la tabla pagos
     */
    public function getPendientesPago(): array {
        $sql = "SELECT p.*, m.numero AS mesa_numero, u.nombre AS mesero_nombre,
                    TIMESTAMPDIFF(MINUTE, p.created_at, NOW()) AS minutos,
                    COUNT(pi.id) AS total_items
                FROM pedidos p
                JOIN mesas m ON p.mesa_id = m.id
                JOIN usuarios u ON p.mesero_id = u.id
                LEFT JOIN pedido_items pi ON pi.pedido_id = p.id
                LEFT JOIN pagos pa ON pa.pedido_id = p.id
                WHERE p.estado IN ('listo','servido')
                  AND pa.id IS NULL
                GROUP BY p.id
                ORDER BY p.created_at ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getSubtotal(int $pedido_id): float {
        $stmt = $this->db->prepare("
            SELECT SUM(cantidad * precio_unitario) FROM pedido_items WHERE pedido_id=?
        ");
        $stmt->execute([$pedido_id]);
        return (float)$stmt->fetchColumn();
    }

    public function countByEstado(): array {
        $stmt = $this->db->query("SELECT estado, COUNT(*) AS total FROM pedidos GROUP BY estado");
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function getVentasHoy(): float {
        $stmt = $this->db->query("
            SELECT COALESCE(SUM(pa.monto),0)
            FROM pagos pa
            JOIN pedidos p ON pa.pedido_id = p.id
            WHERE DATE(pa.created_at) = CURDATE()
        ");
        return (float)$stmt->fetchColumn();
    }

    public function getKitchenOrders(): array {
        $stmt = $this->db->query("
            SELECT p.*, m.numero AS mesa_numero, u.nombre AS mesero_nombre,
                TIMESTAMPDIFF(MINUTE, p.created_at, NOW()) AS minutos
            FROM pedidos p
            JOIN mesas m ON p.mesa_id = m.id
            JOIN usuarios u ON p.mesero_id = u.id
            WHERE p.estado IN ('en_cocina','listo','pendiente')
            ORDER BY p.created_at ASC
        ");
        $pedidos = $stmt->fetchAll();
        foreach ($pedidos as &$pedido) {
            $pedido['items'] = $this->getItems($pedido['id']);
        }
        return $pedidos;
    }

    public function getActividadReciente(int $limit = 5): array {
        $stmt = $this->db->prepare("
            SELECT a.*, u.nombre AS usuario_nombre
            FROM actividad a
            LEFT JOIN usuarios u ON a.usuario_id = u.id
            ORDER BY a.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
