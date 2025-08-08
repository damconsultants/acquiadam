<?php

namespace DamConsultants\AcquiaDam\Model\ResourceModel\Collection;

class AcquiaDamAutoReplaceDataCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    
    /**
     * AcquiaDamConfigSyncDataCollection
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(
            \DamConsultants\AcquiaDam\Model\AcquiaDamAutoReplaceData::class,
            \DamConsultants\AcquiaDam\Model\ResourceModel\AcquiaDamAutoReplaceData::class
        );
    }
}
