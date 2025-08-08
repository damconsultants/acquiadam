<?php

namespace DamConsultants\AcquiaDam\Model\ResourceModel;

class MetaProperty extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * MetaProperty
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init('widen_metaproperty', 'id');
    }
}
