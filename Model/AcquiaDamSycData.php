<?php

namespace DamConsultants\AcquiaDam\Model;

class AcquiaDamSycData extends \Magento\Framework\Model\AbstractModel
{
    public const CACHE_TAG = 'DamConsultants_AcquiaDam';
    /**
     * @var $_cacheTag
     */
    protected $_cacheTag = 'DamConsultants_AcquiaDam';
    /**
     * @var $_eventPrefix
     */
    protected $_eventPrefix = 'DamConsultants_AcquiaDam';
    /**
     * AcquiaDam Syc Data
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(\DamConsultants\AcquiaDam\Model\ResourceModel\AcquiaDamSycData::class);
    }
}
