<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Group;
use App\Repository\GroupRepository;

/**
 * @implements ProcessorInterface<Group,Group>
 */
class GroupChildServicesEnabledProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly GroupRepository $groupRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Group
    {
        $this->groupRepository->disableServicesForChildGroup($data);

        return $data;
    }
}
