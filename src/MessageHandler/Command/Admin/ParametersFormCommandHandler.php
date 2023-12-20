<?php

declare(strict_types=1);

namespace App\MessageHandler\Command\Admin;

use App\Message\Command\Admin\ParametersFormCommand;
use App\Repository\ConfigurationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ParametersFormCommandHandler
{
    public function __construct(
        private readonly ConfigurationRepository $configurationRepository
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
    }
}
