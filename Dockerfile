# 基于官方 PHP-FPM 镜像，使用 Alpine 版本以减小镜像大小
FROM php:8.3-fpm-alpine

# 安装 Nginx
# --no-cache 选项用于在安装后清理缓存，进一步减小镜像大小
RUN apk add --no-cache nginx

# 复制自定义的 Nginx 配置文件到镜像中
COPY nginx.conf /etc/nginx/nginx.conf

# 将本地的 html 目录中的所有应用代码复制到 Nginx 的 Web 根目录
COPY index.php /var/www/html/

# 设置 Web 根目录的权限，确保 www-data 用户（PHP-FPM 运行的用户）可以读写
RUN chown -R www-data:www-data /var/www/html

# 暴露容器的 80 端口，表示 Nginx 将在此端口监听传入的请求
EXPOSE 80

# 容器启动时执行的命令：
# 首先启动 PHP-FPM 服务 (& 允许其在后台运行)
# 然后启动 Nginx，并使用 'daemon off;' 参数使其在前台运行
# 在 Docker 容器中，主进程必须在前台运行，否则容器会立即退出
CMD ["/bin/sh", "-c", "php-fpm -F & nginx -g 'daemon off;'"]
