<?php

declare(strict_types=1);

namespace App\Form;

use interop\container\containerinterface;
use Laminas\Form\Form;

class LoginFormFactory extends Form
{
    public function __invoke(containerinterface $container): \App\Form\LoginForm
    {
        $form = new LoginForm();
        $form->init();

        return $form;
    }
}
