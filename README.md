# চেয়ারঘর

বাংলা Laravel 13 + Bootstrap 5 ভিত্তিক chair eCommerce storefront। এখানে product browsing, search/category filtering, session cart, validated checkout, atomic stock update, order tracking এবং order-status dashboard রয়েছে।

## চালু করার নিয়ম

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

MySQL-এ `chairghor` database তৈরি করে `.env`-এ নিচের মান বসান:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chairghor
DB_USERNAME=root
DB_PASSWORD=
```

এরপর:

```bash
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

সাইট: `http://127.0.0.1:8000`  
ডেমো dashboard: `http://127.0.0.1:8000/admin`

Production-এ dashboard route-এ অবশ্যই Laravel authentication/authorization middleware যোগ করুন। Seed data-র Unsplash image URLগুলো `DatabaseSeeder.php` থেকে নিজের product photography দিয়ে পরিবর্তন করা যাবে।

## পরীক্ষা

```bash
php artisan route:list
php artisan view:cache
php artisan test
```
