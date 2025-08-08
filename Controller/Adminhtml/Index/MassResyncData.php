<?php
namespace DamConsultants\AcquiaDam\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use DamConsultants\AcquiaDam\Model\ResourceModel\Collection\AcquiaDamSycDataCollectionFactory;

class MassResyncData extends Action
{
    /**
     * @var $collectionFactory
     */
    public $collectionFactory;
    /**
     * @var $filter
     */
    public $filter;
    /**
     * @var AcquiaDamFactory
     */
    public $AcquiaDamFactory;
    /**
     * @var productRepository
     */
    public $_productRepository;
    /**
     * @var storeManagerInterface
     */
    public $storeManagerInterface;
    /**
     * Closed constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param AcquiaDamSycDataCollectionFactory $collectionFactory
     * @param \DamConsultants\AcquiaDam\Model\AcquiaDamSycDataFactory $acquiadamFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Catalog\Model\Product\Action $action
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     */
    public function __construct(
        Context $context,
        Filter $filter,
        AcquiaDamSycDataCollectionFactory $collectionFactory,
        \DamConsultants\AcquiaDam\Model\AcquiaDamSycDataFactory $acquiadamFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\Product\Action $action,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->AcquiaDamFactory = $acquiadamFactory;
        $this->_productRepository = $productRepository;
        $this->action = $action;
        $this->storeManagerInterface = $storeManagerInterface;
        parent::__construct($context);
    }
    /**
     * Execute
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $storeId = $this->storeManagerInterface->getStore()->getId();
            $count = 0;
            foreach ($collection as $model) {
                if ($model->getStatus() == 2) {
                    $_product = $this->_productRepository->get($model->getSku());
                    $product_ids[] = $_product->getId();
                    $model = $this->AcquiaDamFactory->create()->load($model->getId());
                    $model->setLable('2');
                    $model->setStatus('0');
                    $model->save();
                    $count++;
                }
            }
            $updated_values = [
                'widen_cron_sync' => null
            ];
            $this->action->updateAttributes(
                $product_ids,
                $updated_values,
                $storeId
            );
            $this->messageManager->addSuccess(__('A total of %1 data(s) have been Re-Sync.', $count));
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
        return $this->_authorization->isAllowed('DamConsultants_AcquiaDam::resync');
    }
}
