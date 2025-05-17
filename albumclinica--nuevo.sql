-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 01-05-2025 a las 14:06:28
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

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id_cita`, `id_paciente`, `id_tratamiento`, `id_dentista`, `fecha_hora`, `duracion`, `estado`, `notas`, `recordatorio_enviado`, `creado_en`, `creado_por`) VALUES
(1, 5, 2, 1, '2025-05-02 10:00:00', 30, 'Confirmada', 'Revisión general y limpieza.', 1, '2025-04-28 06:49:00', 3),
(2, 1, 8, 1, '2025-05-02 10:00:00', 45, 'Confirmada', 'Paciente solicitó limpieza profunda.', 1, '2025-05-01 08:49:27', 3),
(3, 2, 7, 2, '2025-05-03 14:30:00', 30, 'Pendiente', 'Evaluación inicial para posible caries.', 0, '2025-05-01 08:49:27', 2),
(4, 3, 3, NULL, '2025-05-04 09:00:00', 60, 'Cancelada', 'Paciente canceló debido a un viaje.', 0, '2025-05-01 08:49:27', 5),
(5, 4, 6, 3, '2025-05-05 16:15:00', 30, 'No asistió', 'No respondió llamadas de confirmación.', 1, '2025-05-01 08:49:27', 7),
(6, 5, 5, 4, '2025-05-06 11:45:00', 30, 'Completada', 'Colocación de brackets sin inconvenientes.', 1, '2025-05-01 08:49:27', 4);

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

--
-- Volcado de datos para la tabla `dentistas`
--

INSERT INTO `dentistas` (`id_dentista`, `id_usuario`, `id_especialidad`, `cedula_profesional`, `biografia`, `experiencia`, `horario`, `foto`) VALUES
(1, 2, 5, 'ABC12345', 'Especialista en ortodoncia con más de 10 años de experiencia.', 10, '{\"lunes\":\"09:00-17:00\", \"martes\":\"09:00-17:00\"}', 'dentista1.jpg'),
(2, 10, 2, 'CP-123456', 'Especialista en ortodoncia con más de 10 años de experiencia.', 10, '{\"lunes\": \"09:00-17:00\", \"miércoles\": \"10:00-18:00\"}', 'foto1.jpg'),
(3, 11, 3, 'CP-654321', 'Odontólogo general apasionado por la salud bucal.', 8, '{\"martes\": \"08:00-16:00\", \"jueves\": \"09:00-17:00\"}', 'foto2.jpg'),
(4, 12, 1, 'CP-789123', 'Cirujano dental especializado en implantes.', 12, '{\"viernes\": \"09:00-15:00\", \"sábado\": \"10:00-14:00\"}', 'foto3.jpg'),
(5, 13, NULL, 'CP-456789', 'Joven profesional dedicado a la estética dental.', 5, '{\"lunes\": \"08:00-14:00\", \"jueves\": \"12:00-18:00\"}', 'foto4.jpg');

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

--
-- Volcado de datos para la tabla `historial_medico`
--

INSERT INTO `historial_medico` (`id_historial`, `id_paciente`, `id_dentista`, `id_tratamiento`, `fecha_procedimiento`, `diagnostico`, `procedimiento`, `observaciones`, `receta`, `proxima_visita`, `adjuntos`) VALUES
(1, 1, 1, 1, '2025-04-30 14:00:00', 'Caries en molar derecho', 'Limpieza y empaste', 'Paciente toleró bien el procedimiento', 'Amoxicilina 500mg cada 8 horas por 7 días', '2025-06-01', '{\"adjunto1.jpg\", \"adjunto2.pdf\"}'),
(2, 2, 1, 2, '2025-04-28 10:30:00', 'Fractura en incisivo superior', 'Reconstrucción dental', 'Se recomienda cuidado al masticar', NULL, '2025-05-15', '{\"radiografia.png\"}'),
(3, 3, NULL, NULL, '2025-04-25 16:00:00', 'Dolor de muelas recurrente', 'Evaluación inicial', 'Se recomienda realizar una radiografía', NULL, '2025-05-02', '{}'),
(4, 4, 1, 3, '2025-04-22 09:00:00', 'Extracción de tercer molar', 'Extracción quirúrgica', 'Proceso sin complicaciones', 'Ibuprofeno 400mg cada 6 horas si hay dolor', '2025-05-20', '{\"informe.pdf\"}'),
(5, 5, 1, 4, '2025-04-20 13:15:00', 'Ortodoncia', 'Colocación de brackets', 'Paciente necesitará ajustes periódicos', NULL, '2025-06-10', '{}');

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

--
-- Volcado de datos para la tabla `pacientes`
--

INSERT INTO `pacientes` (`id_paciente`, `id_usuario`, `fecha_nacimiento`, `genero`, `alergias`, `enfermedades_cronicas`, `medicamentos`, `seguro_medico`, `numero_seguro`, `creado_en`) VALUES
(1, 6, '1990-05-15', 'Masculino', 'Polen', 'Hipertensión', 'Lisinopril', 'Seguro A', '123456', '2025-04-28 03:21:26'),
(2, 7, '1985-08-23', 'Femenino', 'Ninguna', 'Diabetes', 'Metformina', 'Seguro B', '789012', '2025-04-28 03:21:26'),
(3, 8, '2000-01-30', 'Otro', 'Maní', 'Asma', 'Salbutamol', 'Seguro C', '345678', '2025-04-28 03:21:26'),
(4, 9, '1995-12-10', 'Masculino', 'Penicilina', 'Ninguna', 'Ibuprofeno', 'Seguro D', '901234', '2025-04-28 03:21:26'),
(5, 10, '1978-07-05', 'Femenino', 'Frutos secos', 'Artritis', 'Celecoxib', 'Seguro E', '567890', '2025-04-28 03:21:26');

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

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id_pago`, `id_cita`, `monto`, `metodo_pago`, `estado`, `referencia`, `fecha_pago`, `notas`) VALUES
(1, 1, 150.00, 'Efectivo', 'Completado', 'REC12345', '2025-04-30 14:30:00', 'Pago realizado en recepción.'),
(2, 2, 320.50, 'Tarjeta crédito', 'Pendiente', 'REC67890', NULL, 'Esperando confirmación bancaria.'),
(3, 3, 250.75, 'Transferencia', 'Completado', 'REC11223', '2025-04-28 11:00:00', 'Pago confirmado vía transferencia bancaria.'),
(4, 4, 180.00, 'Tarjeta débito', 'Reembolsado', 'REC44556', '2025-04-25 09:45:00', 'Reembolso procesado por error en facturación.'),
(5, 5, 500.00, 'Efectivo', 'Cancelado', NULL, NULL, 'Pago cancelado por el paciente.');

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
(1, 1, 'admin@clinicadental.com', 'SecurePass123!', 'admin', 'Diego Apaza Quispe', '+51987654321', 1, '2025-04-29 12:30:14', '2025-04-25 09:42:47', '2025-04-29 17:30:14'),
(2, 2, 'dr.gonzales@clinicadental.com', 'Denta##lClinic2023', 'drCarolay', 'Carolay Corvacho FLores', '+51988776655', 1, '2025-04-29 12:26:14', '2025-04-25 09:42:47', '2025-04-29 17:26:14'),
(3, 3, 'recepcion@clinicadental.com', 'Welcome@2023', 'recepcion01', 'Morelia Rodriguez garcia', '+51955667788', 1, '2025-05-01 05:14:27', '2025-04-25 09:42:47', '2025-05-01 10:14:27'),
(4, 1, 'adminn@clinicadental.com', 'delinae@20', 'admin01', 'Delinia Figueroa Gonzalez', '+51955667758', 1, NULL, '2025-04-25 09:42:47', '2025-04-29 00:11:32'),
(5, 4, 'paciente.martinez@gmail.com', 'Patient$$#Secure1', 'jmartinez', 'Juan Martínez Flores', '+51933445566', 1, '2025-05-01 05:02:06', '2025-04-25 09:42:47', '2025-05-01 10:02:06'),
(6, 4, 'figueroa@untels.com', 'gien#B%%eenenn', 'GinerBush', 'Figueroa', '98766773', 1, NULL, '2025-04-25 13:20:51', '2025-05-01 08:32:02'),
(7, 4, 'figueroa@unteffls.com', 'giner%%BB##4', 'GinerBufsh#rrtty', 'Figueroaf', '9876673273', 1, NULL, '2025-04-25 13:46:38', '2025-05-01 08:32:26'),
(8, 4, 'hilariweb@gmail.com', 'fffii&%%GGNN#', 'GinerBushsd123#', 'Figueroaew', '6667771234', 1, NULL, '2025-04-25 13:49:02', '2025-05-01 08:32:48'),
(9, 2, 'giner@hotewemail.comw', 'josefenI#%', 'juanp12133#$', 'Figueroawew', '9876673273', 1, NULL, '2025-04-25 13:50:57', '2025-05-01 08:28:12'),
(10, 2, 'hilariwebe@gmail.com', 'juananN#&', 'juanp122Mfsd#', 'Gonzalez', '9876673', 1, NULL, '2025-04-25 13:54:41', '2025-05-01 08:26:57'),
(11, 2, 'maeia@uteml.comn', 'More#rali', 'maria12M#', 'mariq', '98766773', 1, NULL, '2025-04-25 13:58:13', '2025-05-01 08:26:36'),
(12, 2, 'hilariweb@gmasdil.com', 'juaana12#M', 'juaana12#M', 'Delina Gonzalez', '98766773', 1, '2025-04-25 09:02:56', '2025-04-25 14:02:48', '2025-04-29 00:13:46'),
(13, 2, 'hilariweb@gdmail.com', 'juanp233M%#', 'juanp233M%#', 'Diego Quispe', '98766732773', 1, NULL, '2025-04-26 11:37:33', '2025-04-29 00:14:13');

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
  MODIFY `id_cita` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id_config` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `dentistas`
--
ALTER TABLE `dentistas`
  MODIFY `id_dentista` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `id_paciente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
