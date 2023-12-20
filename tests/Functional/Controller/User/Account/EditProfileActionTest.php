<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\Account;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Test\ContainerRepositoryTrait;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @see EditProfileAction
 */
final class EditProfileActionTest extends WebTestCase
{
    use RefreshDatabaseTrait;
    use KernelTrait;
    use ContainerRepositoryTrait;

    private const ROUTE = '/fr/mon-compte/editer';

    public function testProfileAction(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);

        $client->request('GET', self::ROUTE);
        self::assertResponseIsSuccessful();

        $imageName = 'apes.png';
        $image = realpath(__DIR__.'/../../../../Fixtures/images/'.$imageName);
        $uploadedFile = new UploadedFile((string) $image, $imageName);

        $form = $client->getCrawler()->selectButton('templates.pages.user.account.edit_profile.submit')->form();
        $client->submit($form, [
            $form->getName().'[firstname]' => 'John',
            $form->getName().'[lastname]' => 'Doe',
            $form->getName().'[avatar]' => $uploadedFile,
            $form->getName().'[category]' => TestReference::CATEGORY_OBJECT_1,
            $form->getName().'[description]' => 'description test',
            $form->getName().'[phone][country]' => 'FR',
            $form->getName().'[phone][number]' => '',
            $form->getName().'[smsNotifications]' => false,
        ]);

        $container = $client->getContainer();
        $repo = $container->get(UserRepository::class);

        /** @var User $editedUser */
        $editedUser = $repo->find(TestReference::USER_16);
        self::assertNull($editedUser->getPhoneNumber());

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }

    public function testPlaceProfileAction(): void
    {
        $client = self::createClient();
        $this->loginAsPlaceApes($client);

        $client->request('GET', self::ROUTE);
        self::assertResponseIsSuccessful();

        $imageName = 'apes.png';
        $image = realpath(__DIR__.'/../../../../Fixtures/images/'.$imageName);
        $uploadedFile = new UploadedFile((string) $image, $imageName);

        $form = $client->getCrawler()->selectButton('templates.pages.user.account.edit_profile.submit')->form();
        $client->submit($form, [
            $form->getName().'[name]' => 'Groupe 1',
            $form->getName().'[avatar]' => $uploadedFile,
            $form->getName().'[schedule]' => 'du lundi au vendredi',
            $form->getName().'[phone][country]' => 'FR',
            $form->getName().'[phone][number]' => '06 10 10 10 10',
            $form->getName().'[smsNotifications]' => true,
        ]);

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }

    public function testPlaceProfileActionWithoutName(): void
    {
        $client = self::createClient();
        $this->loginAsPlaceApes($client);

        $client->request('GET', self::ROUTE);
        self::assertResponseIsSuccessful();

        $form = $client->getCrawler()->selectButton('templates.pages.user.account.edit_profile.submit')->form();
        $client->submit($form, [
            $form->getName().'[name]' => '',
            $form->getName().'[schedule]' => 'du lundi au vendredi',
            $form->getName().'[phone][number]' => '',
            $form->getName().'[smsNotifications]' => true,
        ]);
        self::assertResponseIsUnprocessable();
        self::assertSelectorTextContains('body', 'account_create.name.empty.error');
    }
}
