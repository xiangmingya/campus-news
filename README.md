# 校园新闻投稿系统

基于PHP的校园媒体投稿管理系统，支持文字、摄影、视频等多种形式投稿。

## 功能特点

- 🔐 多角色权限管理（超管/管理员/审核员/投稿用户）
- ✅ 人工身份认证（学生证/校园卡审核）
- 📝 两级审核流程（初审→终审→发布）
- 🎨 多媒体内容支持（文字/摄影/视频）
- ☁️ 百度网盘链接管理
- 📊 完整的操作日志记录
- 🛡️ 投稿限制与防滥用机制

## 技术栈

- **后端框架**: ThinkPHP 6.0
- **前端技术**: HTML5 + CSS3 + JavaScript + jQuery + Bootstrap 5
- **数据库**: MySQL 5.7+
- **编辑器**: wangEditor
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

## 开发文档

详细的开发文档请参考 `docs/开发文档.md`

## 更新日志

### v1.0.0 (2024-03-20)
- 初始版本发布
- 实现核心功能模块
- 完成基础界面开发

## License

MIT License

Copyright (c) 2024 Campus News System

## 联系方式

项目负责人：______  
技术支持：______
