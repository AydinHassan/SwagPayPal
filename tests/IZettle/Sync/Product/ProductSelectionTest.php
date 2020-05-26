<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\PayPal\Test\IZettle\Sync\Product;

use PHPUnit\Framework\MockObject\MockObject;
use Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainCollection;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Swag\PayPal\IZettle\DataAbstractionLayer\Entity\IZettleSalesChannelEntity;
use Swag\PayPal\IZettle\Resource\ProductResource;
use Swag\PayPal\IZettle\Sync\Context\ProductContextFactory;
use Swag\PayPal\IZettle\Sync\ProductSelection;
use Swag\PayPal\IZettle\Sync\ProductSyncer;
use Swag\PayPal\Test\Mock\IZettle\ProductContextMock;
use Swag\PayPal\Test\Mock\IZettle\SalesChannelProductRepoMock;

class ProductSelectionTest extends AbstractProductSyncTest
{
    /**
     * @var MockObject
     */
    private $productContextFactory;

    /**
     * @var ProductContextMock
     */
    private $productContext;

    /**
     * @var ProductSyncer
     */
    private $pruductSyncer;

    /**
     * @var MockObject
     */
    private $productResource;

    /**
     * @var SalesChannelProductRepoMock
     */
    private $productRepository;

    /**
     * @var SalesChannelEntity
     */
    private $salesChannel;

    /**
     * @var MockObject
     */
    private $newUpdater;

    /**
     * @var MockObject
     */
    private $outdatedUpdater;

    /**
     * @var MockObject
     */
    private $deletedUpdater;

    /**
     * @var MockObject
     */
    private $unsyncedChecker;

    /**
     * @var ProductSelection
     */
    private $productSelection;

    public function setUp(): void
    {
        $context = Context::createDefaultContext();

        $this->salesChannel = $this->createSalesChannel($context);

        $this->productContext = new ProductContextMock($this->salesChannel, $context);
        $this->productContextFactory = $this->createMock(ProductContextFactory::class);
        $this->productContextFactory->method('getContext')->willReturn($this->productContext);

        $productStreamBuilder = $this->createStub(ProductStreamBuilderInterface::class);
        $productStreamBuilder->method('buildFilters')->willReturn(
            [new NotFilter(NotFilter::CONNECTION_AND, [
                new EqualsFilter('id', null),
            ])]
        );

        $this->productResource = $this->createPartialMock(
            ProductResource::class,
            ['createProduct', 'updateProduct', 'deleteProduct']
        );

        $domain = new SalesChannelDomainEntity();
        $domain->setId(Uuid::randomHex());
        $domain->setSalesChannelId($this->salesChannel->getId());
        $domainRepository = $this->createStub(EntityRepositoryInterface::class);
        $domainRepository->method('search')->willReturn(
            new EntitySearchResult(
                1,
                new SalesChannelDomainCollection([$domain]),
                null,
                new Criteria(),
                $context
            )
        );

        $this->productRepository = new SalesChannelProductRepoMock();

        $this->productSelection = new ProductSelection(
            $this->productRepository,
            $productStreamBuilder,
            $domainRepository,
            $this->createMock(SalesChannelContextFactory::class)
        );
    }

    public function dataProviderProductSelection(): array
    {
        return [
            [false, false],
            [false, true],
            [true, false],
            [true, true],
        ];
    }

    /**
     * @dataProvider dataProviderProductSelection
     */
    public function testProductSelection(bool $withProductStream, bool $withAssociations): void
    {
        $context = Context::createDefaultContext();

        $product = $this->getProduct();
        $this->productRepository->addMockEntity($product);

        $iZettleSalesChannel = $this->salesChannel->getExtension('paypalIZettleSalesChannel');
        static::assertNotNull($iZettleSalesChannel);
        static::assertInstanceOf(IZettleSalesChannelEntity::class, $iZettleSalesChannel);
        if (!$withProductStream) {
            $iZettleSalesChannel->setProductStreamId(null);
        }

        $products = $this->productSelection->getProducts($iZettleSalesChannel, $context, $withAssociations);

        static::assertCount(1, $products);
    }
}
