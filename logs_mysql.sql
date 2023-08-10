SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

DROP TABLE IF EXISTS `logs`;
CREATE TABLE IF NOT EXISTS `logs` (
  `id` varchar(16) NOT NULL,
  `date` varchar(255) NOT NULL,
  `instanceid` varchar(255) NOT NULL,
  `logsinfo` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;;
COMMIT;