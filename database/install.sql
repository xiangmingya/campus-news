-- 校园新闻投稿系统数据库结构
-- 版本: 1.0.0
-- 创建时间: 2024-03-20

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `email` varchar(100) NOT NULL COMMENT '邮箱',
  `phone` varchar(20) DEFAULT NULL COMMENT '手机号',
  `real_name` varchar(50) DEFAULT NULL COMMENT '真实姓名',
  `student_id` varchar(50) DEFAULT NULL COMMENT '学号',
  `role_id` int(11) DEFAULT '4' COMMENT '角色ID：1超管 2管理员 3审核员 4投稿用户',
  `auth_status` tinyint(1) DEFAULT '0' COMMENT '认证状态：0未认证 1已认证 2拒绝 3审核中',
  `cert_file_url` varchar(255) DEFAULT NULL COMMENT '认证文件URL',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态：0禁用 1正常',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  KEY `auth_status` (`auth_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

-- ----------------------------
-- Table structure for roles
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '角色ID',
  `name` varchar(50) NOT NULL COMMENT '角色名称',
  `description` varchar(255) DEFAULT NULL COMMENT '角色描述',
  `permissions` text COMMENT '权限JSON',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色表';

-- ----------------------------
-- Records of roles
-- ----------------------------
INSERT INTO `roles` VALUES 
(1, '超级管理员', '拥有所有权限', '{"all": true}'),
(2, '管理员', '用户管理、稿件管理、系统设置', '{"user_manage": true, "article_manage": true, "article_review": true, "article_final_review": true, "article_publish": true, "system_manage": true}'),
(3, '审核员', '稿件审核权限', '{"article_review": true, "article_final_review": true}'),
(4, '投稿用户', '投稿和管理自己的稿件', '{"article_create": true, "article_edit": true}');

-- ----------------------------
-- Table structure for categories
-- ----------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '栏目ID',
  `name` varchar(50) NOT NULL COMMENT '栏目名称',
  `slug` varchar(50) DEFAULT NULL COMMENT '栏目标识',
  `description` varchar(255) DEFAULT NULL COMMENT '栏目描述',
  `sort_order` int(11) DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态：0禁用 1启用',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='栏目分类表';

-- ----------------------------
-- Records of categories
-- ----------------------------
INSERT INTO `categories` VALUES 
(1, '校园新闻', 'campus-news', '校园新闻报道', 1, 1, NOW()),
(2, '学术科研', 'academic', '学术科研成果', 2, 1, NOW()),
(3, '文艺作品', 'literature', '文学、诗歌、散文等', 3, 1, NOW()),
(4, '摄影作品', 'photography', '摄影作品展示', 4, 1, NOW()),
(5, '视频作品', 'video', '视频作品展示', 5, 1, NOW());

