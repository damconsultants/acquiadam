<?php

namespace DamConsultants\AcquiaDam\Model\ResourceModel;

class MagentoSku extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Widen Syc Data
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init('widen_update_sku', 'id');
    }
}
