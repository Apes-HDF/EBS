<?php

declare(strict_types=1);

namespace App\Entity;

use App\Controller\Admin\AdministratorCrudController;
use App\Controller\Admin\PlaceCrudController;
use App\Controller\Admin\UserCrudController;
use App\Doctrine\Behavior\TimestampableEntity;
use App\Doctrine\Listener\UserListener;
use App\Doctrine\Manager\UserManager;
use App\Enum\User\UserType;
use App\Form\Type\Security\AccountCreateStep1FormType;
use App\Form\Type\Security\AccountCreateStep2FormType;
use App\Form\Type\User\ChangeLoginFormType;
use App\Form\Type\User\ChangePasswordFormType;
use App\Form\Type\User\EditProfileFormType;
use App\Repository\UserRepository;
use App\Validator\Constraints\User\MembershipPaid;
use App\Validator\Constraints\User\UniqueUser;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

use function Symfony\Component\String\u;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Index(columns: ['type'])]
#[ORM\Index(columns: ['email'])]
#[ORM\Index(columns: ['confirmation_token'])]
#[ORM\Index(columns: ['lost_password_token'])]
#[ORM\Table(name: '`user`')] // we also need escaping here
#[ORM\EntityListeners([UserListener::class])]
#[UniqueUser(groups: [AccountCreateStep1FormType::class, ChangeLoginFormType::class])]
#[UniqueEntity('email', groups: ['Default'])]
#[MembershipPaid]
class User implements UserInterface, PasswordAuthenticatedUserInterface, ImageInterface, EquatableInterface
{
    use UserConfirmationTrait;
    use UserLostPasswordTrait;
    use TimestampableEntity;

    final public const ROLE_USER = 'ROLE_USER';
    final public const ROLE_ADMIN = 'ROLE_ADMIN';
    final public const ROLE_GROUP_ADMIN = 'ROLE_GROUP_ADMIN';
    final public const MEMBERSHIP_PAID = 'MEMBERSHIP_PAID';

    private const EMAIL_MAX_LENGTH = 180;
    private const NAME_LENGTH = 180;
    private const DESCRIPTION_LENGTH = 255;
    private const PHONE_LENGTH = 15;
    private const SCHEDULE_LENGTH = 180;

    /**
     * Generates a V6 uuid.
     */
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    /**
     * Type of account: user, admin or place. The type is nullable because it is
     * choosen by the user at the second step of the account creation.
     */
    #[ORM\Column(name: 'type', type: 'string', nullable: true, enumType: UserType::class)]
    #[Assert\NotBlank]
    protected ?UserType $type = null;

    /**
     * The email is unique and normalized (lowercase).
     */
    #[ORM\Column(length: self::EMAIL_MAX_LENGTH, unique: true, nullable: false)]
    #[Assert\Length(max: self::EMAIL_MAX_LENGTH, groups: [AccountCreateStep1FormType::class, ChangeLoginFormType::class, 'Default'])]
    #[Assert\NotBlank(groups: [AccountCreateStep1FormType::class, ChangeLoginFormType::class, 'Default'])]
    #[Assert\Email(groups: [AccountCreateStep1FormType::class, ChangeLoginFormType::class, 'Default'])]
    private string $email;

    /**
     * Flag that tells if the user has confirmed his email with the account confirmation
     * token he received. This flag should never be updated manually.
     */
    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $emailConfirmed = false;

