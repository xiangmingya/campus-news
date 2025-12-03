# 辅助函数使用说明

本文档说明系统中新增的辅助函数及其使用方法。

## 审核相关函数

### 1. get_review_status_text($status)

获取审核状态的中文文本。

**参数：**
- `$status` (string) - 状态代码

**返回值：** string - 中文状态文本

**示例：**
```php
$status_text = get_review_status_text('pending_first'); // 返回 "待初审"
```

**支持的状态：**
- `draft` → 草稿
- `pending_first` → 待初审
- `pending_final` → 待终审
- `approved` → 已批准
- `rejected` → 已拒绝
- `revision_required` → 需修改

---

### 2. get_review_status_class($status)

获取审核状态对应的Bootstrap标签类。

**参数：**
- `$status` (string) - 状态代码

**返回值：** string - Bootstrap类名

**示例：**
```php
$class = get_review_status_class('pending_first'); // 返回 "primary"

// 在视图中使用
<span class="badge bg-<?php echo get_review_status_class($article['review_status']); ?>">
    <?php echo get_review_status_text($article['review_status']); ?>
</span>
```

**状态对应的颜色：**
- `draft` → secondary (灰色)
- `pending_first` → primary (蓝色)
- `pending_final` → warning (黄色)
- `approved` → success (绿色)
- `rejected` → danger (红色)
- `revision_required` → warning (黄色)

---

## 通知相关函数

### 3. send_notification($user_id, $type, $title, $content)

发送通知给指定用户。

**参数：**
- `$user_id` (int) - 用户ID
- `$type` (string) - 通知类型
- `$title` (string) - 通知标题
- `$content` (string) - 通知内容

**返回值：** bool - 成功返回true，失败返回false

**示例：**
```php
send_notification(
    $article['user_id'], 
    'review_result',
    '审核通知',
    '您的稿件《' . $article['title'] . '》初审通过'
);
```

**常用通知类型：**
- `review_result` - 审核结果
- `system` - 系统通知
- `reminder` - 提醒通知

---

### 4. get_unread_notifications_count($user_id = null)

获取用户的未读通知数量。

**参数：**
- `$user_id` (int, 可选) - 用户ID，默认为当前登录用户

**返回值：** int - 未读通知数量

**示例：**
```php
// 获取当前用户的未读通知数
$unread_count = get_unread_notifications_count();

// 在导航栏显示
<?php if ($unread_count > 0): ?>
    <span class="badge bg-danger"><?php echo $unread_count; ?></span>
<?php endif; ?>
```

---

## 投稿限制相关函数

### 5. get_today_submit_count($user_id = null)

获取用户今日投稿数量。

**参数：**
- `$user_id` (int, 可选) - 用户ID，默认为当前登录用户

**返回值：** int - 今日投稿数量

**示例：**
```php
$today_count = get_today_submit_count();
echo "今日已投稿：{$today_count} 篇";
```

---

### 6. get_week_video_count($user_id = null)

获取用户本周视频投稿数量。

**参数：**
- `$user_id` (int, 可选) - 用户ID，默认为当前登录用户

**返回值：** int - 本周视频投稿数量

**示例：**
```php
$week_video_count = get_week_video_count();
echo "本周已投稿视频：{$week_video_count} 个";
```

---

### 7. can_submit_article($user_id = null, $type = 'text')

检查用户是否可以投稿。

**参数：**
- `$user_id` (int, 可选) - 用户ID，默认为当前登录用户
- `$type` (string, 可选) - 类型，'text'或'video'，默认为'text'

**返回值：** array - `['can' => bool, 'message' => string]`

**示例：**
```php
// 检查是否可以投稿文字稿件
$check = can_submit_article(null, 'text');
if (!$check['can']) {
    echo $check['message']; // 输出限制原因
    exit;
}

// 检查是否可以投稿视频
$check = can_submit_article(null, 'video');
if (!$check['can']) {
    $this->error($check['message']);
}
```

**在控制器中使用：**
```php
public function submit() {
    $this->checkLogin();
    
    $type = post('content_type') === 'multimedia' ? 'video' : 'text';
    $check = can_submit_article(null, $type);
    
    if (!$check['can']) {
        $this->error($check['message']);
    }
    
    // 继续投稿处理...
}
```

---

