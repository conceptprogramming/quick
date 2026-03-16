<?php
namespace Controllers;

class PublicController
{
    public function faq(): void
    {
        require __DIR__ . '/../../views/public/faq.php';
    }

    public function terms(): void
    {
        require __DIR__ . '/../../views/public/terms.php';
    }

    public function privacy(): void
    {
        require __DIR__ . '/../../views/public/privacy.php';
    }

    public function support(): void
    {
        require __DIR__ . '/../../views/public/support.php';
    }
}
