<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\CatalogImportExport\Model\Import\Product\Validator;
use Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor;

class CategoryProcessorTest extends \PHPUnit_Framework_TestCase
{
    const PARENT_CATEGORY_ID = 1;

    const CHILD_CATEGORY_ID = 2;

    const CHILD_CATEGORY_NAME = 'Child';

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryProcessor;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected $product;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $childCategory = $this->getMockBuilder('Magento\Catalog\Model\Category')
            ->disableOriginalConstructor()
            ->getMock();
        $childCategory->method('getId')->will($this->returnValue(self::CHILD_CATEGORY_ID));
        $childCategory->method('getName')->will($this->returnValue('Child'));
        $childCategory->method('getPath')->will($this->returnValue(
            self::PARENT_CATEGORY_ID . CategoryProcessor::DELIMITER_CATEGORY
            . self::CHILD_CATEGORY_ID
        ));

        $parentCategory = $this->getMockBuilder('Magento\Catalog\Model\Category')
            ->disableOriginalConstructor()
            ->getMock();
        $parentCategory->method('getId')->will($this->returnValue(self::PARENT_CATEGORY_ID));
        $parentCategory->method('getName')->will($this->returnValue('Parent'));
        $parentCategory->method('getPath')->will($this->returnValue(self::PARENT_CATEGORY_ID));

        $categoryCollection =
            $this->objectManagerHelper->getCollectionMock(
                'Magento\Catalog\Model\Resource\Category\Collection',
                [
                    self::PARENT_CATEGORY_ID => $parentCategory,
                    self::CHILD_CATEGORY_ID => $childCategory,
                ]
            );
        $map = array(
            array(self::PARENT_CATEGORY_ID, $parentCategory),
            array(self::CHILD_CATEGORY_ID, $childCategory),
        );
        $categoryCollection->expects($this->any())
            ->method('getItemById')
            ->will($this->returnValueMap($map));

        $categoryColFactory = $this->getMockBuilder('Magento\Catalog\Model\Resource\Category\CollectionFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $categoryColFactory->method('create')->will($this->returnValue($categoryCollection));

        $categoryFactory = $this->getMockBuilder('Magento\Catalog\Model\CategoryFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $categoryFactory->method('create')->will($this->returnValue($childCategory));

        $this->categoryProcessor =
            new \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor($categoryColFactory, $categoryFactory);
    }

    public function testUpsertCategories()
    {
        $categoryIds = $this->categoryProcessor->upsertCategories(self::CHILD_CATEGORY_NAME);
        $this->assertArrayHasKey(self::CHILD_CATEGORY_ID, array_flip($categoryIds));
    }
}
