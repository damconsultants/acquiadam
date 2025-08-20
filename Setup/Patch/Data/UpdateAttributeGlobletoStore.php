<?php
namespace DamConsultants\AcquiaDam\Setup\Patch\Data;

/**
 * DamConsultants
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 *  DamConsultants_AcquiaDam
 */
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateAttributeGlobletoStore implements DataPatchInterface
{
    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var EavSetupFactory */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
		$eavSetup->updateAttribute(
		Product::ENTITY,
			'widen_multi_img',
			['is_global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE]
		);

		$eavSetup->updateAttribute(
			Product::ENTITY,
			'widen_document',
			['is_global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE]
		);

		$eavSetup->updateAttribute(
			Product::ENTITY,
			'use_widen_both_image',
			['is_global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE]
		);

		$eavSetup->updateAttribute(
			Product::ENTITY,
			'use_widen_cdn',
			[
				'is_global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
				'default'   => '1'
			]
		);

		$eavSetup->updateAttribute(
			Product::ENTITY,
			'widen_isMain',
			['is_global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE]
		);

		$eavSetup->updateAttribute(
			Product::ENTITY,
			'widen_auto_replace',
			['is_global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE]
		);

    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
