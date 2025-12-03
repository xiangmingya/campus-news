# 部署检查清单

在部署校园新闻投稿系统之前，请按照此清单逐项检查。

## 一、环境准备

### 1.1 服务器环境
- [ ] PHP 版本 >= 7.4
- [ ] MySQL 版本 >= 5.7
- [ ] Nginx 或 Apache
- [ ] 已安装宝塔面板（可选，但推荐）

### 1.2 PHP扩展检查
```bash
php -m | grep -E "fileinfo|exif|gd|mysqli|pdo_mysql"
```
- [ ] fileinfo 扩展
- [ ] exif 扩展
- [ ] gd 扩展
- [ ] mysqli 扩展
- [ ] pdo_mysql 扩展

### 1.3 PHP配置
编辑 `php.ini` 确保：
- [ ] `upload_max_filesize = 10M`
- [ ] `post_max_size = 10M`
- [ ] `max_execution_time = 300`
- [ ] `memory_limit = 128M`

---

## 二、文件部署

### 2.1 上传项目文件
- [ ] 将项目文件上传到服务器（如：`/www/wwwroot/campus-news`）
- [ ] 确保文件完整，没有缺失

### 2.2 目录权限设置
```bash
cd /www/wwwroot/campus-news
chmod -R 755 .
chmod -R 777 runtime
chmod -R 777 uploads
```
- [ ] `runtime/` 目录及子目录权限为 777
- [ ] `uploads/` 目录及子目录权限为 777
- [ ] 其他目录权限为 755

### 2.3 检查必要目录
确保以下目录存在：
- [ ] `runtime/cache/`
- [ ] `runtime/log/`
- [ ] `runtime/temp/`
- [ ] `uploads/cert/`
- [ ] `uploads/cover/`
- [ ] `uploads/word/`
- [ ] `uploads/avatar/`

---

## 三、数据库配置

### 3.1 创建数据库
```bash
mysql -u root -p
```
```sql
CREATE DATABASE campus_news DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
- [ ] 数据库已创建
- [ ] 字符集为 utf8mb4

### 3.2 导入数据库结构
```bash
mysql -u root -p campus_news < database/install.sql
```
- [ ] 数据库表结构已导入
- [ ] 初始数据已导入
- [ ] 默认管理员账号已创建

### 3.3 配置数据库连接
复制配置文件模板：
```bash
cp config/database.php.example config/database.php
```
编辑 `config/database.php`：
```php
'mysql' => [
    'hostname' => '127.0.0.1',
    'database' => 'campus_news',
    'username' => 'root',
    'password' => 'your_password',  // 修改为实际密码
    'hostport' => '3306',
    'charset' => 'utf8mb4',
],
```
- [ ] 数据库连接信息已配置
- [ ] 数据库连接测试成功

---

## 四、Web服务器配置

### 4.1 Nginx配置（推荐）

#### 使用宝塔面板
1. 登录宝塔面板
2. 网站 → 添加站点
3. 设置：
   - [ ] 网站根目录指向 `/www/wwwroot/campus-news/public`
   - [ ] PHP版本选择 7.4 或以上

4. 伪静态设置：
   点击网站 → 设置 → 伪静态，选择 ThinkPHP 或添加以下规则：
   ```nginx
   location / {
       if (!-e $request_filename){
           rewrite ^(.*)$ /index.php?s=$1 last;
           break;
       }
   }
   ```
   - [ ] 伪静态规则已配置

#### 手动配置Nginx
使用项目提供的配置文件：
```bash
cp nginx.conf.example /etc/nginx/sites-available/campus-news.conf
# 编辑配置文件，修改域名和路径
ln -s /etc/nginx/sites-available/campus-news.conf /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```
- [ ] Nginx配置文件已创建
- [ ] 配置测试通过
- [ ] Nginx已重载

### 4.2 Apache配置（备选）
确保 `.htaccess` 文件存在于 `public/` 目录：
```apache
<IfModule mod_rewrite.c>
  Options +FollowSymlinks -Multiviews
  RewriteEngine On

  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^(.*)$ index.php?/$1 [QSA,PT,L]
</IfModule>
```
- [ ] `.htaccess` 文件已配置
- [ ] mod_rewrite 模块已启用

---

## 五、应用配置

### 5.1 修改应用配置
编辑 `config/app.php`：
```php
// 生产环境关闭调试模式
'app_debug' => false,

