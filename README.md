# 校园新闻投稿系统

基于原生PHP开发的校园媒体投稿管理系统，支持文字、摄影、视频等多种形式投稿。

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-blue.svg)](https://php.net)
[![MySQL Version](https://img.shields.io/badge/MySQL-%3E%3D5.7-orange.svg)](https://www.mysql.com)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

## ✨ 功能特点

- 🔐 **多角色权限管理** - 超管/管理员/审核员/投稿用户，权限清晰分明
- ✅ **人工身份认证** - 学生证/校园卡人工审核，确保用户真实性
- 📝 **两级审核流程** - 初审→终审→发布，严格把控稿件质量
- 🎨 **多媒体内容支持** - 文字稿件/Word上传/百度网盘链接
- 📊 **完整的操作日志** - 记录所有重要操作，可追溯
- 🛡️ **投稿限制机制** - 每日/每周投稿限制，防止滥用
- 🔔 **通知系统** - 审核结果实时通知，用户体验友好
- 📈 **审核统计分析** - 多维度统计，图表可视化

## 🚀 技术栈

- **后端框架**: 原生PHP 7.4+ (轻量级MVC架构)
- **前端技术**: HTML5 + CSS3 + JavaScript + jQuery + Bootstrap 5
- **数据库**: MySQL 5.7+
- **图表库**: Chart.js
- **部署环境**: 宝塔面板 + Nginx + PHP-FPM

## 环境要求

- PHP >= 7.4
- MySQL >= 5.7
- Nginx >= 1.20
- PHP扩展: fileinfo, exif, gd, mysqli, pdo_mysql

## 安装部署

### 1. 上传项目文件

将项目文件上传到服务器的网站根目录（如：`/www/wwwroot/campus-news`）

### 2. 配置数据库

```bash
# 创建数据库
mysql -u root -p

CREATE DATABASE campus_news DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 导入数据库结构
mysql -u root -p campus_news < database/install.sql
```

### 3. 配置项目

编辑 `config/database.php` 文件，修改数据库连接信息：

```php
'connections' => [
    'mysql' => [
        'hostname' => '127.0.0.1',
        'database' => 'campus_news',
        'username' => 'root',
        'password' => 'your_password',
        'hostport' => '3306',
    ],
],
```

### 4. 设置目录权限

```bash
chmod -R 755 /www/wwwroot/campus-news
chmod -R 777 /www/wwwroot/campus-news/runtime
chmod -R 777 /www/wwwroot/campus-news/uploads
```

### 5. 配置Nginx伪静态

宝塔面板 → 网站 → 设置 → 伪静态，选择ThinkPHP或添加以下规则：

```nginx
location / {
    if (!-e $request_filename){
        rewrite ^(.*)$ /index.php?s=$1 last;
        break;
    }
}
```

### 6. 访问网站

访问：`http://your-domain.com`

默认管理员账号：`admin@example.com`  
默认密码：`admin123456`

**⚠️ 首次登录后请立即修改密码！**

## 目录结构

```
campus-news/
├── app/                    # 应用核心目录
│   ├── controller/         # 控制器
│   ├── model/              # 数据模型
│   ├── service/            # 业务服务
│   ├── middleware/         # 中间件
│   └── common/             # 公共函数
├── config/                 # 配置文件
├── public/                 # 公共入口
│   ├── index.php           # 入口文件
│   └── static/             # 静态资源
├── runtime/                # 运行时目录
├── uploads/                # 上传文件
│   ├── cert/               # 认证文件
│   ├── cover/              # 封面图
│   └── word/               # Word文档
├── vendor/                 # 第三方依赖
├── view/                   # 视图模板
└── database/               # 数据库文件
```

## 主要功能模块

### 用户模块
- 用户注册与登录
- 身份认证（学生证/校园卡审核）
- 个人信息管理
- 权限管理

### 投稿模块
- 文字稿件投稿（富文本编辑器/Word上传）
- 多媒体稿件投稿（网盘链接）
- 草稿保存
- 投稿限制检查
- 我的稿件管理

### 审核模块
- 两级审核流程
- 审核工作台
- 审核意见管理
- 审核统计

### 后台管理
- 用户管理
- 稿件管理
- 栏目管理
- 系统设置
- 操作日志
- 数据统计

## 系统配置

### 投稿限制设置

在后台管理 → 系统设置中可配置：

- 每日投稿上限（默认5篇）
- 每周视频投稿上限（默认2个）
- 文件上传大小限制（默认10MB）

### 审核流程

```
投稿 → 待初审 → 审核中(待终审) → 已批准(待发布) → 已发布 → 已归档
          ↓           ↓           ↓
      需修改      需修改       已拒绝
```

## 常见问题

### 1. 文件上传失败

- 检查uploads目录权限是否为777
- 检查PHP配置的upload_max_filesize和post_max_size
- 检查磁盘空间是否足够

### 2. 数据库连接失败

- 检查config/database.php配置是否正确
- 检查MySQL服务是否启动
- 检查数据库用户权限

### 3. 页面404错误

- 检查Nginx伪静态规则是否配置
- 检查网站根目录设置是否指向public目录
- 检查.htaccess文件（如使用Apache）

## 安全建议

1. **修改默认密码**：首次登录后立即修改管理员密码
2. **定期备份**：设置定时任务备份数据库和上传文件
3. **SSL证书**：建议启用HTTPS访问
4. **防火墙**：配置宝塔防火墙，限制访问IP（可选）
5. **更新维护**：定期更新PHP和系统补丁

## 技术支持

如遇问题，请查看：

- 系统日志：`runtime/log/`
- 错误日志：宝塔面板 → 网站 → 日志
- 操作日志：后台管理 → 操作日志

## 📚 开发文档

- **[开发指南](DEVELOPMENT.md)** - 详细的开发说明和规范
- **[辅助函数文档](HELPER_FUNCTIONS.md)** - 所有辅助函数的使用说明
- **[部署检查清单](DEPLOYMENT_CHECKLIST.md)** - 完整的部署步骤和检查项
- **[审核模块说明](REVIEW_MODULE_COMPLETED.md)** - 审核功能详细文档

## 🧪 快速测试

系统提供了测试数据，可以快速体验所有功能：

```bash
# 导入测试数据
mysql -u root -p campus_news < database/test_data.sql
```

**测试账号：**
- 管理员：`admin@example.com` / `admin123456`
- 审核员：`editor1@test.com` / `123456`
- 学生用户：`student1@test.com` / `123456`

## 🎯 核心功能模块

### 1. 用户认证模块
- 用户注册/登录
- 身份认证（学生证/校园卡）
- 权限管理

### 2. 投稿模块
- 文字稿件投稿（富文本编辑）
- Word文档上传
- 多媒体稿件（网盘链接）
- 草稿保存
- 投稿限制检查

### 3. 审核模块 ⭐
- **两级审核流程**：初审→终审
- **审核工作台**：统计数据、待审列表
- **审核操作**：通过/拒绝/要求修改
- **审核历史**：完整的审核记录
- **审核统计**：多维度数据分析

### 4. 通知系统
- 审核结果通知
- 系统消息通知
- 未读消息提醒

## 📊 项目结构

```
campus-news/
├── app/                      # 应用核心
│   ├── controller/           # 控制器
│   │   ├── AuthController.php         # 认证
│   │   ├── ArticleController.php      # 投稿
│   │   ├── ReviewController.php       # 审核 ⭐
│   │   └── ...
│   └── common/
│       └── functions.php     # 辅助函数（17个）
├── view/                     # 视图模板
│   ├── auth/                 # 认证页面
│   ├── article/              # 投稿页面
│   ├── editor/               # 审核页面 ⭐
│   └── error/                # 错误页面
├── public/                   # 公共目录
│   ├── index.php             # 入口文件
│   └── static/               # 静态资源
├── database/                 # 数据库
│   ├── install.sql           # 安装脚本
│   └── test_data.sql         # 测试数据
├── runtime/                  # 运行时目录
└── uploads/                  # 上传目录
```

## 🔧 开发相关

### 添加新的控制器
```php
<?php
namespace App\Controller;

require_once __DIR__ . '/BaseController.php';

class ExampleController extends BaseController {
    public function index() {
        $this->checkLogin();
        $this->checkPermission('example_permission');
        
        // 业务逻辑
        view('example/index', ['data' => $data]);
    }
}
```

### 使用辅助函数
```php
// 获取审核状态文本
$status_text = get_review_status_text('pending_first'); // "待初审"

// 检查投稿限制
$check = can_submit_article();
if (!$check['can']) {
    $this->error($check['message']);
}

// 发送通知
send_notification($user_id, 'system', '标题', '内容');

// 友好的时间显示
echo time_ago('2024-03-20 10:30:00'); // "5分钟前"
```

## 📝 更新日志

### v1.2.0 (2024-12-03) - 最新版本
- ✅ 完整的审核模块实现
- ✅ AuthController - 认证功能
- ✅ ArticleController - 投稿功能
- ✅ 9个新增辅助函数
- ✅ 友好的错误页面
- ✅ 完整的测试数据
- ✅ 详细的文档体系

### v1.1.0 (2024-12-03)
- ✅ 审核控制器完整实现
- ✅ 4个审核视图页面
- ✅ 数据库结构完善
- ✅ 静态资源优化

### v1.0.0 (2024-03-20)
- 初始版本发布
- 基础框架搭建

## 🤝 贡献

欢迎提交 Issue 和 Pull Request！

## 📄 License

MIT License

Copyright (c) 2024 Campus News System

## 💬 技术支持

如遇问题，请查看：
1. [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md) - 部署常见问题
2. [HELPER_FUNCTIONS.md](HELPER_FUNCTIONS.md) - 函数使用说明
3. 系统日志：`runtime/log/error.log`
4. 操作日志：后台管理 → 操作日志

## 🌟 Star History

如果这个项目对你有帮助，请给个 Star ⭐
