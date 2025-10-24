FROM php:8.2-fpm

# 必要なパッケージをインストール
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Composerをインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 作業ディレクトリを設定
WORKDIR /var/www/html

# アプリケーションファイルをコピー（apiディレクトリの中身を/var/www/htmlにコピー）
COPY . /var/www/html

# Composerの依存関係をインストール（fakerも含める）
RUN composer install --optimize-autoloader

# .envファイルを削除（環境変数を優先）
RUN rm -f .env

# 権限設定
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# ポート設定
EXPOSE 8000

# 起動スクリプトを作成
RUN echo '#!/bin/bash\n\
set -e\n\
echo "Waiting for database..."\n\
sleep 5\n\
echo "Running migrations..."\n\
php artisan migrate --force || true\n\
echo "Running seeders..."\n\
php artisan db:seed --force || true\n\
echo "Starting server..."\n\
php artisan serve --host=0.0.0.0 --port=$PORT\n\
' > /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

# Artisan serveでアプリケーションを起動
CMD ["/usr/local/bin/start.sh"]
