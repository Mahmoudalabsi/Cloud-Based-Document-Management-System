# استخدم صورة PHP-FPM الرسمية
FROM php:8.2-fpm

# تعيين دليل العمل داخل الحاوية
WORKDIR /var/www

# تثبيت تبعيات النظام
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    git \
    curl \
    libonig-dev \
    libxml2-dev \
    libsqlite3-dev \
    zip \
    unzip \
    libicu-dev \  # أضف هذه المكتبة لتمكين intl
    && rm -rf /var/lib/apt/lists/* # قم بتنظيف ذاكرة التخزين المؤقت لتقليل حجم الصورة

# تثبيت امتدادات PHP
# تأكد هنا من وجود pdo_sqlite وإزالة pdo_mysql إذا لم تعد بحاجته
RUN docker-php-ext-install \
    pdo_sqlite \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    curl \
    intl

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# نسخ ملفات التطبيق
COPY . /var/www

# إعداد صلاحيات المجلدات اللازمة لـ Laravel
RUN chown -R www-data:www-data /var/www/storage \
    && chown -R www-data:www-data /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

# تشغيل Composer install
RUN composer install --no-dev --optimize-autoloader

# كاش Laravel للـ config والـ routes
RUN php artisan config:cache
RUN php artisan artisan route:cache

# أمر البدء (يمكن أن يختلف حسب إعدادات Render الخاصة بك)
# CMD ["php-fpm"]