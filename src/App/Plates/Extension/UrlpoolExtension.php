<?php

declare(strict_types=1);

namespace App\Plates\Extension;

use App\Service\UrlpoolService;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use League\Plates\Template\Template;

class UrlpoolExtension implements ExtensionInterface
{
    protected Engine $engine;

    public Template $template;

    public function __construct() {}

    public function register(Engine $engine): void
    {
        $this->engine = $engine;

        $engine->registerFunction('urlpool', [$this, 'urlpool']);
    }

    public function urlpool(): UrlpoolService
    {
        return $this->template->data()['urlpool'];
    }
}
