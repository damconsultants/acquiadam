<?php

namespace DamConsultants\AcquiaDam\Ui\DataProvider\Product;

use DamConsultants\AcquiaDam\Model\ResourceModel\Collection\AcquiaDamConfigSyncDataCollectionFactory;

class SyncDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    /**
     * @param AcquiaDamConfigSyncDataCollectionFactory $AcquiaDamSycDataCollectionFactory
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        AcquiaDamConfigSyncDataCollectionFactory $AcquiaDamSycDataCollectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $collection = $AcquiaDamSycDataCollectionFactory;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        return $this->collection = $AcquiaDamSycDataCollectionFactory->create();
    }
}
