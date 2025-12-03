<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>审核稿件 - <?php echo e($article['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/static/css/style.css" rel="stylesheet">
    <style>
        .article-content {
            font-size: 16px;
            line-height: 1.8;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
        }
        .article-content img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 20px auto;
        }
        .review-action-panel {
            position: sticky;
            top: 20px;
        }
    </style>
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
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="javascript:history.back()">
                            <i class="bi bi-arrow-left"></i> 返回列表
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- 主要内容 -->
    <div class="container-fluid mt-4 mb-5">
        <div class="row">
            <!-- 左侧：稿件内容 -->
            <div class="col-lg-8">
                <!-- 稿件基本信息 -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h3 class="mb-2"><?php echo e($article['title']); ?></h3>
                                <div class="d-flex gap-2 mb-2">
                                    <span class="badge bg-secondary"><?php echo e($article['category_name']); ?></span>
                                    <?php if ($article['review_status'] === 'pending_first'): ?>
                                        <span class="badge bg-primary">待初审</span>
                                    <?php elseif ($article['review_status'] === 'pending_final'): ?>
                                        <span class="badge bg-warning">待终审</span>
                                    <?php elseif ($article['review_status'] === 'approved'): ?>
                                        <span class="badge bg-success">已批准</span>
                                    <?php elseif ($article['review_status'] === 'rejected'): ?>
                                        <span class="badge bg-danger">已拒绝</span>
                                    <?php elseif ($article['review_status'] === 'revision_required'): ?>
                                        <span class="badge bg-warning">需修改</span>
                                    <?php endif; ?>
                                    <?php if ($article['content_type'] === 'multimedia'): ?>
                                        <span class="badge bg-info">多媒体</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- 作者信息 -->
                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-md-6">
                                <p class="mb-1">
                                    <i class="bi bi-person text-muted"></i>
                                    <strong>作者：</strong><?php echo e($article['author_real_name'] ?: $article['author_name']); ?>
                                </p>
                                <p class="mb-1">
                                    <i class="bi bi-card-text text-muted"></i>
                                    <strong>学号：</strong><?php echo e($article['student_id']); ?>
                                </p>
                                <p class="mb-1">
                                    <i class="bi bi-envelope text-muted"></i>
                                    <strong>邮箱：</strong><?php echo e($article['email']); ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1">
                                    <i class="bi bi-phone text-muted"></i>
                                    <strong>电话：</strong><?php echo e($article['phone']); ?>
                                </p>
                                <p class="mb-1">
                                    <i class="bi bi-clock text-muted"></i>
                                    <strong>提交时间：</strong><?php echo date('Y-m-d H:i:s', strtotime($article['submit_time'])); ?>
                                </p>
                            </div>
                        </div>

                        <!-- 封面图 -->
                        <?php if ($article['cover_image']): ?>
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-image"></i> 封面图
                                </label>
                                <div>
                                    <img src="<?php echo e($article['cover_image']); ?>" 
                                         alt="封面图" 
                                         class="img-fluid rounded" 
                                         style="max-height: 400px;">
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- 摘要 -->
                        <?php if ($article['summary']): ?>
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-card-text"></i> 摘要
                                </label>
                                <p class="text-muted"><?php echo nl2br(e($article['summary'])); ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- 稿件内容 -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-file-text"></i> 稿件内容
                            </label>
                            
                            <?php if ($article['content_type'] === 'text'): ?>
                                <div class="article-content border">
                                    <?php echo $article['content']; ?>
                                </div>
                            <?php elseif ($article['content_type'] === 'word'): ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-file-word"></i> Word文档上传
                                    <div class="mt-2">
                                        <a href="<?php echo e($article['word_file_url']); ?>" 
                                           class="btn btn-primary" 
                                           download>
                                            <i class="bi bi-download"></i> 下载Word文档
                                        </a>
                                    </div>
                                </div>
                            <?php elseif ($article['content_type'] === 'multimedia'): ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-cloud"></i> 多媒体稿件（网盘链接）
                                    <div class="mt-2">
                                        <p class="mb-1"><strong>网盘链接：</strong></p>
                                        <a href="<?php echo e($article['baidu_link']); ?>" 
                                           target="_blank" 
                                           class="btn btn-primary">
                                            <i class="bi bi-link-45deg"></i> 打开网盘链接
                                        </a>
                                        <?php if ($article['baidu_password']): ?>
                                            <p class="mt-2 mb-0">
                                                <strong>提取码：</strong>
                                                <code class="fs-5"><?php echo e($article['baidu_password']); ?></code>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($article['content']): ?>
                                        <hr>
                                        <p class="mb-1"><strong>作品说明：</strong></p>
                                        <div><?php echo nl2br(e($article['content'])); ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- 关键词和标签 -->
                        <?php if ($article['keywords']): ?>
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-tags"></i> 关键词
                                </label>
                                <div>
                                    <?php foreach (explode(',', $article['keywords']) as $keyword): ?>
                                        <span class="badge bg-light text-dark border me-1"><?php echo e(trim($keyword)); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 审核历史 -->
                <?php if (!empty($review_history)): ?>
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history"></i> 审核历史
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <?php foreach ($review_history as $review): ?>
                                <div class="timeline-item mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong><?php echo e($review['reviewer_real_name'] ?: $review['reviewer_name']); ?></strong>
                                            <span class="ms-2">
                                                <?php if ($review['review_type'] === 'first_review'): ?>
                                                    <span class="badge bg-primary">初审</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">终审</span>
                                                <?php endif; ?>
                                            </span>
                                            <span class="ms-2">
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
                                            </span>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo date('Y-m-d H:i', strtotime($review['created_at'])); ?>
                                        </small>
                                    </div>
                                    <?php if ($review['comment']): ?>
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <small><?php echo nl2br(e($review['comment'])); ?></small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- 右侧：审核操作面板 -->
            <div class="col-lg-4">
                <div class="review-action-panel">
                    <?php 
                        $can_review = false;
                        if ($article['review_status'] === 'pending_first') {
                            $can_review = has_permission('article_review');
                            $review_type_text = '初审';
                        } elseif ($article['review_status'] === 'pending_final') {
                            $can_review = has_permission('article_final_review');
                            $review_type_text = '终审';
                        }
                    ?>

                    <?php if ($can_review): ?>
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-clipboard-check"></i> <?php echo $review_type_text; ?>操作
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- 审核意见 -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">审核意见</label>
                                    <textarea id="reviewComment" class="form-control" rows="4" placeholder="请输入审核意见..."></textarea>
                                    <small class="text-muted">拒绝或要求修改时必须填写原因</small>
                                </div>

                                <!-- 审核按钮 -->
                                <div class="d-grid gap-2">
                                    <button class="btn btn-success btn-lg" onclick="reviewAction('approve')">
                                        <i class="bi bi-check-circle"></i> 通过
                                    </button>
                                    <button class="btn btn-warning btn-lg" onclick="reviewAction('revision')">
                                        <i class="bi bi-arrow-clockwise"></i> 要求修改
                                    </button>
                                    <button class="btn btn-danger btn-lg" onclick="reviewAction('reject')">
                                        <i class="bi bi-x-circle"></i> 拒绝
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- 审核要点提示 -->
                        <div class="card mt-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="bi bi-lightbulb"></i> 审核要点
                                </h6>
                            </div>
                            <div class="card-body">
                                <ul class="small mb-0">
                                    <li>内容是否符合栏目定位</li>
                                    <li>是否存在政治敏感或不当内容</li>
                                    <li>是否存在抄袭或侵权问题</li>
                                    <li>标题是否准确、吸引人</li>
                                    <li>文字是否流畅、无错别字</li>
                                    <li>图片/视频质量是否达标</li>
                                    <li>格式排版是否规范</li>
                                </ul>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="bi bi-info-circle text-muted" style="font-size: 3rem;"></i>
                                <p class="mt-3 text-muted">
                                    <?php if ($article['review_status'] === 'approved'): ?>
                                        稿件已批准，等待发布
                                    <?php elseif ($article['review_status'] === 'rejected'): ?>
                                        稿件已被拒绝
                                    <?php elseif ($article['review_status'] === 'revision_required'): ?>
                                        稿件需要作者修改
                                    <?php else: ?>
                                        暂无可执行的审核操作
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 页脚 -->
    <footer class="bg-light py-3">
        <div class="container text-center">
            <p class="text-muted small mb-0">
                &copy; 2024 <?php echo e(setting('site_name', '校园新闻投稿系统')); ?>. All rights reserved.
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script>
        function reviewAction(action) {
            const comment = $('#reviewComment').val().trim();
            const articleId = <?php echo $article['id']; ?>;

            // 拒绝和要求修改必须填写意见
            if ((action === 'reject' || action === 'revision') && !comment) {
                alert('请填写审核意见');
                return;
            }

            // 确认操作
            let confirmMsg = '';
            if (action === 'approve') {
                confirmMsg = '确认通过此稿件？';
            } else if (action === 'reject') {
                confirmMsg = '确认拒绝此稿件？此操作不可撤销。';
            } else if (action === 'revision') {
                confirmMsg = '确认要求作者修改？';
            }

            if (!confirm(confirmMsg)) {
                return;
            }

            // 发送请求
            let endpoint = '';
            if (action === 'approve') {
                endpoint = '/review/approve';
            } else if (action === 'reject') {
                endpoint = '/review/reject';
            } else if (action === 'revision') {
                endpoint = '/review/requestRevision';
            }

            $.ajax({
                url: endpoint,
                type: 'POST',
                data: {
                    id: articleId,
                    comment: comment
                },
                dataType: 'json',
                success: function(res) {
                    if (res.code === 200) {
                        alert(res.msg);
                        // 返回列表页
                        <?php if ($article['review_status'] === 'pending_first'): ?>
                            window.location.href = '/review/pending?type=first';
                        <?php else: ?>
                            window.location.href = '/review/pending?type=final';
                        <?php endif; ?>
                    } else {
                        alert(res.msg);
                    }
                },
                error: function() {
                    alert('操作失败，请稍后重试');
                }
            });
        }
    </script>
</body>
</html>
