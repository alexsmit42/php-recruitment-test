<?php

namespace Snowdog\DevTest\Controller;

class RegisterFormAction
{
    public function execute() {
        if ($_SESSION['login']) {
            require __DIR__ . '/../view/403.phtml';
        } else {
            require __DIR__ . '/../view/register.phtml';
        }
    }
}