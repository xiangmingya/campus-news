# 安装指南

本文档详细说明如何在宝塔面板上部署校园新闻投稿系统。

## 系统要求

- **服务器**: Linux (CentOS/Ubuntu推荐)
- **宝塔面板**: 7.9.0 或更高版本
- **PHP**: 7.4 或更高版本
- **MySQL**: 5.7 或更高版本
- **Nginx**: 1.20 或更高版本
- **磁盘空间**: 至少500MB可用空间

## 安装步骤

### 第一步：安装宝塔面板

如果还未安装宝塔面板，请执行以下命令：

```bash
# CentOS系统
yum install -y wget && wget -O install.sh http://download.bt.cn/install/install_6.0.sh && sh install.sh

# Ubuntu系统
wget -O install.sh http://download.bt.cn/install/install-ubuntu_6.0.sh && sudo bash install.sh
```

安装完成后，记录登录地址、用户名和密码。

### 第二步：安装软件环境

登录宝塔面板后台，在"软件商店"中安装以下软件：

1. **PHP 7.4**
   - 点击"设置" → "安装扩展"
   - 安装以下扩展：
     - fileinfo
     - exif
     - gd
     - mysqli
     - pdo_mysql

2. **MySQL 5.7**
   - 安装后设置root密码
   - 记录数据库root密码

3. **Nginx 1.20**
   - 使用默认配置即可

4. **phpMyAdmin**（可选）
   - 用于可视化管理数据库

### 第三步：创建网站

1. 在宝塔面板点击"网站" → "添加站点"

2. 填写站点信息：
   - 域名：填写你的域名或服务器IP
   - 根目录：/www/wwwroot/campus-news
   - PHP版本：选择 PHP-74
   - 数据库：MySQL
   - 数据库名：campus_news
   - 记录数据库用户名和密码

3. 点击"提交"创建站点

### 第四步：上传项目文件

#### 方法一：使用宝塔文件管理

1. 在宝塔面板点击"文件"
2. 进入 `/www/wwwroot/campus-news` 目录
3. 上传项目压缩包并解压
4. 确保目录结构正确

#### 方法二：使用FTP

1. 在宝塔面板点击"FTP" → "添加FTP"
2. 创建FTP账号
3. 使用FTP客户端（如FileZilla）上传文件

#### 方法三：使用Git

```bash
cd /www/wwwroot
git clone <你的仓库地址> campus-news
```

### 第五步：配置数据库

1. **导入数据库结构**

在宝塔面板中：
- 点击"数据库" → 找到 campus_news
- 点击"导入" → 选择 `database/install.sql` 文件
- 点击"导入"

或使用命令行：
```bash
mysql -u root -p campus_news < /www/wwwroot/campus-news/database/install.sql
```

2. **修改数据库配置**

编辑 `config/database.php` 文件：

```php
'connections' => [
    'mysql' => [
        'hostname' => '127.0.0.1',
        'database' => 'campus_news',
        'username' => '你的数据库用户名',
        'password' => '你的数据库密码',
        'hostport' => '3306',
    ],
],
```

### 第六步：设置目录权限

在宝塔面板文件管理中，或使用SSH命令：

```bash
cd /www/wwwroot/campus-news
chmod -R 755 .
chmod -R 777 runtime
chmod -R 777 uploads
chown -R www:www .
```

### 第七步：配置伪静态

1. 在宝塔面板点击"网站" → 找到你的站点
2. 点击"设置" → "伪静态"
3. 选择"ThinkPHP"或手动输入以下规则：

```nginx
location / {
    if (!-e $request_filename){
        rewrite ^(.*)$ /index.php?s=$1 last;
        break;
    }
}
```

4. 点击"保存"

### 第八步：配置网站根目录

**重要：网站根目录必须指向 public 目录**

1. 在宝塔面板点击"网站" → 找到你的站点
2. 点击"设置" → "网站目录"
3. 将"网站目录"修改为：`/www/wwwroot/campus-news/public`
4. 勾选"防跨站攻击"
5. 点击"保存"

### 第九步：初始化管理员账号

数据库已包含默认管理员账号，但密码是哈希值，需要修改：

