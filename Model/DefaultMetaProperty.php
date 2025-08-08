<?php

namespace DamConsultants\AcquiaDam\Model;

class DefaultMetaProperty extends \Magento\Framework\Model\AbstractModel
{
    protected const CACHE_TAG = 'DamConsultants_AcquiaDam';

    /**
     * @var $_cacheTag
     */
    protected $_cacheTag = 'DamConsultants_AcquiaDam';

    /**
     * @var $_eventPrefix
     */
    protected $_eventPrefix = 'DamConsultants_AcquiaDam';

    /**
     * Meta Property
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(\DamConsultants\AcquiaDam\Model\ResourceModel\DefaultMetaProperty::class);
    }
}
