<?php

namespace DamConsultants\AcquiaDam\Block\Adminhtml\AcquiaDamMetaproperty;

use DamConsultants\AcquiaDam\Model\ResourceModel\Collection\MetaPropertyCollectionFactory;
use DamConsultants\AcquiaDam\Model\ResourceModel\Collection\DefaultMetaPropertyCollectionFactory;

class Index extends \Magento\Backend\Block\Template
{
    /**
     * @var \DamConsultants\AcquiaDam\Helper\Data
     */
    protected $_helperdata;

    /**
     * @var \DamConsultants\AcquiaDam\Model\MetaPropertyFactory
     */
    protected $_metaProperty;

    /**
     * @var \DamConsultants\AcquiaDam\Model\ResourceModel\Collection\MetaPropertyCollectionFactory
     */
    protected $_metaPropertyCollectionFactory;
    /**
     * @var \DamConsultants\AcquiaDam\Model\ResourceModel\Collection\DefaultMetaPropertyCollectionFactory
     */
    protected $_default_metaProperty_collection;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Metaproperty
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \DamConsultants\AcquiaDam\Helper\Data $helperdata
     * @param \DamConsultants\AcquiaDam\Model\MetaPropertyFactory $metaProperty
     * @param MetaPropertyCollectionFactory $metaPropertyCollectionFactory
     * @param DefaultMetaPropertyCollectionFactory $DefaultMetaPropertyCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \DamConsultants\AcquiaDam\Helper\Data $helperdata,
        \DamConsultants\AcquiaDam\Model\MetaPropertyFactory $metaProperty,
        MetaPropertyCollectionFactory $metaPropertyCollectionFactory,
        DefaultMetaPropertyCollectionFactory $DefaultMetaPropertyCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_helperdata = $helperdata;
        $this->_metaProperty = $metaProperty;
        $this->_metaPropertyCollectionFactory = $metaPropertyCollectionFactory;
        $this->_default_metaProperty_collection = $DefaultMetaPropertyCollectionFactory;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * SubmitUrl.
     *
     * @return $this
     */
    public function getSubmitUrl()
    {
        return $this->getUrl("acquiadam/index/submit");
    }

    /**
     * Get MetaData.
     *
     * @return array
     */
    public function getMetaData()
    {
        $response_data = [];
        $attribute_array = [];
        $defaultmetaPropertycollection = $this->_default_metaProperty_collection->create();
        $defaultmetaPropertycollection_data = $defaultmetaPropertycollection->getData();
        if (count($defaultmetaPropertycollection_data) > 0) {
            foreach ($defaultmetaPropertycollection_data as $meta_val) {
                $attribute_array[$meta_val['widen_property_slug']] = $meta_val['property_name'];
            }
        }
        $collection = $this->_metaPropertyCollectionFactory->create();
        if (count($attribute_array) > 0) {
            $response_data['metadata'] = $attribute_array;
        } else {
            $response_data['metadata'] = [];
        }
        $properties_details = [];
        $collection_data_array = $collection->getData();
        if (count($collection_data_array) > 0) {
            foreach ($collection_data_array as $metacollection) {
                $properties_details[$metacollection['system_slug']] = [
                    "id" => $metacollection['id'],
                    "property_name" => $metacollection['property_name'],
                    "property_id" => $metacollection['property_id'],
                    "widen_property_slug" => $metacollection['widen_property_slug'],
                    "system_slug" => $metacollection['system_slug'],
                    "system_name" => $metacollection['system_name'],
                ];
            }
            $response_data['style_selected'] = isset($properties_details["style"]["widen_property_slug"])
            ? $properties_details["style"]["widen_property_slug"]
            : '0';
            $response_data['image_role_selected'] = isset($properties_details["image_role"]["widen_property_slug"])
            ? $properties_details["image_role"]["widen_property_slug"]
            : '0';
            $response_data['image_alt_text'] = isset($properties_details["alt_text"]["widen_property_slug"])
            ? $properties_details["alt_text"]["widen_property_slug"]
            : '0';
            $response_data['image_color'] = isset($properties_details["color"]["widen_property_slug"])
            ? $properties_details["color"]["widen_property_slug"]
            : '0';
			$response_data['order'] = isset($properties_details["orders"]["widen_property_slug"])
            ? $properties_details["orders"]["widen_property_slug"]
            : '0';
            $response_data['subtypes'] = isset($properties_details["subtype"]["widen_property_slug"])
            ? $properties_details["subtype"]["widen_property_slug"]
            : '0';
            $response_data['leads'] = isset($properties_details["lead"]["widen_property_slug"])
            ? $properties_details["lead"]["widen_property_slug"]
            : '0';
        } else {
            $response_data['style_selected'] = '0';
            $response_data['image_role_selected'] = '0';
            $response_data['image_alt_text'] = '0';
            $response_data['image_color'] = '0';
			$response_data['order'] = '0';
            $response_data['subtypes'] = '0';
            $response_data['leads'] = '0';
        }
        return $response_data;
    }
    /**
     * Get main url.
     *
     * @return string
     */
    public function getMainUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
}
