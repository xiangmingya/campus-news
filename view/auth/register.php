<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注册 - <?php echo e(setting('site_name', '校园新闻投稿系统')); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/static/css/style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 30px 0;
        }
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 500px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-card">
            <div class="text-center mb-4">
                <i class="bi bi-person-plus" style="font-size: 3rem; color: #667eea;"></i>
                <h2 class="mt-3">用户注册</h2>
            </div>
            
            <form id="registerForm">
                <div class="mb-3">
                    <label class="form-label">用户名 *</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">邮箱 *</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">真实姓名</label>
                    <input type="text" name="real_name" class="form-control">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">学号</label>
                    <input type="text" name="student_id" class="form-control">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">密码 *</label>
                    <input type="password" name="password" class="form-control" required>
                    <small class="text-muted">密码长度6-20位</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">确认密码 *</label>
                    <input type="password" name="password_confirm" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                    <i class="bi bi-check-circle"></i> 注册
                </button>
                
                <div class="text-center">
                    <span class="text-muted">已有账号？</span>
                    <a href="/auth/login">立即登录</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/static/js/common.js"></script>
    <script>
        $('#registerForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: '/auth/doRegister',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(res) {
                    if (res.code === 200) {
                        showToast(res.msg, 'success');
                        setTimeout(function() {
                            window.location.href = res.data.redirect || '/auth/login';
                        }, 1500);
                    } else {
                        showToast(res.msg, 'danger');
                    }
                },
                error: function() {
                    showToast('注册失败，请稍后重试', 'danger');
                }
            });
        });
    </script>
</body>
</html>