// 根据需要调整投稿限制
'article' => [
    'daily_limit' => 5,
    'weekly_video_limit' => 2,
],
```
- [ ] 调试模式已关闭（生产环境）
- [ ] 投稿限制已设置

### 5.2 修改系统设置
登录后台管理，进入"系统设置"：
- [ ] 网站名称
- [ ] 网站Logo（可选）
- [ ] 联系方式
- [ ] 投稿规则

---

## 六、安全检查

### 6.1 默认密码修改
- [ ] 已使用默认管理员账号登录
  - 用户名：`admin@example.com`
  - 密码：`admin123456`
- [ ] 已修改管理员密码
- [ ] 新密码强度足够（建议12位以上，包含大小写字母、数字、特殊字符）

### 6.2 文件访问限制
- [ ] 确保 `config/database.php` 不能直接访问
- [ ] 确保 `.env` 文件（如有）不能直接访问
- [ ] 确保 `database/install.sql` 不能直接访问

### 6.3 目录浏览禁用
Nginx配置：
```nginx
autoindex off;
```
- [ ] 目录浏览已禁用

### 6.4 PHP信息隐藏
php.ini 配置：
```ini
expose_php = Off
```
- [ ] PHP版本信息已隐藏

---

## 七、SSL证书配置（推荐）

### 7.1 申请SSL证书
- [ ] 已申请SSL证书（Let's Encrypt / 阿里云 / 腾讯云等）

### 7.2 配置HTTPS
使用宝塔面板：
1. 网站 → 设置 → SSL
2. 选择证书类型并部署
- [ ] SSL证书已部署
- [ ] HTTPS访问正常
- [ ] HTTP自动跳转HTTPS

---

## 八、功能测试

### 8.1 基础功能测试
- [ ] 首页可以正常访问
- [ ] 登录功能正常
- [ ] 注册功能正常

### 8.2 用户功能测试
创建测试账号进行测试：
- [ ] 用户注册
- [ ] 身份认证申请
- [ ] 投稿功能（文字/Word/多媒体）
- [ ] 我的稿件查看
- [ ] 通知接收

### 8.3 审核功能测试
使用审核员账号测试：
- [ ] 审核工作台访问
- [ ] 待审列表显示
- [ ] 稿件审核（通过/拒绝/要求修改）
- [ ] 审核统计查看

### 8.4 管理功能测试
使用管理员账号测试：
- [ ] 用户管理
- [ ] 认证审核
- [ ] 稿件管理
- [ ] 栏目管理
- [ ] 系统设置
- [ ] 操作日志查看

---

## 九、性能优化

### 9.1 PHP性能优化
- [ ] 启用OPcache
- [ ] 调整PHP-FPM进程数

### 9.2 数据库优化
- [ ] 为常用查询字段添加索引
- [ ] 配置数据库连接池
- [ ] 定期清理过期数据

### 9.3 静态资源优化
- [ ] 启用Gzip压缩
- [ ] 配置浏览器缓存
- [ ] 使用CDN加速（可选）

---

## 十、备份方案

### 10.1 数据库备份
设置定时任务（宝塔面板或crontab）：
```bash
# 每天凌晨3点备份数据库
0 3 * * * mysqldump -u root -p'password' campus_news > /backup/campus_news_$(date +\%Y\%m\%d).sql
```
- [ ] 数据库自动备份已配置
- [ ] 备份文件自动清理（保留30天）

### 10.2 文件备份
```bash
# 每周备份上传文件
0 2 * * 0 tar -czf /backup/uploads_$(date +\%Y\%m\%d).tar.gz /www/wwwroot/campus-news/uploads
```
- [ ] 上传文件自动备份已配置

---

## 十一、监控和日志

### 11.1 错误日志
- [ ] PHP错误日志路径已配置
- [ ] Nginx错误日志已启用
- [ ] 系统操作日志正常记录

### 11.2 监控告警
- [ ] 服务器监控已配置（CPU/内存/磁盘）
- [ ] 网站可用性监控
- [ ] 异常告警已配置（可选）

---

## 十二、文档准备

### 12.1 用户文档
- [ ] 用户使用手册
- [ ] 投稿指南
- [ ] 常见问题FAQ

### 12.2 管理员文档
- [ ] 管理员操作手册
- [ ] 系统维护指南
- [ ] 故障排查指南

---

## 十三、上线前最后检查

- [ ] 所有功能测试通过
- [ ] 默认密码已修改
- [ ] 调试模式已关闭
- [ ] 备份方案已执行
- [ ] 团队培训已完成
- [ ] 用户文档已准备
- [ ] 应急预案已制定

---

## 十四、上线后任务

### 第一天
- [ ] 监控系统运行状态
- [ ] 检查错误日志
- [ ] 收集用户反馈

### 第一周
- [ ] 性能数据分析
- [ ] 用户使用情况统计
- [ ] 已知问题修复

### 第一个月
- [ ] 功能优化调整
- [ ] 用户体验改进
- [ ] 安全漏洞扫描

---

## 常用命令参考

```bash
# 查看PHP版本
php -v

# 查看PHP扩展
php -m

# 查看Nginx状态
systemctl status nginx

# 重启Nginx
systemctl restart nginx

# 查看MySQL状态
systemctl status mysql

# 查看错误日志
tail -f /www/wwwroot/campus-news/runtime/log/error.log

# 清理缓存
rm -rf /www/wwwroot/campus-news/runtime/cache/*

# 数据库备份
mysqldump -u root -p campus_news > backup.sql

# 数据库恢复
mysql -u root -p campus_news < backup.sql
```

---

## 紧急联系方式

- 技术负责人：______
- 运维负责人：______
- 服务器供应商：______
- 域名供应商：______

---

## 附录：故障排查

### 问题1：页面显示404
**可能原因：**
- Nginx伪静态未配置
- 网站根目录设置错误

**解决方案：**
1. 检查Nginx配置
2. 检查网站根目录是否指向 `public/` 目录

### 问题2：数据库连接失败
**可能原因：**
- 数据库配置错误
- MySQL服务未启动
- 数据库用户权限不足

**解决方案：**
1. 检查 `config/database.php` 配置
2. 检查MySQL服务状态
3. 验证数据库用户权限

### 问题3：文件上传失败
**可能原因：**
- uploads目录权限不足
- PHP上传限制太小
- 磁盘空间不足

**解决方案：**
1. 设置uploads目录权限为777
2. 调整php.ini的upload_max_filesize
3. 检查磁盘空间

### 问题4：审核工作台空白
**可能原因：**
- 权限不足
- 数据库表缺失
- PHP错误

**解决方案：**
1. 检查用户角色和权限
2. 验证数据库表是否完整
3. 查看PHP错误日志

---

**部署完成后，请将此检查清单归档保存，以便后续维护参考。**
