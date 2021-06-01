<?php
namespace Codificar\PushNotification;
use Illuminate\Support\ServiceProvider;

class PushNotificationServiceProvider extends ServiceProvider {

    public function boot()
    {

        // Load routes (carrega as rotas)
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');

        // Load laravel views (Carregas as views do Laravel, blade)
        $this->loadViewsFrom(__DIR__.'/resources/views', 'push_notification');

        // Load Migrations (Carrega todas as migrations)
        $this->loadMigrationsFrom(__DIR__.'/Database/migrations');

        // Load trans files (Carrega tos arquivos de traducao) 
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'push');

        // Publish the VueJS files inside public folder of main project (Copia os arquivos do vue minificados dessa biblioteca para pasta public do projeto que instalar essa lib)
        $this->publishes([
            __DIR__.'/../public/js' => public_path('vendor/codificar/push_notification'),
        ], 'public_vuejs_libs');
    }

    public function register()
    {

    }
}
?>