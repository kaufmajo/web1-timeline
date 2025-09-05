<?php

declare(strict_types=1);

namespace App\Plates\Extension;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use League\Plates\Template\Template;

class QuoteExtension implements ExtensionInterface
{
    protected Engine $engine;

    public Template $template;

    public function register(Engine $engine): void
    {
        $this->engine = $engine;

        $engine->registerFunction('quote', [$this, 'quote']);
    }

    public function quote(): string
    {
        return 'Character, not circumstances makes the man.';
    }
}
