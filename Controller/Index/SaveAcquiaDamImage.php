<?php

namespace DamConsultants\AcquiaDam\Controller\Index;

use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\ProductRepository;

class SaveAcquiaDamImage extends \Magento\Framework\App\Action\Action
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
     * @var resource
     */
    protected $_resource;
    /**
     * Get
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param ProductRepository $productRepositoryModel
     * @param ProductAction $action
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        ProductRepository $productRepositoryModel,
        ProductAction $action,
        \Magento\Framework\App\ResourceConnection $resource,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_logger = $logger;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->productAction = $action;
        $this->_productRepositoryModel = $productRepositoryModel;
        $this->_resource = $resource->getConnection();
        return parent::__construct($context);
    }
    /**
     * Execute
     *
     * @return $this
     */
    public function execute()
    {
        $data = [];
        $lastInsertId = 0;
        $img_data_post = $this->getRequest()->getPost("acquiadam_derivative_image");
        $tableName = $this->_resource->getTableName("acquiadam_image_data");
        $images_json = "";
        if (isset($img_data_post) && !empty($img_data_post)) {
            $images_json = json_encode($img_data_post);
            $data = [
                'images_json' => $images_json
            ];
            $this->_resource->insert($tableName, $data);
        }
       
        $lastInsertId = $this->_resource->lastInsertId();
        return $this->getResponse()->setBody($lastInsertId);
    }
}
