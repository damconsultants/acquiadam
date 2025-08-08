<?php

namespace DamConsultants\AcquiaDam\Model\ResourceModel\Collection;

class DefaultMetaPropertyCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * MetaPropertyCollection
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(
            \DamConsultants\AcquiaDam\Model\DefaultMetaProperty::class,
            \DamConsultants\AcquiaDam\Model\ResourceModel\DefaultMetaProperty::class
        );
    }
}
