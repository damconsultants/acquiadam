<?php
namespace DamConsultants\AcquiaDam\Block\Adminhtml\AcquiaDamSync;

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
     * To option array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'image', 'label' => __('Image')],
            ['value' => 'video', 'label' => __('Video')],
            ['value' => 'document', 'label' => __('Document')],
          ];
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
    /**
     * Return ajax url for custom button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('acquiadam/index/getsynsku');
    }
    /**
     * Return ajax url for custom button
     *
     * @return string
     */
    public function getSyncAjaxUrl()
    {
        return $this->getUrl('acquiadam/index/syncsku');
    }
}
