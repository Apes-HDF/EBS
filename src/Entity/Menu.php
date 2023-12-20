<?php

declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\Behavior\TimestampableEntity;
use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
#[ORM\Table]
class Menu implements ImageInterface, \Stringable
{
    use TimestampableEntity;

    final public const MENU = 'menu';
    final public const FOOTER = 'footer';

    /**
     * We don't need uuid here.
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    /**
     * Logo of the menu.
     */
    #[ORM\Column(nullable: true)]
    private ?string $logo = null;

    /**
     * Code of the menu.
     */
    #[ORM\Column(length: 255, unique: true)]
    private string $code;

    /**
     * @var Collection<string, MenuItem> $items
     */
    #[ORM\OneToMany(mappedBy: 'menu', targetEntity: MenuItem::class, cascade: ['persist', 'remove', 'detach'])]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->code;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->getLogo();
    }

    /**
     * @return Collection<string, MenuItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * @param Collection<string, MenuItem> $items
     */
    public function setItems(Collection $items): self
    {
        $this->items = $items;

        return $this;
    }

    public function addItem(MenuItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setMenu($this);
        }

        return $this;
    }

    public function removeItem(MenuItem $item): self
    {
        $this->items->removeElement($item);
//        if ($this->items->removeElement($item)) {
        // set the owning side to null (unless already changed)
//            if ($item->getMenu() === $this) {
//                $item->setMenu(null);
//            }
//        }

        return $this;
    }

    public function itemsCount(): int
    {
        return $this->items->count();
    }
}
