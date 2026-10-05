<?php
class Padel
{
    private const PRECIO_HORA_PREDETERMINADO = 20000;
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
        $this->all('CREATE TABLE IF NOT EXISTS pagos_cancha (id INT NOT NULL AUTO_INCREMENT, fecha DATE NOT NULL, monto DECIMAL(12,2) NOT NULL, medio_pago ENUM("efectivo", "transferencia", "otro") NOT NULL DEFAULT "efectivo", observacion VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id), KEY idx_pagos_cancha_fecha (fecha)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        $this->all('CREATE TABLE IF NOT EXISTS complejos (id INT NOT NULL AUTO_INCREMENT, nombre VARCHAR(100) NOT NULL, PRIMARY KEY (id), UNIQUE KEY uq_complejos_nombre (nombre)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        $this->all('CREATE TABLE IF NOT EXISTS configuracion (clave VARCHAR(50) NOT NULL, valor DECIMAL(12,2) NOT NULL, PRIMARY KEY (clave)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        $this->all('INSERT IGNORE INTO configuracion (clave, valor) VALUES ("precio_hora", ' . self::PRECIO_HORA_PREDETERMINADO . ')');
        $this->migrarVinculoAlumnoUsuario();

        $columnaComplejo = $this->all("SHOW COLUMNS FROM canchas LIKE 'complejo_id'");
        $requiereNotNull = $columnaComplejo->num_rows === 0;
        if ($columnaComplejo->num_rows === 0) {
            $this->all('ALTER TABLE canchas ADD complejo_id INT NULL AFTER id');
        } else {
            $requiereNotNull = $columnaComplejo->fetch_assoc()['Null'] === 'YES';
        }

        $canchasSinComplejo = $this->all('SELECT id FROM canchas WHERE complejo_id IS NULL LIMIT 1');
        if ($canchasSinComplejo->num_rows > 0) {
            $this->execute(
                'INSERT INTO complejos (nombre) VALUES (?) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)',
                's',
                'Complejo principal'
            );
            $complejoPredeterminado = (int) $this->db->insert_id;
            $this->execute('UPDATE canchas SET complejo_id = ? WHERE complejo_id IS NULL', 'i', $complejoPredeterminado);
        }
        if ($requiereNotNull) {
            $this->all('ALTER TABLE canchas MODIFY complejo_id INT NOT NULL');
        }

        $indiceAntiguo = $this->all("SHOW INDEX FROM canchas WHERE Key_name = 'uq_canchas_nombre'");
        if ($indiceAntiguo->num_rows > 0) {
            $this->all('ALTER TABLE canchas DROP INDEX uq_canchas_nombre');
        }
        $indiceComplejo = $this->all("SHOW INDEX FROM canchas WHERE Key_name = 'uq_canchas_complejo_nombre'");
        if ($indiceComplejo->num_rows === 0) {
            $this->all('ALTER TABLE canchas ADD UNIQUE KEY uq_canchas_complejo_nombre (complejo_id, nombre)');
        }
        $foreignKey = $this->all("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'canchas' AND CONSTRAINT_NAME = 'fk_canchas_complejo'");
        if ($foreignKey->num_rows === 0) {
            $this->all('ALTER TABLE canchas ADD CONSTRAINT fk_canchas_complejo FOREIGN KEY (complejo_id) REFERENCES complejos (id) ON DELETE RESTRICT ON UPDATE CASCADE');
        }
        $this->migrarSesionesRecurrentes();
    }

    private function migrarVinculoAlumnoUsuario()
    {
        $columnaAlumno = $this->all("SHOW COLUMNS FROM usuarios LIKE 'alumno_id'");
        if ($columnaAlumno->num_rows === 0) {
            $this->all('ALTER TABLE usuarios ADD alumno_id INT NULL AFTER rol');
        }
        $indiceAlumno = $this->all("SHOW INDEX FROM usuarios WHERE Key_name = 'uq_usuarios_alumno'");
        if ($indiceAlumno->num_rows === 0) {
            $this->all('ALTER TABLE usuarios ADD UNIQUE KEY uq_usuarios_alumno (alumno_id)');
        }

        $foreignKey = $this->all("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios' AND CONSTRAINT_NAME = 'fk_usuarios_alumno'");
        if ($foreignKey->num_rows === 0) {
            $this->all('ALTER TABLE usuarios ADD CONSTRAINT fk_usuarios_alumno FOREIGN KEY (alumno_id) REFERENCES alumnos (id) ON DELETE SET NULL ON UPDATE CASCADE');
        }
    }

    private function migrarSesionesRecurrentes()
    {
        $columnaAsistencia = $this->all("SHOW COLUMNS FROM alquiler_alumnos LIKE 'asistencia'");
        if ($columnaAsistencia->num_rows === 0) {
            $this->all("ALTER TABLE alquiler_alumnos ADD asistencia ENUM('programada', 'asistio', 'ausente') NOT NULL DEFAULT 'asistio'");
        }
        $columnaImporteProgramado = $this->all("SHOW COLUMNS FROM alquiler_alumnos LIKE 'importe_programado'");
        if ($columnaImporteProgramado->num_rows === 0) {
            $this->all('ALTER TABLE alquiler_alumnos ADD importe_programado DECIMAL(12,2) NOT NULL DEFAULT 0.00');
            $this->all('UPDATE alquiler_alumnos SET importe_programado = importe WHERE importe > 0');
        }

        $this->all('CREATE TABLE IF NOT EXISTS sesiones_recurrentes (
            id INT NOT NULL AUTO_INCREMENT,
            nombre VARCHAR(100) NOT NULL,
            cancha_id INT NOT NULL,
            dia_semana TINYINT NOT NULL,
            hora_inicio TIME NOT NULL,
            hora_fin TIME NOT NULL,
            importe_asistencia DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            activa TINYINT(1) NOT NULL DEFAULT 1,
            PRIMARY KEY (id),
            KEY idx_recurrentes_dia (dia_semana, activa),
            CONSTRAINT fk_recurrentes_cancha FOREIGN KEY (cancha_id) REFERENCES canchas (id) ON DELETE RESTRICT ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        $this->all('CREATE TABLE IF NOT EXISTS sesiones_recurrentes_alumnos (
            recurrencia_id INT NOT NULL,
            alumno_id INT NOT NULL,
            PRIMARY KEY (recurrencia_id, alumno_id),
            CONSTRAINT fk_recurrentes_alumnos_recurrencia FOREIGN KEY (recurrencia_id) REFERENCES sesiones_recurrentes (id) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT fk_recurrentes_alumnos_alumno FOREIGN KEY (alumno_id) REFERENCES alumnos (id) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');

        $columnaRecurrencia = $this->all("SHOW COLUMNS FROM alquileres LIKE 'recurrencia_id'");
        if ($columnaRecurrencia->num_rows === 0) {
            $this->all('ALTER TABLE alquileres ADD recurrencia_id INT NULL DEFAULT NULL');
        }
        $indiceRecurrencia = $this->all("SHOW INDEX FROM alquileres WHERE Key_name = 'uq_alquileres_recurrencia_fecha'");
        if ($indiceRecurrencia->num_rows === 0) {
            $this->all('ALTER TABLE alquileres ADD UNIQUE KEY uq_alquileres_recurrencia_fecha (recurrencia_id, fecha)');
        }
        $foreignKey = $this->all("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'alquileres' AND CONSTRAINT_NAME = 'fk_alquileres_recurrencia'");
        if ($foreignKey->num_rows === 0) {
            $this->all('ALTER TABLE alquileres ADD CONSTRAINT fk_alquileres_recurrencia FOREIGN KEY (recurrencia_id) REFERENCES sesiones_recurrentes (id) ON DELETE SET NULL ON UPDATE CASCADE');
        }
    }

    private function all($sql)
    {
        $resultado = $this->db->query($sql);
        if (!$resultado) throw new RuntimeException($this->db->error);
        return $resultado;
    }

    private function execute($sql, $types = '', ...$values)
    {
        $stmt = $this->db->prepare($sql);
        if (!$stmt) throw new RuntimeException($this->db->error);
        if ($types !== '') $stmt->bind_param($types, ...$values);
        if (!$stmt->execute()) throw new RuntimeException($stmt->error);
        return $stmt;
    }

    public function complejos()
    {
        return $this->all('SELECT co.*, COUNT(c.id) AS cantidad_canchas FROM complejos co LEFT JOIN canchas c ON c.complejo_id = co.id GROUP BY co.id ORDER BY co.nombre');
    }
    public function complejo($id) { return $this->all('SELECT * FROM complejos WHERE id = ' . (int) $id); }
    public function guardarComplejo($nombre, $id = 0)
    {
        $nombre = trim($nombre);
        if ($id) return $this->execute('UPDATE complejos SET nombre = ? WHERE id = ?', 'si', $nombre, (int) $id);
        return $this->execute('INSERT INTO complejos (nombre) VALUES (?)', 's', $nombre);
    }
    public function borrarComplejo($id)
    {
        $stmt = $this->execute('SELECT id FROM canchas WHERE complejo_id = ? LIMIT 1', 'i', (int) $id);
        $tieneCanchas = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        if ($tieneCanchas) throw new InvalidArgumentException('No se puede eliminar un complejo que todavía tiene canchas.');
        return $this->execute('DELETE FROM complejos WHERE id = ?', 'i', (int) $id);
    }

    public function canchas() { return $this->all('SELECT c.*, co.nombre AS complejo FROM canchas c JOIN complejos co ON co.id = c.complejo_id ORDER BY co.nombre, c.nombre'); }
    public function cancha($id) { return $this->all('SELECT * FROM canchas WHERE id = ' . (int) $id); }
    public function guardarCancha($nombre, $complejoId, $id = 0)
    {
        $nombre = trim($nombre);
        $stmt = $this->execute('SELECT id FROM complejos WHERE id = ?', 'i', (int) $complejoId);
        $complejoExiste = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        if (!$complejoExiste) throw new InvalidArgumentException('Seleccione un complejo válido.');
        if ($id) return $this->execute('UPDATE canchas SET nombre = ?, complejo_id = ? WHERE id = ?', 'sii', $nombre, (int) $complejoId, (int) $id);
        return $this->execute('INSERT INTO canchas (nombre, complejo_id) VALUES (?, ?)', 'si', $nombre, (int) $complejoId);
    }
    public function borrarCancha($id) { return $this->execute('DELETE FROM canchas WHERE id = ?', 'i', (int) $id); }

    public function alumnos() { return $this->all('SELECT al.*, u.id AS usuario_id FROM alumnos al LEFT JOIN usuarios u ON u.alumno_id = al.id ORDER BY al.apellido, al.nombre'); }
    public function alumno($id) { return $this->all('SELECT * FROM alumnos WHERE id = ' . (int) $id); }
    public function alumnoIdUsuario($usuarioId)
    {
        $stmt = $this->execute('SELECT alumno_id FROM usuarios WHERE id = ?', 'i', (int) $usuarioId);
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int) ($fila['alumno_id'] ?? 0);
    }
    public function guardarAlumno($nombre, $apellido, $id = 0)
    {
        $nombre = trim($nombre);
        $apellido = trim($apellido);
        if ($id) return $this->execute('UPDATE alumnos SET nombre = ?, apellido = ? WHERE id = ?', 'ssi', $nombre, $apellido, (int) $id);
        return $this->execute('INSERT INTO alumnos (nombre, apellido) VALUES (?, ?)', 'ss', $nombre, $apellido);
    }
    public function borrarAlumno($id) { return $this->execute('DELETE FROM alumnos WHERE id = ?', 'i', (int) $id); }

    public function alquileres($desde = '', $hasta = '')
    {
        $filtro = $this->filtroFechas($desde, $hasta, 'a.fecha');
        return $this->all('SELECT a.*, c.nombre AS cancha, co.nombre AS complejo, GROUP_CONCAT(CONCAT(al.apellido, ", ", al.nombre) ORDER BY al.apellido, al.nombre SEPARATOR ", ") AS alumnos, GREATEST(COUNT(aa.id), a.cantidad_alumnos) AS cantidad_alumnos, COALESCE(SUM(aa.importe), 0) AS ingresos, COALESCE(SUM(CASE WHEN aa.estado_pago = "pagado" THEN aa.importe ELSE aa.monto_pagado END), 0) AS cobrado, SUM(aa.estado_pago = "pagado") AS pagos_completos, COALESCE(SUM(aa.asistencia = "programada"), 0) AS asistencias_pendientes FROM alquileres a JOIN canchas c ON c.id = a.cancha_id JOIN complejos co ON co.id = c.complejo_id LEFT JOIN alquiler_alumnos aa ON aa.alquiler_id = a.id LEFT JOIN alumnos al ON al.id = aa.alumno_id ' . $filtro . ' GROUP BY a.id, c.id, co.id ORDER BY a.fecha DESC, a.hora_inicio DESC, a.id DESC');
    }
    public function alquiler($id) { return $this->all('SELECT * FROM alquileres WHERE id = ' . (int) $id); }
    public function participantes($alquilerId)
    {
        return $this->all('SELECT aa.id, aa.alquiler_id, aa.alumno_id, aa.importe, aa.importe_programado, aa.asistencia, CASE WHEN aa.estado_pago = "pagado" THEN aa.importe ELSE aa.monto_pagado END AS monto_pagado, aa.estado_pago, aa.medio_pago, aa.periodo, al.nombre, al.apellido FROM alquiler_alumnos aa JOIN alumnos al ON al.id = aa.alumno_id WHERE aa.alquiler_id = ' . (int) $alquilerId . ' ORDER BY al.apellido, al.nombre');
    }
    public function guardarAlquiler($canchaId, $fecha, $horaInicio, $horaFin, $horas, $participantes, $id = 0)
    {
        if ((bool) $horaInicio !== (bool) $horaFin) {
            throw new InvalidArgumentException('Completa las dos horas de la reserva o deja ambas vacías.');
        }
        if ($horaInicio && $horaFin && $horaFin <= $horaInicio) {
            throw new InvalidArgumentException('La hora de finalización debe ser posterior a la hora de inicio.');
        }
        if ($horaInicio && $horaFin) {
            $inicio = new DateTimeImmutable('1970-01-01 ' . $horaInicio);
            $fin = new DateTimeImmutable('1970-01-01 ' . $horaFin);
            $horas = ($fin->getTimestamp() - $inicio->getTimestamp()) / 3600;
        }
        if ((float) $horas < 0.25 || (float) $horas > 24 || !$participantes) {
            throw new InvalidArgumentException('La duración debe ser válida y la sesión debe tener al menos un alumno.');
        }
        $this->validarDisponibilidadCancha($canchaId, $fecha, $horaInicio, $horaFin, $id);
        $precioHora = $this->precioHora();
        if ($id) {
            $sesionExistente = $this->alquiler($id)->fetch_assoc();
            if ($sesionExistente && (int) ($sesionExistente['recurrencia_id'] ?? 0) > 0) {
                $pendientes = $this->execute('SELECT id FROM alquiler_alumnos WHERE alquiler_id = ? AND asistencia = "programada" LIMIT 1', 'i', (int) $id);
                $tienePendientes = $pendientes->get_result()->num_rows > 0;
                $pendientes->close();
                if ($tienePendientes) {
                    throw new InvalidArgumentException('Esta sesión recurrente tiene asistencias pendientes. Confirma primero el grupo desde el panel del profesor o elimina la sesión si fue cancelada.');
                }
            }
            if ($sesionExistente && (float) $sesionExistente['horas'] > 0) {
                $precioHora = (float) $sesionExistente['costo'] / (float) $sesionExistente['horas'];
            }
        }
        $costo = (float) $horas * $precioHora;
        $ids = array_column($participantes, 'alumno_id');
        if (count($ids) !== count(array_unique($ids))) throw new InvalidArgumentException('No puede repetir el mismo alumno en una sesión.');
        $primerAlumno = (int) $participantes[0]['alumno_id'];
        if ($id) {
            $this->execute('UPDATE alquileres SET alumno_id = ?, cancha_id = ?, fecha = ?, hora_inicio = ?, hora_fin = ?, horas = ?, costo = ?, cantidad_alumnos = ?, recurrencia_id = NULL WHERE id = ?', 'iisssddii', $primerAlumno, (int) $canchaId, $fecha, $horaInicio ?: null, $horaFin ?: null, (float) $horas, $costo, count($participantes), (int) $id);
            $this->execute('DELETE FROM alquiler_alumnos WHERE alquiler_id = ?', 'i', (int) $id);
        } else {
            $stmt = $this->execute('INSERT INTO alquileres (alumno_id, cancha_id, fecha, hora_inicio, hora_fin, horas, costo, cantidad_alumnos) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', 'iisssddi', $primerAlumno, (int) $canchaId, $fecha, $horaInicio ?: null, $horaFin ?: null, (float) $horas, $costo, count($participantes));
            $id = $this->db->insert_id;
        }
        foreach ($participantes as $participante) {
            $this->execute('INSERT INTO alquiler_alumnos (alquiler_id, alumno_id, importe, importe_programado, monto_pagado, estado_pago, medio_pago, periodo, asistencia) VALUES (?, ?, ?, ?, ?, ?, ?, ?, "asistio")', 'iidddsss', (int) $id, (int) $participante['alumno_id'], (float) $participante['importe'], (float) $participante['importe'], (float) $participante['monto_pagado'], $participante['estado_pago'], $participante['medio_pago'] ?: null, $participante['periodo'] ?: null);
        }
        return $id;
    }

    private function validarDisponibilidadCancha($canchaId, $fecha, $horaInicio, $horaFin, $excluirId = 0)
    {
        if (!$horaInicio || !$horaFin) {
            $sql = 'SELECT id FROM alquileres WHERE cancha_id = ? AND fecha = ? AND id <> ? LIMIT 1';
            $stmt = $this->execute($sql, 'isi', (int) $canchaId, $fecha, (int) $excluirId);
        } else {
            $sql = 'SELECT id FROM alquileres WHERE cancha_id = ? AND fecha = ? AND id <> ? AND (hora_inicio IS NULL OR hora_fin IS NULL OR (hora_inicio < ? AND hora_fin > ?)) LIMIT 1';
            $stmt = $this->execute($sql, 'isiss', (int) $canchaId, $fecha, (int) $excluirId, $horaFin, $horaInicio);
        }
        $conflicto = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($conflicto) {
            throw new InvalidArgumentException('La cancha ya tiene una reserva que coincide con ese día y horario.');
        }
    }
    public function borrarAlquiler($id) { return $this->execute('DELETE FROM alquileres WHERE id = ?', 'i', (int) $id); }

    public function recurrencias()
    {
        return $this->all('SELECT r.*, c.nombre AS cancha, co.nombre AS complejo, COUNT(ra.alumno_id) AS cantidad_alumnos FROM sesiones_recurrentes r JOIN canchas c ON c.id = r.cancha_id JOIN complejos co ON co.id = c.complejo_id LEFT JOIN sesiones_recurrentes_alumnos ra ON ra.recurrencia_id = r.id GROUP BY r.id ORDER BY r.activa DESC, r.dia_semana, r.hora_inicio');
    }

    public function recurrencia($id)
    {
        return $this->all('SELECT * FROM sesiones_recurrentes WHERE id = ' . (int) $id);
    }

    public function alumnosRecurrencia($id)
    {
        return $this->all('SELECT alumno_id FROM sesiones_recurrentes_alumnos WHERE recurrencia_id = ' . (int) $id . ' ORDER BY alumno_id');
    }

    public function guardarRecurrencia($nombre, $canchaId, $diaSemana, $horaInicio, $horaFin, $importe, $alumnoIds, $id = 0)
    {
        $inicio = DateTimeImmutable::createFromFormat('!H:i', $horaInicio);
        $fin = DateTimeImmutable::createFromFormat('!H:i', $horaFin);
        $alumnoIds = array_map('intval', $alumnoIds);
        if (trim($nombre) === '' || strlen($nombre) > 100 || (int) $diaSemana < 1 || (int) $diaSemana > 7 || !$inicio || !$fin || $fin <= $inicio || (float) $importe < 0 || (float) $importe > 9999999999.99 || !$alumnoIds || in_array(0, $alumnoIds, true) || count(array_unique($alumnoIds)) !== count($alumnoIds)) {
            throw new InvalidArgumentException('Completa la clase, el día, un horario válido, el importe por asistencia y al menos un alumno sin duplicados.');
        }
        $stmt = $this->execute('SELECT id FROM canchas WHERE id = ?', 'i', (int) $canchaId);
        $canchaExiste = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        if (!$canchaExiste) throw new InvalidArgumentException('Selecciona una cancha válida.');
        $stmt = $this->execute(
            'SELECT id FROM sesiones_recurrentes WHERE cancha_id = ? AND dia_semana = ? AND activa = 1 AND id <> ? AND hora_inicio < ? AND hora_fin > ? LIMIT 1',
            'iiiss',
            (int) $canchaId,
            (int) $diaSemana,
            (int) $id,
            $horaFin,
            $horaInicio
        );
        $conflicto = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($conflicto) throw new InvalidArgumentException('Ya existe una clase recurrente en esa cancha y franja horaria.');

        $this->db->begin_transaction();
        try {
            if ($id) {
                $this->execute('UPDATE sesiones_recurrentes SET nombre = ?, cancha_id = ?, dia_semana = ?, hora_inicio = ?, hora_fin = ?, importe_asistencia = ? WHERE id = ?', 'siissdi', trim($nombre), (int) $canchaId, (int) $diaSemana, $horaInicio, $horaFin, (float) $importe, (int) $id);
                $this->execute('DELETE FROM sesiones_recurrentes_alumnos WHERE recurrencia_id = ?', 'i', (int) $id);
            } else {
                $this->execute('INSERT INTO sesiones_recurrentes (nombre, cancha_id, dia_semana, hora_inicio, hora_fin, importe_asistencia) VALUES (?, ?, ?, ?, ?, ?)', 'siissd', trim($nombre), (int) $canchaId, (int) $diaSemana, $horaInicio, $horaFin, (float) $importe);
                $id = (int) $this->db->insert_id;
            }
            foreach ($alumnoIds as $alumnoId) {
                $this->execute('INSERT INTO sesiones_recurrentes_alumnos (recurrencia_id, alumno_id) VALUES (?, ?)', 'ii', (int) $id, $alumnoId);
            }
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
        return (int) $id;
    }

    public function generarSesionesMes($id, $mes)
    {
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
            throw new InvalidArgumentException('Selecciona un mes válido para generar las clases.');
        }
        $plantilla = $this->recurrencia($id)->fetch_assoc();
        if (!$plantilla || !(int) $plantilla['activa']) {
            throw new InvalidArgumentException('La clase recurrente no existe o está desactivada.');
        }
        $alumnos = $this->alumnosRecurrencia($id);
        $alumnoIds = [];
        while ($fila = $alumnos->fetch_assoc()) $alumnoIds[] = (int) $fila['alumno_id'];
        if (!$alumnoIds) throw new InvalidArgumentException('Agrega al menos un alumno a la clase recurrente antes de generar el calendario.');

        $fecha = new DateTimeImmutable($mes . '-01');
        $finMes = $fecha->modify('last day of this month');
        $horaInicio = new DateTimeImmutable('1970-01-01 ' . $plantilla['hora_inicio']);
        $horaFin = new DateTimeImmutable('1970-01-01 ' . $plantilla['hora_fin']);
        $horas = ($horaFin->getTimestamp() - $horaInicio->getTimestamp()) / 3600;
        $costoCancha = $horas * $this->precioHora();
        $creadas = 0;
        $omitidas = 0;
        $conflictos = 0;
        $this->db->begin_transaction();
        try {
            for (; $fecha <= $finMes; $fecha = $fecha->modify('+1 day')) {
                if ((int) $fecha->format('N') !== (int) $plantilla['dia_semana']) continue;
                $fechaIso = $fecha->format('Y-m-d');
                $existente = $this->execute('SELECT id FROM alquileres WHERE recurrencia_id = ? AND fecha = ? LIMIT 1', 'is', (int) $id, $fechaIso);
                $yaGenerada = $existente->get_result()->num_rows > 0;
                $existente->close();
                if ($yaGenerada) {
                    $omitidas++;
                    continue;
                }
                $reservaConflictiva = $this->execute(
                    'SELECT id FROM alquileres WHERE cancha_id = ? AND fecha = ? AND (hora_inicio IS NULL OR hora_fin IS NULL OR (hora_inicio < ? AND hora_fin > ?)) LIMIT 1',
                    'isss',
                    (int) $plantilla['cancha_id'],
                    $fechaIso,
                    $plantilla['hora_fin'],
                    $plantilla['hora_inicio']
                );
                $hayConflicto = $reservaConflictiva->get_result()->num_rows > 0;
                $reservaConflictiva->close();
                if ($hayConflicto) {
                    $conflictos++;
                    continue;
                }
                $stmt = $this->execute('INSERT IGNORE INTO alquileres (alumno_id, cancha_id, fecha, hora_inicio, hora_fin, horas, costo, cantidad_alumnos, recurrencia_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', 'iisssddii', $alumnoIds[0], (int) $plantilla['cancha_id'], $fechaIso, $plantilla['hora_inicio'], $plantilla['hora_fin'], $horas, $costoCancha, count($alumnoIds), (int) $id);
                if ($stmt->affected_rows === 0) {
                    $omitidas++;
                    $stmt->close();
                    continue;
                }
                $alquilerId = (int) $this->db->insert_id;
                $stmt->close();
                foreach ($alumnoIds as $alumnoId) {
                    $this->execute('INSERT INTO alquiler_alumnos (alquiler_id, alumno_id, importe, importe_programado, monto_pagado, estado_pago, asistencia) VALUES (?, ?, 0, ?, 0, "pendiente", "programada")', 'iid', $alquilerId, $alumnoId, (float) $plantilla['importe_asistencia']);
                }
                $creadas++;
            }
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
        return ['creadas' => $creadas, 'omitidas' => $omitidas, 'conflictos' => $conflictos];
    }

    public function marcarAsistencia($alquilerId, $alumnoId, $asistencia)
    {
        if (!in_array($asistencia, ['asistio', 'ausente'], true)) {
            throw new InvalidArgumentException('Selecciona un estado de asistencia válido.');
        }
        $sesion = $this->execute(
            'SELECT a.fecha FROM alquiler_alumnos aa JOIN alquileres a ON a.id = aa.alquiler_id WHERE aa.alquiler_id = ? AND aa.alumno_id = ? AND aa.asistencia = "programada"',
            'ii',
            (int) $alquilerId,
            (int) $alumnoId
        );
        $filaSesion = $sesion->get_result()->fetch_assoc();
        $sesion->close();
        if (!$filaSesion) throw new InvalidArgumentException('La asistencia ya fue registrada o el alumno no pertenece a esta sesión.');
        if ($filaSesion['fecha'] > date('Y-m-d')) throw new InvalidArgumentException('La asistencia solo se puede confirmar el día de la clase o después.');
        $importe = $asistencia === 'asistio' ? 'importe_programado' : '0';
        $stmt = $this->execute("UPDATE alquiler_alumnos SET asistencia = ?, importe = $importe, estado_pago = 'pendiente', monto_pagado = 0 WHERE alquiler_id = ? AND alumno_id = ? AND asistencia = 'programada'", 'sii', $asistencia, (int) $alquilerId, (int) $alumnoId);
        if ($stmt->affected_rows !== 1) {
            $stmt->close();
            throw new InvalidArgumentException('La asistencia ya fue registrada o la sesión no está pendiente de confirmación.');
        }
        $stmt->close();
    }

    public function desactivarRecurrencia($id)
    {
        return $this->execute('UPDATE sesiones_recurrentes SET activa = 0 WHERE id = ?', 'i', (int) $id);
    }

    public function precioHora()
    {
        $fila = $this->all('SELECT valor FROM configuracion WHERE clave = "precio_hora"')->fetch_assoc();
        if (!$fila) throw new RuntimeException('No se encontró el precio por hora configurado.');
        return (float) $fila['valor'];
    }

    public function guardarPrecioHora($precio)
    {
        $precio = filter_var($precio, FILTER_VALIDATE_FLOAT);
        if ($precio === false || $precio <= 0 || $precio > 9999999999.99) {
            throw new InvalidArgumentException('El precio por hora debe ser mayor que cero y no superar 9.999.999.999,99 Gs.');
        }
        return $this->execute('UPDATE configuracion SET valor = ? WHERE clave = "precio_hora"', 'd', (float) $precio);
    }

    public function resumenAsistencias($alumnoId = 0)
    {
        $where = (int) $alumnoId > 0 ? 'WHERE al.id = ' . (int) $alumnoId : '';
        return $this->all('SELECT al.id AS alumno_id, CONCAT(al.apellido, ", ", al.nombre) AS alumno, COUNT(DISTINCT CASE WHEN aa.asistencia = "asistio" THEN a.fecha END) AS sesiones, COALESCE(SUM(aa.importe), 0) AS importe, COALESCE(SUM(CASE WHEN aa.estado_pago = "pagado" THEN aa.importe ELSE aa.monto_pagado END), 0) AS pagado, COALESCE(SUM(GREATEST(aa.importe - CASE WHEN aa.estado_pago = "pagado" THEN aa.importe ELSE aa.monto_pagado END, 0)), 0) AS deuda FROM alumnos al LEFT JOIN alquiler_alumnos aa ON aa.alumno_id = al.id LEFT JOIN alquileres a ON a.id = aa.alquiler_id ' . $where . ' GROUP BY al.id ORDER BY al.apellido, al.nombre');
    }

    public function historialAsistencias($alumnoId = 0)
    {
        $where = (int) $alumnoId > 0 ? 'WHERE aa.alumno_id = ' . (int) $alumnoId : '';
        return $this->all('SELECT aa.alumno_id, CONCAT(al.apellido, ", ", al.nombre) AS alumno, a.id AS alquiler_id, a.fecha, a.hora_inicio, a.hora_fin, a.horas, c.nombre AS cancha, co.nombre AS complejo, aa.asistencia, aa.importe, aa.importe_programado, CASE WHEN aa.estado_pago = "pagado" THEN aa.importe ELSE aa.monto_pagado END AS pagado, GREATEST(aa.importe - CASE WHEN aa.estado_pago = "pagado" THEN aa.importe ELSE aa.monto_pagado END, 0) AS deuda FROM alquiler_alumnos aa JOIN alumnos al ON al.id = aa.alumno_id JOIN alquileres a ON a.id = aa.alquiler_id JOIN canchas c ON c.id = a.cancha_id JOIN complejos co ON co.id = c.complejo_id ' . $where . ' ORDER BY a.fecha DESC, a.hora_inicio DESC, al.apellido, al.nombre');
    }
    public function deudasSemanales()
    {
        return $this->all('SELECT aa.alumno_id, CONCAT(al.apellido, ", ", al.nombre) AS alumno, DATE_SUB(a.fecha, INTERVAL WEEKDAY(a.fecha) DAY) AS semana_desde, SUM(aa.importe - CASE WHEN aa.estado_pago = "pagado" THEN aa.importe ELSE aa.monto_pagado END) AS deuda FROM alquiler_alumnos aa JOIN alquileres a ON a.id = aa.alquiler_id JOIN alumnos al ON al.id = aa.alumno_id WHERE aa.asistencia = "asistio" GROUP BY aa.alumno_id, semana_desde HAVING deuda > 0 ORDER BY semana_desde DESC, al.apellido, al.nombre');
    }
    public function registrarPagoAlumnoSemana($alumnoId, $semanaDesde, $monto, $medio)
    {
        $inicio = DateTimeImmutable::createFromFormat('!Y-m-d', $semanaDesde);
        if (!$inicio || $inicio->format('Y-m-d') !== $semanaDesde || (int) $inicio->format('N') !== 1) throw new InvalidArgumentException('La semana indicada debe comenzar un lunes.');
        if ($monto <= 0 || !in_array($medio, ['efectivo', 'transferencia', 'otro'], true)) throw new InvalidArgumentException('Indique un monto y medio de pago válidos.');
        $fin = $inicio->modify('+6 days')->format('Y-m-d');
        $this->db->begin_transaction();
        try {
            $stmt = $this->execute('SELECT aa.id, aa.importe, aa.monto_pagado FROM alquiler_alumnos aa JOIN alquileres a ON a.id = aa.alquiler_id WHERE aa.alumno_id = ? AND a.fecha BETWEEN ? AND ? AND aa.asistencia = "asistio" AND aa.estado_pago <> "pagado" AND aa.importe > aa.monto_pagado ORDER BY a.fecha, aa.id', 'iss', (int) $alumnoId, $semanaDesde, $fin);
            $resultado = $stmt->get_result();
            $deudas = [];
            $totalPendiente = 0;
            while ($fila = $resultado->fetch_assoc()) {
                $deudas[] = $fila;
                $totalPendiente += round((float) $fila['importe'] - (float) $fila['monto_pagado'], 2);
            }
            $stmt->close();
            if (!$deudas) throw new InvalidArgumentException('Este alumno no tiene deuda pendiente en esa semana.');
            $restante = round((float) $monto, 2);
            if ($restante > round($totalPendiente, 2)) throw new InvalidArgumentException('El pago supera la deuda pendiente de esa semana.');
            foreach ($deudas as $deuda) {
                if ($restante <= 0) break;
                $importe = (float) $deuda['importe'];
                $pagado = (float) $deuda['monto_pagado'];
                $nuevoPagado = round(min($importe, $pagado + $restante), 2);
                $restante = round($restante - ($nuevoPagado - $pagado), 2);
                $estado = $nuevoPagado >= $importe ? 'pagado' : 'pagado_parcial';
                $this->execute('UPDATE alquiler_alumnos SET monto_pagado = ?, estado_pago = ?, medio_pago = ? WHERE id = ?', 'dssi', $nuevoPagado, $estado, $medio, (int) $deuda['id']);
            }
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    public function registrarPagoCancha($fecha, $monto, $medio, $observacion = '')
    {
        return $this->execute('INSERT INTO pagos_cancha (fecha, monto, medio_pago, observacion) VALUES (?, ?, ?, ?)', 'sdss', $fecha, (float) $monto, $medio, trim($observacion) ?: null);
    }

    public function borrarPagoCancha($id) { return $this->execute('DELETE FROM pagos_cancha WHERE id = ?', 'i', (int) $id); }

    public function pagosCancha($desde = '', $hasta = '')
    {
        $filtro = $this->filtroFechas($desde, $hasta, 'fecha');
        return $this->all('SELECT * FROM pagos_cancha ' . $filtro . ' ORDER BY fecha DESC, id DESC');
    }

    private function filtroFechas($desde, $hasta, $campo)
    {
        $condiciones = [];
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) $condiciones[] = "$campo >= '" . $this->db->real_escape_string($desde) . "'";
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) $condiciones[] = "$campo <= '" . $this->db->real_escape_string($hasta) . "'";
        return $condiciones ? 'WHERE ' . implode(' AND ', $condiciones) : '';
    }

    public function resumen($desde = '', $hasta = '', $alumnoId = 0)
    {
        $alumnoId = (int) $alumnoId;
        $filtro = $this->filtroFechas($desde, $hasta, 'a.fecha');
        $filtroPagos = $this->filtroFechas($desde, $hasta, 'fecha');
        $general = $this->all('SELECT (SELECT COALESCE(SUM(a.horas), 0) FROM alquileres a ' . $filtro . ') AS horas, (SELECT COALESCE(SUM(a.costo), 0) FROM alquileres a ' . $filtro . ') AS costo, (SELECT COALESCE(SUM(aa.importe), 0) FROM alquiler_alumnos aa JOIN alquileres a ON a.id = aa.alquiler_id ' . $filtro . ') AS ingresos, (SELECT COALESCE(SUM(CASE WHEN aa.estado_pago = "pagado" THEN aa.importe ELSE aa.monto_pagado END), 0) FROM alquiler_alumnos aa JOIN alquileres a ON a.id = aa.alquiler_id ' . $filtro . ') AS cobrado, (SELECT COUNT(*) FROM alquileres a ' . $filtro . ') AS registros, (SELECT COALESCE(SUM(p.monto), 0) FROM pagos_cancha p ' . $filtroPagos . ') AS pagado_cancha, (SELECT COALESCE(SUM(aa.importe - (a.costo / NULLIF(a.cantidad_alumnos, 0))), 0) FROM alquileres a JOIN alquiler_alumnos aa ON aa.alquiler_id = a.id ' . $filtro . ') AS ganancia')->fetch_assoc();
        $filtroAlumnos = $filtro;
        if ($alumnoId > 0) $filtroAlumnos .= ($filtroAlumnos ? ' AND' : 'WHERE') . ' al.id = ' . $alumnoId;
        return [
            'general' => $general,
            'alumnos' => $this->all('SELECT CONCAT(al.apellido, ", ", al.nombre) AS nombre, COALESCE(SUM(a.horas), 0) AS horas, COALESCE(SUM(aa.importe), 0) AS ingresos, COALESCE(SUM(CASE WHEN aa.estado_pago = "pagado" THEN aa.importe ELSE aa.monto_pagado END), 0) AS cobrado, COALESCE(SUM(aa.importe - (a.costo / NULLIF(a.cantidad_alumnos, 0))), 0) AS ganancia FROM alumnos al LEFT JOIN alquiler_alumnos aa ON aa.alumno_id = al.id LEFT JOIN alquileres a ON a.id = aa.alquiler_id ' . $filtroAlumnos . ' GROUP BY al.id ORDER BY al.apellido, al.nombre'),
            'canchas' => $this->all('SELECT c.nombre, co.nombre AS complejo, COALESCE(SUM(a.horas), 0) AS horas, COALESCE(SUM(a.costo), 0) AS costo FROM canchas c JOIN complejos co ON co.id = c.complejo_id LEFT JOIN alquileres a ON a.cancha_id = c.id ' . $filtro . ' GROUP BY c.id, co.id ORDER BY co.nombre, c.nombre')
        ];
    }
}