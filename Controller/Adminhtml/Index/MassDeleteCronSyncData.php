<?php
namespace DamConsultants\AcquiaDam\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use DamConsultants\AcquiaDam\Model\ResourceModel\Collection\AcquiaDamSycDataCollectionFactory;

class MassDeleteCronSyncData extends Action
{
    /**
     * @var $collectionFactory
     */
    public $collectionFactory;
    /**
     * @var Filter
     */
    public $filter;
    /**
     * @var acquiaDamFactory
     */
    public $acquiaDamFactory;
    /**
     * Closed constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param AcquiaDamSycDataCollectionFactory $collectionFactory
     * @param \DamConsultants\AcquiaDam\Model\AcquiaDamSycDataFactory $acquiadamFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        AcquiaDamSycDataCollectionFactory $collectionFactory,
        \DamConsultants\AcquiaDam\Model\AcquiaDamSycDataFactory $acquiadamFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->acquiaDamFactory = $acquiadamFactory;
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
                $model = $this->acquiaDamFactory->create()->load($model->getId());
                $model->delete();
                $count++;
            }
            $this->messageManager->addSuccess(__('A total of %1 data(s) have been deleted.', $count));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('acquiadam/index/acquiadamgrid');
    }
    /**
     * Is Allowed
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('DamConsultants_AcquiaDam::delete');
    }
}
