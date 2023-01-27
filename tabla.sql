CREATE TABLE IF NOT EXISTS `app_logs` (
    `log_id` int(11) NOT NULL AUTO_INCREMENT,
    `fecha` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `evento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `tabla` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `registro` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `consulta` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `usuario` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `ip_origen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `url_solicitada` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `datos_solicitados` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `estado` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `mensaje` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    PRIMARY KEY (`log_id`)
) ENGINE = InnoDB AUTO_INCREMENT = 8 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
