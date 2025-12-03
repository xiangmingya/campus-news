<?php
namespace App\Controller;

require_once __DIR__ . '/BaseController.php';

/**
 * 稿件控制器
 */
class ArticleController extends BaseController {
    
    /**
     * 投稿页面
     */
    public function submit() {
        $this->checkLogin();
        
        $user = user();
        
        // 检查是否已认证
        if ($user['auth_status'] != 1) {
            echo '请先完成身份认证后再投稿';
            exit;
        }
        
        // 检查投稿限制
        $check = can_submit_article();
        if (!$check['can']) {
            echo $check['message'];
            exit;
        }
        
        // 获取栏目列表
        $stmt = db()->prepare('SELECT * FROM categories WHERE status = 1 ORDER BY sort_order ASC');
        $stmt->execute();
        $categories = $stmt->fetchAll();
        
        view('article/submit', [
            'user' => $user,
            'categories' => $categories
        ]);
    }
    
    /**
     * 提交稿件
     */
    public function doSubmit() {
        $this->checkLogin();
        
        $user = user();
        $user_id = $user['id'];
        
        // 检查认证状态
        if ($user['auth_status'] != 1) {
            $this->error('请先完成身份认证');
        }
        
        $category_id = post('category_id');
        $title = post('title');
        $summary = post('summary');
        $content_type = post('content_type', 'text'); // text/word/multimedia
        $content = post('content');
        $keywords = post('keywords');
        $is_draft = post('is_draft', 0); // 是否保存为草稿
        
        // 验证
        if (empty($category_id) || empty($title)) {
            $this->error('请填写标题和选择栏目');
        }
        
        // 检查投稿限制
        if (!$is_draft) {
            $check = can_submit_article(null, $content_type === 'multimedia' ? 'video' : 'text');
            if (!$check['can']) {
                $this->error($check['message']);
            }
        }
        
        // 处理不同类型的稿件
        $cover_image = null;
        $word_file_url = null;
        $baidu_link = null;
        $baidu_password = null;
        
        // 上传封面图（可选）
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $cover_image = upload_file('cover_image', 'cover');
        }
        
        if ($content_type === 'word') {
            // Word文档上传
            $word_file_url = upload_file('word_file', 'word');
            if (!$word_file_url) {
                $this->error('请上传Word文档');
            }
        } elseif ($content_type === 'multimedia') {
            // 多媒体稿件（网盘链接）
            $baidu_link = post('baidu_link');
            $baidu_password = post('baidu_password');
            
            if (empty($baidu_link)) {
                $this->error('请填写网盘链接');
            }
        } else {
            // 文字稿件
            if (empty($content)) {
                $this->error('请填写稿件内容');
            }
        }
        
