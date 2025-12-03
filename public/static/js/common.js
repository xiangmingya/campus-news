/**
 * 校园新闻投稿系统 - 公共JavaScript
 */

$(document).ready(function() {
    // 初始化提示工具
    initTooltips();
    
    // 自动隐藏提示消息
    autoHideAlerts();
    
    // 确认删除对话框
    confirmDelete();
    
    // 图片懒加载
    lazyLoadImages();
});

/**
 * 初始化Bootstrap提示工具
 */
function initTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * 自动隐藏提示消息
 */
function autoHideAlerts() {
    $('.alert[data-auto-hide="true"]').each(function() {
        var $alert = $(this);
        setTimeout(function() {
            $alert.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    });
}

/**
 * 确认删除对话框
 */
function confirmDelete() {
    $('[data-confirm-delete]').on('click', function(e) {
        var message = $(this).data('confirm-message') || '确定要删除吗？此操作不可撤销。';
        if (!confirm(message)) {
            e.preventDefault();
            return false;
        }
    });
}

/**
 * 图片懒加载
 */
function lazyLoadImages() {
    if ('IntersectionObserver' in window) {
        var imageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    var image = entry.target;
                    image.src = image.dataset.src;
                    image.classList.remove('lazy');
                    imageObserver.unobserve(image);
                }
            });
        });

        document.querySelectorAll('img.lazy').forEach(function(img) {
            imageObserver.observe(img);
        });
    }
}

/**
 * 显示加载提示
 */
function showLoading(message) {
    message = message || '加载中...';
    var html = '<div class="loading-overlay" style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:9999;display:flex;align-items:center;justify-content:center;">' +
               '<div class="spinner-border text-light" role="status">' +
               '<span class="visually-hidden">' + message + '</span>' +
               '</div>' +
               '</div>';
    $('body').append(html);
}

/**
 * 隐藏加载提示
 */
function hideLoading() {
    $('.loading-overlay').remove();
}

/**
 * 显示Toast提示
 */
function showToast(message, type) {
    type = type || 'info';
    var bgClass = 'bg-' + type;
    var html = '<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999">' +
               '<div class="toast ' + bgClass + ' text-white" role="alert">' +
               '<div class="toast-body">' + message + '</div>' +
               '</div>' +
               '</div>';
    
    var $toast = $(html);
    $('body').append($toast);
    
    var toast = new bootstrap.Toast($toast.find('.toast')[0], {
        autohide: true,
        delay: 3000
    });
    toast.show();
    
    setTimeout(function() {
        $toast.remove();
    }, 3500);
}

/**
 * AJAX表单提交
 */
function submitForm(form, callback) {
    var $form = $(form);
    var url = $form.attr('action');
    var method = $form.attr('method') || 'POST';
    var data = $form.serialize();
    
    showLoading();
    
    $.ajax({
        url: url,
        type: method,
        data: data,
        dataType: 'json',
        success: function(res) {
            hideLoading();
            if (res.code === 200) {
                showToast(res.msg, 'success');
                if (callback) callback(res);
            } else {
                showToast(res.msg, 'danger');
            }
        },
        error: function() {
            hideLoading();
            showToast('请求失败，请稍后重试', 'danger');
        }
    });
}

/**
 * 格式化日期时间
 */
function formatDateTime(timestamp) {
    var date = new Date(timestamp * 1000);
    var year = date.getFullYear();
    var month = ('0' + (date.getMonth() + 1)).slice(-2);
    var day = ('0' + date.getDate()).slice(-2);
    var hour = ('0' + date.getHours()).slice(-2);
    var minute = ('0' + date.getMinutes()).slice(-2);
    var second = ('0' + date.getSeconds()).slice(-2);
    
    return year + '-' + month + '-' + day + ' ' + hour + ':' + minute + ':' + second;
}

/**
 * 格式化文件大小
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    var k = 1024;
    var sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    var i = Math.floor(Math.log(bytes) / Math.log(k));
    return (bytes / Math.pow(k, i)).toFixed(2) + ' ' + sizes[i];
}

/**
 * 复制到剪贴板
 */
function copyToClipboard(text) {
    var $temp = $('<textarea>');
    $('body').append($temp);
    $temp.val(text).select();
    document.execCommand('copy');
    $temp.remove();
    showToast('已复制到剪贴板', 'success');
}

/**
 * 防抖函数
 */
function debounce(func, wait) {
    var timeout;
    return function() {
        var context = this;
        var args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            func.apply(context, args);
        }, wait);
    };
}

/**
 * 节流函数
 */
function throttle(func, wait) {
    var previous = 0;
    return function() {
        var now = Date.now();
        var context = this;
        var args = arguments;
        if (now - previous > wait) {
            func.apply(context, args);
            previous = now;
        }
    };
}

/**
 * 验证邮箱格式
 */
function isValidEmail(email) {
    var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * 验证手机号格式
 */
function isValidPhone(phone) {
    var regex = /^1[3-9]\d{9}$/;
    return regex.test(phone);
}

/**
 * 表单验证
 */
function validateForm(form) {
    var isValid = true;
    var $form = $(form);
    
    $form.find('[required]').each(function() {
        var $input = $(this);
        var value = $input.val().trim();
        
        if (!value) {
            $input.addClass('is-invalid');
            isValid = false;
        } else {
            $input.removeClass('is-invalid');
        }
    });
    
    return isValid;
}

/**
 * 字符串截断
 */
function truncate(str, length) {
    if (str.length <= length) {
        return str;
    }
    return str.substring(0, length) + '...';
}

/**
 * 转义HTML
 */
function escapeHtml(text) {
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}
