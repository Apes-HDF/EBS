<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Group;
use App\Entity\GroupOffer;
use App\Entity\Product;
use App\Entity\UserGroup;
use App\Enum\Group\GroupType;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class GroupTest extends TestCase
{
    public function testGroup(): void
    {
        $group = new Group();
        $id = Uuid::v6();
        self::assertSame($id, $group->setId($id)->getId());
        self::assertSame('grp1', $group->setName('grp1')->getName());
        self::assertSame('desc1', $group->setDescription('desc1')->getDescription());
        self::assertSame('https://example.com', $group->setUrl('https://example.com')->getUrl());
        self::assertSame(GroupType::PRIVATE, $group->setType(GroupType::PRIVATE)->getType());
        self::assertSame('grp-1', $group->setSlug('grp-1')->getSlug());
        self::assertSame(['id' => (string) $id, 'slug' => 'grp-1'], $group->getRoutingParameters());

        $userGroup = (new UserGroup());
        self::assertCount(0, $group->getUserGroups());
        $group->addUserGroup($userGroup);
        self::assertCount(1, $group->getUserGroups());
        $group->removeUserGroup($userGroup);
        self::assertCount(0, $group->getUserGroups());

        $groupOffer = new GroupOffer();
        $group->setOffers(new ArrayCollection([$groupOffer]));
        self::assertTrue($group->getOffers()->contains($groupOffer));

        $product = new Product();
        self::assertCount(0, $group->getProducts());
        $group->addProduct($product);
        self::assertCount(1, $group->getProducts());
        $group->removeProduct($product);
        self::assertCount(0, $group->getProducts());
    }
}
