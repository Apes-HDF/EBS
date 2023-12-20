<?php

declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\Behavior\TimestampableEntity;
use App\Enum\Group\UserMembership;
use App\Repository\UserGroupRepository;
use Carbon\Carbon;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserGroupRepository::class)]
#[ORM\UniqueConstraint(columns: ['user', 'group'])]
class UserGroup
{
    use TimestampableEntity;

    /**
     * Generates a V6 uuid.
     */
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    /**
     * Related user.
     */
    #[ORM\ManyToOne(inversedBy: 'userGroups')]
    #[ORM\JoinColumn(name: '`user`', nullable: false)]
    #[Assert\NotNull]
    private User $user;

    /**
     * Related group.
     */
    #[ORM\ManyToOne(inversedBy: 'userGroups')]
    #[ORM\JoinColumn(name: '`group`', nullable: false)]
    #[Assert\NotNull]
    private Group $group;

    /**
     * Role the user has in this group.
     */
    #[ORM\Column(name: 'membership', type: 'string', nullable: false, enumType: UserMembership::class)]
    #[Assert\NotNull]
    protected UserMembership $membership = UserMembership::INVITATION;

    /**
     * Tells if the account is the main group administrator account. This account
     * can't be deleted unless a new main group admin is assigned.
     */
    #[ORM\Column(type: 'boolean', nullable: false)]
    protected bool $mainAdminAccount = false;

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

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $uuid): self
    {
        $this->id = $uuid;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getGroup(): Group
    {
        return $this->group;
    }

    public function setGroup(Group $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function getMembership(): UserMembership
    {
        return $this->membership;
    }

    public function setMembership(UserMembership $membership): UserGroup
    {
        $this->membership = $membership;

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

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(?\DateTimeImmutable $startAt): UserGroup
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeImmutable
    {
        return $this->endAt;
    }

    public function setEndAt(?\DateTimeImmutable $endAt): UserGroup
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getPayedAt(): ?\DateTimeImmutable
    {
        return $this->payedAt;
    }

    public function setPayedAt(?\DateTimeImmutable $payedAt): UserGroup
    {
        $this->payedAt = $payedAt;

        return $this;
    }

    /** -- End of basic getters and setters ----------------------------------------------*/

    /**
     * Don't remove the admin role if set.
     */
    public function setMember(): self
    {
        if (!$this->membership->isAdmin()) {
            $this->membership = UserMembership::MEMBER;
        }

        return $this;
    }

    public static function newUserGroup(User $user, Group $group): UserGroup
    {
        return (new self())
            ->setUser($user)
            ->setMembership(UserMembership::ADMIN)
            ->setMainAdminAccount(true)
            ->setGroup($group);
    }

    /**
     * Return the number of days the mmebership will expires in relative days without
     * taking take of the hour.
     */
    public function expiresIn(): ?int
    {
        $today = Carbon::today();
        if ($this->endAt === null || $this->endAt < $today) {
            return null;
        }

        $endAt = new Carbon($this->endAt);

        return $today->diffInDays($endAt);
    }
}
