<?php

namespace App\Framework;

/**
 * Dead-simple template renderer: extracts variables and includes a plain-PHP
 * template from templates/, capturing its output. No template engine.
 *
 * Templates render a partial with:  <?php $this->partial('check'); ?>
 * (inside a template, $this is the View instance.)
 */
class View
{
    private string $templateDir;

    public function __construct(string $templateDir)
    {
        $this->templateDir = rtrim($templateDir, '/');
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $name, array $data = []): string
    {
        $view = new self(BASE_PATH . '/templates');

        return $view->renderTemplate($name, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function renderTemplate(string $name, array $data = []): string
    {
        $file = $this->templateDir . '/' . $name . '.php';
        if (! is_file($file)) {
            throw new \RuntimeException('View not found: ' . $name);
        }

        extract($data, EXTR_SKIP);
        ob_start();
        include $file;

        return ob_get_clean();
    }

    /**
     * Include a partial from templates/partials/ (used from within templates).
     */
    public function partial(string $name): void
    {
        include $this->templateDir . '/partials/' . $name . '.php';
    }

    /**
     * Escape a value for safe HTML output.
     *
     * @param mixed $value
     */
    public function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
