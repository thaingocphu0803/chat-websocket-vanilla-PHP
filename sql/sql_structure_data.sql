/*
 Source Server         : ubuntu mysql
 Source Server Type    : MySQL
 Source Server Version : 80042 (8.0.42-0ubuntu0.22.04.1)
 Source Host           : 192.168.0.99:3306
 Source Schema         : chatdb

 Target Server Type    : MySQL
 Target Server Version : 80042 (8.0.42-0ubuntu0.22.04.1)
 File Encoding         : 65001

 Date: 14/08/2025 09:28:57
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for group_chat
-- ----------------------------
DROP TABLE IF EXISTS `group_chat`;
CREATE TABLE `group_chat`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `group_uid` char(18) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_by` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `group_uid_2`(`group_uid` ASC) USING BTREE,
  INDEX `fk_created_by`(`created_by` ASC) USING BTREE,
  INDEX `group_uid`(`group_uid` ASC) USING BTREE,
  CONSTRAINT `fk_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`userid`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 48 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of group_chat
-- ----------------------------
INSERT INTO `group_chat` VALUES (42, 'group_f5ae0c5d68d3', 'Dev Group', 'test_user1');
INSERT INTO `group_chat` VALUES (43, 'group_711811bec84f', 'Backend Group', 'test_user1');
INSERT INTO `group_chat` VALUES (47, 'group_83a317759be6', 'Frontend Group', 'test_user1');

-- ----------------------------
-- Table structure for group_member
-- ----------------------------
DROP TABLE IF EXISTS `group_member`;
CREATE TABLE `group_member`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `group_uid` char(18) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `member_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_member_id`(`member_id` ASC) USING BTREE,
  INDEX `fk_group_uid`(`group_uid` ASC) USING BTREE,
  CONSTRAINT `fk_group_uid` FOREIGN KEY (`group_uid`) REFERENCES `group_chat` (`group_uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_member_id` FOREIGN KEY (`member_id`) REFERENCES `users` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 23 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of group_member
-- ----------------------------
INSERT INTO `group_member` VALUES (1, 'group_f5ae0c5d68d3', 'test_user2');
INSERT INTO `group_member` VALUES (2, 'group_f5ae0c5d68d3', 'test_user3');
INSERT INTO `group_member` VALUES (3, 'group_f5ae0c5d68d3', 'test_user4');
INSERT INTO `group_member` VALUES (4, 'group_f5ae0c5d68d3', 'test_user1');
INSERT INTO `group_member` VALUES (5, 'group_711811bec84f', 'test_user3');
INSERT INTO `group_member` VALUES (6, 'group_711811bec84f', 'test_user4');
INSERT INTO `group_member` VALUES (7, 'group_711811bec84f', 'test_user1');
INSERT INTO `group_member` VALUES (19, 'group_83a317759be6', 'test_user2');
INSERT INTO `group_member` VALUES (20, 'group_83a317759be6', 'test_user5');
INSERT INTO `group_member` VALUES (21, 'group_83a317759be6', 'test_user6');
INSERT INTO `group_member` VALUES (22, 'group_83a317759be6', 'test_user1');

-- ----------------------------
-- Table structure for messages
-- ----------------------------
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `sender` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `partner` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `group_uid` char(18) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT (now()),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_group_chat_uid`(`group_uid` ASC) USING BTREE,
  INDEX `fk_sender`(`sender` ASC) USING BTREE,
  INDEX `fk_partner`(`partner` ASC) USING BTREE,
  CONSTRAINT `fk_group_chat_uid` FOREIGN KEY (`group_uid`) REFERENCES `group_chat` (`group_uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_partner` FOREIGN KEY (`partner`) REFERENCES `users` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sender` FOREIGN KEY (`sender`) REFERENCES `users` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 99 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of messages
-- ----------------------------
INSERT INTO `messages` VALUES (75, 'test_user1', NULL, 'group_f5ae0c5d68d3', 'hello new member', '2025-08-13 16:58:39');
INSERT INTO `messages` VALUES (76, 'test_user1', NULL, 'group_f5ae0c5d68d3', 'are you ok?', '2025-08-13 16:58:56');
INSERT INTO `messages` VALUES (77, 'test_user3', NULL, 'group_f5ae0c5d68d3', 'im fine', '2025-08-13 16:59:42');
INSERT INTO `messages` VALUES (78, 'test_user3', 'test_user1', NULL, 'hello', '2025-08-13 17:00:10');
INSERT INTO `messages` VALUES (79, 'test_user1', 'test_user3', NULL, 'morning', '2025-08-13 17:00:26');
INSERT INTO `messages` VALUES (80, 'test_user3', NULL, 'group_f5ae0c5d68d3', 'tks', '2025-08-13 17:01:58');
INSERT INTO `messages` VALUES (88, 'test_user2', NULL, 'group_f5ae0c5d68d3', 'hi im new bie', '2025-08-13 17:39:24');
INSERT INTO `messages` VALUES (89, 'test_user1', NULL, 'group_83a317759be6', 'wellcome fontend group', '2025-08-13 17:39:54');
INSERT INTO `messages` VALUES (90, 'test_user3', NULL, 'group_711811bec84f', 'wellcome backend group', '2025-08-13 17:40:16');
INSERT INTO `messages` VALUES (91, 'test_user2', 'test_user1', NULL, 'dear user 1', '2025-08-13 17:40:44');
INSERT INTO `messages` VALUES (92, 'test_user1', 'test_user2', NULL, 'morning user 2', '2025-08-13 17:40:56');
INSERT INTO `messages` VALUES (93, 'test_user3', 'test_user2', NULL, 'Dear user 3', '2025-08-13 17:41:30');
INSERT INTO `messages` VALUES (94, 'test_user2', 'test_user3', NULL, 'wrong !! user 2', '2025-08-13 17:41:46');
INSERT INTO `messages` VALUES (95, 'test_user1', NULL, 'group_711811bec84f', 'Hi! im new', '2025-08-13 17:42:49');
INSERT INTO `messages` VALUES (96, 'test_user2', NULL, 'group_83a317759be6', 'morning !!', '2025-08-13 17:43:26');
INSERT INTO `messages` VALUES (97, 'test_user2', NULL, 'group_83a317759be6', 'Im new', '2025-08-13 17:43:54');
INSERT INTO `messages` VALUES (98, 'test_user3', 'test_user1', NULL, 'morning !!', '2025-08-13 17:45:09');

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
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'test_user1', 'bcb15f821479b4d5772bd0ca866c00ad5f926e3580720659cc80d39c9d09802a', 'test user 1');
INSERT INTO `users` VALUES (2, 'test_user2', 'bcb15f821479b4d5772bd0ca866c00ad5f926e3580720659cc80d39c9d09802a', 'test user 2');
INSERT INTO `users` VALUES (3, 'test_user3', 'bcb15f821479b4d5772bd0ca866c00ad5f926e3580720659cc80d39c9d09802a', 'test user 3');
INSERT INTO `users` VALUES (4, 'test_user4', 'bcb15f821479b4d5772bd0ca866c00ad5f926e3580720659cc80d39c9d09802a', 'test user 4');
INSERT INTO `users` VALUES (5, 'test_user5', 'bcb15f821479b4d5772bd0ca866c00ad5f926e3580720659cc80d39c9d09802a', 'test user 5');
INSERT INTO `users` VALUES (6, 'test_user6', 'bcb15f821479b4d5772bd0ca866c00ad5f926e3580720659cc80d39c9d09802a', 'test user 6');
INSERT INTO `users` VALUES (7, 'test_user7', 'bcb15f821479b4d5772bd0ca866c00ad5f926e3580720659cc80d39c9d09802a', 'test user 7');
INSERT INTO `users` VALUES (8, 'test_user8', 'bcb15f821479b4d5772bd0ca866c00ad5f926e3580720659cc80d39c9d09802a', 'test user 8');

SET FOREIGN_KEY_CHECKS = 1;
