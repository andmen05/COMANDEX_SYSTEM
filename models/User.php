<?php
// models/User.php
class User {
    private PDO $db;

    public function __construct() {
        $this->db = getPDO();
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("
            SELECT u.*, r.nombre AS rol, s.nombre AS sucursal
            FROM usuarios u
            JOIN roles r ON u.rol_id = r.id
            JOIN sucursales s ON u.sucursal_id = s.id
            WHERE u.email = ? AND u.activo = 1
        ");
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT u.*, r.nombre AS rol, s.nombre AS sucursal
            FROM usuarios u
            JOIN roles r ON u.rol_id = r.id
            JOIN sucursales s ON u.sucursal_id = s.id
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getAll(array $filters = []): array {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['search'])) {
            $where[] = "(u.nombre LIKE ? OR u.email LIKE ?)";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
        }
        if (!empty($filters['rol_id'])) {
            $where[] = "u.rol_id = ?";
            $params[] = $filters['rol_id'];
        }
        if (isset($filters['activo'])) {
            $where[] = "u.activo = ?";
            $params[] = $filters['activo'];
        }
        $sql = "SELECT u.*, r.nombre AS rol, s.nombre AS sucursal
                FROM usuarios u
                JOIN roles r ON u.rol_id = r.id
                JOIN sucursales s ON u.sucursal_id = s.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY u.nombre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO usuarios (nombre, email, password, rol_id, sucursal_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['nombre'],
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            $data['rol_id'],
            $data['sucursal_id'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void {
        $fields = ['nombre=?', 'email=?', 'rol_id=?', 'sucursal_id=?', 'activo=?'];
        $params = [$data['nombre'], $data['email'], $data['rol_id'], $data['sucursal_id'], $data['activo'] ?? 1];
        if (!empty($data['password'])) {
            $fields[] = 'password=?';
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }
        $params[] = $id;
        $this->db->prepare("UPDATE usuarios SET " . implode(',', $fields) . " WHERE id=?")->execute($params);
    }

    public function updateLastAccess(int $id): void {
        $this->db->prepare("UPDATE usuarios SET ultimo_acceso=NOW() WHERE id=?")->execute([$id]);
    }

    public function count(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM usuarios WHERE activo=1")->fetchColumn();
    }
}
