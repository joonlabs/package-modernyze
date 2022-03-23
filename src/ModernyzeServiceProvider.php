<?php

namespace Modernyze;

use Illuminate\Support\ServiceProvider;

class ModernyzeServiceProvider extends ServiceProvider
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        $this->app->singleton("Modernyze", function () {
            return new ModernyzeManager();
        });
    }
}