SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `gar_info_files` (
  `id` int NOT NULL,
  `ts` decimal(31,4) NOT NULL DEFAULT '0.0000',
  `tf` decimal(31,4) NOT NULL DEFAULT '0.0000',
  `n` int NOT NULL DEFAULT '0',
  `sz` int NOT NULL DEFAULT '0',
  `filename` varchar(127) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE `l_worker` (
  `id` int NOT NULL,
  `worker` int NOT NULL DEFAULT '0',
  `instance` int NOT NULL DEFAULT '0',
  `s` int NOT NULL DEFAULT '0',
  `ts` datetime DEFAULT NULL,
  `f` int NOT NULL DEFAULT '0',
  `tf` datetime DEFAULT NULL,
  `runtime` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `n_queue` int NOT NULL DEFAULT '0',
  `n_proc` int NOT NULL DEFAULT '0',
  `result` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE `l_worker_conflict` (
  `id` int NOT NULL,
  `t` int NOT NULL DEFAULT '0',
  `ts` datetime DEFAULT NULL,
  `worker` int NOT NULL DEFAULT '0',
  `instance` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE `z_settings` (
  `id` int NOT NULL,
  `key` varchar(127) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `v_string` varchar(1023) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `v_int` int DEFAULT NULL,
  `v_boolean` int NOT NULL DEFAULT '0',
  `v_object` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


ALTER TABLE `gar_info_files`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `l_worker`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `l_worker_conflict`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rec` (`worker`,`instance`),
  ADD KEY `worker` (`worker`);

ALTER TABLE `z_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);


ALTER TABLE `gar_info_files`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `l_worker`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `l_worker_conflict`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `z_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
