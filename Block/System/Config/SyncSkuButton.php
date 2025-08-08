<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace DamConsultants\AcquiaDam\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use \Magento\Store\Model\StoreManagerInterface;

class SyncSkuButton extends Field
{
    /**
     * Block template.
     *
     * @var string
     */
    protected $_template = 'DamConsultants_AcquiaDam::system/config/syncskubutton.phtml';
    /**
     *
     * @var storeManager
     */
    protected $_storeManager;
    /**
     *
     * @var HelperBackend
     */
    protected $HelperBackend;

    /**
     * Get Sync Button
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Backend\Helper\Data $HelperBackend
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        \Magento\Backend\Helper\Data $HelperBackend,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->HelperBackend = $HelperBackend;
        parent::__construct($context, $data);
    }

    /**
     * Render
     *
     * @return $this
     * @param object $element
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
    /**
     * Render
     *
     * @return $this
     * @param object $element
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
    /**
     * Return ajax url for custom button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('acquiadam/index/syncsku');
    }

    /**
     * Get Button Html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()
            ->createBlock(\Magento\Backend\Block\Widget\Button::class)
            ->setData(
                [
                    'id' => 'sync_sku_button',
                    'label' => __('Sync Sku Data'),
                ]
            );

        return $button->toHtml();
    }
}
