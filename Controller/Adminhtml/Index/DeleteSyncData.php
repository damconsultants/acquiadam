<?php
namespace DamConsultants\AcquiaDam\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class DeleteSyncData extends Action
{
    /**
     * @var AcquiaDamConfigSyncDataFactory
     */
    public $acquiadamConfigSyncDataFactory;
    /**
     * Closed constructor.
     *
     * @param Context $context
     * @param DamConsultants\AcquiaDam\Model\AcquiaDamConfigSyncDataFactory $AcquiaDamConfigSyncDataFactory
     */
    public function __construct(
        Context $context,
        \DamConsultants\AcquiaDam\Model\AcquiaDamConfigSyncDataFactory $AcquiaDamConfigSyncDataFactory
    ) {
        $this->acquiadamConfigSyncDataFactory = $AcquiaDamConfigSyncDataFactory;
        parent::__construct($context);
    }
    /**
     * Execute
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        try {
            $syncModel = $this->acquiadamConfigSyncDataFactory->create();
            $syncModel->load($id);
            $syncModel->delete();
            $this->messageManager->addSuccessMessage(__('You deleted the sync data.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setPath('acquiadam/index/sync');
    }
    /**
     * Execute
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('DamConsultants_AcquiaDam::delete');
    }
}
