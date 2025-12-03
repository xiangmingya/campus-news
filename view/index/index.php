<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(setting('site_name', '校园新闻投稿系统')); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/static/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- 导航栏 -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">
                <?php echo e(setting('site_name', '校园新闻投稿系统')); ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="/">首页</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/article/submit">投稿</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (is_login()): ?>
                        <?php $current_user = user(); ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <?php echo e($current_user['username']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/user/profile">个人中心</a></li>
                                <li><a class="dropdown-item" href="/article/my">我的稿件</a></li>
                                <?php if (has_permission('article_review')): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/review/pending">审核工作台</a></li>
                                <?php endif; ?>
                                <?php if (has_permission('user_manage')): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/admin/dashboard">后台管理</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/auth/logout">退出登录</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/auth/login">登录</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary text-white ms-2" href="/auth/register">注册</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- 主要内容 -->
    <div class="container mt-4">
        <!-- 欢迎横幅 -->
        <div class="jumbotron bg-light p-5 rounded-3 mb-4">
            <h1 class="display-4">欢迎来到校园新闻投稿系统</h1>
            <p class="lead">分享你的故事，记录校园生活的精彩瞬间</p>
            <hr class="my-4">
            <p>支持新闻报道、文艺作品、摄影作品、视频作品等多种形式投稿</p>
            <?php if (!is_login()): ?>
                <a class="btn btn-primary btn-lg" href="/auth/register">立即注册投稿</a>
            <?php else: ?>
                <?php $current_user = user(); ?>
                <?php if ($current_user['auth_status'] == 0): ?>
                    <a class="btn btn-warning btn-lg" href="/user/apply-cert">完成身份认证</a>
                <?php elseif ($current_user['auth_status'] == 3): ?>
                    <button class="btn btn-secondary btn-lg" disabled>认证审核中...</button>
                <?php elseif ($current_user['auth_status'] == 1): ?>
                    <a class="btn btn-success btn-lg" href="/article/submit">开始投稿</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- 栏目分类 -->
        <div class="row mb-4">
            <div class="col-md-12">
                <h3 class="mb-3">热门栏目</h3>
                <div class="btn-group" role="group">
                    <a href="/" class="btn btn-outline-primary active">全部</a>
                    <a href="/?category=1" class="btn btn-outline-primary">校园新闻</a>
                    <a href="/?category=3" class="btn btn-outline-primary">文艺作品</a>
                    <a href="/?category=4" class="btn btn-outline-primary">摄影作品</a>
                    <a href="/?category=5" class="btn btn-outline-primary">视频作品</a>
                </div>
            </div>
        </div>

        <!-- 稿件列表 -->
        <div class="row">
            <?php if (empty($articles)): ?>
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <h5>暂无稿件</h5>
                        <p>还没有发布的稿件，快来成为第一个投稿者吧！</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($articles as $article): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <?php if ($article['cover_image']): ?>
                                <img src="<?php echo e($article['cover_image']); ?>" class="card-img-top" alt="封面图" style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center text-white" style="height: 200px;">
                                    <h3><?php echo e($article['category_name']); ?></h3>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <span class="badge bg-primary mb-2"><?php echo e($article['category_name']); ?></span>
                                <h5 class="card-title"><?php echo e($article['title']); ?></h5>
                                <?php if ($article['summary']): ?>
                                    <p class="card-text text-muted">
                                        <?php echo e(mb_substr($article['summary'], 0, 100)); ?>...
                                    </p>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <?php echo e($article['author_name']); ?>
                                    </small>
                                    <small class="text-muted">
                                        <?php echo date('Y-m-d', strtotime($article['publish_time'])); ?>
                                    </small>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="/index/detail?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-outline-primary w-100">
                                    查看详情
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- 页脚 -->
    <footer class="bg-light mt-5 py-4">
        <div class="container text-center">
            <p class="text-muted mb-0">
                &copy; 2024 <?php echo e(setting('site_name', '校园新闻投稿系统')); ?>. All rights reserved.
            </p>
            <p class="text-muted small">
                Version 1.0.0 | Powered by PHP
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="/static/js/common.js"></script>
</body>
</html>
