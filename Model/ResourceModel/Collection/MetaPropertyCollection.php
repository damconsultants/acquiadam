<?php

namespace DamConsultants\AcquiaDam\Model\ResourceModel\Collection;

class MetaPropertyCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * MetaPropertyCollection
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(
            \DamConsultants\AcquiaDam\Model\MetaProperty::class,
            \DamConsultants\AcquiaDam\Model\ResourceModel\MetaProperty::class
        );
    }
}