## 时间相关函数

### 8. time_ago($time)

将时间格式化为友好的相对时间格式。

**参数：**
- `$time` (string) - 时间字符串（如：2024-03-20 10:30:00）

**返回值：** string - 友好的时间格式

**示例：**
```php
echo time_ago('2024-03-20 10:30:00');
// 输出：刚刚 / 5分钟前 / 2小时前 / 3天前 / 2024-03-20
```

**在视图中使用：**
```php
<small class="text-muted">
    发布于 <?php echo time_ago($article['publish_time']); ?>
</small>
```

**时间显示规则：**
- 小于1分钟：刚刚
- 小于1小时：X分钟前
- 小于1天：X小时前
- 小于1周：X天前
- 1周以上：显示日期（Y-m-d）

---

## 使用场景示例

### 场景1：在稿件列表中显示状态

```php
<?php foreach ($articles as $article): ?>
    <tr>
        <td><?php echo e($article['title']); ?></td>
        <td>
            <span class="badge bg-<?php echo get_review_status_class($article['review_status']); ?>">
                <?php echo get_review_status_text($article['review_status']); ?>
            </span>
        </td>
        <td><?php echo time_ago($article['created_at']); ?></td>
    </tr>
<?php endforeach; ?>
```

### 场景2：投稿前检查限制

```php
public function create() {
    $this->checkLogin();
    
    // 检查投稿限制
    $check = can_submit_article();
    if (!$check['can']) {
        return view('article/limit', ['message' => $check['message']]);
    }
    
    // 显示投稿表单
    view('article/create');
}
```

### 场景3：审核后发送通知

```php
public function approve() {
    // ... 审核逻辑 ...
    
    // 发送通知
    send_notification(
        $article['user_id'],
        'review_result',
        '审核通知',
        '恭喜！您的稿件《' . $article['title'] . '》已通过审核。'
    );
    
    $this->success('审核通过');
}
```

### 场景4：在导航栏显示未读通知

```php
<!-- 在导航栏 -->
<li class="nav-item">
    <a class="nav-link" href="/user/notifications">
        <i class="bi bi-bell"></i> 通知
        <?php 
        $unread = get_unread_notifications_count();
        if ($unread > 0): 
        ?>
            <span class="badge bg-danger"><?php echo $unread; ?></span>
        <?php endif; ?>
    </a>
</li>
```

### 场景5：投稿页面显示剩余额度

```php
<div class="alert alert-info">
    <p>今日已投稿：<?php echo get_today_submit_count(); ?> / <?php echo config('article.daily_limit', 5); ?> 篇</p>
    <?php if ($category_slug === 'video'): ?>
        <p>本周视频已投稿：<?php echo get_week_video_count(); ?> / <?php echo config('article.weekly_video_limit', 2); ?> 个</p>
    <?php endif; ?>
</div>
```

---

## 配置项

这些函数依赖的配置项在 `config/app.php` 中：

```php
// 稿件配置
'article' => [
    'daily_limit' => 5,           // 每日投稿上限
    'weekly_video_limit' => 2,    // 每周视频投稿上限
],
```

可以根据需要修改这些限制值。

---

## 注意事项

1. **权限检查**：使用这些函数前，确保已经进行了必要的登录和权限检查。

2. **错误处理**：`send_notification()` 函数在失败时会记录错误日志，但不会抛出异常。

3. **数据库连接**：这些函数都依赖 `db()` 函数，确保数据库连接正常。

4. **缓存考虑**：如果系统访问量大，可以考虑对通知数量等进行缓存优化。

5. **时区设置**：`time_ago()` 函数依赖系统时区设置，确保在 `public/index.php` 中正确设置了时区。

---

## 扩展建议

如果需要更多功能，可以继续添加辅助函数：

1. **get_user_articles_stats($user_id)** - 获取用户稿件统计
2. **get_popular_articles($limit)** - 获取热门稿件
3. **get_recent_notifications($user_id, $limit)** - 获取最近通知
4. **mark_notification_read($notification_id)** - 标记通知已读
5. **get_category_by_slug($slug)** - 根据标识获取栏目
6. **check_article_ownership($article_id, $user_id)** - 检查稿件所有权

这些函数可以根据实际需求逐步添加到 `app/common/functions.php` 文件中。
