<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - <?php echo e(setting('site_name', '校园新闻投稿系统')); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/static/css/style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            max-width: 450px;
            margin: 0 auto;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo h2 {
            color: #667eea;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-card">
                <div class="login-logo">
                    <i class="bi bi-newspaper" style="font-size: 3rem; color: #667eea;"></i>
                    <h2 class="mt-3">用户登录</h2>
                    <p class="text-muted"><?php echo e(setting('site_name', '校园新闻投稿系统')); ?></p>
                </div>
                
                <form id="loginForm">
                    <div class="mb-3">
                        <label class="form-label">邮箱</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="请输入邮箱" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">密码</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="请输入密码" required>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember" value="1">
                        <label class="form-check-label" for="remember">
                            记住登录状态
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                        <i class="bi bi-box-arrow-in-right"></i> 登录
                    </button>
                    
                    <div class="text-center">
                        <span class="text-muted">还没有账号？</span>
                        <a href="/auth/register">立即注册</a>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="/" class="text-muted"><i class="bi bi-house"></i> 返回首页</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/static/js/common.js"></script>
    <script>
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: '/auth/doLogin',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(res) {
                    if (res.code === 200) {
                        showToast(res.msg, 'success');
                        setTimeout(function() {
                            window.location.href = res.data.redirect || '/';
                        }, 1000);
                    } else {
                        showToast(res.msg, 'danger');
                    }
                },
                error: function() {
                    showToast('登录失败，请稍后重试', 'danger');
                }
            });
        });
    </script>
</body>
</html>
