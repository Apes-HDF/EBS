<?php

declare(strict_types=1);

namespace App\MessageHandler\Query\Admin;

use App\Message\Command\Admin\ParametersFormCommand;
use App\Message\Query\Admin\ParametersFormQuery;
use App\Repository\ConfigurationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ParametersFormQueryHandler
{
    public function __construct(
        private readonly ConfigurationRepository $configurationRepository,
    ) {
    }

    public function __invoke(ParametersFormQuery $message): ParametersFormCommand
    {
        $cfg = $this->configurationRepository->getInstanceConfigurationOrCreate();
        $parametersForm = (new ParametersFormCommand());
        $parametersForm->hydrate($cfg);

        return $parametersForm;
    }
}
