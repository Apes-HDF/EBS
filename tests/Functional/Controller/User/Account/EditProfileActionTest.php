<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\Account;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Test\ContainerRepositoryTrait;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Doctrine\ORM\EntityManagerInterface;
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
            $form->getName().'[phone][number]' => '634563424',
            $form->getName().'[smsNotifications]' => false,
        ]);

        $container = $client->getContainer();
        $repo = $container->get(UserRepository::class);

        /** @var User $editedUser */
        $editedUser = $repo->find(TestReference::USER_16);
        self::assertNotNull($editedUser->getPhoneNumber());

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

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideScriptNames(): iterable
    {
        yield 'renamed in .png' => ['avatar.png'];
        yield 'php extension' => ['avatar.php'];
    }

    /**
     * The avatar must be an image: a script is refused, whatever its name, and
     * the current avatar is kept.
     *
     * @dataProvider provideScriptNames
     */
    public function testAvatarMustBeAnImage(string $clientName): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);
        /** @var User $user */
        $user = $this->getUserRepository()->find(TestReference::USER_16);
        $avatar = $user->getAvatar();

        $script = tempnam(sys_get_temp_dir(), 'avatar');
        file_put_contents((string) $script, '<?php echo "test";');
        $uploadedFile = new UploadedFile((string) $script, $clientName);

        $client->request('GET', self::ROUTE);
        $form = $client->getCrawler()->selectButton('templates.pages.user.account.edit_profile.submit')->form();
        $client->submit($form, [
            $form->getName().'[avatar]' => $uploadedFile,
            $form->getName().'[category]' => TestReference::CATEGORY_OBJECT_1,
            $form->getName().'[phone][country]' => 'FR',
            $form->getName().'[phone][number]' => '634563424',
        ]);
        self::assertResponseIsUnprocessable();
        self::assertSelectorTextContains('body', 'validator.extensions_message');

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        /** @var User $user */
        $user = $this->getUserRepository()->find(TestReference::USER_16);
        self::assertSame($avatar, $user->getAvatar());
        @unlink((string) $script);
    }
}
