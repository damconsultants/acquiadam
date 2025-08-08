<?php
namespace DamConsultants\AcquiaDam\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use DamConsultants\AcquiaDam\Model\ResourceModel\Collection\AcquiaDamAutoReplaceDataCollectionFactory;

class MassDeleteCronAutoData extends Action
{
    /**
     * @var collectionFactory
     */
    public $collectionFactory;
    /**
     * @var filter
     */
    public $filter;
    /**
     * @var acquiadamFactory
     */
    public $acquiadamFactory;
    /**
     * Get Sku
     * @param Context $context
     * @param Filter $filter
     * @param AcquiaDamAutoReplaceDataCollectionFactory $collectionFactory
     * @param \DamConsultants\AcquiaDam\Model\AcquiaDamAutoReplaceDataFactory $acquiadamFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        AcquiaDamAutoReplaceDataCollectionFactory $collectionFactory,
        \DamConsultants\AcquiaDam\Model\AcquiaDamAutoReplaceDataFactory $acquiadamFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->acquiadamFactory = $acquiadamFactory;
        parent::__construct($context);
    }
    /**
     * Execute
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());

            $count = 0;
            foreach ($collection as $model) {
                $model = $this->acquiadamFactory->create()->load($model->getId());
                $model->delete();
                $count++;
            }
            $this->messageManager->addSuccess(__('A total of %1 data(s) have been deleted.', $count));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('acquiadam/index/replacecrongrid');
    }
    /**
     * Execute
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('DamConsultants_AcquiaDam::delete');
    }
}
