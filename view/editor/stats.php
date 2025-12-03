<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>审核统计 - 审核工作台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/static/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
                        <a class="nav-link" href="/review/pending?type=first">
                            <i class="bi bi-file-earmark-text"></i> 初审列表
                        </a>
                    </li>
                    <?php if (has_permission('article_final_review')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/review/pending?type=final">
                            <i class="bi bi-file-earmark-check"></i> 终审列表
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link active" href="/review/stats">
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
        <!-- 页面标题和筛选 -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>
                <i class="bi bi-bar-chart"></i> 审核统计
            </h4>
            <div class="btn-group">
                <a href="?range=7" class="btn btn-outline-primary <?php echo $date_range == '7' ? 'active' : ''; ?>">
                    最近7天
                </a>
                <a href="?range=30" class="btn btn-outline-primary <?php echo $date_range == '30' ? 'active' : ''; ?>">
                    最近30天
                </a>
                <a href="?range=all" class="btn btn-outline-primary <?php echo $date_range == 'all' ? 'active' : ''; ?>">
                    全部
                </a>
            </div>
        </div>

        <!-- 我的审核统计 -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-success h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-check-circle text-success" style="font-size: 2.5rem;"></i>
                        <h3 class="mt-2 mb-0"><?php echo $my_stats['approve'] ?? 0; ?></h3>
                        <p class="text-muted mb-0">审核通过</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-arrow-clockwise text-warning" style="font-size: 2.5rem;"></i>
                        <h3 class="mt-2 mb-0"><?php echo $my_stats['request_revision'] ?? 0; ?></h3>
                        <p class="text-muted mb-0">要求修改</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-danger h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-x-circle text-danger" style="font-size: 2.5rem;"></i>
                        <h3 class="mt-2 mb-0"><?php echo $my_stats['reject'] ?? 0; ?></h3>
                        <p class="text-muted mb-0">拒绝稿件</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-primary h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-clipboard-data text-primary" style="font-size: 2.5rem;"></i>
                        <h3 class="mt-2 mb-0">
                            <?php 
                                $total = ($my_stats['approve'] ?? 0) + ($my_stats['request_revision'] ?? 0) + ($my_stats['reject'] ?? 0);
                                echo $total;
                            ?>
                        </h3>
                        <p class="text-muted mb-0">总计审核</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- 每日审核趋势 -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-graph-up"></i> 每日审核趋势
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($daily_trend)): ?>
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle"></i> 暂无数据
                            </div>
                        <?php else: ?>
                            <canvas id="dailyTrendChart" style="max-height: 300px;"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 分类统计 -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-pie-chart"></i> 分类统计
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($category_stats)): ?>
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle"></i> 暂无数据
                            </div>
                        <?php else: ?>
                            <canvas id="categoryChart" style="max-height: 300px;"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 团队统计（仅管理员可见） -->
        <?php if (has_permission('user_manage') && !empty($team_stats)): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-people"></i> 团队审核统计
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>审核员</th>
                                        <th>总计</th>
                                        <th>通过</th>
                                        <th>拒绝</th>
                                        <th>要求修改</th>
                                        <th>通过率</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($team_stats as $stat): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo e($stat['real_name'] ?: $stat['username']); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo $stat['count']; ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success"><?php echo $stat['approved']; ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger"><?php echo $stat['rejected']; ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning"><?php echo $stat['revision']; ?></span>
                                            </td>
                                            <td>
                                                <?php 
                                                    $pass_rate = $stat['count'] > 0 ? round(($stat['approved'] / $stat['count']) * 100, 1) : 0;
                                                ?>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-success" 
                                                         role="progressbar" 
                                                         style="width: <?php echo $pass_rate; ?>%">
                                                        <?php echo $pass_rate; ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
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
    
    <?php if (!empty($daily_trend)): ?>
    <script>
        // 每日趋势图
        const dailyCtx = document.getElementById('dailyTrendChart').getContext('2d');
        const dailyChart = new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: [
                    <?php foreach (array_reverse($daily_trend) as $item): ?>
                        '<?php echo date('m-d', strtotime($item['date'])); ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    label: '审核数量',
                    data: [
                        <?php foreach (array_reverse($daily_trend) as $item): ?>
                            <?php echo $item['count']; ?>,
                        <?php endforeach; ?>
                    ],
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
    <?php endif; ?>

    <?php if (!empty($category_stats)): ?>
    <script>
        // 分类统计饼图
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryChart = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    <?php foreach ($category_stats as $stat): ?>
                        '<?php echo e($stat['category_name']); ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    data: [
                        <?php foreach ($category_stats as $stat): ?>
                            <?php echo $stat['count']; ?>,
                        <?php endforeach; ?>
                    ],
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)',
                        'rgb(255, 159, 64)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
