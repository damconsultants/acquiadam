<?php
/**
 * DamConsultants
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  DamConsultants
 * @package   DamConsultants_AcquiaDam
 *
 */
namespace DamConsultants\AcquiaDam\Controller\Index;

use DamConsultants\AcquiaDam\Model\ResourceModel\Collection\MetaPropertyCollectionFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class InitAcquiaDamDocPopup extends \Magento\Framework\App\Action\Action
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
     * @var productRepository
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
     * @var metaPropertyCollectionFactory
     */
    protected $metaPropertyCollectionFactory;
    /**
     * @var helper
     */
    protected $_helper;
    /**
     * @var assetRepo
     */
    protected $_assetRepo;
    /**
     * @var allow_assets_type
     */
    protected $allow_assets_type;
    /**
     * Get
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \DamConsultants\AcquiaDam\Helper\Data $Helper_Data
     * @param MetaPropertyCollectionFactory $metaPropertyCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param ProductRepository $productRepositoryModel
     * @param ProductAction $action
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \DamConsultants\AcquiaDam\Helper\Data $Helper_Data,
        MetaPropertyCollectionFactory $metaPropertyCollectionFactory,
        StoreManagerInterface $storeManager,
        ProductRepository $productRepositoryModel,
        ProductAction $action,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_logger = $logger;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->productAction = $action;
        $this->_productRepositoryModel = $productRepositoryModel;
        $this->metaPropertyCollectionFactory = $metaPropertyCollectionFactory;
        $this->_helper = $Helper_Data;
        $this->_assetRepo = $assetRepo;
        $this->allow_assets_type = ["pdf","office"];
        /*$this->allow_assets_type = array("generic_binary","pdf","office");*/
        return parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return $this
     */
    public function execute()
    {
        $scrollId = '';
        
        $collection_data_value = [];
        $collection_data_slug_val = [];
        
        $collection = $this->metaPropertyCollectionFactory->create()->getData();

        if (count($collection) >= 1) {
            foreach ($collection as $key => $collection_value) {
                
                $collection_data_value[] = [
                    'id' => $collection_value['id'],
                    'property_name' => $collection_value['property_name'],
                    'property_id' => $collection_value['property_id'],
                    'widen_property_slug' => $collection_value['widen_property_slug'],
                    'system_slug' => $collection_value['system_slug'],
                    'system_name' => $collection_value['system_name'],
                ];
                $collection_data_slug_val[$collection_value['system_slug']] = [
                    'widen_property_slug' => $collection_value['widen_property_slug'],
                ];
            }
        }
        $query = $this->getRequest()->getParam('query');
        if ($query == "modalLoadData") {
            $param_data = [
                "query" => $query,
                "collection_slug_details" => $collection_data_slug_val
            ];
        } elseif ($query == "loadMoreBtn") {
            $scrollId = $this->getRequest()->getParam('scrollId');
            $jValueData = $this->getRequest()->getParam('jValueData');
            $param_data = [
                "query" => $query,
                "collection_slug_details" => $collection_data_slug_val,
                "scrollId" => $scrollId,
                "jValueData" => $jValueData
            ];
        } elseif ($query == "checkboxValue") {
            $param_sorting_data = $this->getRequest()->getParam('sortingData');
            $param_checkBox_val = $this->getRequest()->getParam('checkBoxVal');
            $param_searchKey = $this->getRequest()->getParam('searchKey');
            $param_goKey = $this->getRequest()->getParam('goKey');
            $param_getAsset_data = $this->getRequest()->getParam('getAssetData');

            $param_data = [
                "query" => $query,
                "collection_slug_details" => $collection_data_slug_val,
                "sortingData" => $param_sorting_data,
                "checkBoxVal" => $param_checkBox_val,
                "searchKey" => $param_searchKey,
                "goKey" => $param_goKey,
                "getAssetData" => $param_getAsset_data
            ];
        }
        $param_data["allow_assets_type"] = $this->allow_assets_type;
        $res_arr = $this->_helper->getAcquiaDamFilterData($param_data);
        return $this->getResponse()->setBody($res_arr);
    }
}
