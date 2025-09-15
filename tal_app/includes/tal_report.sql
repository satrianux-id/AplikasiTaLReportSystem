/*
 Navicat Premium Dump SQL

 Source Server         : Newton_DB
 Source Server Type    : MySQL
 Source Server Version : 100432 (10.4.32-MariaDB)
 Source Host           : localhost:3306
 Source Schema         : tal_report

 Target Server Type    : MySQL
 Target Server Version : 100432 (10.4.32-MariaDB)
 File Encoding         : 65001

 Date: 12/09/2025 16:10:47
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for task_history
-- ----------------------------
DROP TABLE IF EXISTS `task_history`;
CREATE TABLE `task_history`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `task_id` int NOT NULL,
  `changed_field` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `old_value` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `new_value` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `changed_by` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'System',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `task_id`(`task_id` ASC) USING BTREE,
  CONSTRAINT `task_history_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 35 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of task_history
-- ----------------------------
INSERT INTO `task_history` VALUES (32, 15, 'status', 'pending', 'in_progress', '2025-09-12 15:03:59', 'System');
INSERT INTO `task_history` VALUES (33, 15, 'image_changed', NULL, 'task_68c3d414a60366.00798450.png', '2025-09-12 15:04:36', 'System');
INSERT INTO `task_history` VALUES (34, 15, 'status', 'in_progress', 'completed', '2025-09-12 15:04:36', 'System');

-- ----------------------------
-- Table structure for tasks
-- ----------------------------
DROP TABLE IF EXISTS `tasks`;
CREATE TABLE `tasks`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `task_name` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `description` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `status` enum('pending','in_progress','completed') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'pending',
  `priority` enum('low','medium','high') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'medium',
  `due_date` datetime NULL DEFAULT NULL,
  `gambar` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  `image_path` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 16 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tasks
-- ----------------------------
INSERT INTO `tasks` VALUES (15, 'Data Asset IT', 'Melakukan data assetIT diruangan', 'completed', 'low', '2025-09-12 00:00:00', 'task_68c3d3de321142.69960509.png', '2025-09-12 15:03:42', '2025-09-12 15:04:36', 'task_68c3d414a60366.00798450.png');

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `password` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `full_name` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `email` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `role` enum('admin','user','supervisor') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'user',
  `is_active` tinyint(1) NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'admin', '$2y$10$FsI6cvD.YoV4QmXKzZ.RfuqtcpFEpzX8HowWzBKONBBhrmNHswQRe', 'Administrator', 'admin@talreport.com', 'admin', 1, '2025-09-11 15:00:34', '2025-09-11 15:00:34');

SET FOREIGN_KEY_CHECKS = 1;
