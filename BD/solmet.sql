-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-08-2025 a las 22:39:04
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
-- Base de datos: `solmet`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inserto`
--

CREATE TABLE `inserto` (
  `id_inserto` varchar(25) NOT NULL,
  `id_molde` varchar(25) NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inserto`
--

INSERT INTO `inserto` (`id_inserto`, `id_molde`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES
('2', 'MTCI0022', 'ejemplo2', 1, '2025-08-23 19:59:26', NULL),
('3', 'MPM0008', 'ejemplo3', 1, '2025-08-23 20:01:01', NULL),
('IINSER3_061', 'M-PEND', 'ESPATULA RESANADORA DE 3\"', 1, '2025-08-18 16:45:43', NULL),
('IINSOR165X3_084', 'M-PEND', 'O RING 15 X 3', 1, '2025-08-18 16:45:43', NULL),
('IINSOR16X3_083', 'M-PEND', 'O RING 16 X 3', 1, '2025-08-18 16:45:43', NULL),
('IINSOR215_085', 'M-PEND', 'O RING 215', 1, '2025-08-18 16:45:43', NULL),
('INSAM05_001', 'MIS0002', 'ADAPTADOR MACHO DE 1/2\"', 1, '2025-08-18 16:42:35', '2025-08-23 19:57:20'),
('INSAM16_041', 'MIS0002', 'ADAPTADOR MACHO DE 16mm', 1, '2025-08-18 16:42:35', NULL),
('INSAM34_007', 'MIL0003', 'ADAPTADOR MACHO DE 3/4\"', 1, '2025-08-18 16:42:35', NULL),
('INSBJ16_096', 'M-PEND', 'BUJE DE 16 MMM', 1, '2025-08-18 16:45:43', '2025-08-23 18:42:07'),
('INSBJ20_088', 'M-PEND', 'BUJE DE 20MM', 1, '2025-08-18 16:45:43', NULL),
('INSBR_064', 'M-PEND', 'NEBULIZADOR ROSCADO', 1, '2025-08-18 16:45:43', NULL),
('INSC0.5_065', 'M-PEND', 'COLLAR DE 1/2\" X 1/2\"', 1, '2025-08-18 16:45:43', NULL),
('INSCEC_057', 'M-PEND', 'CAJA ELECTRICA 2400 (CUADRADA 4X4)', 1, '2025-08-18 16:45:43', NULL),
('INSCEOC_058', 'M-PEND', 'CAJA ELECTRICA OCTAGONAL', 1, '2025-08-18 16:45:43', NULL),
('INSCER_056', 'M-PEND', 'CAJA ELECTRICA 5800 (RECTANGULAR 4X2)', 1, '2025-08-18 16:45:43', NULL),
('INSCI05_047', 'MIM0001', 'CODO INTERNO DE 1/2\"', 1, '2025-08-18 16:42:35', NULL),
('INSCI12_046', 'MIM0001', 'CODO INTERNO DE 12mm', 1, '2025-08-18 16:42:35', NULL),
('INSCI16_006', 'MIM0001', 'CODO INTERNO DE 16mm', 1, '2025-08-18 16:42:35', NULL),
('INSCI1_018', 'MIL0003', 'CODO INTERNO DE 1\"', 1, '2025-08-18 16:42:35', NULL),
('INSCI34_012', 'MIL0003', 'CODO INTERNO DE 3/4\"', 1, '2025-08-18 16:42:35', NULL),
('INSCMAEE_103', 'M-PEND', 'CUERPO MICROASPERSOR ESTACA ECO', 1, '2025-08-18 16:45:43', NULL),
('INSCMARE_104', 'M-PEND', 'CUERPO MICROASPERSOR ROSCA ECO', 1, '2025-08-18 16:45:43', NULL),
('INSCT05_051', 'M-PEND', 'CONECTOR TERMINAL DE 1/2\"', 1, '2025-08-18 16:45:43', NULL),
('INSCTR200_092', 'M-PEND', 'CUERPO TEE ROSCADA 200mm X 1/2\"', 1, '2025-08-18 16:45:43', NULL),
('INSEAG_063', 'M-PEND', 'ESPATULA AGITADORA', 1, '2025-08-18 16:45:43', NULL),
('INSEM4_059', 'M-PEND', 'ESPATULA MASILLADORA DE 4\"', 1, '2025-08-18 16:45:43', NULL),
('INSER105_060', 'M-PEND', 'ESPATULA  RESANADORA DE 1 1/2\"', 1, '2025-08-18 16:45:43', NULL),
('INSER6_062', 'M-PEND', 'ESPATULA RESANADORA DE 6\"', 1, '2025-08-18 16:45:43', NULL),
('INSESTEC_049', 'M-PEND', 'ESTACA ECONOMICA', 1, '2025-08-18 16:45:43', NULL),
('INSMC_050', 'M-PEND', 'MICROCONECTOR', 1, '2025-08-18 16:45:43', NULL),
('INSMT5_066', 'M-PEND', 'MICROTUBO DE 5MM ROLLO', 1, '2025-08-18 16:45:43', NULL),
('INSOR112_101', 'M-PEND', 'O RING 112', 1, '2025-08-18 16:45:43', NULL),
('INSOR208_098', 'M-PEND', 'O RING 208', 1, '2025-08-18 16:45:43', NULL),
('INSOR210_090', 'M-PEND', 'O RING 210', 1, '2025-08-18 16:45:43', NULL),
('INSPAZ_102', 'M-PEND', 'PERILLA AZUL', 1, '2025-08-18 16:45:43', NULL),
('INSPZ16_097', 'M-PEND', 'PINZA DE16 MM', 1, '2025-08-18 16:45:43', NULL),
('INSPZ20_089', 'M-PEND', 'PINZA DE 20MM', 1, '2025-08-18 16:45:43', NULL),
('INSRRM20X05_038', 'M-PEND', 'CUERPO ADAPT.MACHO RAPIDO 20 X 1/2\"', 1, '2025-08-18 16:45:43', NULL),
('INSRRU20_020', 'M-PEND', 'CUERPO RAPIDO UNION DE 20mm', 1, '2025-08-18 16:45:43', NULL),
('INSTCC_052', 'M-PEND', 'TAPA CIEGA 2400 (CUADRADA 4X4)', 1, '2025-08-18 16:45:43', NULL),
('INSTCRD_054', 'M-PEND', 'TAPA CIEGA REDONDA', 1, '2025-08-18 16:45:43', NULL),
('INSTCR_053', 'M-PEND', 'TAPA CIEGA 5800 (RECTANGULAR 4X2)', 1, '2025-08-18 16:45:43', NULL),
('INSTC_100', 'M-PEND', 'TUERCA CINTA', 1, '2025-08-18 16:45:43', NULL),
('INSTH1/4-20_081', 'M-PEND', 'TORNILLO HEXAGONAL 1/4\"  - 20', 1, '2025-08-18 16:45:43', NULL),
('INSTH1/4X1/4_078', 'M-PEND', 'TORNILLO HEXAGONAL 1/4\" X 1 1/4\"', 1, '2025-08-18 16:45:43', NULL),
('INSTH15/16-18_082', 'M-PEND', 'TORNILLO HEXAGONAL 5/16\" - 18', 1, '2025-08-18 16:45:43', NULL),
('INSTH5/164X1 1/2_079', 'M-PEND', 'TORNILLO HEXAGONAL 5/16\" X 1 1/2\"', 1, '2025-08-18 16:45:43', NULL),
('INSTH5/16X2_080', 'M-PEND', 'TORNILLO HEXAGONAL 5/16\" X 2\"', 1, '2025-08-18 16:45:43', NULL),
('INSTI12_045', 'MIM0001', 'TEE INTERNA DE 12mm', 1, '2025-08-18 16:42:35', NULL),
('INSTI16_011', 'MIM0001', 'TEE INTERNA DE 16mm', 1, '2025-08-18 16:42:35', NULL),
('INSTI1_023', 'MIL0003', 'TEE INTERNA DE 1\"', 1, '2025-08-18 16:42:35', NULL),
('INSTI34_017', 'MIL0003', 'TEE INTERNA DE 3/4\"', 1, '2025-08-18 16:42:35', NULL),
('INSTL_039', 'M-PEND', 'TAPON DE LINEA', 1, '2025-08-18 16:45:43', NULL),
('INSTMAEC_105', 'M-PEND', 'TURBINA MICROASPERSION ECO', 1, '2025-08-18 16:45:43', NULL),
('INSTR16_093', 'M-PEND', 'TUERCA RAPIDA DE 16 MM', 1, '2025-08-18 16:45:43', NULL),
('INSTR20_086', 'M-PEND', 'TUERCA RAPIDA DE 20 MM', 1, '2025-08-18 16:45:43', NULL),
('INSTRTR20_040', 'M-PEND', 'TAPON RAPIDO TEE ROSCADA 20mm', 1, '2025-08-18 16:45:43', NULL),
('INSTS_055', 'M-PEND', 'TAPA SUPLEMENTO', 1, '2025-08-18 16:45:43', NULL),
('INSUI05_010', 'MIM0001', 'UNION INTERNA DE 1/2\"', 1, '2025-08-18 16:42:35', NULL),
('INSUI12_044', 'MIM0001', 'UNION INTERNA DE 12mm', 1, '2025-08-18 16:42:35', NULL),
('INSUI16_004', 'MIM0001', 'UNION INTERNA DE 16mm', 1, '2025-08-18 16:42:35', NULL),
('INSUI1_022', 'MIL0003', 'UNIÓN INTERNA DE 1\"', 1, '2025-08-18 16:42:35', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `maquina`
--

CREATE TABLE `maquina` (
  `id_maquina` varchar(25) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `estado` enum('activa','inactiva') NOT NULL DEFAULT 'activa',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `maquina`
--

INSERT INTO `maquina` (`id_maquina`, `nombre`, `estado`, `created_at`, `updated_at`) VALUES
('IHYW6803', 'HAIYING 68 (HYW)', 'activa', '2025-08-18 16:21:19', '2025-08-23 18:39:21'),
('ISPH9502', 'SAPHIR 95', 'activa', '2025-08-18 16:21:19', NULL),
('ISSM150001', 'SSF1500', 'activa', '2025-08-18 16:21:19', NULL),
('ISSM170005', 'SSF1700', 'activa', '2025-08-18 16:21:19', NULL),
('ISSM72004', 'SSF720', 'activa', '2025-08-18 16:21:19', '2025-08-23 18:39:40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `maquina_molde`
--

CREATE TABLE `maquina_molde` (
  `id_molde` varchar(25) NOT NULL,
  `id_maquina` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `maquina_molde`
--

INSERT INTO `maquina_molde` (`id_molde`, `id_maquina`) VALUES
('1', 'ISPH9502'),
('1', 'ISSM150001'),
('1', 'ISSM170005'),
('MAH0035', 'IHYW6803'),
('MAH0035', 'ISPH9502'),
('MAH0035', 'ISSM72004'),
('MAS0011', 'IHYW6803'),
('MAS0011', 'ISPH9502'),
('MAS0011', 'ISSM72004'),
('MBU160036', 'ISPH9502'),
('MBU160036', 'ISSM150001'),
('MBU200037', 'ISPH9502'),
('MBU200037', 'ISSM150001'),
('MCE0024', 'IHYW6803'),
('MCE0024', 'ISPH9502'),
('MCE0024', 'ISSM72004'),
('MCEC0029', 'IHYW6803'),
('MCEC0029', 'ISPH9502'),
('MCEC0029', 'ISSM72004'),
('MCER0030', 'IHYW6803'),
('MCER0030', 'ISPH9502'),
('MCER0030', 'ISSM72004'),
('MCI0012', 'IHYW6803'),
('MCI0012', 'ISPH9502'),
('MCI0012', 'ISSM72004'),
('MCO0013', 'ISPH9502'),
('MCO0013', 'ISSM150001'),
('MCO0013', 'ISSM170005'),
('MCO1050014', 'ISPH9502'),
('MCO1050014', 'ISSM150001'),
('MCO1050014', 'ISSM170005'),
('MCO20015', 'ISPH9502'),
('MCO20015', 'ISSM150001'),
('MCO20015', 'ISSM170005'),
('MCO30016', 'ISPH9502'),
('MCO30016', 'ISSM150001'),
('MCO30016', 'ISSM170005'),
('MCO40017', 'ISPH9502'),
('MCO40017', 'ISSM150001'),
('MCOCT0031', 'IHYW6803'),
('MCOCT0031', 'ISPH9502'),
('MCOCT0031', 'ISSM72004'),
('MEM0018', 'ISPH9502'),
('MEM0018', 'ISSM150001'),
('MES0019', 'ISPH9502'),
('MES0019', 'ISSM150001'),
('MES0027', 'ISSM150001'),
('MES0027', 'ISSM170005'),
('MI1050004', 'ISSM150001'),
('MI20005', 'ISSM150001'),
('MI30006', 'ISSM150001'),
('MIAH0007', 'IHYW6803'),
('MIAH0007', 'ISPH9502'),
('MIAH0007', 'ISSM72004'),
('MIL0003', 'ISSM170005'),
('MIM0001', 'ISPH9502'),
('MIS0002', 'IHYW6803'),
('MIS0002', 'ISPH9502'),
('MIS0002', 'ISSM72004'),
('MMS0032', 'IHYW6803'),
('MMS0032', 'ISPH9502'),
('MMS0032', 'ISSM72004'),
('MPI00041', 'ISPH9502'),
('MPI00041', 'ISSM150001'),
('MPI00041', 'ISSM170005'),
('MPI160038', 'ISPH9502'),
('MPI160038', 'ISSM150001'),
('MPI200039', 'ISPH9502'),
('MPI200039', 'ISSM150001'),
('MPM0008', 'ISPH9502'),
('MPM0008', 'ISSM150001'),
('MPM0008', 'ISSM170005'),
('MPS0009', 'IHYW6803'),
('MPS0009', 'ISPH9502'),
('MPS0009', 'ISSM72004'),
('MSCO0034', 'IHYW6803'),
('MSCO0034', 'ISPH9502'),
('MSCO0034', 'ISSM72004'),
('MSI0033', 'IHYW6803'),
('MSI0033', 'ISPH9502'),
('MSI0033', 'ISSM72004'),
('MTB0020', 'IHYW6803'),
('MTB0020', 'ISPH9502'),
('MTB0020', 'ISSM72004'),
('MTC160040', 'ISPH9502'),
('MTC160040', 'ISSM150001'),
('MTC160040', 'ISSM170005'),
('MTC200021', 'ISPH9502'),
('MTC200021', 'ISSM150001'),
('MTC200021', 'ISSM170005'),
('MTCI0022', 'ISPH9502'),
('MTCI0022', 'ISSM150001'),
('MTCI0022', 'ISSM170005'),
('MTE0025', 'IHYW6803'),
('MTE0025', 'ISPH9502'),
('MTE0025', 'ISSM72004'),
('MVA0010', 'IHYW6803'),
('MVA0010', 'ISPH9502'),
('MVA0010', 'ISSM72004');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materia_prima`
--

CREATE TABLE `materia_prima` (
  `codigo` varchar(25) NOT NULL,
  `polimero` varchar(20) NOT NULL,
  `referencia` varchar(100) NOT NULL,
  `estado` enum('original','peletizado','molido') NOT NULL,
  `color` varchar(50) DEFAULT NULL,
  `procedencia` varchar(100) DEFAULT NULL,
  `uso_final` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `materia_prima`
--

INSERT INTO `materia_prima` (`codigo`, `polimero`, `referencia`, `estado`, `color`, `procedencia`, `uso_final`, `activo`, `created_at`, `updated_at`) VALUES
('ABSCOMONE015', 'ABS', 'COPO', 'molido', 'NEGRO', 'SOLMET', 'MICRO CONECTORES', 1, '2025-08-18 16:28:12', NULL),
('ABSCOPENE012', 'ABS', 'COPO', 'peletizado', 'NEGRO', 'NO DEFINIDA', 'MICRO CONECTORES', 1, '2025-08-18 16:28:12', NULL),
('PELDMOAM025', 'PE', 'LD', 'molido', 'AMARILLO', 'SOLMET', 'ESPATULA', 1, '2025-08-18 16:28:12', NULL),
('PELDMOAZ023', 'PE', 'LD', 'molido', 'AZUL', 'SOLMET', 'ESPATULA', 1, '2025-08-18 16:28:12', NULL),
('PELDMONA026', 'PE', 'LD', 'molido', 'NARANJA', 'SOLMET', 'ESPATULA', 1, '2025-08-18 16:28:12', NULL),
('PELDMOR027', 'PE', 'LD', 'molido', 'ROJO', 'SOLMET', 'ESPATULA', 1, '2025-08-18 16:28:12', NULL),
('PELDMOV024', 'PE', 'LD', 'molido', 'VERDE', 'SOLMET', 'ESPATULA', 1, '2025-08-18 16:28:12', NULL),
('PELDPENAT011', 'PE', 'LD', 'peletizado', 'NATURAL', 'NO DEFINIDA', 'ESPATULA', 1, '2025-08-18 16:28:12', NULL),
('PEPOL641ORNAT004', 'PE', 'POLIFEN 641', 'original', 'NATURAL', 'SALFER', 'ESPATULA', 1, '2025-08-18 16:28:12', NULL),
('PEREORNAT005', 'PE', 'RE EMPACADO', 'original', 'NATURAL', 'NO DEFINIDA', 'ESPATULA', 1, '2025-08-18 16:28:12', NULL),
('POMACMONAT016', 'POM', 'ACETAL', 'molido', 'NATURAL', 'SOLMET', 'PINZA', 1, '2025-08-18 16:28:12', NULL),
('POMACORNAT006', 'POM', 'ACETAL', 'original', 'NATURAL', 'NO DEFINIDA', 'PINZA', 1, '2025-08-18 16:28:12', NULL),
('PP11HORNAT002', 'PP', '11 H', 'original', 'NATURAL', 'PROPILCO', 'PF', 1, '2025-08-18 16:28:12', NULL),
('PP60HORNAT001', 'PP', '60 H', 'original', 'NATURAL', 'PROPILCO', 'PERILLA', 1, '2025-08-18 16:28:12', NULL),
('PPHOPEAZ009', 'PP', 'HOMO', 'peletizado', 'AZUL', 'ALEX', 'TUERCA', 1, '2025-08-18 16:28:12', NULL),
('PPHOPEBL007', 'PP', 'HOMO', 'peletizado', 'BLANCO', 'ALEX', 'CAJA', 1, '2025-08-18 16:28:12', NULL),
('PPHOPENE008', 'PP', 'HOMO', 'peletizado', 'NEGRO', 'ALEX', 'ACCESORIOS', 1, '2025-08-18 16:28:12', NULL),
('PPHOPEVC010', 'PP', 'HOMO', 'peletizado', 'VERDE CONECTOR', 'ALEX', 'CONECTOR ELECT', 1, '2025-08-18 16:28:12', NULL),
('PPPIMOAP021', 'PP', 'POS INDUSTRIA', 'molido', 'AZUL P', 'SOLMET', 'PERILLA', 1, '2025-08-18 16:28:12', NULL),
('PPPIMOAZSOL019', 'PP', 'POS INDUSTRIA', 'molido', 'AZUL SOLMET', 'SOLMET', 'TUERCA', 1, '2025-08-18 16:28:12', NULL),
('PPPIMOBL013', 'PP', 'POS INDUSTRIA', 'molido', 'BLANCOX', '', 'TAPAS', 1, '2025-08-18 16:28:12', NULL),
('PPPIMOBL018', 'PP', 'POS INDUSTRIA', 'molido', 'BLANCO', 'SOLMET', 'CAJA', 1, '2025-08-18 16:28:12', NULL),
('PPPIMONE017', 'PP', 'POS INDUSTRIA', 'molido', 'NEGRO', 'SOLMET', 'ACCESORIOS', 1, '2025-08-18 16:28:12', NULL),
('PPPIMORP022', 'PP', 'POS INDUSTRIA', 'molido', 'ROJO P', 'SOLMET', 'PERILLA', 1, '2025-08-18 16:28:12', NULL),
('PPPIMOVC020', 'PP', 'POS INDUSTRIA', 'molido', 'VERDE CONECTOR', 'SOLMET', 'CONECTOR ELECT', 1, '2025-08-18 16:28:12', NULL),
('PPREMONE014', 'PP', 'REFORZADO', 'molido', 'NEGRO', 'NO DEFINIDA', 'ESTACA', 1, '2025-08-18 16:28:12', NULL),
('PPREORNAT003', 'PP', 'RE EMPACADO', 'original', 'NATURAL', 'NO DEFINIDA', 'VALVULAS', 1, '2025-08-18 16:28:12', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `molde`
--

CREATE TABLE `molde` (
  `id_molde` varchar(25) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `peso_colada_g` decimal(10,3) NOT NULL,
  `estado` enum('disponible','mantenimiento','fuera_servicio') NOT NULL DEFAULT 'disponible',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `molde`
--

INSERT INTO `molde` (`id_molde`, `nombre`, `peso_colada_g`, `estado`, `created_at`, `updated_at`) VALUES
('1', 'CINTA_M', 0.000, 'disponible', '2025-08-18 16:20:07', NULL),
('M-PEND', 'MOLDE PENDIENTE', 0.000, 'disponible', '2025-08-18 16:43:19', NULL),
('MAH0035', 'ADAPTADOR HEMBRA', 0.000, 'disponible', '2025-08-18 16:19:35', '2025-08-23 18:41:16'),
('MAS0011', 'ASPERSORES', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MBU160036', 'BUJE Ø16', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MBU200037', 'BUJE Ø20', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MCE0024', 'CONECTOR_E', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MCEC0029', 'CAJA_EC2400', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MCER0030', 'CAJA_ER5800', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MCI0012', 'CINTA', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MCO0013', 'COLLARES_S', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MCO1050014', 'COLLARES _1 1/2\"', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MCO20015', 'COLLARES_2\"', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MCO30016', 'COLLARES_3\"', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MCO40017', 'COLLARES_4\"', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MCOCT0031', 'CAJA_OCT', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MEM0018', 'ESPATULA_M', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MES0019', 'ESPATULA_S', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MES0027', 'ESTACA', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MI1050004', 'INTERNOS_1 1/2\"', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MI20005', 'INTERNOS_2\"', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MI30006', 'INTERNOS_ 3\"', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MIAH0007', 'INTERNOS_AH', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MIL0003', 'INTERNOS_L', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MIM0001', 'INTERNOS_M', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MIS0002', 'INTERNOS_S', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MMS0032', 'MUÑECOS_S', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MPI00041', 'PINZAS', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MPI160038', 'PINZA Ø16', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MPI200039', 'PINZA Ø20', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MPM0008', 'PATINES_M', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MPS0009', 'PATINES_S', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MSCO0034', 'SELLOR_COLLARES', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MSI0033', 'SILLETA', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MTB0020', 'TURBINA', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MTC160040', 'TUERCA_16', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MTC200021', 'TUERCA_20', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MTCI0022', 'TUERCA_CINTA', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MTE0025', 'TAPAS_E', 0.000, 'disponible', '2025-08-18 16:19:35', NULL),
('MVA0010', 'VALVULAS', 15.000, 'disponible', '2025-08-18 16:19:35', '2025-08-22 01:41:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `molde_producto`
--

CREATE TABLE `molde_producto` (
  `id_molde` varchar(25) NOT NULL,
  `id_producto` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `molde_producto`
--

INSERT INTO `molde_producto` (`id_molde`, `id_producto`) VALUES
('MAS0011', 'MABE0000106'),
('MAS0011', 'MABR0000105'),
('MAS0011', 'MACMAEE0000501'),
('MAS0011', 'MACMAHSE0000503'),
('MAS0011', 'MACMAHSR0000504'),
('MAS0011', 'MACMARE0000502'),
('MAS0011', 'MAMC0000503'),
('MAS0011', 'MAMT0001610'),
('MBU160036', 'RB0001618'),
('MBU200037', 'RB0002020'),
('MCE0024', 'FECT0000501'),
('MCI0012', 'CICAT0001612'),
('MCI0012', 'CICCCA0001603'),
('MCI0012', 'CICCCC0001602'),
('MCI0012', 'CICCCM0001601'),
('MCI0012', 'CIVCAT0001610'),
('MCI0012', 'CIVCATS0001611'),
('MCI0012', 'CIVCCT0001608'),
('MCI0012', 'CIVCMT0001609'),
('MCI0012', 'MVCCA0001607'),
('MCI0012', 'MVCCC0001606'),
('MCI0012', 'MVCCM0001605'),
('MCO0013', 'COCC0032105'),
('MCO0013', 'COCC0040108'),
('MCO0013', 'COCC0050111'),
('MCO0013', 'COCC0050512'),
('MCO0013', 'COCC0250501'),
('MCO0013', 'COCC0253402'),
('MCO0013', 'COCC0320503'),
('MCO0013', 'COCC0323404'),
('MCO0013', 'COCC0400506'),
('MCO0013', 'COCC0403407'),
('MCO0013', 'COCC0500509'),
('MCO0013', 'COCC0503410'),
('MCO1050014', 'COC1050114'),
('MCO1050014', 'COC1050512'),
('MCO1050014', 'COC1053413'),
('MCO20015', 'COC0020117'),
('MCO20015', 'COC0020515'),
('MCO20015', 'COC00210518'),
('MCO20015', 'COC0023416'),
('MCO30016', 'COC0030121'),
('MCO30016', 'COC0030223'),
('MCO30016', 'COC0030519'),
('MCO30016', 'COC00310522'),
('MCO30016', 'COC0033420'),
('MCO40017', 'COC0040126'),
('MCO40017', 'COC0040228'),
('MCO40017', 'COC0040329'),
('MCO40017', 'COC0040524'),
('MCO40017', 'COC00410527'),
('MCO40017', 'COC0043425'),
('MCOCT0031', 'OCO0050402'),
('MEM0018', 'EM0000401'),
('MEM0018', 'EM0000506'),
('MEM0018', 'EM0000507'),
('MEM0018', 'ER0000303'),
('MEM0018', 'ER0000604'),
('MEM0018', 'ER0011202'),
('MES0019', 'EA0001205'),
('MES0019', 'MAEG0000508'),
('MES0019', 'MAEU0000509'),
('MES0027', 'MAEF0000505'),
('MI1050004', 'IC0010521'),
('MI1050004', 'IM0010526'),
('MI1050004', 'IT0010531'),
('MI1050004', 'IU0010515'),
('MI20005', 'IC0000222'),
('MI20005', 'IM0000227'),
('MI20005', 'IT0000232'),
('MI20005', 'IU0000216'),
('MI30006', 'IC0000323'),
('MI30006', 'IM0000328'),
('MI30006', 'IT0000333'),
('MI30006', 'IU0000317'),
('MIAH0007', 'IAHC0000537'),
('MIAH0007', 'IAHC0003838'),
('MIL0003', 'IC0000120'),
('MIL0003', 'IC0003419'),
('MIL0003', 'IM0000125'),
('MIL0003', 'IM0003424'),
('MIL0003', 'IT0000130'),
('MIL0003', 'IT0003429'),
('MIL0003', 'IU0000114'),
('MIL0003', 'IU0003413'),
('MIM0001', 'IC0000518'),
('MIM0001', 'IC0001201'),
('MIM0001', 'IC0001602'),
('MIM0001', 'IF0001608'),
('MIM0001', 'IM0000507'),
('MIM0001', 'IM0001606'),
('MIM0001', 'IT0000505'),
('MIM0001', 'IT0001203'),
('MIM0001', 'IT0001604'),
('MIS0002', 'ICE1601212'),
('MIS0002', 'IM0000507'),
('MIS0002', 'IM0001606'),
('MIS0002', 'IU0000511'),
('MIS0002', 'IU0001209'),
('MIS0002', 'IU0001610'),
('MMS0032', 'OMPVCF03'),
('MPI160038', 'RP0001619'),
('MPI200039', 'RP0002021'),
('MPM0008', 'PFCM0051602'),
('MPM0008', 'PFCU0051601'),
('MPM0008', 'RCH0160507'),
('MPM0008', 'RCH0200505'),
('MPM0008', 'RCH0203406'),
('MPM0008', 'RCM0160504'),
('MPM0008', 'RCM0203403'),
('MPM0008', 'RCU0001602'),
('MPM0008', 'RCU0002001'),
('MPS0009', 'PFCH0051603'),
('MPS0009', 'PFCU0051601'),
('MPS0009', 'RCM0200508'),
('MSI0033', 'ISTT0000135'),
('MSI0033', 'ISTY0001634'),
('MTB0020', 'MATMAE0000504'),
('MTB0020', 'MATMAHS0000505'),
('MTC160040', 'PFTA0001604'),
('MTC160040', 'RTR0001617'),
('MTC200021', 'RTR0002016'),
('MTCI0022', 'CITC0001604'),
('MTE0025', 'FETC240002'),
('MTE0025', 'FETC580003'),
('MTE0025', 'FETCR36004'),
('MTE0025', 'FETS245805'),
('MTE0025', 'OTCO0050401'),
('MVA0010', 'MACMAN0000501'),
('MVA0010', 'MVCMV0001202'),
('MVA0010', 'MVCMV0001601'),
('MVA0010', 'MVPA0001603'),
('MVA0010', 'MVPR0001604'),
('MVA0010', 'RCC0001612'),
('MVA0010', 'RCC0002013'),
('MVA0010', 'RCT0001609'),
('MVA0010', 'RCT0002010'),
('MVA0010', 'RCTR0200511'),
('MVA0010', 'RCV0001614'),
('MVA0010', 'RCV0002015');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_estado_historial`
--

CREATE TABLE `orden_estado_historial` (
  `id_estado` bigint(20) NOT NULL,
  `id_op` bigint(20) NOT NULL,
  `estado_anterior` enum('creada','programada','en_proceso','finalizada','cancelada') NOT NULL,
  `estado_nuevo` enum('creada','programada','en_proceso','finalizada','cancelada') NOT NULL,
  `cambiado_por` int(11) NOT NULL,
  `fecha_cambio` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `orden_estado_historial`
--

INSERT INTO `orden_estado_historial` (`id_estado`, `id_op`, `estado_anterior`, `estado_nuevo`, `cambiado_por`, `fecha_cambio`) VALUES
(1, 20, 'creada', 'finalizada', 2, '2025-08-23 00:01:42'),
(2, 20, 'finalizada', 'creada', 2, '2025-08-23 00:02:35'),
(3, 20, 'creada', 'programada', 2, '2025-08-23 00:02:58'),
(4, 21, 'creada', 'en_proceso', 2, '2025-08-23 11:50:42'),
(5, 24, 'creada', 'programada', 1, '2025-08-23 14:03:03'),
(6, 24, 'programada', 'creada', 1, '2025-08-23 14:03:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_inserto`
--

CREATE TABLE `orden_inserto` (
  `id_op` bigint(20) NOT NULL,
  `id_inserto` varchar(25) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Disparadores `orden_inserto`
--
DELIMITER $$
CREATE TRIGGER `trg_orden_inserto_molde_match` BEFORE INSERT ON `orden_inserto` FOR EACH ROW BEGIN
  DECLARE v_molde_op  VARCHAR(25);
  DECLARE v_molde_ins VARCHAR(25);

  SELECT id_molde INTO v_molde_op
  FROM orden_produccion WHERE id_op = NEW.id_op;

  SELECT id_molde INTO v_molde_ins
  FROM inserto WHERE id_inserto = NEW.id_inserto;

  IF v_molde_op IS NULL OR v_molde_ins IS NULL OR v_molde_op <> v_molde_ins THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'El inserto no pertenece al molde de la orden';
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_materia_prima`
--

CREATE TABLE `orden_materia_prima` (
  `id_op` bigint(20) NOT NULL,
  `tipo` enum('original','peletizado','molido') NOT NULL,
  `codigo_mp` varchar(25) NOT NULL,
  `porcentaje_plan` decimal(6,3) DEFAULT NULL,
  `kg_plan` decimal(12,3) DEFAULT NULL,
  `kg_real` decimal(12,3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `orden_materia_prima`
--

INSERT INTO `orden_materia_prima` (`id_op`, `tipo`, `codigo_mp`, `porcentaje_plan`, `kg_plan`, `kg_real`) VALUES
(5, 'original', 'PP11HORNAT002', 0.000, 100.000, NULL),
(5, 'peletizado', 'PPHOPEAZ009', 0.000, 100.000, NULL),
(5, 'molido', 'PPPIMOAP021', 0.000, 400.000, NULL),
(6, 'original', 'PPREORNAT003', 0.000, 1.000, NULL),
(7, 'original', 'PP11HORNAT002', 0.000, 10.000, NULL),
(7, 'peletizado', 'ABSCOPENE012', 0.000, 20.000, NULL),
(7, 'molido', 'PPREMONE014', 0.000, 10.000, NULL),
(8, 'original', 'PP11HORNAT002', 0.000, 1.000, NULL),
(8, 'peletizado', 'PPHOPEAZ009', 0.000, 1.000, NULL),
(8, 'molido', 'PPPIMOBL013', 0.000, 1.000, NULL),
(9, 'original', 'PP11HORNAT002', NULL, 200.000, NULL),
(9, 'peletizado', 'ABSCOPENE012', NULL, 200.000, NULL),
(9, 'molido', 'PPREMONE014', NULL, 100.000, NULL),
(10, 'original', 'PPREORNAT003', NULL, 500.000, NULL),
(10, 'peletizado', 'PELDPENAT011', NULL, 100.000, NULL),
(10, 'molido', 'PPREMONE014', NULL, 50.000, NULL),
(11, 'original', 'PP11HORNAT002', NULL, 50.000, NULL),
(11, 'peletizado', 'PPHOPEAZ009', NULL, 50.000, NULL),
(16, 'original', 'PP11HORNAT002', NULL, 75.000, NULL),
(18, 'original', 'PP11HORNAT002', NULL, 500.000, NULL),
(18, 'peletizado', 'PPHOPEAZ009', NULL, 200.000, NULL),
(18, 'molido', 'PPREMONE014', NULL, 36.000, NULL),
(19, 'original', 'PPREORNAT003', NULL, 50.000, NULL),
(19, 'peletizado', 'PELDPENAT011', NULL, 50.000, NULL),
(19, 'molido', 'PPREMONE014', NULL, 23.000, NULL),
(20, 'original', 'PP11HORNAT002', NULL, 4.000, NULL),
(20, 'peletizado', 'PPHOPEAZ009', NULL, 4.000, NULL),
(20, 'molido', 'PPREMONE014', NULL, 1.000, NULL),
(21, 'original', 'PP11HORNAT002', NULL, 800.006, NULL),
(22, 'original', 'PP11HORNAT002', NULL, 500.000, NULL),
(22, 'peletizado', 'PPHOPEAZ009', NULL, 300.000, NULL),
(22, 'molido', 'PPPIMOVC020', NULL, 2000.000, NULL),
(23, 'original', 'PP11HORNAT002', NULL, 650.000, NULL),
(24, 'original', 'PP11HORNAT002', NULL, 500.000, NULL),
(24, 'peletizado', 'PPHOPEBL007', NULL, 200.000, NULL),
(24, 'molido', 'PPPIMOAP021', NULL, 80.000, NULL),
(25, 'original', 'PP11HORNAT002', NULL, 56.000, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_metricas`
--

CREATE TABLE `orden_metricas` (
  `id_op` bigint(20) NOT NULL,
  `peso_teorico_total_kg` decimal(12,3) NOT NULL,
  `peso_real_total_kg` decimal(12,3) DEFAULT NULL,
  `devolucion_teorica_kg` decimal(12,3) NOT NULL,
  `peso_piezas_total_orden_kg` decimal(12,3) DEFAULT NULL,
  `peso_total_orden_kg` decimal(12,3) DEFAULT NULL,
  `devolucion_real_kg` decimal(12,3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `orden_metricas`
--

INSERT INTO `orden_metricas` (`id_op`, `peso_teorico_total_kg`, `peso_real_total_kg`, `devolucion_teorica_kg`, `peso_piezas_total_orden_kg`, `peso_total_orden_kg`, `devolucion_real_kg`) VALUES
(4, 0.009, 24.000, 0.000, NULL, NULL, NULL),
(5, 0.005, 20.100, 0.000, NULL, NULL, NULL),
(6, 0.008, 23.300, 0.000, NULL, NULL, NULL),
(7, 0.008, 23.300, 0.000, NULL, NULL, NULL),
(8, 0.008, 23.300, 0.000, NULL, NULL, NULL),
(9, 0.017, NULL, 0.000, NULL, NULL, NULL),
(10, 0.010, NULL, 139.327, NULL, NULL, NULL),
(11, 0.005, NULL, 49.252, NULL, NULL, NULL),
(12, 0.008, NULL, 0.007, NULL, NULL, NULL),
(13, 0.008, NULL, 143.775, NULL, NULL, NULL),
(14, 0.008, NULL, 13.802, NULL, NULL, NULL),
(15, 0.008, NULL, 22.425, NULL, NULL, NULL),
(16, 0.025, NULL, 0.000, NULL, NULL, NULL),
(17, 0.033, NULL, 0.000, NULL, NULL, NULL),
(18, 0.027, NULL, 0.000, NULL, NULL, NULL),
(19, 0.027, NULL, 0.000, NULL, NULL, NULL),
(20, 0.008, NULL, 66.136, 0.000, 0.000, NULL),
(21, 0.017, NULL, 0.000, 0.000, 0.000, NULL),
(22, 0.010, NULL, 200.000, 0.000, 0.000, NULL),
(23, 0.017, NULL, 0.000, 0.000, 0.000, NULL),
(24, 0.043, NULL, 0.000, 0.000, 0.000, NULL),
(25, 0.008, NULL, 16.100, 0.000, 0.000, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_produccion`
--

CREATE TABLE `orden_produccion` (
  `id_op` bigint(20) NOT NULL,
  `numero_op` varchar(32) NOT NULL,
  `id_molde` varchar(25) NOT NULL,
  `id_maquina` varchar(25) NOT NULL,
  `fecha_inicio_prog` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_fin_estimada` datetime NOT NULL,
  `fecha_inicio_real` datetime DEFAULT NULL,
  `fecha_fin_real` datetime DEFAULT NULL,
  `estado` enum('creada','programada','en_proceso','finalizada','cancelada') NOT NULL DEFAULT 'creada',
  `observaciones` text DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `cerrado_por` int(11) DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `orden_produccion`
--

INSERT INTO `orden_produccion` (`id_op`, `numero_op`, `id_molde`, `id_maquina`, `fecha_inicio_prog`, `fecha_fin_estimada`, `fecha_inicio_real`, `fecha_fin_real`, `estado`, `observaciones`, `creado_por`, `cerrado_por`, `fecha_creacion`, `fecha_actualizacion`, `created_at`, `updated_at`) VALUES
(4, '20250822001', 'MVA0010', 'IHYW6803', '2025-08-22 01:40:17', '2025-08-28 02:21:17', NULL, NULL, 'programada', NULL, 2, NULL, '2025-08-22 01:40:17', NULL, '2025-08-22 06:40:17', NULL),
(5, '20250822002', 'MVA0010', 'IHYW6803', '2025-08-22 01:46:50', '2025-09-01 10:31:50', NULL, NULL, 'programada', NULL, 2, NULL, '2025-08-22 01:46:50', NULL, '2025-08-22 06:46:50', NULL),
(6, '20250822003', 'MVA0010', 'ISSM72004', '2025-08-22 05:04:01', '2025-08-22 05:22:01', NULL, NULL, 'programada', NULL, 2, NULL, '2025-08-22 05:04:01', NULL, '2025-08-22 10:04:01', NULL),
(7, '20250822004', 'MVA0010', 'IHYW6803', '2025-08-22 05:24:07', '2025-08-22 17:19:07', NULL, NULL, 'programada', NULL, 2, NULL, '2025-08-22 05:24:07', NULL, '2025-08-22 10:24:07', NULL),
(8, '20250822005', 'MVA0010', 'ISPH9502', '2025-08-22 05:35:07', '2025-08-22 16:01:07', NULL, NULL, 'programada', NULL, 2, NULL, '2025-08-22 05:35:07', NULL, '2025-08-22 10:35:07', NULL),
(9, '20250822006', 'MVA0010', 'IHYW6803', '2025-08-22 19:14:53', '2025-08-26 22:14:53', NULL, NULL, 'creada', '', NULL, NULL, '2025-08-22 19:14:53', NULL, '2025-08-23 00:14:53', NULL),
(10, '20250822007', 'MVA0010', 'IHYW6803', '2025-08-22 19:22:02', '2025-08-25 05:22:02', NULL, NULL, 'creada', '', NULL, NULL, '2025-08-22 19:22:02', NULL, '2025-08-23 00:22:02', NULL),
(11, '20250822008', 'MVA0010', 'IHYW6803', '2025-08-22 19:22:50', '2025-08-24 17:22:50', NULL, NULL, 'creada', '', NULL, NULL, '2025-08-22 19:22:50', NULL, '2025-08-23 00:22:50', NULL),
(12, '20250822009', 'MVA0010', 'IHYW6803', '2025-08-22 19:32:03', '2025-08-22 19:32:03', NULL, NULL, 'creada', '', NULL, NULL, '2025-08-22 19:32:03', NULL, '2025-08-23 00:32:03', NULL),
(13, '20250822010', 'MVA0010', 'IHYW6803', '2025-08-22 19:34:24', '2025-08-22 21:34:24', NULL, NULL, 'creada', '', NULL, NULL, '2025-08-22 19:34:24', NULL, '2025-08-23 00:34:24', NULL),
(14, '20250822011', 'MVA0010', 'IHYW6803', '2025-08-22 19:44:03', '2025-08-24 02:44:03', NULL, NULL, 'creada', '', NULL, NULL, '2025-08-22 19:44:03', NULL, '2025-08-23 00:44:03', NULL),
(15, '20250822012', 'MVA0010', 'ISSM72004', '2025-08-22 19:44:39', '2025-08-23 19:44:39', NULL, NULL, 'creada', '', NULL, NULL, '2025-08-22 19:44:39', NULL, '2025-08-23 00:44:39', NULL),
(16, '20250822013', 'MVA0010', 'IHYW6803', '2025-08-22 19:49:44', '2025-08-23 10:49:44', NULL, NULL, 'creada', '', NULL, NULL, '2025-08-22 19:49:44', NULL, '2025-08-23 00:49:44', NULL),
(17, '20250822014', 'MVA0010', 'IHYW6803', '2025-08-22 19:57:30', '2025-08-26 09:57:30', NULL, NULL, 'creada', '', NULL, NULL, '2025-08-22 19:57:30', NULL, '2025-08-23 00:57:30', NULL),
(18, '20250822015', 'MVA0010', 'IHYW6803', '2025-08-22 21:44:54', '2025-08-22 05:06:00', NULL, NULL, 'creada', '', NULL, NULL, '2025-08-22 21:44:54', NULL, '2025-08-23 02:44:54', NULL),
(19, '20250822016', 'MVA0010', 'ISSM72004', '2025-08-22 21:58:00', '2025-08-22 22:58:00', NULL, NULL, 'creada', '', NULL, NULL, '2025-08-22 21:58:00', NULL, '2025-08-23 02:58:00', NULL),
(20, '20250822017', 'MVA0010', 'IHYW6803', '2025-08-22 22:47:11', '2025-08-25 05:47:11', NULL, NULL, 'programada', '', 2, NULL, '2025-08-22 22:47:11', '2025-08-23 00:02:58', '2025-08-23 03:47:11', '2025-08-23 05:02:58'),
(21, '20250823001', 'MVA0010', 'ISSM72004', '2025-08-23 11:48:48', '2025-08-26 00:48:48', NULL, NULL, 'en_proceso', '', 2, NULL, '2025-08-23 11:48:48', '2025-08-23 11:50:42', '2025-08-23 16:48:48', '2025-08-23 16:50:42'),
(22, '20250823002', 'MVA0010', 'ISPH9502', '2025-08-23 12:05:50', '2025-08-24 10:05:50', NULL, NULL, 'creada', 'bcbuvbbvvjb', 2, NULL, '2025-08-23 12:05:50', NULL, '2025-08-23 17:05:50', NULL),
(23, '20250823003', 'MVA0010', 'IHYW6803', '2025-08-23 12:59:21', '2025-08-23 09:49:00', NULL, NULL, 'creada', '', 1, NULL, '2025-08-23 12:59:21', NULL, '2025-08-23 17:59:21', NULL),
(24, '20250823004', 'MVA0010', 'ISPH9502', '2025-08-23 13:05:08', '2025-08-23 11:07:00', NULL, NULL, 'creada', 'ASDFGHJ', 1, NULL, '2025-08-23 13:05:08', '2025-08-23 14:03:07', '2025-08-23 18:05:08', '2025-08-23 19:03:07'),
(25, '20250823005', 'MVA0010', 'IHYW6803', '2025-08-23 14:33:30', '2025-08-26 11:33:30', NULL, NULL, 'creada', '', 1, NULL, '2025-08-23 14:33:30', NULL, '2025-08-23 19:33:30', NULL);

--
-- Disparadores `orden_produccion`
--
DELIMITER $$
CREATE TRIGGER `trg_op_autonum` BEFORE INSERT ON `orden_produccion` FOR EACH ROW BEGIN
  IF NEW.numero_op IS NULL OR NEW.numero_op = '' THEN
    SET NEW.numero_op = CONCAT(
      'OP-', DATE_FORMAT(CURRENT_DATE(), '%Y%m%d'), '-',
      LPAD((SELECT COALESCE(MAX(id_op),0)+1 FROM orden_produccion), 5, '0')
    );
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_producto`
--

CREATE TABLE `orden_producto` (
  `id_item` bigint(20) NOT NULL,
  `id_op` bigint(20) NOT NULL,
  `id_producto` varchar(25) NOT NULL,
  `cantidad_unidades` int(11) NOT NULL,
  `peso_teorico_g` decimal(10,3) NOT NULL,
  `ciclos_por_min` decimal(10,3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `orden_producto`
--

INSERT INTO `orden_producto` (`id_item`, `id_op`, `id_producto`, `cantidad_unidades`, `peso_teorico_g`, `ciclos_por_min`) VALUES
(7, 4, 'MVPA0001603', 1, 9.000, 2.400),
(8, 5, 'MVCMV0001202', 1, 5.100, 2.000),
(9, 6, 'MVPR0001604', 1, 8.300, 2.400),
(10, 7, 'MVPR0001604', 1, 8.300, 2.400),
(11, 8, 'MVPR0001604', 1, 8.300, 2.400),
(12, 9, 'MVPR0001604', 1, 8.300, 2.400),
(13, 9, 'MVPA0001603', 1, 9.000, 2.400),
(14, 10, 'MVPR0001604', 1, 8.300, 2.400),
(15, 10, 'RCV0002015', 2, 1.000, 2.400),
(16, 11, 'MVCMV0001202', 1, 5.100, 2.000),
(17, 12, 'MVPR0001604', 1, 8.300, 2.400),
(18, 13, 'MVPR0001604', 1, 8.300, 2.400),
(19, 14, 'MVPR0001604', 1, 8.300, 2.400),
(20, 15, 'MVPR0001604', 1, 8.300, 2.400),
(21, 16, 'MVPR0001604', 3, 8.300, 2.400),
(22, 17, 'MVPR0001604', 4, 8.300, 2.400),
(23, 18, 'MVPA0001603', 3, 9.000, 2.400),
(24, 19, 'MVPR0001604', 1, 8.300, 2.400),
(25, 19, 'MVPA0001603', 2, 9.000, 2.400),
(26, 19, 'RCV0002015', 1, 1.000, 2.400),
(27, 19, 'RCV0001614', 1, 0.000, 1.000),
(28, 19, 'MVCMV0001601', 1, 0.000, 1.000),
(29, 19, 'RCC0001612', 1, 0.000, 1.000),
(30, 20, 'MVPR0001604', 1, 8.300, 2.400),
(31, 21, 'MVPR0001604', 2, 8.300, 2.400),
(32, 22, 'RCV0001614', 1, 0.000, 1.000),
(33, 22, 'RCV0002015', 1, 1.000, 2.400),
(34, 22, 'MVPA0001603', 1, 9.000, 2.400),
(35, 23, 'MVPR0001604', 1, 8.300, 2.400),
(36, 23, 'MVPA0001603', 1, 9.000, 2.400),
(37, 24, 'MVPR0001604', 3, 8.300, 2.400),
(38, 24, 'MVPA0001603', 2, 9.000, 2.400),
(39, 25, 'MVPR0001604', 1, 8.300, 2.400);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `id_producto` varchar(25) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `peso_teorico_g` decimal(10,3) NOT NULL,
  `ciclos_por_min` decimal(10,3) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`id_producto`, `nombre`, `peso_teorico_g`, `ciclos_por_min`, `activo`, `created_at`, `updated_at`) VALUES
('CICAT0001612', 'CONECTOR ARRANCADOR CON TUERCA', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('CICCCA0001603', 'CUERPO CONECTOR CINTA / ARRANCADOR', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('CICCCC0001602', 'CUERPO CONECTOR CINTA / CIINTA', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('CICCCM0001601', 'CUERPO CONECTOR CINTA / MANGUERA', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('CITC0001604', 'TUERCA/CINTA', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('CIVCAT0001610', 'VALVULA CINTA/ARRANCADORA CON TUERCA', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('CIVCATS0001611', 'VALVULA CINTA/ARRANCADORA CON TUERCA Y SILLETA', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('CIVCCT0001608', 'VALVULA CINTA/CINTA CON TUERCA', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('CIVCMT0001609', 'VALVULA CINTA/MANGUERA CON TUERCA', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COC0020117', 'COLLAR DE 2\"X 1\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COC0020515', 'COLLAR DE 2\"X  1/2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COC00210518', 'COLLAR DE 2\"X 1 1/2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COC0023416', 'COLLAR DE 2\"X 3/4\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COC0030121', 'COLLAR DE 3\"X 1\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COC0030223', 'COLLAR DE 3\"X 2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COC0030519', 'COLLAR DE 3\"X 1/2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COC00310522', 'COLLAR DE 3\"X 1 1/2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COC0033420', 'COLLAR DE 3\"X 3/4\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COC0040126', 'COLLAR DE 4\"X 1\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COC0040228', 'COLLAR DE 4\"X 2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COC0040329', 'COLLAR DE 4\"X 3\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COC0040524', 'COLLAR DE 4\"X 1/2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COC00410527', 'COLLAR DE 4\"X 1 1/2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COC0043425', 'COLLAR DE 4\"X 3/4\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COC1050114', 'COLLAR DE 11/2\"X 1\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COC1050512', 'COLLAR DE 11/2\"X 1/2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COC1053413', 'COLLAR DE 11/2\"X 3/4\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COCC0032105', 'CUERPO COLLAR 32 mm X (1\") X 1\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COCC0040108', 'CUERPO COLLAR 40 mm X (1\") X 1\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COCC0050111', 'CUERPO COLLAR 50mm X (11/2\") X 1\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COCC0050512', 'CUERPO COLLAR 1/2\" X 1/2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COCC0250501', 'CUERPO COLLAR 25mm X (3/4\") X 1/2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COCC0253402', 'CUERPO COLLAR 25mm X (3/4\") X 3/4\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COCC0320503', 'CUERPO COLLAR 32 mm X (1\") X 1/2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COCC0323404', 'CUERPO COLLAR 32 mm X (1\") X 3/4\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COCC0400506', 'CUERPO COLLAR 40 mm X (11/4\") X 1/2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COCC0403407', 'CUERPO COLLAR 40 mm X (1\") X 3/4\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COCC0500509', 'CUERPO COLLAR 50mm X (11/2\") X 1/2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('COCC0503410', 'CUERPO COLLAR 50mm X (11/2\") X 3/4\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('EA0001205', 'ESPATULA AGITADORA', 1.000, 2.000, 1, '2025-08-18 16:05:54', '2025-08-18 16:12:38'),
('EM0000401', 'ESPATULA MASILLADORA DE 4\"', 1.000, 2.000, 1, '2025-08-18 16:05:54', '2025-08-18 16:12:38'),
('EM0000506', 'ESPATULA MASILLADORA DE 5\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('EM0000507', 'ESPATULA MASILLADORA DE 6\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('ER0000303', 'ESPATULA RESANADORA DE 3\"', 1.000, 2.000, 1, '2025-08-18 16:05:54', '2025-08-18 16:11:57'),
('ER0000604', 'ESPATULA RESANADORA DE 6\"', 1.000, 2.000, 1, '2025-08-18 16:05:54', '2025-08-18 16:12:38'),
('ER0011202', 'ESPATULA RESANADORA DE 11/2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('FECT0000501', 'CONECTOR TERMINAL DE 1/2\"', 6.300, 2.000, 1, '2025-08-18 16:05:54', '2025-08-18 16:12:38'),
('FETC240002', 'TAPA CIEGA 2400 (CUADRADA 4X4)', 1.000, 1.000, 1, '2025-08-18 16:05:54', '2025-08-18 16:13:11'),
('FETC580003', 'TAPA CIEGA 5800 (RECTANGULAR 4X2)', 1.000, 1.000, 1, '2025-08-18 16:05:54', '2025-08-18 16:13:11'),
('FETCR36004', 'TAPA CIEGA REDONDA', 1.000, 1.000, 1, '2025-08-18 16:05:54', '2025-08-18 16:13:11'),
('FETS245805', 'TAPA SUPLEMENTO', 1.000, 1.000, 1, '2025-08-18 16:05:54', '2025-08-18 16:13:11'),
('IAHC0000537', 'ADAPTADOR HEMBRA DE 1/2\" X 1/2\" (COPA)', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('IAHC0003838', 'ADAPTADOR HEMBRA DE 1/2\" X 3/8\" (COPA)', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('IC0000120', 'CODO INTERNO DE 1\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('IC0000222', 'CODO INTERNO DE 2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('IC0000323', 'CODO INTERNO DE 3\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('IC0000518', 'CODO INTERNO DE 1/2\"', 1.000, 2.000, 1, '2025-08-18 16:05:54', NULL),
('IC0001201', 'CODO INTERNO DE 12mm', 1.000, 2.400, 1, '2025-08-18 16:05:54', '2025-08-18 16:12:38'),
('IC0001602', 'CODO INTERNO DE 16mm', 3.300, 2.400, 1, '2025-08-18 16:05:54', '2025-08-18 16:12:38'),
('IC0003419', 'CODO INTERNO DE 3/4\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('IC0010521', 'CODO INTERNO DE 1 1/2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('ICE1601212', 'CUERPO ESPIGO DE  16mm 12mm', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('IF0001608', 'TAPON FINAL DE LINEA 16mm', 5.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('IM0000125', 'ADAPTADOR MACHO INT. DE 1\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('IM0000227', 'ADAPTADOR MACHO INT. DE 2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('IM0000328', 'ADAPTADOR MACHO INT. DE 3\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('IM0000507', 'ADAPTADOR MACHO INT DE  1/2\"', 1.000, 5.000, 1, '2025-08-18 16:05:54', NULL),
('IM0001606', 'ADAPTADOR MACHO INT DE 16mm', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('IM0003424', 'ADAPTADOR MACHO INT. DE 3/4\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('IM0010526', 'ADAPTADOR MACHO INT. DE 1 1/2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('ISTT0000135', 'SILLETA TIPO \"T\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('ISTY0001634', 'SILLETA TIPO YOYO 16mm', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('IT0000130', 'TEE INTERNA DE 1\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('IT0000232', 'TEE INTERNA DE 2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('IT0000333', 'TEE INTERNA DE 3\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('IT0000505', 'TEE INTERNA DE 1/2\"', 1.000, 2.400, 1, '2025-08-18 16:05:54', '2025-08-18 16:13:11'),
('IT0001203', 'TEE INTERNA DE 12mm', 1.000, 2.400, 1, '2025-08-18 16:05:54', '2025-08-18 16:13:11'),
('IT0001604', 'TEE INTERNA DE 16mm', 4.600, 2.400, 1, '2025-08-18 16:05:54', '2025-08-18 16:13:11'),
('IT0003429', 'TEE INTERNA DE 3/4\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('IT0010531', 'TEE INTERNA DE 1 1/2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('IU0000114', 'UNION INTERNA DE 1\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('IU0000216', 'UNION INTERNA DE 2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('IU0000317', 'UNION INTERNA DE 3\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('IU0000511', 'UNION INTERNA DE  1/2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('IU0001209', 'UNION INTERNA DE 12mm', 1.000, 2.400, 1, '2025-08-18 16:05:54', '2025-08-18 16:13:11'),
('IU0001610', 'UNION INTERNA DE  16mm', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('IU0003413', 'UNION INTERNA DE 3/4\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('IU0010515', 'UNION INTERNA DE 1 1/2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('MABE0000106', 'BOQUILLA ESTACA', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('MABR0000105', 'BOQUILLA ROSCA', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('MACMAEE0000501', 'CUERPO MICROSPERSOR ESTACA ECO', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('MACMAHSE0000503', 'CUERPO MICROASPERSOR HIGH SPEED ESTACA', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('MACMAHSR0000504', 'CUERPO MICROASPERSOR HIGH SPEED ROSCA', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('MACMAN0000501', 'CUERPO MICROASPERSOR NEBULIZADOR 1/2\" NPT', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('MACMARE0000502', 'CUERPO MICROSPERSOR ROSCA  ECO', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('MAEF0000505', 'ESTACA FIJA', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('MAEG0000508', 'ESTACA GOTEADORA', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('MAEU0000509', 'ESTACA UNIVERSAL', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('MAMC0000503', 'MICROCONECTOR', 1.000, 1.000, 1, '2025-08-18 16:05:54', '2025-08-18 16:12:38'),
('MAMT0001610', 'MICROTAPÓN', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('MATMAE0000504', 'TURBINA MICROASPERSION ECO', 1.000, 1.000, 1, '2025-08-18 16:05:54', '2025-08-18 16:13:11'),
('MATMAHS0000505', 'TURBINA MICROASPERSION HIGH SPEED', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('MVCCA0001607', 'CUERPO MINI VALVULA CINTA / ARRANCADOR', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('MVCCC0001606', 'CUERPO MINI VALVULA CINTA / CINTA', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('MVCCM0001605', 'CUERPO MIINI VALVULA CINTA / MANGUERA', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('MVCMV0001202', 'CUERPO VALVULA DE 12mm', 5.100, 2.000, 1, '2025-08-18 16:05:54', '2025-08-18 16:12:38'),
('MVCMV0001601', 'CUERPO VALVULA DE 16mm', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('MVPA0001603', 'PERILLA AZUL', 9.000, 2.400, 1, '2025-08-18 16:05:54', '2025-08-22 03:35:28'),
('MVPR0001604', 'PERILLA ROJA', 8.300, 2.400, 1, '2025-08-18 16:05:54', '2025-08-18 16:12:38'),
('OCO0050402', 'CAJA ORGANIZADORA 5 X 4', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('OFP0000104', 'FILTRO PERCOLADOR', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('OMPVCF03', 'MUÑECO SORPRESA', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('OTCO0050401', 'TAPA CAJA ORGANIZADORA 5 X 4', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('PFCH0051603', 'CUERPO ADAP. PF HEMBRA DE 1/2\" X  16mm ', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('PFCM0051602', 'CUERPO ADAP. PF MACHO DE 1/2\" X  16mm ', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('PFCU0051601', 'CUERPO ADAP. PF UNION 1/2\" X  16mm', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('PFTA0001604', 'TUERCA ACOMETIDA DE 16mm', 10.900, 1.400, 1, '2025-08-18 16:05:54', '2025-08-18 16:13:11'),
('RB0001618', 'BUJE DE 16mm', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('RB0002020', 'BUJE DE 20mm', 1.200, 2.000, 1, '2025-08-18 16:05:54', '2025-08-18 16:11:57'),
('RCC0001612', 'CUERPO RACOR RAPIDO CODO  DE  16mm', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('RCC0002013', 'CUERPO RACOR RAPIDO CODO  DE 20mm', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('RCH0160507', 'CUERPO RACOR RAPIDO HEMBRA 16mm X 1/2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('RCH0200505', 'CUERPO RACOR RAPIDO HEMBRA  20mm X 1/2', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('RCH0203406', 'CUERPO RACOR RAPIDO  HEMBRA 20mm X 3/4', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('RCM0160504', 'CUERPO RACOR RAPIDO MACHO  16mm X 1/2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('RCM0200508', 'CUERPO RACOR RAPIDO  MACHO  20mm X 1/2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('RCM0203403', 'CUERPO RACOR RAPIDO MACHO  20mm X 3/4\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('RCT0001609', 'CUERPO RACOR RAPIDO TEE  DE 16mm', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('RCT0002010', 'CUERPO RACOR RAPIDO TEE DE 20mm', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('RCTR0200511', 'CUERPO RACOR RAPIDO TEE ROSCADO DE 20mm X 1/2\"', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('RCU0001602', 'CUERPO RACOR RAPIDO UNION 16mm', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('RCU0002001', 'CUERPO RACOR RAPIDO UNION DE 20mm', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('RCV0001614', 'CUERPO VALVULA RAPIDA DE 16mm', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('RCV0002015', 'CUERPO VALVULA RAPIDA DE 20mm', 1.000, 2.400, 1, '2025-08-18 16:05:54', '2025-08-18 16:12:38'),
('RP0001619', 'PINZA DE 16mm', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('RP0002021', 'PINZA DE 20mm', 1.000, 2.000, 1, '2025-08-18 16:05:54', '2025-08-18 16:12:38'),
('RTR0001617', 'TUERCA RAPIDA DE 16mm', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL),
('RTR0002016', 'TUERCA RAPIDA DE 20mm', 0.000, 1.000, 1, '2025-08-18 16:05:54', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id_rol` int(11) NOT NULL,
  `nombre` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id_rol`, `nombre`) VALUES
(1, 'admin'),
(2, 'operador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(20) NOT NULL,
  `username` varchar(60) NOT NULL,
  `nombre` varchar(80) NOT NULL DEFAULT '',
  `apellido` varchar(80) NOT NULL DEFAULT '',
  `foto` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `username`, `nombre`, `apellido`, `foto`, `password_hash`, `id_rol`, `activo`, `created_at`) VALUES
(1, 'admin', '', '', NULL, '$2y$10$Jr47SJbE8zdjkC5D185cG.U6NaQX74STRWMddD4AUXXaaK9fG95wW', 1, 1, '2025-08-18 17:28:46'),
(2, 'oper', 'astrid janeth', 'castroo', 'IMG/Usuarios/ingeniero2.png', '$2y$10$dtBbUcabeL1HED4duoz1JuqObBSfLb/6zXUV0mjnX3JYSpc.Th/Qe', 2, 1, '2025-08-18 17:28:46');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `inserto`
--
ALTER TABLE `inserto`
  ADD PRIMARY KEY (`id_inserto`),
  ADD KEY `idx_inserto_molde` (`id_molde`);

--
-- Indices de la tabla `maquina`
--
ALTER TABLE `maquina`
  ADD PRIMARY KEY (`id_maquina`),
  ADD KEY `idx_maq_estado` (`estado`);

--
-- Indices de la tabla `maquina_molde`
--
ALTER TABLE `maquina_molde`
  ADD PRIMARY KEY (`id_molde`,`id_maquina`),
  ADD KEY `fk_mm_maquina` (`id_maquina`);

--
-- Indices de la tabla `materia_prima`
--
ALTER TABLE `materia_prima`
  ADD PRIMARY KEY (`codigo`);

--
-- Indices de la tabla `molde`
--
ALTER TABLE `molde`
  ADD PRIMARY KEY (`id_molde`),
  ADD KEY `idx_molde_estado` (`estado`);

--
-- Indices de la tabla `molde_producto`
--
ALTER TABLE `molde_producto`
  ADD PRIMARY KEY (`id_molde`,`id_producto`),
  ADD KEY `fk_producto_mp` (`id_producto`);

--
-- Indices de la tabla `orden_estado_historial`
--
ALTER TABLE `orden_estado_historial`
  ADD PRIMARY KEY (`id_estado`),
  ADD KEY `id_op` (`id_op`);

--
-- Indices de la tabla `orden_inserto`
--
ALTER TABLE `orden_inserto`
  ADD PRIMARY KEY (`id_op`,`id_inserto`),
  ADD KEY `fk_oin_inserto` (`id_inserto`);

--
-- Indices de la tabla `orden_materia_prima`
--
ALTER TABLE `orden_materia_prima`
  ADD PRIMARY KEY (`id_op`,`tipo`,`codigo_mp`),
  ADD KEY `fk_omp_mp` (`codigo_mp`);

--
-- Indices de la tabla `orden_metricas`
--
ALTER TABLE `orden_metricas`
  ADD PRIMARY KEY (`id_op`);

--
-- Indices de la tabla `orden_produccion`
--
ALTER TABLE `orden_produccion`
  ADD PRIMARY KEY (`id_op`),
  ADD UNIQUE KEY `numero_op` (`numero_op`),
  ADD KEY `fk_op_molde` (`id_molde`),
  ADD KEY `fk_op_maquina` (`id_maquina`),
  ADD KEY `idx_op_estado_fecha` (`estado`,`fecha_inicio_prog`),
  ADD KEY `idx_creado_por` (`creado_por`),
  ADD KEY `idx_cerrado_por` (`cerrado_por`);

--
-- Indices de la tabla `orden_producto`
--
ALTER TABLE `orden_producto`
  ADD PRIMARY KEY (`id_item`),
  ADD KEY `fk_oi_producto` (`id_producto`),
  ADD KEY `idx_oi_op` (`id_op`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `idx_prod_activo` (`activo`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_usuario_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `orden_estado_historial`
--
ALTER TABLE `orden_estado_historial`
  MODIFY `id_estado` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `orden_produccion`
--
ALTER TABLE `orden_produccion`
  MODIFY `id_op` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `orden_producto`
--
ALTER TABLE `orden_producto`
  MODIFY `id_item` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `inserto`
--
ALTER TABLE `inserto`
  ADD CONSTRAINT `fk_inserto_molde` FOREIGN KEY (`id_molde`) REFERENCES `molde` (`id_molde`);

--
-- Filtros para la tabla `maquina_molde`
--
ALTER TABLE `maquina_molde`
  ADD CONSTRAINT `fk_mm_maquina` FOREIGN KEY (`id_maquina`) REFERENCES `maquina` (`id_maquina`),
  ADD CONSTRAINT `fk_mm_molde` FOREIGN KEY (`id_molde`) REFERENCES `molde` (`id_molde`);

--
-- Filtros para la tabla `molde_producto`
--
ALTER TABLE `molde_producto`
  ADD CONSTRAINT `fk_molde_mp` FOREIGN KEY (`id_molde`) REFERENCES `molde` (`id_molde`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_producto_mp` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `orden_estado_historial`
--
ALTER TABLE `orden_estado_historial`
  ADD CONSTRAINT `fk_hist_op` FOREIGN KEY (`id_op`) REFERENCES `orden_produccion` (`id_op`) ON DELETE CASCADE;

--
-- Filtros para la tabla `orden_inserto`
--
ALTER TABLE `orden_inserto`
  ADD CONSTRAINT `fk_oin_inserto` FOREIGN KEY (`id_inserto`) REFERENCES `inserto` (`id_inserto`),
  ADD CONSTRAINT `fk_oin_op` FOREIGN KEY (`id_op`) REFERENCES `orden_produccion` (`id_op`);

--
-- Filtros para la tabla `orden_materia_prima`
--
ALTER TABLE `orden_materia_prima`
  ADD CONSTRAINT `fk_omp_mp` FOREIGN KEY (`codigo_mp`) REFERENCES `materia_prima` (`codigo`),
  ADD CONSTRAINT `fk_omp_op` FOREIGN KEY (`id_op`) REFERENCES `orden_produccion` (`id_op`);

--
-- Filtros para la tabla `orden_metricas`
--
ALTER TABLE `orden_metricas`
  ADD CONSTRAINT `fk_om_op` FOREIGN KEY (`id_op`) REFERENCES `orden_produccion` (`id_op`);

--
-- Filtros para la tabla `orden_produccion`
--
ALTER TABLE `orden_produccion`
  ADD CONSTRAINT `fk_op_maquina` FOREIGN KEY (`id_maquina`) REFERENCES `maquina` (`id_maquina`),
  ADD CONSTRAINT `fk_op_molde` FOREIGN KEY (`id_molde`) REFERENCES `molde` (`id_molde`);

--
-- Filtros para la tabla `orden_producto`
--
ALTER TABLE `orden_producto`
  ADD CONSTRAINT `fk_oi_op` FOREIGN KEY (`id_op`) REFERENCES `orden_produccion` (`id_op`),
  ADD CONSTRAINT `fk_oi_producto` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`);

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
