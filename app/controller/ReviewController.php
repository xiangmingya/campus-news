<?php
namespace App\Controller;

require_once __DIR__ . '/BaseController.php';

/**
 * 审核控制器
 */
class ReviewController extends BaseController {
    
    /**
     * 审核工作台首页
     */
    public function dashboard() {
        $this->checkLogin();
        $this->checkPermission('article_review');
        
        $user = user();
        $role_id = $user['role_id'];
        
        // 统计数据
        $stats = [
            'pending_first' => 0,
            'pending_final' => 0,
            'my_today' => 0,
            'my_week' => 0,
            'my_total' => 0
        ];
        
        // 待初审数量
        $stmt = db()->prepare('SELECT COUNT(*) as cnt FROM articles WHERE review_status = ?');
        $stmt->execute(['pending_first']);
        $stats['pending_first'] = $stmt->fetch()['cnt'];
        
        // 待终审数量（仅对有终审权限的用户显示）
        if (has_permission('article_final_review')) {
            $stmt = db()->prepare('SELECT COUNT(*) as cnt FROM articles WHERE review_status = ?');
            $stmt->execute(['pending_final']);
            $stats['pending_final'] = $stmt->fetch()['cnt'];
        }
        
        // 我今天审核的数量
        $stmt = db()->prepare('
            SELECT COUNT(*) as cnt FROM review_logs 
            WHERE reviewer_id = ? AND DATE(created_at) = CURDATE()
        ');
        $stmt->execute([$user['id']]);
        $stats['my_today'] = $stmt->fetch()['cnt'];
        
        // 我本周审核的数量
        $stmt = db()->prepare('
            SELECT COUNT(*) as cnt FROM review_logs 
            WHERE reviewer_id = ? AND YEARWEEK(created_at) = YEARWEEK(NOW())
        ');
        $stmt->execute([$user['id']]);
        $stats['my_week'] = $stmt->fetch()['cnt'];
        
        // 我总共审核的数量
        $stmt = db()->prepare('SELECT COUNT(*) as cnt FROM review_logs WHERE reviewer_id = ?');
        $stmt->execute([$user['id']]);
        $stats['my_total'] = $stmt->fetch()['cnt'];
        
        // 最近审核记录
        $stmt = db()->prepare('
            SELECT 
                rl.*,
                a.title as article_title,
                u.username as author_name
            FROM review_logs rl
            LEFT JOIN articles a ON rl.article_id = a.id
            LEFT JOIN users u ON a.user_id = u.id
            WHERE rl.reviewer_id = ?
            ORDER BY rl.created_at DESC
            LIMIT 10
        ');
        $stmt->execute([$user['id']]);
        $recent_reviews = $stmt->fetchAll();
        
        view('editor/dashboard', [
            'stats' => $stats,
            'recent_reviews' => $recent_reviews,
            'user' => $user
        ]);
    }
    
    /**
     * 待审稿件列表
     */
    public function pending() {
        $this->checkLogin();
        $this->checkPermission('article_review');
        
        $user = user();
        $type = get('type', 'first'); // first: 初审, final: 终审
        $category_id = get('category');
        $page = max(1, intval(get('page', 1)));
        $per_page = 20;
        $offset = ($page - 1) * $per_page;
        
        // 检查终审权限
        if ($type === 'final' && !has_permission('article_final_review')) {
            echo '没有终审权限';
            exit;
        }
        
        $review_status = $type === 'first' ? 'pending_first' : 'pending_final';
        
        // 构建查询
        $where = 'WHERE a.review_status = ?';
        $params = [$review_status];
        
        if ($category_id) {
            $where .= ' AND a.category_id = ?';
            $params[] = $category_id;
        }
        
        // 查询总数
        $stmt = db()->prepare("SELECT COUNT(*) as cnt FROM articles a $where");
        $stmt->execute($params);
        $total = $stmt->fetch()['cnt'];
        
        // 查询列表
        $stmt = db()->prepare("
            SELECT 
                a.*,
                c.name as category_name,
                u.username as author_name,
                u.real_name as author_real_name,
                u.student_id
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.id
            LEFT JOIN users u ON a.user_id = u.id
            $where
            ORDER BY a.submit_time DESC
            LIMIT $per_page OFFSET $offset
        ");
        $stmt->execute($params);
        $articles = $stmt->fetchAll();
        
        // 获取分类列表
        $stmt = db()->prepare('SELECT * FROM categories ORDER BY sort_order ASC');
        $stmt->execute();
        $categories = $stmt->fetchAll();
        
        view('editor/pending', [
            'type' => $type,
            'articles' => $articles,
            'categories' => $categories,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        ]);
    }
    
    /**
     * 稿件审核详情
     */
    public function detail() {
        $this->checkLogin();
        $this->checkPermission('article_review');
        
        $id = get('id');
        if (!$id) {
            echo '参数错误';
            exit;
        }
        
        // 查询稿件详情
        $stmt = db()->prepare('
            SELECT 
                a.*,
                c.name as category_name,
                u.username as author_name,
                u.real_name as author_real_name,
                u.student_id,
                u.email,
                u.phone
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.id
            LEFT JOIN users u ON a.user_id = u.id
            WHERE a.id = ?
        ');
        $stmt->execute([$id]);
        $article = $stmt->fetch();
        
        if (!$article) {
            echo '稿件不存在';
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
        
        view('editor/detail', [
            'article' => $article,
            'review_history' => $review_history
        ]);
    }
    
    /**
     * 审核通过
     */
    public function approve() {
        $this->checkLogin();
        $this->checkPermission('article_review');
        
        $id = post('id');
        $comment = post('comment', '');
        
        if (!$id) {
            $this->error('参数错误');
        }
        
        $user = user();
        
        // 查询稿件
        $stmt = db()->prepare('SELECT * FROM articles WHERE id = ?');
        $stmt->execute([$id]);
        $article = $stmt->fetch();
        
        if (!$article) {
            $this->error('稿件不存在');
        }
        
        // 判断审核类型
        if ($article['review_status'] === 'pending_first') {
            // 初审通过，转到待终审
            $new_status = 'pending_final';
            $review_type = 'first_review';
            $action = 'approve';
        } elseif ($article['review_status'] === 'pending_final') {
            // 终审通过，转到已批准
            if (!has_permission('article_final_review')) {
                $this->error('没有终审权限');
            }
            $new_status = 'approved';
            $review_type = 'final_review';
            $action = 'approve';
        } else {
            $this->error('稿件状态不正确');
        }
        
        try {
            db()->beginTransaction();
            
            // 更新稿件状态
            $stmt = db()->prepare('
                UPDATE articles 
                SET review_status = ?, 
                    first_reviewer_id = IF(? = "first_review", ?, first_reviewer_id),
                    first_review_time = IF(? = "first_review", NOW(), first_review_time),
                    final_reviewer_id = IF(? = "final_review", ?, final_reviewer_id),
                    final_review_time = IF(? = "final_review", NOW(), final_review_time),
                    updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([
                $new_status,
                $review_type, $user['id'],
                $review_type,
                $review_type, $user['id'],
                $review_type,
                $id
            ]);
            
            // 记录审核日志
            $stmt = db()->prepare('
                INSERT INTO review_logs 
                (article_id, reviewer_id, review_type, action, comment, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ');
            $stmt->execute([$id, $user['id'], $review_type, $action, $comment]);
            
            // 发送通知给作者
            $notification_content = $review_type === 'first_review' 
                ? '您的稿件《' . $article['title'] . '》初审通过，正在等待终审。'
                : '您的稿件《' . $article['title'] . '》终审通过，已进入待发布状态。';
            
            $stmt = db()->prepare('
                INSERT INTO notifications 
                (user_id, type, title, content, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ');
            $stmt->execute([
                $article['user_id'],
                'review_result',
                '审核通知',
                $notification_content
            ]);
            
            // 记录操作日志
            log_operation(
                'article_review_approve',
                'article',
                $id,
                '审核通过：' . $review_type
            );
            
            db()->commit();
            
            $this->success('审核通过');
            
        } catch (\Exception $e) {
            db()->rollBack();
            $this->error('操作失败：' . $e->getMessage());
        }
    }
    
    /**
     * 拒绝稿件
     */
    public function reject() {
        $this->checkLogin();
        $this->checkPermission('article_review');
        
        $id = post('id');
        $comment = post('comment');
        
        if (!$id || empty($comment)) {
            $this->error('请填写拒绝原因');
        }
        
        $user = user();
        
        // 查询稿件
        $stmt = db()->prepare('SELECT * FROM articles WHERE id = ?');
        $stmt->execute([$id]);
        $article = $stmt->fetch();
        
        if (!$article) {
            $this->error('稿件不存在');
        }
        
        // 判断审核类型
        if ($article['review_status'] === 'pending_first') {
            $review_type = 'first_review';
        } elseif ($article['review_status'] === 'pending_final') {
            if (!has_permission('article_final_review')) {
                $this->error('没有终审权限');
            }
            $review_type = 'final_review';
        } else {
            $this->error('稿件状态不正确');
        }
        
        try {
            db()->beginTransaction();
            
            // 更新稿件状态为已拒绝
            $stmt = db()->prepare('
                UPDATE articles 
                SET review_status = "rejected",
                    first_reviewer_id = IF(? = "first_review", ?, first_reviewer_id),
                    first_review_time = IF(? = "first_review", NOW(), first_review_time),
                    final_reviewer_id = IF(? = "final_review", ?, final_reviewer_id),
                    final_review_time = IF(? = "final_review", NOW(), final_review_time),
                    updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([
                $review_type, $user['id'],
                $review_type,
                $review_type, $user['id'],
                $review_type,
                $id
            ]);
            
            // 记录审核日志
            $stmt = db()->prepare('
                INSERT INTO review_logs 
                (article_id, reviewer_id, review_type, action, comment, created_at)
                VALUES (?, ?, ?, "reject", ?, NOW())
            ');
            $stmt->execute([$id, $user['id'], $review_type, $comment]);
            
            // 发送通知给作者
            $stmt = db()->prepare('
                INSERT INTO notifications 
                (user_id, type, title, content, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ');
            $stmt->execute([
                $article['user_id'],
                'review_result',
                '审核通知',
                '您的稿件《' . $article['title'] . '》被拒绝。拒绝原因：' . $comment
            ]);
            
            // 记录操作日志
            log_operation(
                'article_review_reject',
                'article',
                $id,
                '拒绝稿件：' . $review_type
            );
            
            db()->commit();
            
            $this->success('已拒绝');
            
        } catch (\Exception $e) {
            db()->rollBack();
            $this->error('操作失败：' . $e->getMessage());
        }
    }
    
    /**
     * 要求修改
     */
    public function requestRevision() {
        $this->checkLogin();
        $this->checkPermission('article_review');
        
        $id = post('id');
        $comment = post('comment');
        
        if (!$id || empty($comment)) {
            $this->error('请填写修改意见');
        }
        
        $user = user();
        
        // 查询稿件
        $stmt = db()->prepare('SELECT * FROM articles WHERE id = ?');
        $stmt->execute([$id]);
        $article = $stmt->fetch();
        
        if (!$article) {
            $this->error('稿件不存在');
        }
        
        // 判断审核类型
        if ($article['review_status'] === 'pending_first') {
            $review_type = 'first_review';
        } elseif ($article['review_status'] === 'pending_final') {
            if (!has_permission('article_final_review')) {
                $this->error('没有终审权限');
            }
            $review_type = 'final_review';
        } else {
            $this->error('稿件状态不正确');
        }
        
        try {
            db()->beginTransaction();
            
            // 更新稿件状态为需修改
            $stmt = db()->prepare('
                UPDATE articles 
                SET review_status = "revision_required",
                    first_reviewer_id = IF(? = "first_review", ?, first_reviewer_id),
                    first_review_time = IF(? = "first_review", NOW(), first_review_time),
                    final_reviewer_id = IF(? = "final_review", ?, final_reviewer_id),
                    final_review_time = IF(? = "final_review", NOW(), final_review_time),
                    updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([
                $review_type, $user['id'],
                $review_type,
                $review_type, $user['id'],
                $review_type,
                $id
            ]);
            
            // 记录审核日志
            $stmt = db()->prepare('
                INSERT INTO review_logs 
                (article_id, reviewer_id, review_type, action, comment, created_at)
                VALUES (?, ?, ?, "request_revision", ?, NOW())
            ');
            $stmt->execute([$id, $user['id'], $review_type, $comment]);
            
            // 发送通知给作者
            $stmt = db()->prepare('
                INSERT INTO notifications 
                (user_id, type, title, content, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ');
            $stmt->execute([
                $article['user_id'],
                'review_result',
                '审核通知',
                '您的稿件《' . $article['title'] . '》需要修改。修改意见：' . $comment
            ]);
            
            // 记录操作日志
            log_operation(
                'article_review_revision',
                'article',
                $id,
                '要求修改：' . $review_type
            );
            
            db()->commit();
            
            $this->success('已要求修改');
            
        } catch (\Exception $e) {
            db()->rollBack();
            $this->error('操作失败：' . $e->getMessage());
        }
    }
    
    /**
     * 审核统计
     */
    public function stats() {
        $this->checkLogin();
        $this->checkPermission('article_review');
        
        $user = user();
        $date_range = get('range', '7'); // 7天、30天、全部
        
        // 构建时间条件
        $date_condition = '';
        if ($date_range == '7') {
            $date_condition = 'AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
        } elseif ($date_range == '30') {
            $date_condition = 'AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
        }
        
        // 我的审核统计
        $stmt = db()->prepare("
            SELECT 
                action,
                COUNT(*) as count
            FROM review_logs
            WHERE reviewer_id = ? $date_condition
            GROUP BY action
        ");
        $stmt->execute([$user['id']]);
        $my_stats = [];
        while ($row = $stmt->fetch()) {
            $my_stats[$row['action']] = $row['count'];
        }
        
        // 每日审核趋势
        $stmt = db()->prepare("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as count
            FROM review_logs
            WHERE reviewer_id = ? $date_condition
            GROUP BY DATE(created_at)
            ORDER BY date DESC
            LIMIT 30
        ");
        $stmt->execute([$user['id']]);
        $daily_trend = $stmt->fetchAll();
        
        // 分类统计
        $stmt = db()->prepare("
            SELECT 
                c.name as category_name,
                COUNT(*) as count
            FROM review_logs rl
            LEFT JOIN articles a ON rl.article_id = a.id
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE rl.reviewer_id = ? $date_condition
            GROUP BY a.category_id
            ORDER BY count DESC
        ");
        $stmt->execute([$user['id']]);
        $category_stats = $stmt->fetchAll();
        
        // 如果有管理权限，显示团队统计
        $team_stats = [];
        if (has_permission('user_manage')) {
            $stmt = db()->prepare("
                SELECT 
                    u.username,
                    u.real_name,
                    COUNT(*) as count,
                    SUM(CASE WHEN rl.action = 'approve' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN rl.action = 'reject' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN rl.action = 'request_revision' THEN 1 ELSE 0 END) as revision
                FROM review_logs rl
                LEFT JOIN users u ON rl.reviewer_id = u.id
                WHERE 1=1 $date_condition
                GROUP BY rl.reviewer_id
                ORDER BY count DESC
                LIMIT 10
            ");
            $stmt->execute();
            $team_stats = $stmt->fetchAll();
        }
        
        view('editor/stats', [
            'my_stats' => $my_stats,
            'daily_trend' => $daily_trend,
            'category_stats' => $category_stats,
            'team_stats' => $team_stats,
            'date_range' => $date_range
        ]);
    }
}
