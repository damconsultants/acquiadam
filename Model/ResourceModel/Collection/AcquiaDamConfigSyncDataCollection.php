<?php

namespace DamConsultants\AcquiaDam\Model\ResourceModel\Collection;

class AcquiaDamConfigSyncDataCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    
    /**
     * AcquiaDam ConfigSyncDataCollection
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(
            \DamConsultants\AcquiaDam\Model\AcquiaDamConfigSyncData::class,
            \DamConsultants\AcquiaDam\Model\ResourceModel\AcquiaDamConfigSyncData::class
        );
    }
}
