<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $type === 'first' ? '初审' : '终审'; ?>列表 - 审核工作台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/static/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- 导航栏 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="/review/dashboard">
                <i class="bi bi-clipboard-check"></i> 审核工作台
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/review/dashboard">
                            <i class="bi bi-house"></i> 工作台首页
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $type === 'first' ? 'active' : ''; ?>" href="/review/pending?type=first">
                            <i class="bi bi-file-earmark-text"></i> 初审列表
                        </a>
                    </li>
                    <?php if (has_permission('article_final_review')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $type === 'final' ? 'active' : ''; ?>" href="/review/pending?type=final">
                            <i class="bi bi-file-earmark-check"></i> 终审列表
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/review/stats">
                            <i class="bi bi-bar-chart"></i> 审核统计
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="/">
                            <i class="bi bi-arrow-left-circle"></i> 返回首页
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo e(user()['username']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/user/profile">个人中心</a></li>
                            <?php if (has_permission('user_manage')): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/admin/dashboard">后台管理</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/auth/logout">退出登录</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- 主要内容 -->
    <div class="container-fluid mt-4">
        <!-- 页面标题 -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>
                <i class="bi bi-<?php echo $type === 'first' ? 'file-earmark-text' : 'file-earmark-check'; ?>"></i>
                待<?php echo $type === 'first' ? '初' : '终'; ?>审稿件
                <span class="badge bg-<?php echo $type === 'first' ? 'primary' : 'warning'; ?>"><?php echo $total; ?></span>
            </h4>
        </div>

        <!-- 筛选条件 -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="get" class="row g-3">
                    <input type="hidden" name="type" value="<?php echo e($type); ?>">
                    
                    <div class="col-md-4">
                        <label class="form-label">栏目分类</label>
                        <select name="category" class="form-select">
                            <option value="">全部栏目</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo get('category') == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo e($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> 筛选
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 稿件列表 -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($articles)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> 暂无待审稿件
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;">ID</th>
                                    <th>稿件标题</th>
                                    <th style="width: 100px;">栏目</th>
                                    <th style="width: 120px;">作者</th>
                                    <th style="width: 100px;">学号</th>
                                    <th style="width: 150px;">提交时间</th>
                                    <th style="width: 180px;">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($articles as $article): ?>
                                    <tr>
                                        <td class="text-muted">#<?php echo $article['id']; ?></td>
                                        <td>
                                            <div class="d-flex align-items-start">
                                                <?php if ($article['cover_image']): ?>
                                                    <img src="<?php echo e($article['cover_image']); ?>" 
                                                         alt="封面" 
                                                         class="me-2 rounded" 
                                                         style="width: 60px; height: 45px; object-fit: cover;">
                                                <?php endif; ?>
                                                <div>
                                                    <a href="/review/detail?id=<?php echo $article['id']; ?>" 
                                                       class="text-decoration-none fw-bold">
                                                        <?php echo e($article['title']); ?>
                                                    </a>
                                                    <?php if ($article['content_type'] === 'multimedia'): ?>
                                                        <span class="badge bg-info ms-1">多媒体</span>
                                                    <?php endif; ?>
                                                    <?php if ($article['summary']): ?>
                                                        <div class="text-muted small mt-1">
                                                            <?php echo e(mb_substr($article['summary'], 0, 50)); ?>...
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo e($article['category_name']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo e($article['author_real_name'] ?: $article['author_name']); ?></strong>
                                            </div>
                                        </td>
                                        <td class="text-muted">
                                            <?php echo e($article['student_id']); ?>
                                        </td>
                                        <td class="text-muted">
                                            <small>
                                                <?php echo date('Y-m-d H:i', strtotime($article['submit_time'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <a href="/review/detail?id=<?php echo $article['id']; ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye"></i> 审核
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- 分页 -->
                    <?php if ($total_pages > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?type=<?php echo e($type); ?>&page=<?php echo $page - 1; ?><?php echo get('category') ? '&category=' . get('category') : ''; ?>">
                                            上一页
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?type=<?php echo e($type); ?>&page=<?php echo $i; ?><?php echo get('category') ? '&category=' . get('category') : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?type=<?php echo e($type); ?>&page=<?php echo $page + 1; ?><?php echo get('category') ? '&category=' . get('category') : ''; ?>">
                                            下一页
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 页脚 -->
    <footer class="bg-light mt-5 py-3">
        <div class="container text-center">
            <p class="text-muted small mb-0">
                &copy; 2024 <?php echo e(setting('site_name', '校园新闻投稿系统')); ?>. All rights reserved.
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
</body>
</html>
