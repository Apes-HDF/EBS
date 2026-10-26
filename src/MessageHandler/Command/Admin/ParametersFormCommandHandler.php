<?php

declare(strict_types=1);

namespace App\MessageHandler\Command\Admin;

use App\Message\Command\Admin\ParametersFormCommand;
use App\Repository\ConfigurationRepository;
use App\Repository\GroupRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ParametersFormCommandHandler
{
    public function __construct(
        private ConfigurationRepository $configurationRepository,
        private GroupRepository $groupRepository,
    ) {
    }

    /**
     * We consider the message valid at this point.
     */
    public function __invoke(ParametersFormCommand $message): void
    {
        $configuration = $this->configurationRepository->getInstanceConfigurationOrCreate();
        $configuration->setConfiguration($message->toJsonArray());
        $this->configurationRepository->save($configuration, true);

        if (!$configuration->getServicesEnabled()) {
            $groups = $this->groupRepository->findAll();
            $this->groupRepository->disableServicesForAllGroups($groups);
        }
    }
}
