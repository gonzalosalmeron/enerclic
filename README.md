## 🎮 Uso de la App

Instala dependencias de composer:
```
composer install
```

Clona el .env.exaple como .env y establece la conexión con la BD:
```
DB_CONNECTION=mysql
DB_HOST=XXXXX
DB_PORT=XXXXX
DB_DATABASE=XXXXX
DB_USERNAME=XXXXX
DB_PASSWORD=XXXXX
```

Pobla la base de datos:
```
php artisan migrate
```

Inicia la app:
```
php artisan serve
```
