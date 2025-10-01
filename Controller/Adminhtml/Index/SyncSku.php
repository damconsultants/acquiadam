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

namespace DamConsultants\AcquiaDam\Controller\Adminhtml\Index;

use DamConsultants\AcquiaDam\Model\ResourceModel\Collection\MetaPropertyCollectionFactory;

class SyncSku extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory = false;
    /**
     * @var attribute
     */
    protected $attribute;
    /**
     * @var collectionFactory
     */
    protected $collectionFactory;
    /**
     * @var storeManagerInterface
     */
    protected $storeManagerInterface;
    /**
     * @var resultJsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var productAction
     */
    protected $productAction;
    /**
     * @var helperData
     */
    protected $_helperData;
    /**
     * @var productRepository
     */
    protected $_productRepository;
    /**
     * @var metaPropertyCollectionFactory
     */
    protected $metaPropertyCollectionFactory;
    /**
     * @var productAttributeManagementInterface
     */
    protected $productAttributeManagementInterface;
    /**
     * @var product
     */
    protected $_product;
    /**
     * @var file
     */
    protected $file;
    /**
     * @var driverFile
     */
    protected $driverFile;
    /**
     * @var acquiadamsycData
     */
    protected $_acquiadamsycData;

    /**
     * Get Sku.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \DamConsultants\AcquiaDam\Helper\Data $helperData
     * @param \Magento\Catalog\Model\Product\Action $action
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param MetaPropertyCollectionFactory $metaPropertyCollectionFactory
     * @param \Magento\Catalog\Api\ProductAttributeManagementInterface $productAttributeManagementInterface
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Filesystem\Io\File $file
     * @param \Magento\Framework\Filesystem\Driver\File $driverFile
     * @param \DamConsultants\AcquiaDam\Model\AcquiaDamConfigSyncDataFactory $acquiadamsycData
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \DamConsultants\AcquiaDam\Helper\Data $helperData,
        \Magento\Catalog\Model\Product\Action $action,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        MetaPropertyCollectionFactory $metaPropertyCollectionFactory,
        \Magento\Catalog\Api\ProductAttributeManagementInterface $productAttributeManagementInterface,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Filesystem\Driver\File $driverFile,
        \DamConsultants\AcquiaDam\Model\AcquiaDamConfigSyncDataFactory $acquiadamsycData
    ) {
        parent::__construct($context);
        $this->attribute = $attribute;
        $this->collectionFactory = $collectionFactory;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->resultJsonFactory = $jsonFactory;
        $this->productAction = $action;
        $this->_helperData = $helperData;
        $this->_productRepository = $productRepository;
        $this->metaPropertyCollectionFactory = $metaPropertyCollectionFactory;
        $this->productAttributeManagementInterface = $productAttributeManagementInterface;
        $this->_product = $product;
        $this->file = $file;
        $this->driverFile = $driverFile;
        $this->_acquiadamsycData = $acquiadamsycData;
    }
    /**
     * Execute
     *
     * @return $this
     */
    public function execute()
	{
		if (!$this->getRequest()->isAjax()) {
			return $this->_forward('noroute');
		}

		$result = $this->resultJsonFactory->create();

		$productSkus   = array_filter(explode(",", (string)$this->getRequest()->getParam('product_sku')));
		$selectAttr    = $this->getRequest()->getParam('select_attribute');
		$extraDetails  = [
			"is_widen_cdn" => $this->getRequest()->getParam('is_widen_cdn'),
			"is_mg_import" => $this->getRequest()->getParam('is_magento_import')
		];

		// 🔹 Fetch meta property collection
		$collection = $this->metaPropertyCollectionFactory->create();
		$properties = $collection->getData();

		if (empty($properties)) {
			return $result->setData(['status' => 0, 'message' => 'Please Select The Metaproperty First.....']);
		}

		// 🔹 Build property details
		$propertiesDetails = [];
		foreach ($properties as $meta) {
			$propertiesDetails[$meta['system_slug']] = [
				"id"                 => $meta['id'],
				"property_name"      => $meta['property_name'],
				"property_id"        => $meta['property_id'],
				"widen_property_slug"=> $meta['widen_property_slug'],
				"system_slug"        => $meta['system_slug'],
				"system_name"        => $meta['system_name'],
			];
		}
		$allSlugs = array_keys($propertiesDetails);

		if (empty($productSkus)) {
			return $result->setData(['status' => 0, 'message' => 'Please enter at least one SKU.']);
		}

		// 🔹 Loop SKUs
		foreach ($productSkus as $sku) {
			try {
				$_product = $this->_productRepository->get($sku);
			} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
				$this->logSyncError($sku, "SKU not found in products");
				continue;
			}

			// Prepare style/color data
			$colorStyle = [];
			if (!empty($_product->getStyle()) || !empty($_product->getColor())) {
				$colorStyle = [
					"color_number" => $_product->getAttributeText('color'),
					"style_number" => $_product->getStyle()
				];
			}

			// Call API
			$response = $this->_helperData->getAcquiaDamImageSyncWithProperties($colorStyle, $propertiesDetails);
			$decoded  = json_decode($response, true);
			$data     = $decoded['data'] ?? [];
			if (!empty($data)) {
				try {
					$this->getDataItem($selectAttr, $data, $allSlugs, $sku, $extraDetails);
				} catch (\Exception $e) {
					$this->logSyncError($sku, $e->getMessage());
				}
			} else {
				$this->logSyncError($sku, "Something went wrong from API side, Please contact support!");
			}
		}

		return $result->setData([
			'status'  => 1,
			'message' => 'Data Sync Successfully. Please check AcquiaDam Synchronization Log.!'
		]);
	}

	/**
	 * Helper to insert error logs.
	 */
	private function logSyncError(string $sku, string $message): void
	{
		$this->getInsertDataTable([
			"sku"     => $sku,
			"message" => $message,
			"data_type" => "",
			"lable"   => "0"
		]);
	}

    /**
     * Is Json
     *
     * @param string $string
     * @return $this
     */
    public function getIsJSON($string)
    {
        return ((json_decode($string)) === null) ? false : true;
    }
    /**
     * Is Json
     *
     * @param array $insert_data
     * @return $this
     */
    public function getInsertDataTable($insert_data)
    {
        $model = $this->_acquiadamsycData->create();
        $data_image_data = [
            'sku' => $insert_data['sku'],
            'widen_sync_data' => $insert_data['message'],
            'widen_data_type' => $insert_data['data_type'],
            'lable' => $insert_data['lable']
        ];
        $model->setData($data_image_data);
        $model->save();
    }
    /**
     * Get Data Item
     *
     * @param string $select_attribute
     * @param array $get_data
     * @param array $all_properties_slug
     * @param array $sku
     * @param array $extra_details
     */
    public function getDataItem($select_attribute, $get_data, $all_properties_slug, $sku, $extra_details)
	{
		$_product    = $this->_productRepository->get($sku);
		$storeId     = $this->storeManagerInterface->getStore()->getId();
		$productId   = $_product->getId();
		$imageValue  = $_product->getWidenMultiImg();
		$docValue    = $_product->getWidenDocument();
		$mediaUrl    = $this->storeManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

		if (empty($get_data)) {
			return;
		}

		switch ($select_attribute) {
			case 'image':
				$this->processImages($sku, $productId, $storeId, $imageValue, $get_data, $extra_details);
				break;

			case 'video':
				$this->processVideos($sku, $productId, $storeId, $imageValue, $get_data, $extra_details, $mediaUrl);
				break;

			case 'document':
			default:
				$this->processDocuments($sku, $productId, $storeId, $docValue, $get_data);
				break;
		}
	}

	/**
	 * Handle image sync logic
	 */
	private function processImages($sku, $productId, $storeId, $imageValue, $get_data, $extra)
	{
		$imageDetail      = [];
		$diffImageDetail  = [];
		$existingUrls     = [];

		if (!empty($imageValue)) {
			$oldValues = json_decode($imageValue, true);

			// Collect existing image URLs
			foreach ($oldValues as $img) {
				if ($img['item_type'] === 'image') {
					$existingUrls[] = $this->getPerfectVideoUrl($img['item_url']);
				}
			}
		} else {
			$oldValues = [];
		}

		// Build new image data
		foreach ($get_data as $data) {
			if ($data['Type'] !== 'image') {
				continue;
			}

			$url      = $this->getPerfectVideoUrl($data['Image_Url']);
			$baseUrl  = explode("?", $url)[0];
			$roles    = $this->getRoleArray($data);
			[$width, $height] = $this->extractDimensions($url);

			$imgData = [
				"item_url"              => $baseUrl,
				"altText"               => $data['Alt_Text'],
				"image_role"            => $roles,
				"item_type"             => $data['Type'],
				"thum_url"              => $baseUrl,
				"selected_template_url" => $baseUrl,
				"height"                => $height,
				"width"                 => $width,
				"asset_order"           => $data['asset_order'],
				"is_import"             => "0"
			];

			if (!in_array($baseUrl, $existingUrls)) {
				$diffImageDetail[] = $imgData;
				$total_new_value = count($diffImageDetail);
				if ($total_new_value > 1) {
					foreach ($diffImageDetail as $nn => $n_img) {
						if ($n_img['item_type'] == "image" && $nn != ($total_new_value - 1)) {
							if ($roles) {
								$new_mg_role_array = (array)$roles;
								if (count($n_img["image_role"]) > 0 && count($new_mg_role_array) > 0) {
									$result_val=array_diff($n_img["image_role"], $new_mg_role_array);
									$diffImageDetail[$nn]["image_role"] = $result_val;
								}
							}
						}
					}
				}
				$this->logAsset($sku, $baseUrl, '1');
			} else {
				$imageDetail[] = $imgData;
				$total_new_value = count($imageDetail);
				if ($total_new_value > 1) {
					foreach ($imageDetail as $nn => $n_img) {
						if ($n_img['item_type'] == "image" && $nn != ($total_new_value - 1)) {
							if ($roles) {
								$new_mg_role_array = (array)$roles;
								if (count($n_img["image_role"]) > 0 && count($new_mg_role_array) > 0) {
									$result_val=array_diff($n_img["image_role"], $new_mg_role_array);
									$imageDetail[$nn]["image_role"] = $result_val;
								}
							}
						}
					}
				}
			}
		}

		$merged = array_merge($imageDetail, $diffImageDetail);

		$flag = $this->getFlag(array_column($merged, 'item_type'));
		$json = json_encode($merged, true);

		if (!empty($extra['is_mg_import'])) {
			$json = $this->uploadImageToProduct($json, $productId);
		}

		$update = ['widen_multi_img' => $json, 'widen_isMain' => $flag, 'widen_auto_replace' => 1];
		if (!empty($extra['is_widen_cdn'])) {
			$update['use_widen_cdn'] = 1;
		}

		$this->productAction->updateAttributes([$productId], $update, $storeId);
	}

	/**
	 * Handle video sync logic
	 */
	private function processVideos($sku, $productId, $storeId, $imageValue, $get_data, $extra, $mediaUrl)
	{
		$videoDetail  = [];
		$existingUrls = [];

		if (!empty($imageValue)) {
			$oldValues = json_decode($imageValue, true);
			foreach ($oldValues as $video) {
				$existingUrls[] = $this->getPerfectVideoUrl($video['item_url']);
			}
		} else {
			$oldValues = [];
		}

		foreach ($get_data as $data) {
			if ($data['Type'] !== 'video') {
				continue;
			}

			$url = $this->getPerfectVideoUrl($data['Image_Url']);
			if (in_array($url, $existingUrls)) {
				continue;
			}

			$thumb = $this->dataHelper->getMakeVideoasThumbForSync($data, $sku, $mediaUrl);
			$videoDetail[] = [
				"item_url"              => $url,
				"altText"               => $data['Alt_Text'] ?? "",
				"image_role"            => null,
				"item_type"             => $data['Type'],
				"thum_url"              => $url,
				"selected_template_url" => $thumb['template_url'] ?? $url,
				"height"                => "",
				"width"                 => "",
				"asset_order"           => $data['asset_order'],
				"is_import"             => "0"
			];
			$this->logAsset($sku, $url, '3');
		}

		if (empty($videoDetail)) {
			return;
		}

		$merged = array_merge($oldValues, $videoDetail);
		$flag   = $this->getFlag(array_column($merged, 'item_type'));

		$update = ['widen_multi_img' => json_encode($merged, true), 'widen_isMain' => $flag];
		if (!empty($extra['is_widen_cdn'])) {
			$update['use_widen_cdn'] = 1;
		}

		$this->productAction->updateAttributes([$productId], $update, $storeId);
	}

	/**
	 * Handle document sync logic
	 */
	private function processDocuments($sku, $productId, $storeId, $docValue, $get_data)
	{
		$docDetail     = [];
		$existingUrls  = [];

		if (!empty($docValue)) {
			$oldValues = json_decode($docValue, true);
			foreach ($oldValues as $doc) {
				$existingUrls[] = $doc["item_url"];
			}
		} else {
			$oldValues = [];
		}

		foreach ($get_data as $data) {
			if (!in_array($data['Type'], ['pdf', 'office'])) {
				continue;
			}

			$url = $this->getPerfectVideoUrl($data["Image_Url"]);
			if (in_array($url, $existingUrls)) {
				continue;
			}

			$docDetail[] = [
				"item_url"    => $url,
				"item_type"   => $data['Type'],
				"altText"     => $data['Alt_Text'],
				"doc_name"    => $data['Alt_Text'],
				"asset_order" => $data['asset_order'],
			];
			$this->logAsset($sku, $url, '2');
		}

		if (empty($docDetail)) {
			return;
		}

		$merged = array_merge($oldValues, $docDetail);
		$this->productAction->updateAttributes([$productId], ['widen_document' => json_encode($merged, true)], $storeId);
	}

	/**
	 * Extract width/height from URL query params
	 */
	private function extractDimensions($url): array
	{
		$parsed = parse_url($url);
		if (empty($parsed['query'])) {
			return ['', ''];
		}

		parse_str($parsed['query'], $params);
		return [$params['w'] ?? '', $params['h'] ?? ''];
	}

	/**
	 * Log insert helper
	 */
	private function logAsset($sku, $message, $type)
	{
		$this->getInsertDataTable([
			'sku'     => $sku,
			'message' => $message,
			'data_type' => $type,
			'lable'   => "1"
		]);
	}

    /**
     * Get Flag
     *
     * @param array $type
     */
    public function getFlag($type)
    {
        $flag = 0;
        if (in_array("image", $type) && in_array("video", $type)) {
            $flag = 1;
        } elseif (in_array("image", $type)) {
            $flag = 2;
        } elseif (in_array("video", $type)) {
            $flag = 3;
        }
        return $flag;
    }

    /**
     * Get perfect video url
     *
     * @param string $url
     */
    public function getPerfectVideoUrl($url)
    {
        $new_url = $url;
        $query_params = [];
        if (strlen(trim($url)) > 0) {
            $query_str = parse_url($url, PHP_URL_QUERY);
            if ($query_str != null) {
                parse_str($query_str, $query_params);
                if (isset($query_params['download'])) {
                    $new_url = str_replace("&download=true", "", $url);
                }
            }
        }
        return $new_url;
    }

    /**
     * Upload image into product
     *
     * @param string $upload_details
     * @param string $id
     */
    public function uploadImageToProduct($upload_details, $id)
    {
        $product = $this->_product->load($id);
        $new_json_decode = json_decode($upload_details, true);
        $result = $this->resultJsonFactory->create();
        $dir_path = "Acquia_DAM_temp/";
        $img_dir = BP . '/pub/media/wysiwyg/' . $dir_path;
        if (!$this->file->fileExists($img_dir)) {
            $this->file->mkdir($img_dir, 0755, true);
        }
        foreach ($new_json_decode as $k => $item) {
            
            if ($item['item_type'] == 'image' && $item["is_import"] == "0") {
                $item_url = trim($item['item_url']);
                if (!empty($item_url)) {
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
                    $result_data = $result->setData([
                        'status' => 1,
                        'message' => 'Image Import in Folder Successfully..!'
                    ]);
                    unlink($img_url);
                    $new_json_decode[$k]["is_import"] = "1";
                }
            }
        }
        return json_encode($new_json_decode, true);
    }

    /**
     * Get Role Array
     *
     * @param array $widen_role_array
     */
    public function getRoleArray($widen_role_array)
	{
		$roles = $widen_role_array['image_roles'] ?? [];
		if (!is_array($roles)) {
			$roles = [$roles];
		}
		$map = [
			'ALL'           => ["image", "small_image", "thumbnail"],
			'BASE'          => ["image"],
			'SMALL'         => ["small_image"],
			'THUMB'         => ["thumbnail"],
			'PLP ROLLOVER'  => ["category_page_list_rollover"],
		];
		$img_role = [];
		foreach ($roles as $role) {
			$role = strtoupper(trim($role));
			if (isset($map[$role])) {
				$img_role = array_merge($img_role, $map[$role]);
			}
		}
		return array_unique($img_role);
	}
}
