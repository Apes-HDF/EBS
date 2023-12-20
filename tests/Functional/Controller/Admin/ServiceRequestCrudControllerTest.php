<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\ServiceRequestCrudController;
use App\Test\ContainerRepositoryTrait;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class ServiceRequestCrudControllerTest extends WebTestCase
{
    use KernelTrait;
    use RefreshDatabaseTrait;
    use ContainerRepositoryTrait;

    /**
     * @see ServiceRequestCrudController
     */
    public function testController(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        // list + filter
        $filters = 'filters[status]=new';
        $client->request('GET', sprintf(TestReference::ADMIN_URL, 'index', ServiceRequestCrudController::class.'&'.$filters));
        self::assertResponseIsSuccessful();

        // detail
        $client->request('GET', sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'detail', ServiceRequestCrudController::class, TestReference::SERVICE_REQUEST_1));
        self::assertResponseIsSuccessful();

        // conversation
        $client->request('GET', sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'conversation', ServiceRequestCrudController::class, TestReference::SERVICE_REQUEST_1));
        self::assertResponseIsSuccessful();
    }

    public function testConversationPageWithAccessDenied(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        $configurationRepo = $this->getConfigurationRepository();
        $config = $configurationRepo->getInstanceConfiguration()?->getConfiguration();
        $config['confidentiality']['confidentialityConversationAdminAccess'] = false;

        $configurationRepo->getInstanceConfiguration()?->setConfiguration($config);

        // conversation page not allowed if confidentiality conversation admin access is set to false
        $client->request('GET', sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'conversation', ServiceRequestCrudController::class, TestReference::SERVICE_REQUEST_1));
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
