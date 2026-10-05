<?php
class PerfilProfesor
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
        $resultado = $this->db->query('CREATE TABLE IF NOT EXISTS perfil_profesor (
            id TINYINT NOT NULL,
            nombre VARCHAR(120) NOT NULL DEFAULT "",
            titulo VARCHAR(160) NOT NULL DEFAULT "",
            descripcion TEXT NOT NULL,
            telefono VARCHAR(40) NOT NULL DEFAULT "",
            email VARCHAR(191) NOT NULL DEFAULT "",
            instagram VARCHAR(100) NOT NULL DEFAULT "",
            imagen VARCHAR(100) DEFAULT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        if (!$resultado) throw new RuntimeException($this->db->error);

        $resultado = $this->db->query("INSERT IGNORE INTO perfil_profesor (id, nombre, titulo, descripcion) VALUES (1, '', 'Entrenamiento de pádel', '')");
        if (!$resultado) throw new RuntimeException($this->db->error);
    }

    public function obtener()
    {
        $resultado = $this->db->query('SELECT * FROM perfil_profesor WHERE id = 1');
        if (!$resultado) throw new RuntimeException($this->db->error);
        $perfil = $resultado->fetch_assoc();
        if (!$perfil) throw new RuntimeException('No se pudo cargar el perfil público del profesor.');
        return $perfil;
    }

    public function guardar($datos, $imagen)
    {
        $stmt = $this->db->prepare('UPDATE perfil_profesor SET nombre = ?, titulo = ?, descripcion = ?, telefono = ?, email = ?, instagram = ?, imagen = ? WHERE id = 1');
        if (!$stmt) throw new RuntimeException($this->db->error);
        $stmt->bind_param(
            'sssssss',
            $datos['nombre'],
            $datos['titulo'],
            $datos['descripcion'],
            $datos['telefono'],
            $datos['email'],
            $datos['instagram'],
            $imagen
        );
        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new RuntimeException($error);
        }
        $stmt->close();
    }
}
