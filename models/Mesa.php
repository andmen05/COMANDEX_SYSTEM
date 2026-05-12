<?php
// models/Mesa.php
class Mesa {
    private PDO $db;

    public function __construct() {
        $this->db = getPDO();
    }

    public function getAll(int $sucursal_id = 1, string $zona = ''): array {
        $sql = "SELECT m.*, 
                    p.id AS pedido_id, p.personas,
                    p.created_at AS pedido_inicio,
                    u.nombre AS mesero_nombre
                FROM mesas m
                LEFT JOIN pedidos p ON p.mesa_id = m.id AND p.estado IN ('pendiente','en_cocina','listo','servido')
                LEFT JOIN usuarios u ON p.mesero_id = u.id
                WHERE m.sucursal_id = ?";
        $params = [$sucursal_id];
        if ($zona) {
            $sql .= " AND m.zona = ?";
            $params[] = $zona;
        }
        $sql .= " ORDER BY m.numero";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM mesas WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function updateEstado(int $id, string $estado): void {
        $this->db->prepare("UPDATE mesas SET estado=? WHERE id=?")->execute([$estado, $id]);
    }

    public function countByEstado(int $sucursal_id = 1): array {
        $stmt = $this->db->prepare("
            SELECT estado, COUNT(*) AS total FROM mesas
            WHERE sucursal_id=? GROUP BY estado
        ");
        $stmt->execute([$sucursal_id]);
        $result = ['libre' => 0, 'ocupada' => 0, 'reservada' => 0, 'por_liberar' => 0, 'total' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['estado']] = (int)$row['total'];
            $result['total'] += (int)$row['total'];
        }
        return $result;
    }
}
