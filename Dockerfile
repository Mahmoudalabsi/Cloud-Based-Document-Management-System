FROM php:8.2-fpm # جيد، تستخدم صورة PHP FPM الرسمية

# تثبيت تبعيات النظام
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip unzip git curl libzip-dev libpq-dev libsqlite3-dev # ممتاز، قمت بتضمين كل ما هو ضروري، بما في ذلك libpq-dev إذا كنت تستخدم PostgreSQL.

# تثبيت امتدادات PHP
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd # ممتاز، أضفت gd وهو حيوي لمعالجة الصور.

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer # هذه هي الطريقة الصحيحة لتثبيت Composer عالميًا.

# تعيين دليل العمل
WORKDIR /var/www # [ملاحظة مهمة هنا] هذا جيد بشكل عام، ولكن Laravel يفضل عادةً أن تكون الملفات في /var/www/html داخل مجلد الويب الخاص بالخادم. إذا كنت تستخدم Nginx/Apache مع php-fpm، فسيتم عادةً توجيه الجذر إلى /var/www/html/public. ومع ذلك، لأنك تستخدم 'php artisan serve'، قد يعمل /var/www بشكل مباشر.

# نسخ ملفات المشروع
COPY . /var/www # جيد، يتم نسخ جميع الملفات إلى دليل العمل.

# تثبيت تبعيات PHP
RUN composer install --no-interaction --prefer-dist --optimize-autoloader # ممتاز، هذه هي أفضل الأعلام لتثبيت تبعيات الإنتاج.

# الأذونات
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage # [ملاحظة مهمة هنا]
    # 1. المجلد 'bootstrap/cache' يحتاج أيضًا إلى أذونات الكتابة: 'chmod -R 775 /var/www/bootstrap/cache'
    # 2. تغيير ملكية 'www-data:www-data' لـ '/var/www' بالكامل أمر جيد.
    # 3. 'chmod -R 755' لـ '/var/www/storage' يعطي أذونات القراءة والتنفيذ للمستخدمين الآخرين، وهو آمن.
    #    بالنسبة لـ 'storage' و 'bootstrap/cache'، غالبًا ما تحتاج إلى 775 أو 777 إذا واجهت مشاكل كتابة في الإنتاج، لكن 755 قد يعمل مع chown www-data:www-data.

# عرض المنفذ 8000
EXPOSE 8000
# جيد، هذا هو المنفذ الذي سيستمع إليه 'php artisan serve'.

# بدء تشغيل خادم Laravel
CMD php artisan serve --host=0.0.0.0 --port=8000 # [ملاحظة مهمة جداً]
    # 1. على Render، يجب أن تستخدم متغير البيئة $PORT الذي يوفره Render، وليس منفذًا ثابتًا.
    #    يجب أن يكون الأمر: CMD php artisan serve --host=0.0.0.0 --port=$PORT
    # 2. قد تحتاج إلى تشغيل الـ migrations هنا أيضًا، أو كجزء من أمر البناء/البدء في Render.
    #    مثال: CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT