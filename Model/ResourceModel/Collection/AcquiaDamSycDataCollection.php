<?php

namespace DamConsultants\AcquiaDam\Model\ResourceModel\Collection;

class AcquiaDamSycDataCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * MetaPropertyCollection
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(
            \DamConsultants\AcquiaDam\Model\AcquiaDamSycData::class,
            \DamConsultants\AcquiaDam\Model\ResourceModel\AcquiaDamSycData::class
        );
    }
}
