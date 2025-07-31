/*
 Source Server         : chatdb
 Source Server Type    : MySQL
 Source Server Version : 80042 (8.0.42-0ubuntu0.22.04.1)
 Source Host           : 192.168.0.99:3306
 Source Schema         : chatdb

 Target Server Type    : MySQL
 Target Server Version : 80042 (8.0.42-0ubuntu0.22.04.1)
 File Encoding         : 65001

 Date: 31/07/2025 11:00:40
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for messages
-- ----------------------------
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `sender` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `receiver` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT (now()),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_sender`(`sender` ASC) USING BTREE,
  INDEX `fk_receiver`(`receiver` ASC) USING BTREE,
  CONSTRAINT `fk_receiver` FOREIGN KEY (`receiver`) REFERENCES `users` (`userid`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_sender` FOREIGN KEY (`sender`) REFERENCES `users` (`userid`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of messages
-- ----------------------------
INSERT INTO `messages` VALUES (1, 'test_user1', 'test_user2', 'hello user2', '2025-07-29 11:59:45');
INSERT INTO `messages` VALUES (2, 'test_user2', 'test_user1', 'hello user1', '2025-07-26 12:00:00');
INSERT INTO `messages` VALUES (3, 'test_user1', 'test_user3', 'hello user3', '2025-07-29 12:00:19');
INSERT INTO `messages` VALUES (4, 'test_user3', 'test_user1', 'hello user1', '2025-07-29 12:00:55');
INSERT INTO `messages` VALUES (5, 'test_user1', 'test_user2', 'helo', '2025-07-30 15:30:56');
INSERT INTO `messages` VALUES (9, 'test_user1', 'test_user3', 'hello user 3', '2025-07-31 11:15:09');
INSERT INTO `messages` VALUES (10, 'test_user1', 'test_user3', 'hello 3', '2025-07-31 11:52:00');

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `pssw` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `userid`(`userid` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'test_user1', 'bcb15f821479b4d5772bd0ca866c00ad5f926e3580720659cc80d39c9d09802a', 'test1');
INSERT INTO `users` VALUES (2, 'test_user2', 'bcb15f821479b4d5772bd0ca866c00ad5f926e3580720659cc80d39c9d09802a', 'test2');
INSERT INTO `users` VALUES (3, 'test_user3', 'bcb15f821479b4d5772bd0ca866c00ad5f926e3580720659cc80d39c9d09802a', 'test3');

SET FOREIGN_KEY_CHECKS = 1;
