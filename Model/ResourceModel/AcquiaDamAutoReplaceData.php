<?php

namespace DamConsultants\AcquiaDam\Model\ResourceModel;

class AcquiaDamAutoReplaceData extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Bynder Syc Data
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init('widen_cron_replace_data', 'id');
    }
}
