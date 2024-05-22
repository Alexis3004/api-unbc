<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Cómo realizar el despliegue

Esta es una api con sanctum que se comunica con un SPA en Vue

## Instalar librerías
```
composer install
```

## Crear el archivo .env y configurar las variables de entorno

Se debe tomar como ejemplo el archivo .env.example

## Ejecutar las migraciones
```
php artisan migrate --seed
```

## Levantar el servicio
puede levantar el servicio con servidores web como apache, Nginx o con artisan
```
php artisan serve
```
