<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\PayPal\IZettle\DataAbstractionLayer\Entity;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                                     add(IZettleSalesChannelRunEntity $entity)
 * @method void                                     set(string $key, IZettleSalesChannelRunEntity $entity)
 * @method \Generator<IZettleSalesChannelRunEntity> getIterator()
 * @method IZettleSalesChannelRunEntity[]           getElements()
 * @method IZettleSalesChannelRunEntity|null        get(string $key)
 * @method IZettleSalesChannelRunEntity|null        first()
 * @method IZettleSalesChannelRunEntity|null        last()
 */
class IZettleSalesChannelRunCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return IZettleSalesChannelRunEntity::class;
    }
}
