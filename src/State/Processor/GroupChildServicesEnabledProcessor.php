<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Group;
use App\Repository\GroupRepository;
use Webmozart\Assert\Assert;

class GroupChildServicesEnabledProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly GroupRepository $groupRepository
    ) {
    }

    /**
     * @param array<mixed> $uriVariables
     * @param array<mixed> $context
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Group
    {
        Assert::isInstanceOf($data, Group::class);
        $this->groupRepository->disableServicesForChildGroup($data);

        return $data;
    }
}