1. 访问 phpMyAdmin
2. 打开 campus_news 数据库
3. 找到 users 表
4. 修改 id=1 的记录：
   ```sql
   UPDATE users SET 
   password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
   email = 'admin@example.com',
   username = 'admin'
   WHERE id = 1;
   ```
   
   默认密码：`password`（登录后务必修改！）

### 第十步：访问网站

1. 在浏览器中访问你的域名或IP地址
2. 如果显示首页，说明安装成功

3. 访问后台管理：
   - URL: `http://your-domain.com/admin/login`
   - 用户名: `admin@example.com`
   - 密码: `password`

4. **首次登录后务必修改密码！**

## 安全配置

### 1. SSL证书配置

强烈建议启用HTTPS：

1. 在宝塔面板点击"网站" → "设置" → "SSL"
2. 选择"Let's Encrypt"申请免费证书
3. 填写邮箱并同意协议
4. 点击"申请"
5. 申请成功后，开启"强制HTTPS"

### 2. 防火墙配置

1. 在宝塔面板点击"安全"
2. 开放必要端口：
   - 80 (HTTP)
   - 443 (HTTPS)
   - 22 (SSH，建议修改默认端口)
3. 可选：设置IP访问限制

### 3. 定期备份

1. 在宝塔面板点击"计划任务"
2. 添加数据库备份任务：
   - 任务类型：备份数据库
   - 执行周期：每天
   - 备份数据库：campus_news
   - 保留份数：7

3. 添加网站备份任务：
   - 任务类型：备份网站
   - 执行周期：每周
   - 备份网站：你的站点
   - 保留份数：4

## 常见问题

### 1. 页面显示404

**原因**：伪静态未配置或网站根目录设置错误

**解决**：
- 检查伪静态规则是否正确
- 确认网站根目录指向 `public` 目录

### 2. 数据库连接失败

**原因**：数据库配置错误

**解决**：
- 检查 `config/database.php` 中的配置
- 确认数据库用户名和密码正确
- 检查MySQL服务是否启动

### 3. 文件上传失败

**原因**：目录权限不足或PHP配置限制

**解决**：
- 检查 `uploads` 目录权限是否为777
- 在宝塔面板修改PHP配置：
  - upload_max_filesize = 10M
  - post_max_size = 10M
  - max_execution_time = 300

### 4. 页面显示乱码

**原因**：字符编码设置错误

**解决**：
- 确保数据库字符集为 utf8mb4
- 检查PHP文件编码为 UTF-8

### 5. Session无法保存

**原因**：Session目录权限问题

**解决**：
```bash
chmod -R 777 /www/wwwroot/campus-news/runtime
```

## 升级说明

升级系统时请遵循以下步骤：

1. **备份数据**
   ```bash
   # 备份数据库
   mysqldump -u用户名 -p密码 campus_news > backup_$(date +%Y%m%d).sql
   
   # 备份文件
   cp -r /www/wwwroot/campus-news /backup/campus-news_$(date +%Y%m%d)
   ```

2. **上传新文件**
   - 下载新版本
   - 覆盖旧文件（保留config和uploads目录）

3. **执行升级脚本**（如有）
   - 查看升级说明
   - 执行SQL升级脚本

4. **测试功能**
   - 检查主要功能是否正常
   - 如有问题，恢复备份

## 卸载说明

如需卸载系统：

1. **备份数据**（如需保留）

2. **删除网站**
   - 在宝塔面板删除站点
   - 选择是否删除数据库和文件

3. **删除文件**
   ```bash
   rm -rf /www/wwwroot/campus-news
   ```

4. **删除数据库**
   ```bash
   mysql -u root -p -e "DROP DATABASE campus_news;"
   ```

## 技术支持

如遇到其他问题，请：

1. 查看系统日志：`runtime/log/error.log`
2. 查看Nginx日志：宝塔面板 → 网站 → 日志
3. 查看PHP错误日志：宝塔面板 → PHP → 日志
4. 联系技术支持（如有）

## 相关链接

- [宝塔面板官网](https://www.bt.cn/)
- [PHP官方文档](https://www.php.net/manual/zh/)
- [MySQL官方文档](https://dev.mysql.com/doc/)
- [Nginx官方文档](https://nginx.org/en/docs/)
