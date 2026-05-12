<?php
// models/Producto.php
class Producto {
    private PDO $db;

    public function __construct() {
        $this->db = getPDO();
    }

    public function getAll(int $categoria_id = 0, bool $soloDisponibles = true): array {
        $sql = "SELECT p.*, c.nombre AS categoria_nombre
                FROM productos p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                WHERE 1=1";
        $params = [];
        if ($soloDisponibles) {
            $sql .= " AND p.disponible=1";
        }
        if ($categoria_id > 0) {
            $sql .= " AND p.categoria_id=?";
            $params[] = $categoria_id;
        }
        $sql .= " ORDER BY p.favorito DESC, p.nombre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getFavoritos(): array {
        $stmt = $this->db->prepare("
            SELECT p.*, c.nombre AS categoria_nombre
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE p.favorito=1 AND p.disponible=1
            ORDER BY p.nombre
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE id=?");
        $stmt->execute([$id]);
        $prod = $stmt->fetch() ?: null;
        if ($prod) {
            $prod['modificadores'] = $this->getModificadores($id);
        }
        return $prod;
    }

    public function getModificadores(int $producto_id): array {
        $stmt = $this->db->prepare("SELECT * FROM modificadores WHERE producto_id=?");
        $stmt->execute([$producto_id]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['opciones'] = json_decode($row['opciones'], true);
        }
        return $rows;
    }

    public function getCategorias(): array {
        $stmt = $this->db->query("SELECT * FROM categorias ORDER BY orden, nombre");
        return $stmt->fetchAll();
    }

    public function count(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM productos WHERE disponible=1")->fetchColumn();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO productos (nombre, descripcion, precio, categoria_id, disponible, favorito)
            VALUES (?,?,?,?,?,?)
        ");
        $stmt->execute([
            $data['nombre'], $data['descripcion'] ?? '',
            $data['precio'], $data['categoria_id'],
            $data['disponible'] ?? 1, $data['favorito'] ?? 0
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void {
        $this->db->prepare("
            UPDATE productos SET nombre=?, descripcion=?, precio=?, categoria_id=?, disponible=?, favorito=?
            WHERE id=?
        ")->execute([
            $data['nombre'], $data['descripcion'] ?? '',
            $data['precio'], $data['categoria_id'],
            $data['disponible'] ?? 1, $data['favorito'] ?? 0, $id
        ]);
    }
}
