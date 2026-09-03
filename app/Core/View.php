<?php

namespace App\Core;

class View
{
    private static array $shared = [];

    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    public static function render(string $view, array $data = [], ?string $layout = null): void
    {
        extract(array_merge(self::$shared, $data));
        ob_start();
        $viewFile = BASE_PATH . '/views/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($viewFile)) {
            throw new \RuntimeException("View not found: {$view}");
        }
        require $viewFile;
        $content = ob_get_clean();

        if ($layout) {
            $layoutFile = BASE_PATH . '/views/layouts/' . $layout . '.php';
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    public static function partial(string $partial, array $data = []): void
    {
        extract(array_merge(self::$shared, $data));
        require BASE_PATH . '/views/' . str_replace('.', '/', $partial) . '.php';
    }
}