    /**
     * Lastname of user or admin.
     */
    #[ORM\Column(length: self::NAME_LENGTH, nullable: true)]
    #[Assert\Length(max: self::NAME_LENGTH)]
    #[Assert\When(
        expression: '!this.isPlace()',
        constraints: [
            new Assert\NotBlank(message: 'account_create.lastname.empty.error'),
        ],
        groups: [EditProfileFormType::class, AccountCreateStep2FormType::class],
    )]
    private ?string $lastname = null;

    /**
     * Firstname of user or admin.
     */
    #[ORM\Column(length: self::NAME_LENGTH, nullable: true)]
    #[Assert\Length(max: self::NAME_LENGTH)]
    #[Assert\When(
        expression: '!this.isPlace()',
        constraints: [
            new Assert\NotBlank(message: 'account_create.firstname.empty.error'),
        ],
        groups: ['Default', EditProfileFormType::class]
    )]
    private ?string $firstname = null;

    /**
     * Name of place.
     */
    #[ORM\Column(length: self::NAME_LENGTH, nullable: true)]
    #[Assert\Length(max: self::NAME_LENGTH)]
    #[Assert\When(
        expression: 'this.isPlace()',
        constraints: [
            new Assert\NotBlank(message: 'account_create.name.empty.error'),
        ],
        groups: ['Default', EditProfileFormType::class]
    )]
    private ?string $name = null;

    /**
     * Phone number of user or admin.
     */
    #[ORM\Column(nullable: true)]
    #[Assert\Length(max: self::PHONE_LENGTH)]
    private ?string $phoneNumber = null;

    /**
     * Special field for country index+number in forms.
     */
    #[AssertPhoneNumber]
    public ?PhoneNumber $phone = null;

    /**
     * Avatar of user or admin (file upload).
     */
    #[ORM\Column(nullable: true)]
    private ?string $avatar = null;

    /**
     * A user must be enabled to be able to login. If a user tries to login with
     * a deactivated account, then he will have a specific message indicating
     * the reason he can't login. He should be adviced to contact the instance administrator.
     */
    #[ORM\Column(type: 'boolean', nullable: false)]
    protected bool $enabled = true;

    /**
     * Tells if the account is the main administrator account that was created at
     * the instance creation. Account with this flag can't be deactivated or deleted.
     * This flag can only be changed by SQL or with a CLI comand.
     */
    #[ORM\Column(type: 'boolean', nullable: false)]
    protected bool $mainAdminAccount = false;

    /**
     * Tells if it is a developper account. It allows to enable some debugging
     * functionnalities that other admin won't see. This should only activated
     * on dev accounts.
     */
    #[ORM\Column(type: 'boolean', nullable: false)]
    protected bool $devAccount = false;

    /**
     * Liste of roles (see constants).
     *
     * @see getRoles()
     *
     * @var array<string>
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * The hashed password.
     */
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    /**
     * The password before it is encrypted.
     */
    #[Assert\Length(min: UserManager::PASWWORD_MIN_LENGTH, max: UserManager::PASWWORD_MAX_LENGTH, groups: [AccountCreateStep2FormType::class, ChangePasswordFormType::class, 'Default'])]
    // #[Assert\NotCompromisedPassword] // enable to check the password with the https://haveibeenpwned.com/ service
    #[Assert\NotBlank(groups: [AccountCreateStep2FormType::class, ChangePasswordFormType::class])]
    private ?string $plainPassword = null;

    #[SecurityAssert\UserPassword(groups: [ChangePasswordFormType::class])]
    private ?string $oldPassword = null;

    /**
     * Last login date of the user, null if has never logged in. The email confirmation
     * does not count as a valid login.
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $loginAt = null;

    /**
     * Tells if the user wants to receive sms notifications.
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Assert\Type('bool')]
    private bool $smsNotifications = false;

    /**
     * If it is a place, it tells its schedules.
     */
    #[ORM\Column(length: self::SCHEDULE_LENGTH, nullable: true)]
    #[Assert\Length(max: self::SCHEDULE_LENGTH)]
    private ?string $schedule = null;

    /**
     * User's favorite category.
     */
    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(referencedColumnName: 'id')]
    private ?Category $category = null;

    /**
     * User's description.
     */
    #[ORM\Column(type: 'string', nullable: true, )]
    #[Assert\Length(max: self::DESCRIPTION_LENGTH)]
    private ?string $description = null;

    /**
     * Tells if the user in on vacation.
     */
    #[ORM\Column(type: 'boolean')]
    #[Assert\Type('bool')]
    private bool $vacationMode = false;

    /**
     * Main address of the user/place.
     */
    #[ORM\ManyToOne(targetEntity: Address::class, cascade: ['persist'])]
    #[ORM\JoinColumn(referencedColumnName: 'id')]
    private ?Address $address = null;

    /**
     * @var Collection<int, UserGroup>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserGroup::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $userGroups;

    /**
     * @var Collection<int, Payment>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Payment::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $payments;

    #[Assert\IsTrue(groups: [AccountCreateStep2FormType::class])]
    public bool $gdpr = true;

    /**
     * Paid for membership of the platform.
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $membershipPaid = false;

    #[ORM\ManyToOne(targetEntity: PlatformOffer::class)]
    #[ORM\JoinColumn(referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?PlatformOffer $platformOffer = null;

    /**
     * Starting date of a paying membership. The starting date of a free membership
     * is stored in the creation date.
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?\DateTimeImmutable $startAt = null;

    /**
     * Ending date of the paying membership. If it only set for recurring membership.
     * For one-shot payments, only the start date is filled.
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?\DateTimeImmutable $endAt = null;

    /**
     * Date of the last payment of this membership.
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?\DateTimeImmutable $payedAt = null;

    /**
     * Local cache to store groups (extracted from related userGroups).
     *
     * @var Collection<int, Group>|null
     */
    private ?Collection $groups = null;

    public function __construct()
    {
        $this->userGroups = new ArrayCollection();
        $this->payments = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->email;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $uuid): self
    {
        $this->id = $uuid;

        return $this;
    }

    public function getType(): ?UserType
    {
        return $this->type;
    }

    public function setType(?UserType $type): User
    {
        $this->type = $type;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function isEmailConfirmed(): bool
    {
        return $this->emailConfirmed;
    }

    public function setEmailConfirmed(bool $emailConfirmed): User
    {
        $this->emailConfirmed = $emailConfirmed;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): User
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): User
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): User
    {
        $this->name = $name;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * Transforms the user phone number string into a phone object.
     */
    public function getPhone(): ?PhoneNumber
    {
        if (u($this->phoneNumber)->isEmpty()) {
            return null;
        }
        \Webmozart\Assert\Assert::notEmpty($this->phoneNumber);

        try {
            return PhoneNumberUtil::getInstance()->parse($this->phoneNumber, PhoneNumberUtil::UNKNOWN_REGION);
        } catch (\Exception) {
            // wrong data in the database, then ignore and return null so a new number can be put
            return null;
        }
    }

    public function setPhone(?PhoneNumber $phone): void
    {
        $this->phone = $phone;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isMainAdminAccount(): bool
    {
        return $this->mainAdminAccount;
    }

    public function setMainAdminAccount(bool $mainAdminAccount): self
    {
        $this->mainAdminAccount = $mainAdminAccount;

        return $this;
    }

    public function isDevAccount(): bool
    {
        return $this->devAccount;
    }

    public function setDevAccount(bool $devAccount): User
    {
        $this->devAccount = $devAccount;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = self::ROLE_USER;

        // add specific group roles
        foreach ($this->userGroups as $userGroup) {
            if ($userGroup->getMembership()->isAdmin()) {
                $roles[] = self::ROLE_GROUP_ADMIN;
            }
        }

        if ($this->isMembershipPaid()) {
            $roles[] = self::MEMBERSHIP_PAID;
        }

        return array_unique($roles);
    }

    /**
     * @param array<string> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    public function setOldPassword(?string $oldPassword): void
    {
        $this->oldPassword = $oldPassword;
    }

    public function getLoginAt(): ?\DateTimeInterface
    {
        return $this->loginAt;
    }

    public function setLoginAt(?\DateTimeInterface $loginAt): User
    {
        $this->loginAt = $loginAt;

        return $this;
    }

    public function getSmsNotifications(): bool
    {
        return $this->smsNotifications;
    }

    public function setSmsNotifications(bool $smsNotifications): self
    {
        $this->smsNotifications = $smsNotifications;

        return $this;
    }

    public function canBeNotifiedBySms(): bool
    {
        return $this->getSmsNotifications()
            && !u($this->phoneNumber)->isEmpty();
    }

    public function getSchedule(): ?string
    {
        return $this->schedule;
    }

    public function setSchedule(?string $schedule): void
    {
        $this->schedule = $schedule;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): void
    {
        $this->category = $category;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getVacationMode(): bool
    {
        return $this->vacationMode;
    }

    public function isInVacation(): bool
    {
        return $this->vacationMode;
    }

    public function setVacationMode(bool $vacationMode): void
    {
        $this->vacationMode = $vacationMode;
    }

    public function switchVacationMode(bool $vacationMode): void
    {
        $this->vacationMode = !$vacationMode;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function isAdmin(): bool
    {
        return $this->type === UserType::ADMIN;
    }

    public function isPlace(): bool
    {
        return $this->type === UserType::PLACE;
    }

    public function setStep2Defaults(): self
    {
        $this->type = UserType::USER;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->getAvatar();
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function hasAddress(): bool
    {
        return $this->address !== null;
    }

    public function setAddress(?Address $address): User
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return Collection<int, UserGroup>
     */
    public function getUserGroups(): Collection
    {
        return $this->userGroups;
    }

    /**
     * @return Collection<int, UserGroup>
     */
    public function getUserGroupsConfirmed(): Collection
    {
        /** @var Collection<int, UserGroup> $collection */
        $collection = $this->userGroups->filter(fn (UserGroup $userGroup) => !$userGroup->getMembership()->isInvited());

        return $collection;
    }

    /**
     * @return Collection<int, UserGroup>
     */
    public function getUserGroupsConfirmedWithServices(): Collection
    {
        /** @var Collection<int, UserGroup> $collection */
        $collection = $this->userGroups->filter(fn (UserGroup $userGroup) => !$userGroup->getMembership()->isInvited() && $userGroup->getGroup()->getServicesEnabled());

        return $collection;
    }

    /**
     * @return array<int, string>
     */
    public function getUserGroupsIds(): array
    {
        return $this->getUserGroupsConfirmed()->map(fn (UserGroup $userGroup) => (string) $userGroup->getGroup()->getId())->toArray();
    }

    public function addUserGroup(UserGroup $userGroup): self
    {
        if (!$this->userGroups->contains($userGroup)) {
            $this->userGroups->add($userGroup);
            $userGroup->setUser($this);
        }

        return $this;
    }

    public function removeUserGroup(UserGroup $userGroup): self
    {
        $this->userGroups->removeElement($userGroup);

        return $this;
    }

    /**
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    /**
     * @param Collection<int, Payment> $payments
     */
    public function setPayments(Collection $payments): User
    {
        $this->payments = $payments;

        return $this;
    }

    public function isMembershipPaid(): bool
    {
        return $this->membershipPaid;
    }

    public function setMembershipPaid(bool $membershipPaid): self
    {
        $this->membershipPaid = $membershipPaid;

        return $this;
    }

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(?\DateTimeImmutable $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeImmutable
    {
        return $this->endAt;
    }

    public function setEndAt(?\DateTimeImmutable $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getPayedAt(): ?\DateTimeImmutable
    {
        return $this->payedAt;
    }

    public function setPayedAt(?\DateTimeImmutable $payedAt): self
    {
        $this->payedAt = $payedAt;

        return $this;
    }

    // —— end of basic 'etters —————————————————————————————————————————————————

    public function promoteToAdmin(): self
    {
        $this->setRoles([self::ROLE_ADMIN]);

        return $this;
    }

    public function getDisplayName(): string
    {
        if ($this->isPlace()) {
            $shortName = $this->getName();
        } else {
            $shortName = $this->getFirstname();
        }

        return (string) $shortName;
    }

    /**
     * @return class-string
     */
    public function getAdminCrudClass(): string
    {
        return match ($this->type) {
            UserType::USER => UserCrudController::class,
            UserType::ADMIN => AdministratorCrudController::class,
            UserType::PLACE => PlaceCrudController::class,
            default => throw new \LogicException('No type assigned to user yet.'),
        };
    }

    /**
     * Get the list of groups the user belong to as a collection (with local cache).
     *
     * @return Collection<int,Group>
     */
    public function getMyGroups(): Collection
    {
        if ($this->groups !== null) {
            return $this->groups;
        }

        $this->groups = new ArrayCollection(
            array_map(static fn (UserGroup $userGroup) => $userGroup->getGroup(), $this->userGroups->toArray())
        );

        return $this->groups;
    }

    /**
     * Get the groups only where the user has the group admin role.
     *
     * @return Collection<int,Group>
     */
    public function getMyGroupsAsAdmin(bool $enabledServices = false): Collection
    {
        $adminUserGroups = $this->userGroups->filter(
            static fn (UserGroup $userGroup) => $userGroup->getMembership()->isAdmin() || $userGroup->isMainAdminAccount()
        );

        $groups = new ArrayCollection(
            array_map(static fn (UserGroup $userGroup) => $userGroup->getGroup(), $adminUserGroups->toArray())
        );

        if ($enabledServices) {
            return $groups->filter(
                static fn (Group $group) => $group->getServicesEnabled()
            );
        }

        return $groups;
    }

    /**
     * Get the groups only where the user has invitations.
     *
     * @return Collection<int,UserGroup>
     */
    public function getMyUserGroupsAsInvited(): Collection
    {
        /** @var Collection<int,UserGroup> $collection */
        $collection = $this->userGroups->filter(
            static fn (UserGroup $userGroup) => $userGroup->getMembership()->isInvited()
        );

        return $collection;
    }

    /**
     * Get the groups only where the user is confirmed (member or admin).
     *
     * @return Collection<int,UserGroup>
     */
    public function getMyUserGroupsAsConfirmed(): Collection
    {
        /** @var Collection<int,UserGroup> $collection */
        $collection = $this->userGroups->filter(
            static fn (UserGroup $userGroup) => $userGroup->getMembership()->isConfirmed()
        );

        return $collection;
    }

    /**
     * Get the groups only where the user has invitations.
     *
     * @return Collection<int,Group>
     */
    public function getMyGroupsAsInvited(): Collection
    {
        return new ArrayCollection(
            array_map(static fn (UserGroup $userGroup) => $userGroup->getGroup(), $this->getMyUserGroupsAsInvited()->toArray())
        );
    }

    /**
     * The invitation status is excluded because, we are only member of the group
     * once the invitation is accepted. We consider we are also a member even we
     * are an admin of the group.
     */
    public function isMemberOf(Group $group): bool
    {
        $notInvited = $this->userGroups->filter(
            fn (UserGroup $userGroup) => $userGroup->getGroup() === $group && !$userGroup->getMembership()->isInvited()
        );

        return !$notInvited->isEmpty();
    }

    /**
     * Tells if the user has already an association with the group whatever the
     * membership status is.
     */
    public function hasLink(Group $group): bool
    {
        return $this->getMyGroups()->contains($group);
    }

    /**
     * Return the membership for a given group if it exists for the user, null otherwise.
     * We can safely use the first() function here. Because of Doctrine constraints,
     * it's impossible to have 2 records for the same group and user.
     *
     * @see UserGroup
     */
    public function getGroupMembership(Group $group): ?UserGroup
    {
        /** @var Collection<int, UserGroup> $contextUserGroup */
        $contextUserGroup = $this->userGroups->filter(
            static fn (UserGroup $userGroup) => $userGroup->getGroup() === $group
        );

        return $contextUserGroup->isEmpty() ? null : $contextUserGroup->first();
    }

    public function isGroupAdmin(Group $group): bool
    {
        $groupAdmin = $group->getUserGroups()->filter(
            fn (UserGroup $userGroup) => $userGroup->getUser()->getId() === $this->getId() && $userGroup->getMembership()->isAdmin()
        );

        return !$groupAdmin->isEmpty();
    }

    public function isIndexable(): bool
    {
        return !$this->isInVacation();
    }

    public function deleteAvatar(): self
    {
        $this->avatar = null;

        return $this;
    }

    public function changePhoneNumber(?PhoneNumber $phone): self
    {
        if ($phone === null) {
            $this->setPhoneNumber(null);
        } else {
            $this->setPhoneNumber('+'.$phone->getCountryCode().$phone->getNationalNumber());
        }

        return $this;
    }

    public function expiresIn(): ?int
    {
        $today = Carbon::today();
        if ($this->endAt === null || $this->endAt < $today) {
            return null;
        }

        $endAt = new Carbon($this->endAt);

        return $today->diffInDays($endAt);
    }

    public function getPlatformOffer(): ?PlatformOffer
    {
        return $this->platformOffer;
    }

    public function setPlatformOffer(?PlatformOffer $platformOffer): void
    {
        $this->platformOffer = $platformOffer;
    }

    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }

        return $this->email === $user->getUserIdentifier() && $this->password === $user->getPassword();
    }
}
