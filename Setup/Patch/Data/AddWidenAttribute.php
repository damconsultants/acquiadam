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
 *  DamConsultants_acquiadam
 */
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddWidenAttribute implements DataPatchInterface
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

        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'widen_multi_img');
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'widen_multi_img', [
            'group' => 'Product Details',
            'type' => 'text',
            'sort_order' => 220,
            'backend' => '',
            'frontend' => '',
            'label' => 'Acquia DAM Image and Video',
            'input' => 'textarea',
            'class' => '',
            'source' => '',
            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'user_defined' => false,
            'default' => '',
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => ''
        ]);

        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'widen_document');
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'widen_document', [
            'group' => 'Product Details',
            'type' => 'text',
            'backend' => '',
            'frontend' => '',
            'sort_order' => 230,
            'label' => 'Acquia DAM Document',
            'input' => 'textarea',
            'class' => '',
            'source' => '',
            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'user_defined' => false,
            'default' => '',
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'apply_to' => ''
        ]);

        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'use_widen_both_image');
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'use_widen_both_image', [
            'group' => 'Product Details',
            'type' => 'int',
            'sort_order' => 260,
            'backend' => '',
            'frontend' => '',
            'label' => 'Use Acquia DAM Image as well as Local Folder Image',
            'input' => 'boolean',
            'class' => '',
            'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
            'visible' => true,
            'required' => false,
            'user_defined' => false,
            'user_defined' => true,
            'default' => '',
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => ''
            /* 'attribute_set_id' => '4' */
        ]);

        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'widen_image_import');
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'widen_image_import', [
            'group' => 'Product Details',
            'type' => 'int',
            'sort_order' => 270,
            'backend' => '',
            'frontend' => '',
            'label' => 'Import Acquia DAM Image into the local Folder',
            'input' => 'boolean',
            'class' => '',
            'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
            'visible' => true,
            'required' => false,
            'user_defined' => false,
            'user_defined' => true,
            'default' => '',
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => ''
            /* 'attribute_set_id' => '4' */
        ]);

        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'use_widen_cdn');
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'use_widen_cdn', [
            'group' => 'Product Details',
            'type' => 'int',
            'sort_order' => 280,
            'backend' => '',
            'frontend' => '',
            'label' => 'Use only Acquia DAM Image on Front side',
            'input' => 'boolean',
            'class' => '',
            'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
            'visible' => true,
            'required' => false,
            'user_defined' => false,
            'user_defined' => true,
            'default' => '',
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => ''
            /* 'attribute_set_id' => '4' */
        ]);

        $this->moduleDataSetup->getConnection()->endSetup();
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

    /**
     * @inheritdoc
     */
   /* public static function getVersion()
    {
        return '2.0.1';
    }*/
}
