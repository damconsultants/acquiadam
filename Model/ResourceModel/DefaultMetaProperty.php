<?php

namespace DamConsultants\AcquiaDam\Model\ResourceModel;

class DefaultMetaProperty extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * MetaProperty
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init('widen_default_metaproperty', 'id');
    }
}
