# 开发指南

## 项目结构说明

本项目是一个基于原生PHP开发的校园新闻投稿系统，采用MVC架构模式。

### 目录结构

```
campus-news/
├── app/                          # 应用核心目录
│   ├── controller/               # 控制器层
│   │   ├── BaseController.php     # 基础控制器
│   │   ├── IndexController.php    # 首页控制器  
│   │   ├── AuthController.php     # 认证控制器
│   │   ├── ArticleController.php  # 稿件控制器
│   │   ├── ReviewController.php   # 审核控制器
│   │   └── AdminController.php    # 管理控制器
│   ├── model/                    # 数据模型层（可选）
│   ├── service/                  # 业务服务层（可选）
│   ├── middleware/               # 中间件（可选）
│   └── common/                   # 公共函数
│       └── functions.php         # 核心函数库
├── config/                       # 配置文件
│   ├── app.php                   # 应用配置
│   └── database.php              # 数据库配置
├── public/                       # 公共目录（网站根目录指向此）
│   ├── index.php                 # 入口文件
│   └── static/                   # 静态资源
│       ├── css/                  # 样式文件
│       ├── js/                   # JavaScript文件
│       └── images/               # 图片资源
├── runtime/                      # 运行时目录
│   ├── cache/                    # 缓存文件
│   ├── log/                      # 日志文件
│   └── temp/                     # 临时文件
├── uploads/                      # 上传文件目录
│   ├── cert/                     # 认证文件
│   ├── cover/                    # 封面图
│   ├── word/                     # Word文档
│   └── avatar/                   # 用户头像
├── view/                         # 视图模板
│   ├── index/                    # 前台页面
│   ├── admin/                    # 后台页面
│   └── editor/                   # 审核端页面
└── database/                     # 数据库文件
    └── install.sql               # 安装脚本
```

## 核心文件说明

### 1. 入口文件 (public/index.php)

系统的唯一入口，负责：
- 定义常量
- 加载配置
- 路由解析
- 控制器调度

### 2. 公共函数库 (app/common/functions.php)

包含系统所有公共函数：
- `config()` - 获取配置
- `db()` - 数据库连接
- `json_response()` - JSON响应
- `view()` - 渲染视图
- `user()` - 获取当前用户
- `has_permission()` - 权限检查
- `log_operation()` - 记录日志
- `upload_file()` - 文件上传
- 更多...

### 3. 配置文件

#### config/app.php
应用核心配置，包括：
- 调试模式
- 时区设置
- Session配置
- 上传配置
- 投稿限制

#### config/database.php
数据库连接配置

## 开发规范

### 控制器开发

所有控制器应继承`BaseController`：

```php
<?php
namespace App\Controller;

require_once __DIR__ . '/BaseController.php';

class ExampleController extends BaseController {
    
    public function index() {
        // 检查登录
        $this->checkLogin();
        
        // 检查权限
        $this->checkPermission('article_create');
        
        // 业务逻辑
        // ...
        
        // 返回视图
        view('example/index', ['data' => $data]);
    }
    
    public function apiExample() {
        // API方法示例
        $this->checkLogin();
        
        $data = post();
        
        // 处理逻辑
        // ...
        
        // 返回JSON
        $this->success('操作成功', ['id' => $id]);
    }
}
```

### 数据库操作

使用PDO进行数据库操作：

```php
// 查询单条
$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();

// 查询多条
$stmt = db()->prepare('SELECT * FROM articles WHERE user_id = ?');
$stmt->execute([$user_id]);
$articles = $stmt->fetchAll();

// 插入
$stmt = db()->prepare('
    INSERT INTO articles (user_id, title, content, created_at) 
    VALUES (?, ?, ?, NOW())
');
$stmt->execute([$user_id, $title, $content]);
$id = db()->lastInsertId();

// 更新
$stmt = db()->prepare('UPDATE articles SET status = ? WHERE id = ?');
$stmt->execute(['published', $id]);

// 删除
$stmt = db()->prepare('DELETE FROM articles WHERE id = ?');
$stmt->execute([$id]);
```

### 视图开发

视图文件放在`view/`目录下，使用PHP原生模板：

```php
<!-- view/example/index.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>页面标题</title>
    <link href="/static/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1><?php echo e($title); ?></h1>
        
        <?php foreach ($items as $item): ?>
            <div class="item">
                <h3><?php echo e($item['title']); ?></h3>
                <p><?php echo e($item['content']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    
    <script src="/static/js/jquery.min.js"></script>
</body>
</html>
```

### 路由规则

URL格式：`/controller/action`

示例：
- `/` 或 `/index/index` → IndexController::index()
- `/auth/login` → AuthController::login()
- `/article/create` → ArticleController::create()
- `/admin/users` → AdminController::users()

### 权限控制

在控制器方法中使用权限检查：

```php
// 检查是否登录
$this->checkLogin();

// 检查特定权限
$this->checkPermission('article_create');
$this->checkPermission('user_manage');
$this->checkPermission('article_review');
```

