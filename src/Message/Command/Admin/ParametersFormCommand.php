<?php

declare(strict_types=1);

namespace App\Message\Command\Admin;

use App\Entity\Configuration;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Dto for the ParametersFormType.
 *
 * @see ParametersFormType
 */
final class ParametersFormCommand extends AbstractFormCommand
{
    final public const ONLY_ADMIN = 'only_admin';
    final public const ALL = 'all';

    // global section —————————————————————————————————————————————
    #[Assert\NotBlank()]
    public ?string $globalName = null;

    #[Assert\Type('bool')]
    public bool $globalServicesEnabled = true;

    #[Assert\Type('bool')]
    public bool $globalPaidMembership = false;

    // notificationsSender section —————————————————————————————————————————————
    #[Assert\Email()]
    #[Assert\NotBlank()]
    public ?string $notificationsSenderEmail = null;

    #[Assert\NotBlank()]
    public ?string $notificationsSenderName = null;

    // contact section —————————————————————————————————————————————————————————
    #[Assert\Type('bool')]
    public bool $contactFormEnabled = true;

    #[Assert\Email()]
    #[Assert\NotBlank()]
    public ?string $contactFormEmail = null;

    // groups section ——————————————————————————————————————————————————————————
    #[Assert\Type('bool')]
    public bool $groupsEnabled = true;

    #[Assert\Choice([self::ALL, self::ONLY_ADMIN])]
    #[Assert\NotBlank()]
    public string $groupsCreationMode = self::ALL;

    #[Assert\Type('bool')]
    public bool $groupsPaying = true;

    // confidentiality section ——————————————————————————————————————————————————————————
    /** if true, admins have access to conversations */
    #[Assert\Type('bool')]
    public bool $confidentialityConversationAdminAccess = true;

    /**
     * @return array<string>
     */
    protected function getSections(): array
    {
        return [
            'global',
            'notificationsSender',
            'contact',
            'groups',
            'articles',
            'confidentiality',
        ];
    }

    /**
     * Hydrate the DTO from the database settings.
     *
     * @todo Should be reverse tranform ?
     */
    public function hydrate(Configuration $configuration): self
    {
        $instanceConfiguration = $configuration->getConfiguration();
        foreach (array_keys(get_class_vars($this::class)) as $classVar) {
            $configValue = $instanceConfiguration[$this->getSection($classVar)][$classVar] ?? null;
            if ($configValue === null) {
                continue;
            }
            $this->{$classVar} = $configValue; // @phpstan-ignore-line
        }

        return $this;
    }
}
