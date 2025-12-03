<?php
namespace App\Controller;

require_once __DIR__ . '/BaseController.php';

/**
 * 认证控制器
 */
class AuthController extends BaseController {
    
    /**
     * 登录页面
     */
    public function login() {
        // 如果已登录，跳转到首页
        if (is_login()) {
            redirect('/');
        }
        
        view('auth/login');
    }
    
    /**
     * 登录处理
     */
    public function doLogin() {
        $email = post('email');
        $password = post('password');
        $remember = post('remember', 0);
        
        // 验证
        if (empty($email) || empty($password)) {
            $this->error('请输入邮箱和密码');
        }
        
        // 查询用户
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ? AND status = 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $this->error('用户不存在或已被禁用');
        }
        
        // 验证密码
        if (!password_verify($password, $user['password'])) {
            $this->error('密码错误');
        }
        
        // 设置Session
        set_session('user_id', $user['id']);
        
        // 记住登录（可选实现Cookie）
        if ($remember) {
            // 这里可以实现记住登录功能
        }
        
        // 记录登录日志
        log_operation('user_login', 'user', $user['id'], '用户登录');
        
        $this->success('登录成功', ['redirect' => '/']);
    }
    
    /**
     * 注册页面
     */
    public function register() {
        // 如果已登录，跳转到首页
        if (is_login()) {
            redirect('/');
        }
        
        view('auth/register');
    }
    
    /**
     * 注册处理
     */
    public function doRegister() {
        $username = post('username');
        $email = post('email');
        $password = post('password');
        $password_confirm = post('password_confirm');
        $student_id = post('student_id');
        $real_name = post('real_name');
        
        // 验证
        if (empty($username) || empty($email) || empty($password)) {
            $this->error('请填写完整信息');
        }
        
        if ($password !== $password_confirm) {
            $this->error('两次密码不一致');
        }
        
        // 验证邮箱格式
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('邮箱格式不正确');
        }
        
        // 验证密码长度
        $min_length = config('password.min_length', 6);
        $max_length = config('password.max_length', 20);
        if (strlen($password) < $min_length || strlen($password) > $max_length) {
            $this->error("密码长度必须在{$min_length}-{$max_length}位之间");
        }
        
        // 检查用户名是否已存在
        $stmt = db()->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $this->error('用户名已存在');
        }
        
        // 检查邮箱是否已存在
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $this->error('邮箱已被注册');
        }
        
        try {
            db()->beginTransaction();
            
            // 创建用户
            $stmt = db()->prepare('
                INSERT INTO users 
                (username, email, password, real_name, student_id, role_id, auth_status, created_at)
                VALUES (?, ?, ?, ?, ?, 4, 0, NOW())
            ');
            $stmt->execute([
                $username,
                $email,
                password_hash($password, PASSWORD_BCRYPT),
                $real_name,
                $student_id
            ]);
            
            $user_id = db()->lastInsertId();
            
            // 发送欢迎通知
            send_notification(
                $user_id,
                'system',
                '欢迎注册',
                '欢迎加入校园新闻投稿系统！请先完成身份认证后即可开始投稿。'
            );
            
            // 记录操作日志
            log_operation('user_register', 'user', $user_id, '用户注册');
            
            db()->commit();
            
            $this->success('注册成功，请登录', ['redirect' => '/auth/login']);
            
        } catch (\Exception $e) {
            db()->rollBack();
            $this->error('注册失败：' . $e->getMessage());
        }
    }
    
    /**
     * 退出登录
     */
    public function logout() {
        $user_id = user_id();
        
        if ($user_id) {
            log_operation('user_logout', 'user', $user_id, '用户退出');
        }
        
        // 清除Session
        unset_session('user_id');
        session_destroy();
        
        redirect('/');
    }
    
    /**
     * 身份认证申请页面
     */
    public function applyCert() {
        $this->checkLogin();
        
        $user = user();
        
        // 如果已认证，不能重复申请
        if ($user['auth_status'] == 1) {
            echo '您已通过身份认证';
            exit;
        }
        
        // 如果正在审核中，不能重复提交
        if ($user['auth_status'] == 3) {
            echo '您的认证申请正在审核中，请耐心等待';
            exit;
        }
        
        view('auth/apply-cert', ['user' => $user]);
    }
    
    /**
     * 提交身份认证
     */
    public function submitCert() {
        $this->checkLogin();
        
        $user = user();
        $user_id = $user['id'];
        
        // 检查状态
        if ($user['auth_status'] == 1) {
            $this->error('您已通过身份认证');
        }
        
        if ($user['auth_status'] == 3) {
            $this->error('您的认证申请正在审核中');
        }
        
        $real_name = post('real_name');
        $student_id = post('student_id');
        
        if (empty($real_name) || empty($student_id)) {
            $this->error('请填写完整信息');
        }
        
        // 上传认证文件（学生证/校园卡照片）
        $cert_file = upload_file('cert_file', 'cert');
        if (!$cert_file) {
            $this->error('请上传学生证或校园卡照片');
        }
        
        try {
            db()->beginTransaction();
            
            // 更新用户信息
            $stmt = db()->prepare('
                UPDATE users 
                SET real_name = ?, 
                    student_id = ?, 
                    cert_file_url = ?,
                    auth_status = 3,
                    updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([$real_name, $student_id, $cert_file, $user_id]);
            
            // 发送通知
            send_notification(
                $user_id,
                'system',
                '认证申请已提交',
                '您的身份认证申请已提交，管理员将在1-3个工作日内完成审核。'
            );
            
            // 记录操作日志
            log_operation('user_cert_apply', 'user', $user_id, '提交身份认证申请');
            
            db()->commit();
            
            $this->success('认证申请已提交，请等待管理员审核');
            
        } catch (\Exception $e) {
            db()->rollBack();
            $this->error('提交失败：' . $e->getMessage());
        }
    }
}
