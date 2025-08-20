<?php
namespace DamConsultants\AcquiaDam\Controller\Adminhtml\Product;

class ImportImage extends \Magento\Backend\App\Action
{
    /**
     * @var string $_pageFactory;
     */
    protected $_pageFactory;
    /**
     * @var string $_product;
     */
    protected $_product;
    /**
     * @var string $file;
     */
    protected $file;
    /**
     * @var string $resultJsonFactory;
     */
    protected $resultJsonFactory;
    /**
     * @var string $driverFile;
     */
    protected $driverFile;
    /**
     * @var string $storeManagerInterface;
     */
    protected $storeManagerInterface;
    /**
     * @var string $cookieManager;
     */
    protected $cookieManager;
    /**
     * @var string $productActionObject;
     */
    protected $productActionObject;
    /**
     * @var string $_registry;
     */
    protected $_registry;
    /**
     * @var string $_resource;
     */
    protected $_resource;
    /**
     * @var string $cookieMetadataFactory;
     */
    protected $cookieMetadataFactory;
    /**
     * @var string $logger;
     */
    protected $logger;
    /**
     * Import Image.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Filesystem\Io\File $file
     * @param \Magento\Framework\Filesystem\Driver\File $driverFile
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Magento\Catalog\Model\Product\Action $productActionObject
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Filesystem\Driver\File $driverFile,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Catalog\Model\Product\Action $productActionObject,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\ResourceConnection $resource,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_product = $product;
        $this->file = $file;
        $this->resultJsonFactory = $jsonFactory;
        $this->driverFile = $driverFile;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->cookieManager = $cookieManager;
        $this->productActionObject = $productActionObject;
        $this->_registry = $registry;
        $this->_resource = $resource;
        $this->logger = $logger;
        return parent::__construct($context);
    }
    /**
     * Execute
     *
     * @return $this
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('product_id');
        $sku = $this->getRequest()->getParam('sku');
        $storeId = $this->storeManagerInterface->getStore()->getId();
        $acquiadam_multi_img = $this->getRequest()->getParam('acquiadam_in');
        $acquiadam_image = $this->getRequest()->getParam('image');
        $old_image_array = [];
        $new_image_json = [];
        $cookie_id = $this->cookieManager->getCookie('image_coockie_id');
        $connection = $this->_resource->getConnection();
        $table_name = $connection->getTableName('widen_temp_data');
        $image = "";
        if ($cookie_id != 0) {
            $select = $connection->select()
            ->from(
                ['c' => $table_name],
            )
            ->where("c.id = ?", $cookie_id);
            $records = $connection->fetchAll($select);
            if (isset($records)) {
                foreach ($records as $record) {
                    $image = $record['value'];
                }
            }
        }
        $result = $this->resultJsonFactory->create();
        $product = $this->_product->load($id);
        $storeId = $this->storeManagerInterface->getStore()->getId();
        try {
            $dir_path = "Acquia_DAM_temp/";
            $img_dir = BP . '/pub/media/wysiwyg/' . $dir_path;
            if (!$this->file->fileExists($img_dir)) {
                $this->file->mkdir($img_dir, 0755, true);
            }
            
            if (!empty($acquiadam_image)) {
                $img_array =  json_decode($acquiadam_image, true);
            } elseif ($image != 0) {
                $img_array =  json_decode($image, true);
            } elseif (!empty($acquiadam_multi_img)) {
                $img_array =  json_decode($acquiadam_multi_img, true);
            }
            $image_roll = [];
            if (count($img_array) > 0) {
                foreach ($img_array as $k => $item) {
                    if ($item['item_type'] == 'image') {
                        $item_url = trim($item['item_url']);
                        if (!empty($item_url) && isset($item["is_import"]) && $item["is_import"] == "0") {
                            $fileInfo = $this->file->getPathInfo($item_url);
                            $basename = $fileInfo['basename'];
                            $file_name = explode("?", $basename);
                            $file_name = $file_name[0];
                            $file_name = str_replace("%20", " ", $file_name);
                            $img_url = $img_dir . $file_name;
                            $this->file->write(
                                $img_url,
                                $this->driverFile->fileGetContents($item_url)
                            );
                            $product->addImageToMediaGallery($img_url, $item['image_role'], false, false);
                            $img_label = $item["altText"];
                            if ($item["altText"] != "") {
                                $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
                                foreach ($existingMediaGalleryEntries as $key => $entry) {
                                    if (empty($entry['label'])) {
                                        $entry->setLabel($img_label);
                                    }
                                }
                                $product->setStoreId(0);
                                $product->setMediaGalleryEntries($existingMediaGalleryEntries);
                            }
                            $product->save();
                            $img_array[$k]["is_import"] = "1";
                            //unlink($img_url);
                        }
                    }
                }
            }
            $res_data = [];
            $res_data['data']  = $img_array;
            $res_data['status'] = 1;
            $result_data = $result->setData([
                'status' => 1,
                'message' => 'Image Import in Folder Successfully..!',
                'data'=>$img_array
            ]);
            return $result_data;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
