<?php
// importar conex 
class Usuario
{

    private $db;
    public function __construct($conn)
    {
        $this->db = $conn;
    }
    public function getALL()
    {
        $sql = "select * from usuarios";
        $rs = $this->db->query($sql); // ejecutamos la consulta
        return $rs;
    }
    public function getByID($dato)
    {
        //DELETE FROM `usuarios` WHERE `usuarios`.`id` = 7
        $sql = "SELECT * FROM `usuarios` WHERE `usuarios`.`id` = " . $dato;
        $rs = $this->db->query($sql);
        return $rs;
    }
    public function alumnoDisponible($alumnoId, $usuarioId = 0)
    {
        $stmt = $this->db->prepare('SELECT id FROM alumnos WHERE id = ? AND NOT EXISTS (SELECT 1 FROM usuarios WHERE alumno_id = ? AND id <> ?) LIMIT 1');
        if (!$stmt) return false;
        $alumnoId = (int) $alumnoId;
        $usuarioId = (int) $usuarioId;
        $stmt->bind_param('iii', $alumnoId, $alumnoId, $usuarioId);
        if (!$stmt->execute()) return false;
        $rs = $stmt->get_result();
        $disponible = $rs->num_rows > 0;
        $stmt->close();
        return $disponible;
    }
    public function insert($datos)
    {

        $apellido = $this->db->real_escape_string($datos['apellido']);
        $nombre = $this->db->real_escape_string($datos['nombre']);
        $fenac = empty($datos['fenac']) ? "NULL" : "'" . $this->db->real_escape_string($datos['fenac']) . "'";
        $doc = $this->db->real_escape_string($datos['doc']);
        $mail = $this->db->real_escape_string($datos['mail']);
        $telefono = $this->db->real_escape_string($datos['telefono']);
        $direccion = $this->db->real_escape_string($datos['direccion']);
        $contrasena = password_hash($datos['contrasena'], PASSWORD_DEFAULT);
        $rol = $this->db->real_escape_string($datos['rol'] ?? 'usuario');
        $esadmin = $rol === 'admin' ? 1 : 0;
        $alumnoId = $rol === 'usuario' && !empty($datos['alumno_id']) ? (int) $datos['alumno_id'] : 'NULL';

        $sql = "INSERT INTO `usuarios` 
    (`id`, `apellido`, `nombre`, `fenac`, `doc`, `mail`, `telefono`, `direccion`, `contrasena`, `esadmin`, `rol`, `alumno_id`)
    VALUES 
    (NULL, '" . $apellido . "', '" . $nombre . "', " . $fenac . ", '" . $doc . "', '" . $mail . "', '" . $telefono . "', '" . $direccion . "', '" . $contrasena . "', " . $esadmin . ", '" . $rol . "', " . $alumnoId . ")";

        $rs = $this->db->query($sql);

        return $rs;
    }
    public function update($datos)
    {

        $id = intval($datos['id']);
        $apellido = $this->db->real_escape_string($datos['apellido']);
        $nombre = $this->db->real_escape_string($datos['nombre']);
        $fenac = empty($datos['fenac']) ? "NULL" : "'" . $this->db->real_escape_string($datos['fenac']) . "'";
        $doc = $this->db->real_escape_string($datos['doc']);
        $mail = $this->db->real_escape_string($datos['mail']);
        $telefono = $this->db->real_escape_string($datos['telefono']);
        $direccion = $this->db->real_escape_string($datos['direccion']);
        $rol = $this->db->real_escape_string($datos['rol'] ?? 'usuario');
        $esadmin = $rol === 'admin' ? 1 : 0;
        $alumnoId = $rol === 'usuario' && !empty($datos['alumno_id']) ? (int) $datos['alumno_id'] : 'NULL';

        if (!empty($datos['contrasena'])) {

            $contrasena = password_hash($datos['contrasena'], PASSWORD_DEFAULT);

            $sql = "UPDATE `usuarios` SET 
        `apellido` = '" . $apellido . "', 
        `nombre` = '" . $nombre . "', 
        `doc` = '" . $doc . "', 
        `mail` = '" . $mail . "', 
        `contrasena` = '" . $contrasena . "', 
        `telefono` = '" . $telefono . "', 
        `direccion` = '" . $direccion . "', 
        `fenac` = " . $fenac . ", 
        `esadmin` = " . $esadmin . ",
        `rol` = '" . $rol . "',
        `alumno_id` = " . $alumnoId . "
        WHERE `usuarios`.`id` = " . $id;
        } else {

            $sql = "UPDATE `usuarios` SET 
        `apellido` = '" . $apellido . "', 
        `nombre` = '" . $nombre . "', 
        `doc` = '" . $doc . "', 
        `mail` = '" . $mail . "', 
        `telefono` = '" . $telefono . "', 
        `direccion` = '" . $direccion . "', 
        `fenac` = " . $fenac . ", 
        `esadmin` = " . $esadmin . ",
        `rol` = '" . $rol . "',
        `alumno_id` = " . $alumnoId . "
        WHERE `usuarios`.`id` = " . $id;
        }

        $rs = $this->db->query($sql);

        return $rs;
    }

    public function delete($dato)
    {
        //DELETE FROM `usuarios` WHERE `usuarios`.`id` = 7
        $dato = intval($dato);

        $sql = "DELETE FROM `usuarios` WHERE `usuarios`.`id` = " . $dato;
        $rs = $this->db->query($sql);
    }

    public function getByMail($mail)
    {
        $mail = $this->db->real_escape_string($mail);

        $sql = "SELECT * FROM `usuarios` 
            WHERE `mail` = '" . $mail . "' 
            LIMIT 1";

        $rs = $this->db->query($sql);

        return $rs;
    }
}
