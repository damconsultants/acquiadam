<?php
namespace DamConsultants\AcquiaDam\Block\Adminhtml\Catalog\Product\Form;

class Gallery extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'group/gallery.phtml';
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\ConfigurableProduct\Block\Adminhtml\Product\Steps\Bulk
     */
    protected $_bulk;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_image;
    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_helperData;

    /**
     * Gallery
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\ConfigurableProduct\Block\Adminhtml\Product\Steps\Bulk $bulk
     * @param \Magento\Catalog\Helper\Image $image
     * @param \Magento\Backend\Helper\Data $helperdata
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\ConfigurableProduct\Block\Adminhtml\Product\Steps\Bulk $bulk,
        \Magento\Catalog\Helper\Image $image,
        \Magento\Backend\Helper\Data $helperdata
    ) {
        $this->_storeManager = $storeManager;
        $this->_bulk = $bulk;
        $this->_registry = $registry;
        $this->_image = $image;
        $this->_helperData = $helperdata;
        parent::__construct($context);
    }
    /**
     * Get Image Roll
     *
     * @return $this
     */
    public function getBulkImageRoll()
    {
        return $this->_bulk->getMediaAttributes();
    }
    /**
     * Get Backend Name
     *
     * @return $this
     */
    public function getBackendArea()
    {
        return $this->_helperData->getAreaFrontName();
    }
    /**
     * Get Image Height Widht
     *
     * @return $this
     * @param string $id
     * @param string $attribute
     */
    public function getHeightWidht($id, $attribute)
    {
        return $this->_image->init($id, $attribute);
    }
    /**
     * Get Image Roll
     *
     * @return $this
     * @param string $currentProduct
     */
    public function getProduct($currentProduct)
    {
        return $this->_registry->registry($currentProduct);
    }
    /**
     * Get Media Url
     *
     * @return $this
     */
    public function getMediaUrl()
    {
        $currentStore =  $this->_storeManager->getStore();
        return $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
    /**
     * EntityId.
     *
     * @return $this
     */
    public function getEntityId()
    {
        return $this->getRequest()->getParam('id');
    }
    /**
     * Image.
     *
     * @return $this
     */
    public function getDrag()
    {
        return $this->getViewFileUrl('DamConsultants_AcquiaDam::images/drag.png');
    }
    /**
     * Image.
     *
     * @return $this
     */
    public function getDelete()
    {
        return $this->getViewFileUrl('DamConsultants_AcquiaDam::images/delete_.avif');
    }
    /**
     * Image.
     *
     * @return $this
     */
    public function getPreloader()
    {
        return $this->getViewFileUrl('DamConsultants_AcquiaDam::images/loader_new.gif');
    }

    /**
     * Json.
     *
     * @return $this
     * @param array $attr
     */
    public function getJson($attr)
    {
        return json_encode($attr);
    }
}
