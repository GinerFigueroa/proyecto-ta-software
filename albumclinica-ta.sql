-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-04-2025 a las 16:12:25
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `albumclinica`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id_cita` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_tratamiento` int(11) NOT NULL,
  `id_dentista` int(11) DEFAULT NULL,
  `fecha_hora` datetime NOT NULL,
  `duracion` int(11) DEFAULT 30 COMMENT 'Duración en minutos',
  `estado` enum('Pendiente','Confirmada','Completada','Cancelada','No asistió') DEFAULT 'Pendiente',
  `notas` text DEFAULT NULL,
  `recordatorio_enviado` tinyint(1) DEFAULT 0,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `creado_por` int(11) DEFAULT NULL COMMENT 'ID del usuario que creó la cita'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id_config` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `valor` text DEFAULT NULL,
  `tipo` enum('string','number','boolean','json') DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id_config`, `nombre`, `valor`, `tipo`, `descripcion`, `actualizado_en`) VALUES
(1, 'horario_apertura', '09:00', 'string', 'Hora de apertura de la clínica', '2025-04-14 00:33:15'),
(2, 'horario_cierre', '19:00', 'string', 'Hora de cierre de la clínica', '2025-04-14 00:33:15'),
(3, 'duracion_cita_default', '30', 'number', 'Duración predeterminada de citas en minutos', '2025-04-14 00:33:15'),
(4, 'dias_anticipacion_cancelacion', '2', 'number', 'Días mínimos de anticipación para cancelar citas', '2025-04-14 00:33:15'),
(5, 'email_notificaciones', 'notificaciones@zazdent.com', 'string', 'Email para enviar notificaciones', '2025-04-14 00:33:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dentistas`
--

CREATE TABLE `dentistas` (
  `id_dentista` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_especialidad` int(11) DEFAULT NULL,
  `cedula_profesional` varchar(20) DEFAULT NULL,
  `biografia` text DEFAULT NULL,
  `experiencia` int(11) DEFAULT NULL COMMENT 'Años de experiencia',
  `horario` text DEFAULT NULL COMMENT 'Horario de trabajo en JSON',
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos`
--

CREATE TABLE `documentos` (
  `id_documento` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `tipo` enum('Radiografía','Consentimiento','Historial','Otro') NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `notas` text DEFAULT NULL,
  `subido_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `subido_por` int(11) DEFAULT NULL COMMENT 'ID del usuario que subió el documento'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidades`
--

CREATE TABLE `especialidades` (
  `id_especialidad` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `icono` varchar(30) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `especialidades`
--

INSERT INTO `especialidades` (`id_especialidad`, `nombre`, `descripcion`, `icono`, `creado_en`) VALUES
(1, 'Limpieza Dental', 'Limpieza profesional y remoción de sarro para mantener la salud bucal', 'toothbrush', '2025-04-14 00:33:15'),
(2, 'Ortodoncia Metálica', 'Corrección de la posición dental mediante brackets metálicos tradicionales', 'braces', '2025-04-14 00:33:15'),
(3, 'Endodoncia', 'Tratamiento de conductos radiculares para salvar dientes con pulpitis o necrosis', 'tooth', '2025-04-14 00:33:15'),
(4, 'Rehabilitación Oral', 'Restauración de la función masticatoria mediante prótesis fijas, removibles e implantes', 'teeth', '2025-04-14 00:33:15'),
(5, 'Extracción Dental', 'Extracción de dientes con daño irreversible o para preparación ortodontica', 'teeth-open', '2025-04-14 00:33:15'),
(6, 'Odontopediatría', 'Cuidado dental especializado para niños desde los primeros años de vida', 'child', '2025-04-14 00:33:15'),
(7, 'Periodoncia', 'Tratamiento de las encías y tejidos de soporte dental para prevenir la pérdida de piezas', 'teeth', '2025-04-14 00:33:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_medico`
--

CREATE TABLE `historial_medico` (
  `id_historial` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_dentista` int(11) DEFAULT NULL,
  `id_tratamiento` int(11) DEFAULT NULL,
  `fecha_procedimiento` datetime NOT NULL,
  `diagnostico` text DEFAULT NULL,
  `procedimiento` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `receta` text DEFAULT NULL,
  `proxima_visita` date DEFAULT NULL,
  `adjuntos` text DEFAULT NULL COMMENT 'Rutas de archivos adjuntos en JSON'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `id_item` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `categoria` enum('Material','Medicamento','Equipo','Consumible') NOT NULL,
  `descripcion` text DEFAULT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 0,
  `unidad_medida` varchar(10) DEFAULT NULL,
  `stock_minimo` int(11) DEFAULT 5,
  `proveedor` varchar(50) DEFAULT NULL,
  `costo_unitario` decimal(10,2) DEFAULT NULL,
  `ubicacion` varchar(50) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs`
--

CREATE TABLE `logs` (
  `id_log` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `accion` varchar(50) NOT NULL,
  `tabla_afectada` varchar(50) DEFAULT NULL,
  `id_registro_afectado` int(11) DEFAULT NULL,
  `datos_anteriores` text DEFAULT NULL COMMENT 'JSON con datos anteriores',
  `datos_nuevos` text DEFAULT NULL COMMENT 'JSON con datos nuevos',
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pacientes`
--

CREATE TABLE `pacientes` (
  `id_paciente` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `genero` enum('Masculino','Femenino','Otro') DEFAULT NULL,
  `alergias` text DEFAULT NULL,
  `enfermedades_cronicas` text DEFAULT NULL,
  `medicamentos` text DEFAULT NULL,
  `seguro_medico` varchar(50) DEFAULT NULL,
  `numero_seguro` varchar(50) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id_pago` int(11) NOT NULL,
  `id_cita` int(11) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo_pago` enum('Efectivo','Tarjeta crédito','Tarjeta débito','Transferencia') DEFAULT NULL,
  `estado` enum('Pendiente','Completado','Reembolsado','Cancelado') DEFAULT 'Pendiente',
  `referencia` varchar(50) DEFAULT NULL,
  `fecha_pago` datetime DEFAULT NULL,
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre`, `descripcion`, `creado_en`) VALUES
(1, 'Administrador', 'Acceso completo al sistema', '2025-04-14 00:33:14'),
(2, 'Dentista', 'Personal odontológico', '2025-04-14 00:33:14'),
(3, 'Recepcionista', 'Personal administrativo', '2025-04-14 00:33:14'),
(4, 'Paciente', 'Pacientes de la clínica', '2025-04-14 00:33:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tratamientos`
--

CREATE TABLE `tratamientos` (
  `id_tratamiento` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `id_especialidad` int(11) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `duracion_estimada` int(11) DEFAULT NULL COMMENT 'Duración en minutos',
  `costo` decimal(10,2) NOT NULL,
  `requisitos` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `tratamientos`
--

INSERT INTO `tratamientos` (`id_tratamiento`, `nombre`, `id_especialidad`, `descripcion`, `duracion_estimada`, `costo`, `requisitos`, `activo`, `creado_en`) VALUES
(1, 'Consulta inicial', NULL, 'Evaluación clínica completa con diagnóstico y plan de tratamiento', 30, 300.00, 'Mayor de 18 años o acompañado de tutor', 1, '2025-04-14 00:33:15'),
(2, 'Limpieza dental profesional', 1, 'Remoción de sarro, placa bacteriana y pulido dental con pasta profiláctica', 45, 500.00, 'No haber comido 2 horas antes', 1, '2025-04-14 00:33:15'),
(3, 'Ortodoncia metálica completa', 2, 'Tratamiento integral con brackets metálicos incluyendo controles mensuales', 60, 8000.00, 'Evaluación ortodóntica previa', 1, '2025-04-14 00:33:15'),
(4, 'Endodoncia uniradicular', 3, 'Tratamiento de conducto en dientes con una sola raíz', 90, 2500.00, 'Radiografía periapical reciente', 1, '2025-04-14 00:33:15'),
(5, 'Rehabilitación con implante unitario', 4, 'Colocación de implante dental y corona protésica sobre implante', 120, 1800.00, 'Estudio radiográfico 3D previo', 1, '2025-04-14 00:33:15'),
(6, 'Extracción dental simple', 5, 'Exodoncia de pieza dentaria sin complicaciones quirúrgicas', 30, 800.00, 'No presentar infección activa', 1, '2025-04-14 00:33:15'),
(7, 'Control odontopediátrico', 6, 'Consulta preventiva y aplicación de sellantes en pacientes infantiles', 30, 800.00, 'Edad entre 3-12 años', 1, '2025-04-14 00:33:15'),
(8, 'Tratamiento periodontal básico', 7, 'Raspado y alisado radicular en un cuadrante de la boca', 60, 800.00, 'Diagnóstico de gingivitis/periodontitis', 1, '2025-04-14 00:33:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `usuario_clave` varchar(255) NOT NULL,
  `usuario_usuario` varchar(50) NOT NULL,
  `nombre_apellido` varchar(50) NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `ultimo_login` datetime DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_rol`, `email`, `usuario_clave`, `usuario_usuario`, `nombre_apellido`, `telefono`, `activo`, `ultimo_login`, `creado_en`, `actualizado_en`) VALUES
(1, 1, 'admin@clinicadental.com', 'SecurePass123!', 'admin', 'Diego Apaza Quispe', '+51987654321', 1, '2025-04-25 04:46:03', '2025-04-25 09:42:47', '2025-04-25 09:46:03'),
(2, 2, 'dr.gonzales@clinicadental.com', 'Denta##lClinic2023', 'drCarolay', 'Carolay Corvacho FLores', '+51988776655', 1, '2025-04-25 04:48:18', '2025-04-25 09:42:47', '2025-04-25 09:48:18'),
(3, 3, 'recepcion@clinicadental.com', 'Welcome@2023', 'recepcion01', 'Morelia Rodriguez garcia', '+51955667788', 1, NULL, '2025-04-25 09:42:47', '2025-04-25 09:42:47'),
(4, 1, 'adminn@clinicadental.com', 'delinae@20', 'admin01', 'Delinia Figueroa Gonzalez', '+51955667758', 1, NULL, '2025-04-25 09:42:47', '2025-04-25 09:42:47'),
(5, 4, 'paciente.martinez@gmail.com', 'Patient$$#Secure1', 'jmartinez', 'Juan Martínez Flores', '+51933445566', 1, '2025-04-25 04:49:20', '2025-04-25 09:42:47', '2025-04-25 09:49:20'),
(6, 4, 'figueroa@untels.com', '$2y$10$e4GWZjUmQDCI.2QaS9uJU.OasaXbnoyX85kQxye3AI3OTOU9XRBTy', 'GinerBush', 'Figueroa', '98766773', 1, NULL, '2025-04-25 13:20:51', '2025-04-25 13:20:51'),
(7, 4, 'figueroa@unteffls.com', '$2y$10$IxCwE.qtIFE424g4gFWNdObKMdDfJv5wnBsKTkYm2k3ScrohgOPXy', 'GinerBufsh#rrtty', 'Figueroaf', '9876673273', 1, NULL, '2025-04-25 13:46:38', '2025-04-25 13:46:38'),
(8, 4, 'hilariweb@gmail.com', '$2y$10$RWWBL0exHrxCShcLshjNo.hY6uz9Dh3EJEsTTk64htKWwQHqj80wG', 'GinerBushsd123#', 'Figueroaew', '6667771234', 1, NULL, '2025-04-25 13:49:02', '2025-04-25 13:49:02'),
(9, 4, 'giner@hotewemail.comw', '$2y$10$LmsCXLkUl2OfL12VZfJPP.OJAx8wXDrgAwYRJihmWNKecPITEkpca', 'juanp12133#$', 'Figueroawew', '9876673273', 1, NULL, '2025-04-25 13:50:57', '2025-04-25 13:50:57'),
(10, 2, 'hilariwebe@gmail.com', '$2y$10$RlF0GMgU9ogsNd43y.ICu.72bEs/sENPX1wKGaPJAoPb7urG8cd0W', 'juanp122Mfsd#', 'Gonzalez', '9876673', 1, NULL, '2025-04-25 13:54:41', '2025-04-25 13:54:41'),
(11, 4, 'maeia@uteml.comn', '$2y$10$2mzCEW0v8SvIJ79NNmxJEOS4felTUStmNC69PANT/vkBxG468lEVq', 'maria12M#', 'mariq', '98766773', 1, NULL, '2025-04-25 13:58:13', '2025-04-25 13:58:13'),
(12, 4, 'hilariweb@gmasdil.com', 'juaana12#M', 'juaana12#M', 'Figueroahd', '98766773', 1, '2025-04-25 09:02:56', '2025-04-25 14:02:48', '2025-04-25 14:02:56');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id_cita`),
  ADD KEY `id_tratamiento` (`id_tratamiento`),
  ADD KEY `idx_citas_fecha` (`fecha_hora`),
  ADD KEY `idx_citas_paciente` (`id_paciente`),
  ADD KEY `idx_citas_dentista` (`id_dentista`),
  ADD KEY `idx_citas_estado` (`estado`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id_config`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `dentistas`
--
ALTER TABLE `dentistas`
  ADD PRIMARY KEY (`id_dentista`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`),
  ADD UNIQUE KEY `cedula_profesional` (`cedula_profesional`),
  ADD KEY `id_especialidad` (`id_especialidad`),
  ADD KEY `idx_dentistas_usuario` (`id_usuario`);

--
-- Indices de la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD PRIMARY KEY (`id_documento`),
  ADD KEY `id_paciente` (`id_paciente`);

--
-- Indices de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  ADD PRIMARY KEY (`id_especialidad`);

--
-- Indices de la tabla `historial_medico`
--
ALTER TABLE `historial_medico`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `id_dentista` (`id_dentista`),
  ADD KEY `id_tratamiento` (`id_tratamiento`),
  ADD KEY `idx_historial_paciente` (`id_paciente`);

--
-- Indices de la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`id_item`);

--
-- Indices de la tabla `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD PRIMARY KEY (`id_paciente`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`),
  ADD KEY `idx_pacientes_usuario` (`id_usuario`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `id_cita` (`id_cita`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `tratamientos`
--
ALTER TABLE `tratamientos`
  ADD PRIMARY KEY (`id_tratamiento`),
  ADD KEY `id_especialidad` (`id_especialidad`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id_cita` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id_config` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `dentistas`
--
ALTER TABLE `dentistas`
  MODIFY `id_dentista` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `documentos`
--
ALTER TABLE `documentos`
  MODIFY `id_documento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  MODIFY `id_especialidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `historial_medico`
--
ALTER TABLE `historial_medico`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inventario`
--
ALTER TABLE `inventario`
  MODIFY `id_item` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `logs`
--
ALTER TABLE `logs`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  MODIFY `id_paciente` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tratamientos`
--
ALTER TABLE `tratamientos`
  MODIFY `id_tratamiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`),
  ADD CONSTRAINT `citas_ibfk_2` FOREIGN KEY (`id_tratamiento`) REFERENCES `tratamientos` (`id_tratamiento`),
  ADD CONSTRAINT `citas_ibfk_3` FOREIGN KEY (`id_dentista`) REFERENCES `dentistas` (`id_dentista`);

--
-- Filtros para la tabla `dentistas`
--
ALTER TABLE `dentistas`
  ADD CONSTRAINT `dentistas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `dentistas_ibfk_2` FOREIGN KEY (`id_especialidad`) REFERENCES `especialidades` (`id_especialidad`);

--
-- Filtros para la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD CONSTRAINT `documentos_ibfk_1` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`);

--
-- Filtros para la tabla `historial_medico`
--
ALTER TABLE `historial_medico`
  ADD CONSTRAINT `historial_medico_ibfk_1` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`),
  ADD CONSTRAINT `historial_medico_ibfk_2` FOREIGN KEY (`id_dentista`) REFERENCES `dentistas` (`id_dentista`),
  ADD CONSTRAINT `historial_medico_ibfk_3` FOREIGN KEY (`id_tratamiento`) REFERENCES `tratamientos` (`id_tratamiento`);

--
-- Filtros para la tabla `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD CONSTRAINT `pacientes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`id_cita`) REFERENCES `citas` (`id_cita`);

--
-- Filtros para la tabla `tratamientos`
--
ALTER TABLE `tratamientos`
  ADD CONSTRAINT `tratamientos_ibfk_1` FOREIGN KEY (`id_especialidad`) REFERENCES `especialidades` (`id_especialidad`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
