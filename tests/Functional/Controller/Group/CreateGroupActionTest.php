<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Group;

use App\Test\ContainerRepositoryTrait;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\String\u;

/**
 * @see CreateGroupAction
 */
final class CreateGroupActionTest extends WebTestCase
{
    use RefreshDatabaseTrait;
    use KernelTrait;
    use ContainerRepositoryTrait;

    public function testNewGroupSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);
        $crawler = $client->request('GET', '/fr/mon-compte/groupes/creer-mon-groupe');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('create_group_form_submit')->form();
        $client->submit($form, [
             $form->getName().'[name]' => 'Groupe 1',
             $form->getName().'[type]' => 'public',
             $form->getName().'[servicesEnabled]' => false,
         ]);
        self::assertResponseRedirects();
        self::assertTrue(u($client->getResponse()->headers->get('Location'))->startsWith('http://localhost/admin'));

        $repo = $this->getGroupRepository();
        self::assertCount(TestReference::GROUP_COUNT + 1, $repo->findAll());
        $repo = $this->getUserGroupRepository();
        self::assertCount(TestReference::USER_GROUP_COUNT + 1, $repo->findAll());
    }

    public function testNewGroupNotAllowed(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);
        $config = $this->getConfigurationRepository()->getInstanceConfigurationOrCreate();
        $config->setGroupsCreationModeToAdminOnly();

        $client->request('GET', '/fr/mon-compte/groupes/creer-mon-groupe');
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
