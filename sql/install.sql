CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nickname` varchar(50) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT '',
  `bio` varchar(255) DEFAULT '这个人很懒，什么都没写~',
  `role` varchar(20) DEFAULT 'admin',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) NOT NULL,
  `video_url` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_video` (`ip`,`video_url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `api_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `endpoint` varchar(50) NOT NULL,
  `ip` varchar(50) NOT NULL,
  `referer` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key_name` varchar(50) NOT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_name` (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `settings` (`key_name`, `value`) VALUES 
('api_url', 'https://douyin.npcq.cn/api.php'),
('api_format', 'json'),
('api_key', '');
