<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
      if (empty(config('app.key'))) {

        try {
            $parsed = \Dotenv\Dotenv::parse(
                file_get_contents(app()->environmentFilePath())
            );
        
            \Illuminate\Support\Facades\Log::error('PTT DOTENV PARSE', [
                'parse_success' => true,
                'parsed_app_key_present' => !empty($parsed['APP_KEY'] ?? null),
                'parsed_keys_count' => count($parsed),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('PTT DOTENV PARSE ERROR', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }

        $envPath = app()->environmentFilePath();

        $envContent = is_readable($envPath)
            ? file_get_contents($envPath)
            : false;
    
        \Illuminate\Support\Facades\Log::error('PTT APP_KEY DIAGNOSTYKA', [
            'uri' => request()->getRequestUri(),
            'pid' => getmypid(),
    
            'env_path' => $envPath,
            'env_exists' => is_file($envPath),
            'env_readable' => is_readable($envPath),
    
            'env_size' => is_file($envPath) ? filesize($envPath) : null,
            'env_read_success' => $envContent !== false,
    
            'env_has_app_key_line' => $envContent !== false
                && preg_match('/^APP_KEY\s*=/m', $envContent) === 1,
    
            'env_key_present' => !empty(env('APP_KEY')),
            'config_key_present' => !empty(config('app.key')),
    
            'server_key_present' => !empty($_SERVER['APP_KEY'] ?? null),
            'environment_key_present' => !empty($_ENV['APP_KEY'] ?? null),
    
            'config_cached' => app()->configurationIsCached(),
            'server_key_exists' => array_key_exists('APP_KEY', $_SERVER),
            'server_key_empty' => empty($_SERVER['APP_KEY']),
            'env_key_exists' => array_key_exists('APP_KEY', $_ENV),
            'env_key_empty' => empty($_ENV['APP_KEY']),
            'getenv_key_exists' => getenv('APP_KEY') !== false,
            'getenv_key_empty' => !getenv('APP_KEY'),
        ]);
      }
    } 
}