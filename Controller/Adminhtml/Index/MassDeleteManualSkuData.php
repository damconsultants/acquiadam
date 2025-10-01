<?php
namespace DamConsultants\AcquiaDam\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use DamConsultants\AcquiaDam\Model\ResourceModel\Collection\MagentoSkuCollectionFactory;
use Magento\Framework\AuthorizationInterface;

class MassDeleteManualSkuData extends Action
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
     * @var $magentoSku
     */
    protected $magentoSku;
	protected $authorization;
    /**
     * Mass Delete
     *
     * @param Context $context
     * @param Filter $filter
     * @param MagentoSkuCollectionFactory $collectionFactory
     * @param \DamConsultants\AcquiaDam\Model\MagentoSkuFactory $bynderFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        MagentoSkuCollectionFactory $collectionFactory,
		AuthorizationInterface $authorization,
        \DamConsultants\AcquiaDam\Model\MagentoSkuFactory $MagentoSkuFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->magentoSku = $MagentoSkuFactory;
		$this->authorization = $authorization;
        parent::__construct($context);
    }
	/**
     * Is Allowed
     */
    public function _isAllowed()
    {
        return $this->authorization->isAllowed('DamConsultants_AcquiaDam::manual_cron_massdelete');
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
                $model = $this->magentoSku->create()->load($model->getId());
                $model->delete();
                $count++;
            }
            $this->messageManager->addSuccess(__('A total of %1 data(s) have been deleted.', $count));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('acquiadam/index/sku');
    }
}
