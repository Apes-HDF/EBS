<?php

declare(strict_types=1);

namespace App\Controller;

trait FlashTrait
{
    public function addFlashSuccess(string $message): void
    {
        $this->addFlash('success', $message);
    }

    public function addFlashWarning(string $message): void
    {
        $this->addFlash('warning', $message);
    }
}
