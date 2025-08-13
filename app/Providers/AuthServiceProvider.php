<?php

namespace App\Providers;

use App\Models\Variable;
use App\Policies\VariablePolicy;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{

    protected $policies = [
        Variable::class => VariablePolicy::class, 
    ];
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
        
        $this->register();
    }
}
