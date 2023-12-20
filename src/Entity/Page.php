<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use App\Doctrine\Behavior\TimestampableEntity;
use App\Repository\PageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[ORM\Table]
#[ORM\Index(columns: ['slug'])]
class Page implements \Stringable
{
    use TimestampableEntity;

    /**
     * Generates a V6 uuid.
     */
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ApiProperty(identifier: true)]
    private Uuid $id;

    /**
     * Name of the page (SEO).
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $name;

    /**
     * SEO friendly name for URLs.
     */
    #[ORM\Column(length: 255, unique: true)]
    #[Gedmo\Slug(fields: ['name'])]
    private string $slug;

    /**
     * HTML content of the page.
     */
    #[ORM\Column(type: Types::TEXT, nullable: false)]
    #[Assert\NotBlank]
    private string $content;

    /**
     * Tells if the page is visible on website or not.
     */
    #[ORM\Column(type: 'boolean', nullable: false)]
    protected bool $enabled = true;

    /**
     * Tells if the page is the one to display on the homepage.
     */
    #[ORM\Column(type: 'boolean', nullable: false)]
    protected bool $home = false;

    public function __toString(): string
    {
        return $this->name;
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): Page
    {
        $this->content = $content;

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

    public function isHome(): bool
    {
        return $this->home;
    }

    public function setHome(bool $home): self
    {
        $this->home = $home;

        return $this;
    }
}
