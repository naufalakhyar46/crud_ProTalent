"# crud_ProTalent" 

Naufal Akhyar
Requirement :
- PHP 8.0 >=
- Node v18.12.1

Cara install :
1. crud-test-backend
- cp .env.example .env
- composer install
- php artisan key:generate
- php artisan migrate
- php artisan serve

2. crud-test-vue
- npm install
- sesuaikan VITE_VUE_APP_API_URL dengan ip server yang jalan di file .env (default: "http://localhost:8000/api")
- npm run dev

Terima Kasih