-- ----------------------------
-- Table structure for articles
-- ----------------------------
DROP TABLE IF EXISTS `articles`;
CREATE TABLE `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '稿件ID',
  `user_id` int(11) NOT NULL COMMENT '作者ID',
  `category_id` int(11) NOT NULL COMMENT '栏目ID',
  `title` varchar(200) NOT NULL COMMENT '标题',
  `summary` text COMMENT '摘要',
  `content` longtext COMMENT '内容',
  `content_type` enum('text','word','multimedia') DEFAULT 'text' COMMENT '内容类型：text富文本 word文档 multimedia多媒体',
  `word_file_url` varchar(255) DEFAULT NULL COMMENT 'Word文档URL',
  `baidu_link` varchar(255) DEFAULT NULL COMMENT '百度网盘链接',
  `baidu_password` varchar(20) DEFAULT NULL COMMENT '百度网盘提取码',
  `cover_image` varchar(255) DEFAULT NULL COMMENT '封面图',
  `keywords` varchar(200) DEFAULT NULL COMMENT '关键词',
  `review_status` enum('draft','pending_first','pending_final','approved','rejected','revision_required') DEFAULT 'draft' COMMENT '审核状态',
  `first_reviewer_id` int(11) DEFAULT NULL COMMENT '初审员ID',
  `first_review_time` datetime DEFAULT NULL COMMENT '初审时间',
  `final_reviewer_id` int(11) DEFAULT NULL COMMENT '终审员ID',
  `final_review_time` datetime DEFAULT NULL COMMENT '终审时间',
  `publish_status` tinyint(1) DEFAULT '0' COMMENT '发布状态：0未发布 1已发布 2已归档',
  `publish_time` datetime DEFAULT NULL COMMENT '发布时间',
  `submit_time` datetime DEFAULT NULL COMMENT '提交时间',
  `views` int(11) DEFAULT '0' COMMENT '浏览次数',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`),
  KEY `review_status` (`review_status`),
  KEY `publish_status` (`publish_status`),
  KEY `first_reviewer_id` (`first_reviewer_id`),
  KEY `final_reviewer_id` (`final_reviewer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='稿件表';

-- ----------------------------
-- Table structure for review_logs
-- ----------------------------
DROP TABLE IF EXISTS `review_logs`;
CREATE TABLE `review_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `article_id` int(11) NOT NULL COMMENT '稿件ID',
  `reviewer_id` int(11) NOT NULL COMMENT '审核员ID',
  `review_type` enum('first_review','final_review') NOT NULL COMMENT '审核类型：first_review初审 final_review终审',
  `action` enum('approve','reject','request_revision') NOT NULL COMMENT '审核动作：approve通过 reject拒绝 request_revision要求修改',
  `comment` text COMMENT '审核意见',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '审核时间',
  PRIMARY KEY (`id`),
  KEY `article_id` (`article_id`),
  KEY `reviewer_id` (`reviewer_id`),
  KEY `review_type` (`review_type`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='审核日志表';

-- ----------------------------
-- Table structure for notifications
-- ----------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '通知ID',
  `user_id` int(11) NOT NULL COMMENT '接收用户ID',
  `type` varchar(50) DEFAULT NULL COMMENT '通知类型',
  `title` varchar(200) NOT NULL COMMENT '通知标题',
  `content` text NOT NULL COMMENT '通知内容',
  `is_read` tinyint(1) DEFAULT '0' COMMENT '是否已读：0未读 1已读',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='通知表';

-- ----------------------------
-- Table structure for operation_logs
-- ----------------------------
DROP TABLE IF EXISTS `operation_logs`;
CREATE TABLE `operation_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `user_id` int(11) DEFAULT NULL COMMENT '操作用户ID',
  `user_role` int(11) DEFAULT NULL COMMENT '用户角色',
  `operation_type` varchar(50) NOT NULL COMMENT '操作类型',
  `target_type` varchar(50) DEFAULT NULL COMMENT '目标类型',
  `target_id` int(11) DEFAULT NULL COMMENT '目标ID',
  `details` text COMMENT '详细信息',
  `ip_address` varchar(50) DEFAULT NULL COMMENT 'IP地址',
  `user_agent` varchar(255) DEFAULT NULL COMMENT '用户代理',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '操作时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `operation_type` (`operation_type`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='操作日志表';

-- ----------------------------
-- Table structure for system_settings
-- ----------------------------
DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '设置ID',
  `setting_key` varchar(100) NOT NULL COMMENT '设置键',
  `setting_value` text COMMENT '设置值',
  `setting_type` varchar(50) DEFAULT 'text' COMMENT '设置类型',
  `description` varchar(255) DEFAULT NULL COMMENT '描述',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统设置表';

-- ----------------------------
-- Records of system_settings
-- ----------------------------
INSERT INTO `system_settings` VALUES 
(1, 'site_name', '校园新闻投稿系统', 'text', '网站名称', NOW()),
(2, 'daily_submit_limit', '5', 'number', '每日投稿上限', NOW()),
(3, 'weekly_video_limit', '2', 'number', '每周视频投稿上限', NOW()),
(4, 'upload_max_size', '10485760', 'number', '上传文件大小限制（字节）', NOW());

-- ----------------------------
-- 初始管理员账号
-- 用户名: admin@example.com
-- 密码: admin123456
-- ----------------------------
INSERT INTO `users` (`id`, `username`, `email`, `password`, `real_name`, `role_id`, `auth_status`, `status`) 
VALUES (1, 'admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '系统管理员', 1, 1, 1);

SET FOREIGN_KEY_CHECKS = 1;
