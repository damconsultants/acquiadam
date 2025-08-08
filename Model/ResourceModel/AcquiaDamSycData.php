<?php

namespace DamConsultants\AcquiaDam\Model\ResourceModel;

class AcquiaDamSycData extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * AcquiaDam Syc Data
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init('widen_cron_data', 'id');
    }
}
