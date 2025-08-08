<?php

namespace DamConsultants\AcquiaDam\Model\ResourceModel;

class AcquiaDamConfigSyncData extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * AcquiaDam Syc Data
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init('widen_config_sync_data', 'id');
    }
}
