<?php

declare(strict_types=1);

namespace App\Tests\Integration\Mailer;

use App\Mailer\AppMailer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class AppMailerTest extends KernelTestCase
{
    public function testSendLogicException(): void
    {
        self::bootKernel();
        /** @var AppMailer $appMailer */
        $appMailer = self::getContainer()->get(AppMailer::class);
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No email found to process the App\Mailer\AppMailer email');
        $appMailer->send(AppMailer::class, []);
    }
}
