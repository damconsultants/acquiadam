<?php
namespace DamConsultants\AcquiaDam\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class DeleteCronSyncData extends Action
{
    /**
     * @var helperData
     */
    public $acquiadamSycDataFactory;
    /**
     * Closed constructor.
     *
     * @param Context $context
     * @param DamConsultants\AcquiaDam\Model\AcquiaDamSycDataFactory $AcquiaDamSycDataFactory
     */
    public function __construct(
        Context $context,
        \DamConsultants\AcquiaDam\Model\AcquiaDamSycDataFactory $AcquiaDamSycDataFactory
    ) {
        $this->acquiadamSycDataFactory = $AcquiaDamSycDataFactory;
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
            $syncModel = $this->acquiadamSycDataFactory->create();
            $syncModel->load($id);
            $syncModel->delete();
            $this->messageManager->addSuccessMessage(__('You deleted the sync data.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setPath('acquiadam/index/acquiadamgrid');
    }
    /**
     * Execute
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('DamConsultants_AcquiaDam::delete');
    }
}