        try {
            db()->beginTransaction();
            
            // 插入稿件
            $stmt = db()->prepare('
                INSERT INTO articles 
                (user_id, category_id, title, summary, content, content_type, 
                 cover_image, word_file_url, baidu_link, baidu_password, keywords,
                 review_status, submit_time, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ');
            
            $review_status = $is_draft ? 'draft' : 'pending_first';
            $submit_time = $is_draft ? null : date('Y-m-d H:i:s');
            
            $stmt->execute([
                $user_id,
                $category_id,
                $title,
                $summary,
                $content,
                $content_type,
                $cover_image,
                $word_file_url,
                $baidu_link,
                $baidu_password,
                $keywords,
                $review_status,
                $submit_time
            ]);
            
            $article_id = db()->lastInsertId();
            
            // 发送通知
            if (!$is_draft) {
                send_notification(
                    $user_id,
                    'system',
                    '投稿成功',
                    '您的稿件《' . $title . '》已提交，正在等待审核。'
                );
            }
            
            // 记录操作日志
            log_operation(
                $is_draft ? 'article_save_draft' : 'article_submit',
                'article',
                $article_id,
                $is_draft ? '保存草稿' : '提交稿件'
            );
            
            db()->commit();
            
            $message = $is_draft ? '草稿保存成功' : '投稿成功，请等待审核';
            $this->success($message, ['redirect' => '/article/my']);
            
        } catch (\Exception $e) {
            db()->rollBack();
            $this->error('提交失败：' . $e->getMessage());
        }
    }
    
    /**
     * 我的稿件
     */
    public function my() {
        $this->checkLogin();
        
        $user_id = user_id();
        $status = get('status', 'all');
        $page = max(1, intval(get('page', 1)));
        $per_page = 15;
        $offset = ($page - 1) * $per_page;
        
        // 构建查询条件
        $where = 'WHERE a.user_id = ?';
        $params = [$user_id];
        
        if ($status !== 'all') {
            $where .= ' AND a.review_status = ?';
            $params[] = $status;
        }
        
        // 查询总数
        $stmt = db()->prepare("SELECT COUNT(*) as cnt FROM articles a $where");
        $stmt->execute($params);
        $total = $stmt->fetch()['cnt'];
        
        // 查询列表
        $stmt = db()->prepare("
            SELECT 
                a.*,
                c.name as category_name
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.id
            $where
            ORDER BY a.created_at DESC
            LIMIT $per_page OFFSET $offset
        ");
        $stmt->execute($params);
        $articles = $stmt->fetchAll();
        
        view('article/my', [
            'articles' => $articles,
            'status' => $status,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        ]);
    }
    
    /**
     * 稿件详情
     */
    public function detail() {
        $this->checkLogin();
        
        $id = get('id');
        if (!$id) {
            echo '参数错误';
            exit;
        }
        
        $user_id = user_id();
        
        // 查询稿件
        $stmt = db()->prepare('
            SELECT 
                a.*,
                c.name as category_name
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE a.id = ?
        ');
        $stmt->execute([$id]);
        $article = $stmt->fetch();
        
        if (!$article) {
            echo '稿件不存在';
            exit;
        }
        
        // 检查权限：只能查看自己的稿件
        if ($article['user_id'] != $user_id) {
            echo '无权查看';
            exit;
        }
        
        // 查询审核历史
        $stmt = db()->prepare('
            SELECT 
                rl.*,
                u.username as reviewer_name,
                u.real_name as reviewer_real_name
            FROM review_logs rl
            LEFT JOIN users u ON rl.reviewer_id = u.id
            WHERE rl.article_id = ?
            ORDER BY rl.created_at DESC
        ');
        $stmt->execute([$id]);
        $review_history = $stmt->fetchAll();
        
        view('article/detail', [
            'article' => $article,
            'review_history' => $review_history
        ]);
    }
    
    /**
     * 编辑稿件
     */
    public function edit() {
        $this->checkLogin();
        
        $id = get('id');
        if (!$id) {
            echo '参数错误';
            exit;
        }
        
        $user_id = user_id();
        
        // 查询稿件
        $stmt = db()->prepare('SELECT * FROM articles WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $user_id]);
        $article = $stmt->fetch();
        
        if (!$article) {
            echo '稿件不存在或无权编辑';
            exit;
        }
        
        // 只有草稿和需修改的稿件可以编辑
        if (!in_array($article['review_status'], ['draft', 'revision_required'])) {
            echo '该稿件当前状态不允许编辑';
            exit;
        }
        
        // 获取栏目列表
        $stmt = db()->prepare('SELECT * FROM categories WHERE status = 1 ORDER BY sort_order ASC');
        $stmt->execute();
        $categories = $stmt->fetchAll();
        
        view('article/edit', [
            'article' => $article,
            'categories' => $categories
        ]);
    }
    
    /**
     * 更新稿件
     */
    public function update() {
        $this->checkLogin();
        
        $user_id = user_id();
        $id = post('id');
        $category_id = post('category_id');
        $title = post('title');
        $summary = post('summary');
        $content = post('content');
        $keywords = post('keywords');
        $is_draft = post('is_draft', 0);
        
        if (!$id) {
            $this->error('参数错误');
        }
        
        // 查询稿件
        $stmt = db()->prepare('SELECT * FROM articles WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $user_id]);
        $article = $stmt->fetch();
        
        if (!$article) {
            $this->error('稿件不存在或无权编辑');
        }
        
        // 检查状态
        if (!in_array($article['review_status'], ['draft', 'revision_required'])) {
            $this->error('该稿件当前状态不允许编辑');
        }
        
        // 验证
        if (empty($title)) {
            $this->error('请填写标题');
        }
        
        try {
            db()->beginTransaction();
            
            // 更新稿件
            $review_status = $is_draft ? 'draft' : 'pending_first';
            $submit_time = $is_draft ? $article['submit_time'] : date('Y-m-d H:i:s');
            
            $stmt = db()->prepare('
                UPDATE articles 
                SET category_id = ?,
                    title = ?,
                    summary = ?,
                    content = ?,
                    keywords = ?,
                    review_status = ?,
                    submit_time = ?,
                    updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([
                $category_id,
                $title,
                $summary,
                $content,
                $keywords,
                $review_status,
                $submit_time,
                $id
            ]);
            
            // 如果重新提交，发送通知
            if (!$is_draft && $article['review_status'] === 'revision_required') {
                send_notification(
                    $user_id,
                    'system',
                    '稿件已重新提交',
                    '您的稿件《' . $title . '》已修改并重新提交审核。'
                );
            }
            
            // 记录操作日志
            log_operation('article_update', 'article', $id, '更新稿件');
            
            db()->commit();
            
            $this->success('更新成功', ['redirect' => '/article/my']);
            
        } catch (\Exception $e) {
            db()->rollBack();
            $this->error('更新失败：' . $e->getMessage());
        }
    }
    
    /**
     * 删除稿件（草稿）
     */
    public function delete() {
        $this->checkLogin();
        
        $user_id = user_id();
        $id = post('id');
        
        if (!$id) {
            $this->error('参数错误');
        }
        
        // 查询稿件
        $stmt = db()->prepare('SELECT * FROM articles WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $user_id]);
        $article = $stmt->fetch();
        
        if (!$article) {
            $this->error('稿件不存在或无权删除');
        }
        
        // 只有草稿可以删除
        if ($article['review_status'] !== 'draft') {
            $this->error('只有草稿可以删除');
        }
        
        try {
            // 删除稿件
            $stmt = db()->prepare('DELETE FROM articles WHERE id = ?');
            $stmt->execute([$id]);
            
            // 记录操作日志
            log_operation('article_delete', 'article', $id, '删除草稿');
            
            $this->success('删除成功');
            
        } catch (\Exception $e) {
            $this->error('删除失败：' . $e->getMessage());
        }
    }
}
