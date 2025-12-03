<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>我的稿件</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/static/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- 导航栏 -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">
                <?php echo e(setting('site_name', '校园新闻投稿系统')); ?>
            </a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/article/submit">
                        <i class="bi bi-plus-circle"></i> 投稿
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/auth/logout">
                        <i class="bi bi-box-arrow-right"></i> 退出
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <h3 class="mb-4">我的稿件</h3>
        
        <!-- 状态筛选 -->
        <div class="mb-3">
            <div class="btn-group">
                <a href="?status=all" class="btn btn-outline-primary <?php echo $status === 'all' ? 'active' : ''; ?>">全部</a>
                <a href="?status=draft" class="btn btn-outline-secondary <?php echo $status === 'draft' ? 'active' : ''; ?>">草稿</a>
                <a href="?status=pending_first" class="btn btn-outline-info <?php echo $status === 'pending_first' ? 'active' : ''; ?>">待初审</a>
                <a href="?status=pending_final" class="btn btn-outline-warning <?php echo $status === 'pending_final' ? 'active' : ''; ?>">待终审</a>
                <a href="?status=approved" class="btn btn-outline-success <?php echo $status === 'approved' ? 'active' : ''; ?>">已批准</a>
                <a href="?status=rejected" class="btn btn-outline-danger <?php echo $status === 'rejected' ? 'active' : ''; ?>">已拒绝</a>
            </div>
        </div>
        
        <!-- 稿件列表 -->
        <?php if (empty($articles)): ?>
            <div class="alert alert-info">
                暂无稿件，<a href="/article/submit">去投稿</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>标题</th>
                            <th>栏目</th>
                            <th>状态</th>
                            <th>提交时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles as $article): ?>
                            <tr>
                                <td><?php echo e($article['title']); ?></td>
                                <td><?php echo e($article['category_name']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo get_review_status_class($article['review_status']); ?>">
                                        <?php echo get_review_status_text($article['review_status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $article['submit_time'] ? date('Y-m-d H:i', strtotime($article['submit_time'])) : '-'; ?></td>
                                <td>
                                    <a href="/article/detail?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-primary">查看</a>
                                    <?php if (in_array($article['review_status'], ['draft', 'revision_required'])): ?>
                                        <a href="/article/edit?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-warning">编辑</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- 分页 -->
            <?php if ($total_pages > 1): ?>
                <nav>
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?status=<?php echo $status; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