角色权限配置在数据库`roles`表中。

### 日志记录

重要操作需记录日志：

```php
log_operation('article_create', 'article', $article_id, '创建稿件');
log_operation('user_cert_approve', 'user', $user_id, '审核通过认证');
log_operation('article_publish', 'article', $article_id, '发布稿件');
```

## API开发规范

### 响应格式

所有API返回统一的JSON格式：

```json
{
    "code": 200,
    "msg": "success",
    "data": {},
    "timestamp": 1234567890
}
```

状态码：
- 200: 成功
- 400: 请求错误
- 401: 未登录
- 403: 权限不足
- 404: 资源不存在
- 500: 服务器错误

### API示例

```php
public function apiCreate() {
    $this->checkLogin();
    
    // 获取参数
    $title = post('title');
    $content = post('content');
    
    // 参数验证
    if (empty($title)) {
        $this->error('标题不能为空');
    }
    
    // 业务处理
    try {
        $stmt = db()->prepare('INSERT INTO articles (title, content) VALUES (?, ?)');
        $stmt->execute([$title, $content]);
        $id = db()->lastInsertId();
        
        $this->success('创建成功', ['id' => $id]);
    } catch (\Exception $e) {
        $this->error('创建失败：' . $e->getMessage());
    }
}
```

## 待开发文件列表

### 控制器 (app/controller/)
- [x] BaseController.php - 基础控制器（已创建）
- [x] IndexController.php - 首页控制器（已创建）
- [ ] AuthController.php - 认证控制器（待开发）
- [ ] ArticleController.php - 稿件控制器（待开发）
- [ ] ReviewController.php - 审核控制器（待开发）
- [ ] AdminController.php - 后台管理控制器（待开发）

### 视图模板 (view/)

#### 前台页面 (view/index/)
- [ ] index.php - 首页
- [ ] detail.php - 稿件详情页
- [ ] login.php - 登录页
- [ ] register.php - 注册页
- [ ] profile.php - 个人中心
- [ ] submit.php - 投稿页面
- [ ] my-articles.php - 我的稿件

#### 后台页面 (view/admin/)
- [ ] dashboard.php - 控制面板
- [ ] users.php - 用户管理
- [ ] cert-review.php - 认证审核
- [ ] articles.php - 稿件管理
- [ ] categories.php - 栏目管理
- [ ] settings.php - 系统设置
- [ ] logs.php - 操作日志

#### 审核页面 (view/editor/)
- [ ] dashboard.php - 审核工作台
- [ ] pending.php - 待审列表
- [ ] stats.php - 审核统计

### 静态资源 (public/static/)
- [ ] css/style.css - 自定义样式
- [ ] js/common.js - 公共JavaScript
- [ ] js/submit.js - 投稿页面脚本

## 开发步骤建议

1. **第一阶段：认证模块**
   - 创建AuthController
   - 实现登录、注册功能
   - 实现身份认证申请
   - 创建相关视图页面

2. **第二阶段：投稿模块**
   - 创建ArticleController
   - 实现投稿功能（文字/多媒体）
   - 实现草稿保存
   - 实现投稿限制检查
   - 创建投稿相关页面

3. **第三阶段：审核模块**
   - 创建ReviewController
   - 实现两级审核流程
   - 创建审核工作台
   - 实现审核通知

4. **第四阶段：后台管理**
   - 创建AdminController
   - 实现用户管理
   - 实现稿件管理
   - 实现系统配置
   - 创建后台管理页面

5. **第五阶段：优化完善**
   - 前端美化
   - 性能优化
   - 安全加固
   - 测试修复

## 注意事项

1. **安全性**
   - 所有用户输入必须过滤和验证
   - 使用PDO预处理防止SQL注入
   - 输出时使用`e()`函数防止XSS
   - 上传文件严格验证类型和大小

2. **数据库操作**
   - 始终使用预处理语句
   - 正确处理事务
   - 捕获异常并记录日志

3. **权限控制**
   - 所有需要登录的页面必须检查登录状态
   - 敏感操作必须检查权限
   - 记录所有重要操作日志

4. **错误处理**
   - 生产环境关闭错误显示
   - 错误信息记录到日志
   - 给用户友好的错误提示

5. **性能优化**
   - 合理使用缓存
   - 优化SQL查询
   - 压缩静态资源
   - 使用CDN（可选）

## 常用命令

```bash
# 修改目录权限
chmod -R 777 runtime uploads

# 查看日志
tail -f runtime/log/error.log

# 清理缓存
rm -rf runtime/cache/*

# 备份数据库
mysqldump -u用户名 -p密码 campus_news > backup.sql

# 导入数据库
mysql -u用户名 -p密码 campus_news < database/install.sql
```

## 相关资源

- [PHP官方文档](https://www.php.net/manual/zh/)
- [PDO文档](https://www.php.net/manual/zh/book.pdo.php)
- [Bootstrap文档](https://getbootstrap.com/)
- [jQuery文档](https://jquery.com/)
- [宝塔面板文档](https://www.bt.cn/bbs/)
