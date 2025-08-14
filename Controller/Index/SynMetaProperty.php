<?php

namespace DamConsultants\AcquiaDam\Controller\Index;

use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Controller\ResultFactory;
use DamConsultants\AcquiaDam\Model\ResourceModel\Collection\DefaultMetaPropertyCollectionFactory;
use \DamConsultants\AcquiaDam\Model\DefaultMetaPropertyFactory;

class SynMetaProperty extends \Magento\Framework\App\Action\Action
{
    /**
     * @var logger
     */
    protected $_logger;
    /**
     * @var productCollectionFactory
     */
    protected $_productCollectionFactory;
    /**
     * @var attribute
     */
    protected $productRepository;
    /**
     * @var storeManager
     */
    protected $storeManager;
    /**
     * @var productAction
     */
    protected $productAction;
    /**
     * @var productRepositoryModel
     */
    protected $_productRepositoryModel;
    /**
     * @var defaultMetaPropertyFactory
     */
    protected $defaultMetaPropertyFactory;
    /**
     * @var collection
     */
    protected $collection;
    /**
     * @var resultRedirect
     */
    protected $_resultRedirect;
    /**
     * @var resultJsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var messageManager
     */
    protected $messageManager;
    /**
     * @var helperdata
     */
    protected $_helperdata;
    /**
     * Get
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \DamConsultants\AcquiaDam\Helper\Data $HelperData
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param DefaultMetaPropertyFactory $DefaultMetaPropertyFactory
     * @param DefaultMetaPropertyCollectionFactory $collection
     * @param StoreManagerInterface $storeManager
     * @param ProductRepository $productRepositoryModel
     * @param ProductAction $action
     * @param \Psr\Log\LoggerInterface $logger
     * @param ResultFactory $result
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \DamConsultants\AcquiaDam\Helper\Data $HelperData,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        DefaultMetaPropertyFactory $DefaultMetaPropertyFactory,
        DefaultMetaPropertyCollectionFactory $collection,
        StoreManagerInterface $storeManager,
        ProductRepository $productRepositoryModel,
        ProductAction $action,
        \Psr\Log\LoggerInterface $logger,
        ResultFactory $result,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_logger = $logger;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->productAction = $action;
        $this->resultJsonFactory = $jsonFactory;
        $this->_resultRedirect = $result;
        $this->collection = $collection;
        $this->_helperdata = $HelperData;
        $this->defaultMetaPropertyFactory = $DefaultMetaPropertyFactory;
        $this->_productRepositoryModel = $productRepositoryModel;
        $this->messageManager = $messageManager;
        return parent::__construct($context);
    }
    /**
     * Execute
     *
     * @return $this
     */
    public function execute()
    {
        $get_meta_data = $this->getDefaultMetaData();
        $metaproperties_data = $get_meta_data['metadata'];
        $resultRedirect = $this->resultRedirectFactory->create();
        $result = $this->resultJsonFactory->create();
        $model = $this->defaultMetaPropertyFactory->create();
        $metaCollection = $this->collection->create();
        $meta_property = [];
        if (count($metaCollection) > 0) {
            foreach ($metaCollection as $collection) {
                $meta_property[] = $collection['property_id'];
            }
        }
        if (isset($metaproperties_data)) {
            if (count($metaproperties_data) > 0) {
                foreach ($metaproperties_data as $key => $meta) {
                    if (!in_array($key, $meta_property)) {
                        $data =  [
                            'property_name' => $meta,
                            'property_id' => $key,
                            'widen_property_slug' => $key,
                            'property_search_query' => "",
                            'possible_values' => "",
                            'status' => 1
                        ];
                        if ($model->setData($data)->save()) {
                            $message = __('Data Sync Successfully..!');
                            $result_data = $result->setData(['status' => 1, 'message' => 'Data Sync Successfully..!']);
                        } else {
                            $message = __('Data not Sync..!');
                            $result_data = $result->setData(['status' => 0, 'message' => 'Data not Sync..!']);
                        }
                    } else {
                        $message = __("New Data Not Available That's Why Data Not Sync ..!");
                        $result_data = $result->setData([
                            'status' => 0,
                            'message' => "New Data Not Available That's Why Data Not Sync ..!"
                        ]);
                    }
                }
                $this->messageManager->addSuccessMessage($message);
                return $result_data;
            }
        }
    }
    /**
     * Get Default Meta Data
     */
    public function getDefaultMetaData()
    {
        $property_name = "";
        $response_data = [];
        $newArr = [];
        $attribute_array = [];
        $metadata = $this->_helperdata->getAttributeDefaultData();
        $category_repsonse = json_decode($metadata, true);
        if (isset($category_repsonse['items'])) {
            foreach ($category_repsonse['items'] as $items) {
                foreach ($items['metadata_info']['field_set_fields'] as $facetCatValue) {
                    $catDataJson = $this->_helperdata->getCheckboxWiseSearch($facetCatValue['key']);
                    $catData = json_decode($catDataJson, true);
                    if (isset($catData['items'][0])) {
                        $assetValue = $catData['items'][0];
                        if (isset($assetValue['metadata_info']['field_set_fields'])) {
                            foreach ($assetValue['metadata_info']['field_set_fields'] as $metaKey => $metadataValue) {
                                $attribute_array[$metadataValue['key']] = $metadataValue['label'];
                            }
                        }
                    }
                    if (isset($catData['facets']['metadata'])) {
                        foreach ($catData['facets']['metadata'] as $facetValue) {
                            if (!isset($attribute_array[$facetValue['display_key']])) {
                                $attribute_array[$facetValue['display_key']] = $facetValue['display_name'];
                            }
                        }
                    }
                }
            }
        }
        if (count($attribute_array) > 0) {
            $response_data['metadata'] = $attribute_array;
        } else {
            $response_data['metadata'] = [];
        }
        return $response_data;
    }
}
