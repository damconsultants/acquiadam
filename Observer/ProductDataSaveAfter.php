<?php

namespace DamConsultants\AcquiaDam\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProductDataSaveAfter implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;
    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;
    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $productActionObject;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerInterface;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $file;
    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $driverFile;
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;
    /**
     * @var \Magento\Backend\Model\View\Result\Redirect
     */
    protected $resultRedirectFactory;
    /**
     * @var \DamConsultants\AcquiaDam\Helper\Data
     */
    protected $dataHelper;

    /**
     * Product save after
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Catalog\Model\Product\Action $productActionObject
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Filesystem\Io\File $file
     * @param \Magento\Framework\Filesystem\Driver\File $driverFile
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \DamConsultants\AcquiaDam\Helper\Data $dataHelper
     * @param \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     */

    public function __construct(
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Catalog\Model\Product\Action $productActionObject,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Filesystem\Driver\File $driverFile,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \DamConsultants\AcquiaDam\Helper\Data $dataHelper,
        \Magento\Backend\Model\View\Result\Redirect $resultRedirect
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->productActionObject = $productActionObject;
        $this->_resource = $resource;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->messageManager = $messageManager;
        $this->file = $file;
        $this->driverFile = $driverFile;
        $this->assetRepo = $assetRepo;
        $this->dataHelper = $dataHelper;
        $this->resultRedirectFactory = $resultRedirect;
    }
    /**
     * Execute
     *
     * @return $this
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        $productId = $product->getId();
        $productSku = $product->getSku();
        
        $image_coockie_id = $this->cookieManager->getCookie('image_coockie_id');
        $doc_coockie_id = $this->cookieManager->getCookie('doc_coockie_id');
        $connection = $this->_resource->getConnection();
        $table_name_image = $connection->getTableName('widen_temp_data');
        $table_name_doc = $connection->getTableName('widen_temp_doc_data');
        $widen_multi_img = $product->getData('widen_multi_img');
        $image = "";
        if ($image_coockie_id != 0) {
            $selectimg = $connection->select()
            ->from(
                ['c' => $table_name_image],
            )
            ->where("c.id = ?", $image_coockie_id);
            $recordsimg = $connection->fetchAll($selectimg);
            if (isset($recordsimg)) {
                foreach ($recordsimg as $record) {
                    $image = $record['value'];
                }
            }
        } else {
            $image = $widen_multi_img;
        }
        if ($doc_coockie_id != 0) {
            $selectdoc = $connection->select()
            ->from(
                ['c' => $table_name_doc],
            )
            ->where("c.id = ?", $doc_coockie_id);
            $recordsdoc = $connection->fetchAll($selectdoc);
            if (isset($recordsdoc)) {
                foreach ($recordsdoc as $recorddoc) {
                    $doc = $recorddoc['value'];
                }
            }
        }
        $widen_image = $product->getWidenMultiImg();
        $widen_image_import = $product->getAcquiaDamImageImport();
        $currentStore = $this->storeManagerInterface->getStore();
        $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $storeId = $this->storeManagerInterface->getStore()->getId();
        $img_array = $this->dataHelper->getMakeVideoImage($image, $productSku, $mediaUrl);
        
        /******************************************Insert Data from DataBase Side****************************** */
        $flag = 0;
        if (!empty($image)) {
            if (!empty($img_array)) {
                foreach ($img_array as $img) {
                    $type[] = $img['item_type'];
                }
                if (in_array("image", $type) && in_array("video", $type)) {
                    $flag = 1;
                } elseif (in_array("image", $type)) {
                    $flag = 2;
                } elseif (in_array("video", $type)) {
                    $flag = 3;
                }
            }
            $this->productActionObject->updateAttributes(
                [$productId],
                ['widen_isMain' => $flag],
                $storeId
            );
            if (count($img_array) > 0) {
                $final_json = json_encode($img_array);
            } elseif (isset($image)) {
                $final_json = $image;
            }
            $this->productActionObject->updateAttributes([$productId], ['widen_multi_img' => $final_json], $storeId);
            $where = ["id = ?" => $image_coockie_id];
            $connection->delete($table_name_image, $where);
            $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
            $publicCookieMetadata->setDurationOneYear();
            $publicCookieMetadata->setPath('/');
            $publicCookieMetadata->setHttpOnly(false);
            $this->cookieManager->setPublicCookie(
                'image_coockie_id',
                0,
                $publicCookieMetadata
            );
        }
        if (isset($doc)) {
            $this->productActionObject->updateAttributes([$productId], ['widen_document' => $doc], $storeId);
            $this->cookieManager->deleteCookie('widen_doc');
            $where = ["id = ?" => $doc_coockie_id];
            $connection->delete($table_name_doc, $where);
            $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
            $publicCookieMetadata->setDurationOneYear();
            $publicCookieMetadata->setPath('/');
            $publicCookieMetadata->setHttpOnly(false);

            $this->cookieManager->setPublicCookie(
                'doc_coockie_id',
                0,
                $publicCookieMetadata
            );
        }
    }
}
