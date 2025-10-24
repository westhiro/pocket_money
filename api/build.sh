#!/usr/bin/env bash
# Renderデプロイ用ビルドスクリプト

set -o errexit

# Composer依存関係のインストール
composer install --no-dev --optimize-autoloader

# キャッシュのクリア
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# マイグレーションの実行
php artisan migrate --force

# シーダーの実行（初回デプロイ時のみ、2回目以降はコメントアウトを推奨）
php artisan db:seed --force

# キャッシュの最適化
php artisan config:cache
php artisan route:cache
php artisan view:cache
