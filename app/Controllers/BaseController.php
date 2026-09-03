<?php

namespace App\Controllers;

use App\App;
use App\Core\View;

abstract class BaseController
{
    protected App $app;

    public function __construct()
    {
        $this->app = App::get();
    }

    protected function settings(): array
    {
        return $this->app->settings()->all();
    }
}
