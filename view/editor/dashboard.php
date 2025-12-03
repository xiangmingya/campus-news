<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>审核工作台 - <?php echo e(setting('site_name', '校园新闻投稿系统')); ?></title>
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
                        <a class="nav-link active" href="/review/dashboard">
                            <i class="bi bi-house"></i> 工作台首页
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/review/pending?type=first">
                            <i class="bi bi-file-earmark-text"></i> 初审列表
                            <?php if ($stats['pending_first'] > 0): ?>
                                <span class="badge bg-danger"><?php echo $stats['pending_first']; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php if (has_permission('article_final_review')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/review/pending?type=final">
                            <i class="bi bi-file-earmark-check"></i> 终审列表
                            <?php if ($stats['pending_final'] > 0): ?>
                                <span class="badge bg-warning"><?php echo $stats['pending_final']; ?></span>
                            <?php endif; ?>
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
                            <i class="bi bi-person-circle"></i> <?php echo e($user['username']); ?>
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
        <!-- 欢迎信息 -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card bg-light">
                    <div class="card-body">
                        <h4 class="mb-1">
                            <i class="bi bi-brightness-high"></i> 
                            <?php 
                                $hour = date('H');
                                if ($hour < 6) echo '夜深了';
                                elseif ($hour < 12) echo '早上好';
                                elseif ($hour < 18) echo '下午好';
                                else echo '晚上好';
                            ?>，<?php echo e($user['real_name'] ?: $user['username']); ?>！
                        </h4>
                        <p class="text-muted mb-0">欢迎回到审核工作台，今天您已经审核了 <strong><?php echo $stats['my_today']; ?></strong> 篇稿件。</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 统计卡片 -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-primary h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-file-earmark-text text-primary" style="font-size: 2.5rem;"></i>
                        <h3 class="mt-2 mb-0"><?php echo $stats['pending_first']; ?></h3>
                        <p class="text-muted mb-0">待初审</p>
                        <a href="/review/pending?type=first" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="bi bi-arrow-right"></i> 去审核
                        </a>
                    </div>
                </div>
            </div>
            
            <?php if (has_permission('article_final_review')): ?>
            <div class="col-md-3">
                <div class="card border-warning h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-file-earmark-check text-warning" style="font-size: 2.5rem;"></i>
                        <h3 class="mt-2 mb-0"><?php echo $stats['pending_final']; ?></h3>
                        <p class="text-muted mb-0">待终审</p>
                        <a href="/review/pending?type=final" class="btn btn-sm btn-outline-warning mt-2">
                            <i class="bi bi-arrow-right"></i> 去审核
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="col-md-3">
                <div class="card border-success h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-calendar-check text-success" style="font-size: 2.5rem;"></i>
                        <h3 class="mt-2 mb-0"><?php echo $stats['my_week']; ?></h3>
                        <p class="text-muted mb-0">本周审核</p>
                        <a href="/review/stats?range=7" class="btn btn-sm btn-outline-success mt-2">
                            <i class="bi bi-bar-chart"></i> 查看详情
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card border-info h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-award text-info" style="font-size: 2.5rem;"></i>
                        <h3 class="mt-2 mb-0"><?php echo $stats['my_total']; ?></h3>
                        <p class="text-muted mb-0">累计审核</p>
                        <a href="/review/stats" class="btn btn-sm btn-outline-info mt-2">
                            <i class="bi bi-bar-chart"></i> 全部统计
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 最近审核记录 -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history"></i> 最近审核记录
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_reviews)): ?>
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle"></i> 暂无审核记录
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>稿件标题</th>
                                            <th>作者</th>
                                            <th>审核类型</th>
                                            <th>审核结果</th>
                                            <th>审核时间</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_reviews as $review): ?>
                                            <tr>
                                                <td>
                                                    <a href="/review/detail?id=<?php echo $review['article_id']; ?>" class="text-decoration-none">
                                                        <?php echo e($review['article_title']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo e($review['author_name']); ?></td>
                                                <td>
                                                    <?php if ($review['review_type'] === 'first_review'): ?>
                                                        <span class="badge bg-primary">初审</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">终审</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($review['action'] === 'approve'): ?>
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-circle"></i> 通过
                                                        </span>
                                                    <?php elseif ($review['action'] === 'reject'): ?>
                                                        <span class="badge bg-danger">
                                                            <i class="bi bi-x-circle"></i> 拒绝
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">
                                                            <i class="bi bi-arrow-clockwise"></i> 需修改
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo date('Y-m-d H:i', strtotime($review['created_at'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <a href="/review/detail?id=<?php echo $review['article_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> 查看
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
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
