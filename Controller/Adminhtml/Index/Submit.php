<?php

namespace DamConsultants\AcquiaDam\Controller\Adminhtml\Index;

use DamConsultants\AcquiaDam\Model\ResourceModel\Collection\MetaPropertyCollectionFactory;

class Submit extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory = false;
    /**
     * @var helperData
     */
    protected $_helperData;
    /**
     * @var metaProperty
     */
    protected $metaProperty;
    /**
     * @var metaPropertyCollectionFactory
     */
    protected $metaPropertyCollectionFactory;

    /**
     * Submit.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \DamConsultants\AcquiaDam\Helper\Data $helperData
     * @param \DamConsultants\AcquiaDam\Model\MetaPropertyFactory $metaProperty
     * @param MetaPropertyCollectionFactory $metaPropertyCollectionFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \DamConsultants\AcquiaDam\Helper\Data $helperData,
        \DamConsultants\AcquiaDam\Model\MetaPropertyFactory $metaProperty,
        MetaPropertyCollectionFactory $metaPropertyCollectionFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_helperData = $helperData;
        $this->metaProperty = $metaProperty;
        $this->metaPropertyCollectionFactory = $metaPropertyCollectionFactory;
        $this->resultPageFactory = $resultPageFactory;
    }
    /**
     * Execute
     *
     * @return $this
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $properites_system_slug = $this->getRequest()->getParam('system_slug');
            $select_meta_tag = $this->getRequest()->getParam('select_meta_tag');
            $collection = $this->metaPropertyCollectionFactory->create();
            $collection_get_data = $collection->getData();
            $meta = [];
            $properties_details = [];
            $all_properties_slug = [];

            if (count($collection_get_data) > 0) {
                foreach ($collection_get_data as $metacollection) {
                    $properties_details[$metacollection['system_slug']] = [
                        "id" => $metacollection['id'],
                        "property_name" => $metacollection['property_name'],
                        "property_id" => $metacollection['property_id'],
                        "widen_property_slug" => $metacollection['widen_property_slug'],
                        "system_slug" => $metacollection['system_slug'],
                        "system_name" => $metacollection['system_name'],
                    ];
                }
                
                $all_properties_slug = array_keys($properties_details);
                foreach ($properites_system_slug as $key => $form_system_slug) {
                    if (in_array($form_system_slug, $all_properties_slug)) {
                        /* update data */
                        $pro_id = $properties_details[$form_system_slug]["id"];
                        $model = $this->metaProperty->create()->load($pro_id);
                    } else {
                        /*insert data*/
                        $model = $this->metaProperty->create();
                    }
                    
                    $model->setData('property_name', $select_meta_tag[$key]);
                    $model->setData('property_id', $select_meta_tag[$key]);
                    $model->setData('widen_property_slug', $select_meta_tag[$key]);
                    $model->setData('system_slug', $form_system_slug);
                    $model->setData('system_name', $form_system_slug);
                    $model->save();
                }
            } else {
                /* insert all data */
                foreach ($properites_system_slug as $key => $form_system_slug) {
                    $model = $this->metaProperty->create();
                    $model->setData('property_name', $select_meta_tag[$key]);
                    $model->setData('property_id', $select_meta_tag[$key]);
                    $model->setData('widen_property_slug', $select_meta_tag[$key]);
                    $model->setData('system_slug', $form_system_slug);
                    $model->setData('system_name', $form_system_slug);
                    $model->save();
                }
            }

            $message = __('Submited MetaProperty...!');
            $this->messageManager->addSuccessMessage($message);
            $this->resultPageFactory->create();
            return $resultRedirect->setPath('acquiadam/index/acquiadammetaproperty');
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t submit your request, Please try again.'));
        }
    }
}
