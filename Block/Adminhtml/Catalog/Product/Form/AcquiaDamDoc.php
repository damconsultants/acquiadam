<?php
namespace DamConsultants\AcquiaDam\Block\Adminhtml\Catalog\Product\Form;

class AcquiaDamDoc extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_helperData;
    /**
     * Block template.
     *
     * @var string
     */
    protected $_template = 'group/acquiadamdoc.phtml';
    /**
     * Gallery
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Helper\Data $helperdata
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Helper\Data $helperdata
    ) {
        $this->_registry = $registry;
        $this->_helperData = $helperdata;
        parent::__construct($context);
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
}
