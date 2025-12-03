-- 测试数据脚本
-- 用于快速测试系统功能

-- 插入测试用户
-- 密码统一为: 123456
INSERT INTO `users` (`id`, `username`, `email`, `password`, `real_name`, `student_id`, `role_id`, `auth_status`, `status`, `created_at`) VALUES
(2, 'editor1', 'editor1@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '张三', '2021001', 3, 1, 1, NOW()),
(3, 'editor2', 'editor2@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '李四', '2021002', 3, 1, 1, NOW()),
(4, 'student1', 'student1@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '王五', '2021101', 4, 1, 1, NOW()),
(5, 'student2', 'student2@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '赵六', '2021102', 4, 1, 1, NOW()),
(6, 'student3', 'student3@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '孙七', '2021103', 4, 0, 1, NOW());

-- 插入测试稿件
INSERT INTO `articles` (`id`, `user_id`, `category_id`, `title`, `summary`, `content`, `content_type`, `review_status`, `submit_time`, `created_at`) VALUES
(1, 4, 1, '校运会圆满落幕，我院取得优异成绩', '本次校运会历时三天，我院运动健儿奋勇拼搏，最终获得团体总分第二名的好成绩。', '<p>11月15日，为期三天的校运会在体育场圆满落幕。本次运动会共有来自全校20个学院的500余名运动员参加。</p><p>我院代表队在本次比赛中表现出色，获得了男子4×100米接力冠军、女子跳远冠军等多个项目的好成绩，最终以总分98分的成绩位居团体总分第二名。</p>', 'text', 'pending_first', NOW(), NOW()),
(2, 4, 3, '秋日校园', '秋天的校园，银杏叶飘落，美不胜收。', '<p>秋日的阳光洒在校园的每一个角落<br>银杏叶在风中轻轻飘落<br>铺成一条金黄的小路<br>通向知识的殿堂</p>', 'text', 'pending_first', NOW(), NOW()),
(3, 5, 1, '图书馆新增自习室投入使用', '为满足同学们的学习需求，图书馆新增200个自习座位。', '<p>11月20日，图书馆四楼新增自习室正式投入使用。新自习室配备了舒适的座椅、充足的电源插座和良好的照明设施。</p>', 'text', 'pending_final', NOW(), NOW()),
(4, 5, 2, '我校科研团队在国际期刊发表重要论文', '计算机学院团队在人工智能领域取得突破性进展。', '<p>近日，我校计算机学院科研团队在国际顶级期刊《Nature》上发表了题为"基于深度学习的图像识别新方法"的研究论文。</p>', 'text', 'approved', NOW(), NOW()),
(5, 4, 3, '月下思', '月光如水，思绪万千。', '<p>明月几时有<br>把酒问青天<br>...</p>', 'text', 'draft', NULL, NOW());

-- 插入审核记录
INSERT INTO `review_logs` (`article_id`, `reviewer_id`, `review_type`, `action`, `comment`, `created_at`) VALUES
(3, 2, 'first_review', 'approve', '内容真实，符合要求。', NOW()),
(4, 2, 'first_review', 'approve', '重要新闻，建议快速发布。', NOW()),
(4, 3, 'final_review', 'approve', '同意发布。', NOW());

-- 插入通知
INSERT INTO `notifications` (`user_id`, `type`, `title`, `content`, `is_read`, `created_at`) VALUES
(4, 'review_result', '审核通知', '您的稿件《校运会圆满落幕，我院取得优异成绩》正在审核中。', 0, NOW()),
(5, 'review_result', '审核通知', '您的稿件《图书馆新增自习室投入使用》初审通过，正在等待终审。', 0, NOW()),
(5, 'review_result', '审核通知', '您的稿件《我校科研团队在国际期刊发表重要论文》审核通过，已发布。', 1, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(6, 'system', '欢迎注册', '欢迎加入校园新闻投稿系统！请先完成身份认证后即可开始投稿。', 0, NOW());

-- 插入操作日志
INSERT INTO `operation_logs` (`user_id`, `user_role`, `operation_type`, `target_type`, `target_id`, `details`, `ip_address`, `created_at`) VALUES
(4, 4, 'article_submit', 'article', 1, '提交稿件', '127.0.0.1', NOW()),
(4, 4, 'article_submit', 'article', 2, '提交稿件', '127.0.0.1', NOW()),
(5, 4, 'article_submit', 'article', 3, '提交稿件', '127.0.0.1', NOW()),
(2, 3, 'article_review_approve', 'article', 3, '审核通过：first_review', '127.0.0.1', NOW()),
(3, 3, 'article_review_approve', 'article', 4, '审核通过：final_review', '127.0.0.1', NOW());

-- 测试账号说明
-- 管理员：admin@example.com / admin123456 (已在install.sql中创建)
-- 审核员1：editor1@test.com / 123456 (有初审和终审权限)
-- 审核员2：editor2@test.com / 123456 (有初审和终审权限)
-- 学生1：student1@test.com / 123456 (已认证，可投稿)
-- 学生2：student2@test.com / 123456 (已认证，可投稿)
-- 学生3：student3@test.com / 123456 (未认证，需先认证)
