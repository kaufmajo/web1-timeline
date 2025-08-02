<?php

declare(strict_types=1);

namespace App\Plates\Extension;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use League\Plates\Template\Template;

class ColorExtension implements ExtensionInterface
{
    protected Engine $engine;

    public Template $template;

    private array $colors = [
        [
            'btn-main' => 'btn-primary',
            'text-main' => 'text-primary',
            'text-main-emphasis' => 'text-primary-emphasis',
            'text-bg-main' => 'text-bg-primary',
            'text-bg-today' => 'text-bg-primary',
            'text-bg-indicator' => 'bg-primary-subtle text-primary-emphasis',
            'bg-main-subtle' => 'bg-primary-subtle',
            'link-main' => 'link-light',
        ],
        [
            'btn-main' => 'btn-secondary',
            'text-main' => 'text-secondary',
            'text-main-emphasis' => 'text-secondary-emphasis',
            'text-bg-main' => 'text-bg-secondary',
            'text-bg-today' => 'text-bg-secondary',
            'text-bg-indicator' => 'bg-secondary-subtle text-secondary-emphasis',
            'bg-main-subtle' => 'bg-secondary-subtle',
            'link-main' => 'link-light',
        ],
        [
            'btn-main' => 'btn-success',
            'text-main' => 'text-success',
            'text-main-emphasis' => 'text-success-emphasis',
            'text-bg-main' => 'text-bg-success',
            'text-bg-today' => 'text-bg-success',
            'text-bg-indicator' => 'bg-success-subtle text-success-emphasis',
            'bg-main-subtle' => 'bg-success-subtle',
            'link-main' => 'link-light',
        ],
        [
            'btn-main' => 'btn-danger',
            'text-main' => 'text-danger',
            'text-main-emphasis' => 'text-danger-emphasis',
            'text-bg-main' => 'text-bg-danger',
            'text-bg-today' => 'text-bg-danger',
            'text-bg-indicator' => 'bg-danger-subtle text-danger-emphasis',
            'bg-main-subtle' => 'bg-danger-subtle',
            'link-main' => 'link-light',
        ],
        [
            'btn-main' => 'btn-warning',
            'text-main' => 'text-warning',
            'text-main-emphasis' => 'text-warning-emphasis',
            'text-bg-main' => 'text-bg-warning',
            'text-bg-today' => 'text-bg-warning',
            'text-bg-indicator' => 'bg-warning-subtle text-warning-emphasis',
            'bg-main-subtle' => 'bg-warning-subtle',
            'link-main' => 'link-dark',
        ],
        [
            'btn-main' => 'btn-info',
            'text-main' => 'text-info',
            'text-main-emphasis' => 'text-info-emphasis',
            'text-bg-main' => 'text-bg-info',
            'text-bg-today' => 'text-bg-info',
            'text-bg-indicator' => 'bg-info-subtle text-info-emphasis',
            'bg-main-subtle' => 'bg-info-subtle',
            'link-main' => 'link-dark',
        ],
        [
            'btn-main' => 'btn-light',
            'text-main' => 'text-light-emphasis',
            'text-main-emphasis' => 'text-light-emphasis',
            'text-bg-main' => 'text-bg-light',
            'text-bg-today' => 'text-bg-light border border-dark-subtle',
            'text-bg-indicator' => 'text-bg-light border border-dark-subtle',
            'bg-main-subtle' => 'bg-light-subtle',
            'link-main' => 'link-dark',
        ],
        [
            'btn-main' => 'btn-dark',
            'text-main' => 'text-dark-emphasis',
            'text-main-emphasis' => 'text-dark-emphasis',
            'text-bg-main' => 'text-bg-dark',
            'text-bg-today' => 'text-bg-dark',
            'text-bg-indicator' => 'text-bg-dark',
            'bg-main-subtle' => 'bg-dark-subtle',
            'link-main' => 'link-light',
        ],
    ];

    public function register(Engine $engine): void
    {
        $this->engine = $engine;

        $engine->registerFunction('color', [$this, 'color']);
    }

    public function color(): array
    {
        $data  = $this->template->data();

        return $this->colors[$data['color']];
    }
}
