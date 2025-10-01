<?php

namespace DamConsultants\AcquiaDam\Model\ResourceModel\Collection;

class MagentoSkuCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    
    /**
     * MagentoSkuCollection
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(
            \DamConsultants\AcquiaDam\Model\MagentoSku::class,
            \DamConsultants\AcquiaDam\Model\ResourceModel\MagentoSku::class
        );
    }
}
