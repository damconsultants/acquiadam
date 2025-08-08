<?php

namespace DamConsultants\AcquiaDam\Ui\DataProvider\Product;

use DamConsultants\AcquiaDam\Model\ResourceModel\Collection\AcquiaDamAutoReplaceDataCollectionFactory;

class AutoReplaceProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    /**
     * @param AcquiaDamAutoReplaceDataCollectionFactory $AcquiaDamAutoReplaceDataCollectionFactory
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        AcquiaDamAutoReplaceDataCollectionFactory $AcquiaDamAutoReplaceDataCollectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $collection = $AcquiaDamAutoReplaceDataCollectionFactory;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        return $this->collection = $AcquiaDamAutoReplaceDataCollectionFactory->create();
    }
}
