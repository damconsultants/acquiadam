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

namespace DamConsultants\AcquiaDam\Cron;

use DamConsultants\AcquiaDam\Model\ResourceModel\Collection\MetaPropertyCollectionFactory;
use DamConsultants\AcquiaDam\Model\ResourceModel\Collection\MagentoSkuCollectionFactory;
use DamConsultants\AcquiaDam\Model\ResourceModel\MagentoSku;

class UpdateAllSku
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
	protected $magentoSkuCollectionFactory;
	protected $magentoSku;

    /**
     * Get Sku.
     *
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
		MagentoSkuCollectionFactory $magentoSkuCollectionFactory,
		MagentoSku $magentoSku,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Filesystem\Driver\File $driverFile,
        \DamConsultants\AcquiaDam\Model\AcquiaDamConfigSyncDataFactory $acquiadamsycData
    ) {
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
		$this->magentoSku = $magentoSku;
		$this->magentoSkuCollectionFactory = $magentoSkuCollectionFactory;
        $this->file = $file;
        $this->driverFile = $driverFile;
        $this->_acquiadamsycData = $acquiadamsycData;
    }

   /**
     * Execute
     *
     * @return boolean
     */
    public function execute()
    {
        $extra_details = [];
        $property_id = null;
		$result = $this->resultJsonFactory->create();
		$skucollection = $this->magentoSkuCollectionFactory->create();
        $skucollection->addFieldToFilter('status', 'pending')->setPageSize(100);
		if ($skucollection->getSize() === 0) {
            return $result->setData(['status' => 0, 'message' => 'No pending SKUs to process.']);
        }
        $collection = $this->metaPropertyCollectionFactory->create();
        $properties_details = [];
        $all_properties_slug = [];
        if (count($collection->getData()) > 0) {
                
            foreach ($collection->getData() as $metacollection) {
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

        } else {
            $result_data = $result->setData(
                ['status' => 0, 'message' => 'Please Select The Metaproperty First.....']
            );
            return $result_data;
        }
		$color_style = [];
		foreach ($skucollection as $skuData) {
			$sku = $skuData['sku'];
			$select_attribute = $skuData['select_attribute'];
			try {
				$_product = $this->_productRepository->get($sku);
			} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
				$insert_data = [
					"sku" => $sku,
					"message" => "Sku not match in products",
					"data_type" => "",
					"lable" => "0"
				];
				$this->getInsertDataTable($insert_data);
				continue;
			}
			if (!empty($_product->getStyle()) || !empty($_product->getColor())) {
				$color_style = [
					"color_number" =>  $_product->getAttributeText('color'),
					"style_number" =>  $_product->getStyle()
				];	
			}
			$get_data =  $this->_helperData->getAcquiaDamImageSyncWithProperties($color_style, $properties_details);
			$get_data_json_decode = json_decode($get_data, true);
			$fetch_details = $get_data_json_decode['data'];
			if (count($fetch_details) > 0) {
				try {
					$this->getDataItem(
						$select_attribute,
						$fetch_details,
						$all_properties_slug,
						$sku,
						$extra_details
					);
				} catch (\Exception $e) {
					$insert_data = [
						"sku" => $sku,
						"message" => $e->getMessage(),
						"data_type" => "",
						"lable" => "0"
					];
					$this->getInsertDataTable($insert_data);
				}
			} else {
				$insert_data = [
					"sku" => $sku,
					"message" => "Something went wrong from API side, Please contact to support team!",
					"data_type" => "",
					"lable" => "0"
				];
				$this->getInsertDataTable($insert_data);
			}
			$this->magentoSku->delete($skuData);
		}
		$result_data = $result->setData([
			'status' => 1,
			'message' => 'Data Sync Successfully.Please check AcquiaDam Synchronization Log.!'
		]);
		return $result_data;
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
        $extra_values = $extra_details;
        $image_detail = [];
        $all_item_url = [];
        $video_detail = [];
        $type = [];
        $diff_image_detail = [];
        $result = $this->resultJsonFactory->create();
        $_product = $this->_productRepository->get($sku);

        $currentStore = $this->storeManagerInterface->getStore();
        $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        $storeId = $this->storeManagerInterface->getStore()->getId();
        $product_ids = $_product->getId();
        $image_value = $_product->getWidenMultiImg();
        $doc_value = $_product->getWidenDocument();
        if (!empty($get_data)) {
            if ($select_attribute == 'image') {
                if (!empty($image_value)) {
                    $item_old_value = json_decode($image_value, true);
                    if (count($item_old_value) > 0) {
                        foreach ($item_old_value as $img) {
                            /*** Code by Jayendra ******/
                            if ($img['item_type'] == 'image') {
                                $item_img_url = $this->getPerfectVideoUrl($img['item_url']);
                                $all_item_url[] = $item_img_url;
                            }
                            /************************* */
                        }
                        
                        foreach ($get_data as $data_value) {
                            if ($data_value['Type'] == 'image') {
                                $image_url_new = $this->getPerfectVideoUrl($data_value["Image_Url"]);
                                $width = '';
                                $height = '';
                                //$widen_role_array = $data_value['image_roles'];
                                $img_role = $this->getRoleArray($data_value);
                                
                                $parsedUrl = \parse_url($image_url_new);
                                $item_url = explode("?", $image_url_new);
                                if (isset($parsedUrl['query'])) {
                                    \parse_str($parsedUrl['query'], $queryParams);
                                    $width = isset($queryParams['w']) ? $queryParams['w'] : '';
                                    $height = isset($queryParams['h']) ? $queryParams['h'] : '';
                                }
                                
                                if (!in_array($item_url[0], $all_item_url)) {
                                    $diff_image_detail[] = [
                                        "item_url" => $item_url[0],
                                        "altText" => $data_value['Alt_Text'],
                                        "image_role" => $img_role,
                                        "item_type" => $data_value['Type'],
                                        "thum_url" => $item_url[0],
                                        "selected_template_url" => $item_url[0],
                                        "height" => $height,
                                        "width"=> $width,
										"asset_order" => $data_value['asset_order'],
                                        "is_import" => "0"
                                    ];
									$total_new_value = count($diff_image_detail);
									if ($total_new_value > 1) {
										foreach ($diff_image_detail as $nn => $n_img) {
											if ($n_img['item_type'] == "image" && $nn != ($total_new_value - 1)) {
												if ($img_role) {
													$new_mg_role_array = (array)$img_role;
													if (count($n_img["image_role"]) > 0 && count($new_mg_role_array) > 0) {
														$result_val=array_diff($n_img["image_role"], $new_mg_role_array);
														$diff_image_detail[$nn]["image_role"] = $result_val;
													}
												}
											}
										}
									}
                                } else {
                                    $image_detail[] = [
                                        "item_url" => $item_url[0],
                                        "altText" => $data_value['Alt_Text'],
                                        "image_role" => $img_role,
                                        "item_type" => $data_value['Type'],
                                        "thum_url" => $item_url[0],
                                        "selected_template_url" => $item_url[0],
                                        "height" => $height,
                                        "width"=> $width,
										"asset_order" => $data_value['asset_order'],
                                        "is_import" => "0"
                                    ];
									$total_new_value = count($image_detail);
                                    if ($total_new_value > 1) {
										foreach ($image_detail as $nn => $n_img) {
											if ($n_img['item_type'] == "image" && $nn != ($total_new_value - 1)) {
												if ($img_role) {
													$new_mg_role_array = (array)$img_role;
													if (count($n_img["image_role"]) > 0 && count($new_mg_role_array) > 0) {
														$result_val=array_diff($n_img["image_role"], $new_mg_role_array);
														$image_detail[$nn]["image_role"] = $result_val;
													}
												}
											}
										}
									}
                                }
                            }
                        }
                        $image = [];
                        if (count($image_detail) > 0) {
                            foreach ($image_detail as $img) {
                                $image[] = $img['item_url'];
                            }
                        }
                        $new_image_detail = [];
                        $item_img_url = "";
                        foreach ($item_old_value as $key1 => $img) {
                            if ($img['item_type'] == 'image') {
                                $item_img_url = $img['item_url'];
                            }
                            if (in_array($item_img_url, $image)) {
                                $item_key = array_search($img['item_url'], array_column($image_detail, "item_url"));
                                $new_image_detail[] = [
                                    "item_url" => $item_img_url,
                                    "altText" => $image_detail[$key1]['altText'],
                                    "image_role" => $image_detail[$key1]['image_role'],
                                    "item_type" => $img['item_type'],
                                    "thum_url" => $img['thum_url'],
                                    "selected_template_url" => $img['selected_template_url'],
                                    "height" => $img['height'],
                                    "width"=> $img['width'],
									"asset_order" => $image_detail[$key1]['asset_order'],
                                    "is_import" => $img['is_import']
                                ];
                            }
                        }
                        $array_merge = array_merge($new_image_detail, $diff_image_detail);
                        $images = [];
                        if (count($diff_image_detail) > 0) {
                            foreach ($diff_image_detail as $diff_image) {
                                $images[] = $diff_image['item_url'];
                                $data_image_data = [
                                    'sku' => $sku,
                                    'message' => $diff_image['item_url'],
                                    'data_type' => '1',
                                    "lable" => "1"
                                ];
                                $this->getInsertDataTable($data_image_data);
                            }
                        }
                        
                        foreach ($array_merge as $merge) {
                            $type[] = $merge['item_type'];
                        }
                        $new_value_array = json_encode($array_merge, true);
                        
                        $flag = $this->getFlag($type);
                        if (isset($extra_details['is_mg_import']) && $extra_values['is_mg_import'] == 1) {
                            $new_value_array = $this->uploadImageToProduct($new_value_array, $product_ids);
                        }
                        
                        if (isset($extra_values['is_widen_cdn']) && $extra_values['is_widen_cdn'] == 1) {
                            $update_details = [
                                'widen_multi_img' => $new_value_array
                            ];
                        } else {
                            $update_details = [
                                'widen_multi_img' => $new_value_array,
                            ];
                        }
                        $this->productAction->updateAttributes(
                            [$product_ids],
                            $update_details,
                            $storeId
                        );
                        $this->productAction->updateAttributes(
                            [$product_ids],
                            ['widen_isMain' => $flag],
                            $storeId
                        );
                        $updated_values = [
                            'widen_auto_replace' => 1
                        ];
                        $this->productAction->updateAttributes(
                            [$product_ids],
                            $updated_values,
                            $storeId
                        );
                    } else {
                        if (isset($extra_values['is_widen_cdn']) && $extra_values['is_widen_cdn'] == 1) {
                            $update_details = [
                                'use_widen_cdn' => 1
                            ];
                        } else {
                            $update_details = [
                                'use_widen_cdn' => 0
                            ];
                        }
                        $this->productAction->updateAttributes(
                            [$product_ids],
                            $update_details,
                            $storeId
                        );
                        $data_image_data = [
                            'sku' => $sku,
                            'message' => "Don't Have Find New Data For this SKU",
                            'data_type' => '',
                            "lable" => ""
                        ];
                        $this->getInsertDataTable($data_image_data);
                    }
                } else {
                    foreach ($get_data as $data_value) {
                        if ($data_value['Type'] == 'image') {
                            $image_url_new = $this->getPerfectVideoUrl($data_value["Image_Url"]);
                            $width = '';
                            $height = '';

                            $img_role = $this->getRoleArray($data_value);

                            $parsedUrl = \parse_url($image_url_new);
                            $item_url = explode("?", $image_url_new);
                            if (isset($parsedUrl['query'])) {
                                \parse_str($parsedUrl['query'], $queryParams);
                                $width = isset($queryParams['w']) ? $queryParams['w'] : '';
                                $height = isset($queryParams['h']) ? $queryParams['h'] : '';
                            }
                            $image_detail[] = [
                                "item_url" => $item_url[0],
                                "altText" => $data_value['Alt_Text'],
                                "image_role" => $img_role,
                                "item_type" => $data_value['Type'],
                                "thum_url" => $item_url[0],
                                "selected_template_url" => $item_url[0],
                                "height" => $height,
                                "width"=> $width,
								"asset_order" => $data_value['asset_order'],
                                "is_import" => "0"
                            ];
							$total_new_value = count($image_detail);
							if ($total_new_value > 1) {
								foreach ($image_detail as $nn => $n_img) {
									if ($n_img['item_type'] == "image" && $nn != ($total_new_value - 1)) {
										if ($img_role) {
											$new_mg_role_array = (array)$img_role;
											if (count($n_img["image_role"]) > 0 && count($new_mg_role_array) > 0) {
												$result_val=array_diff($n_img["image_role"], $new_mg_role_array);
												$image_detail[$nn]["image_role"] = $result_val;
											}
										}
									}
								}
							}
                            $data_image_data = [
                                'sku' => $sku,
                                'message' => $item_url[0],
                                'data_type' => '1',
                                "lable" => "1"
                            ];
                            $this->getInsertDataTable($data_image_data);
                        }
                    }
                    foreach ($image_detail as $img) {
                        $type[] = $img['item_type'];
                        $image[] = $img['item_url'];
                    }
                    $image_value_array = implode(',', $image);
                    $flag = $this->getFlag($type);
                    $new_value_array = json_encode($image_detail, true);

                    if (isset($extra_details['is_mg_import']) && $extra_values['is_mg_import'] == 1) {
                        $new_value_array = $this->uploadImageToProduct($new_value_array, $product_ids);
                    }

                    if (isset($extra_values['is_widen_cdn']) && $extra_values['is_widen_cdn'] == true) {
                        $update_details = [
                            'widen_multi_img' => $new_value_array,
                            'use_widen_cdn' => 1
                        ];
                    } else {
                        $update_details = [
                            'widen_multi_img' => $new_value_array
                        ];
                    }
                    $this->productAction->updateAttributes(
                        [$product_ids],
                        $update_details,
                        $storeId
                    );
                    $this->productAction->updateAttributes(
                        [$product_ids],
                        ['widen_isMain' => $flag],
                        $storeId
                    );
                }
            } elseif ($select_attribute == "video") {
                $product_sku_key = "";
                if (!empty($image_value)) {
                    $item_old_value = json_decode($image_value, true);
                    if (count($item_old_value) > 0) {
                        foreach ($item_old_value as $video) {
                            $vide_url = $this->getPerfectVideoUrl($video['item_url']);
                            $all_item_url[] = $vide_url;
                        }
                        foreach ($get_data as $data_value) {
                            if ($data_value['Type'] == 'video') {
                                $data_img_url = $this->getPerfectVideoUrl($data_value["Image_Url"]);
                                if (!in_array($data_img_url, $all_item_url)) {
                                    $img_array = $this->dataHelper->getMakeVideoasThumbForSync(
                                        $data_value,
                                        $sku,
                                        $mediaUrl
                                    );
                                    $video_detail[] = [
                                        "item_url" => $data_img_url,
                                        "altText" => !empty($data_value['Alt_Text'])?$data_value['Alt_Text']:"",
                                        "image_role" => null,
                                        "item_type" => $data_value['Type'],
                                        "thum_url" => $data_img_url,
                                        "selected_template_url" => $img_array['template_url'],
                                        "height" => "",
                                        "width"=> "",
                                        "asset_order" => $data_value['asset_order'],
                                        "is_import" => "0"
                                    ];
                                    $data_video_data = [
                                        'sku' => $sku,
                                        'message' => $data_img_url,
                                        'data_type' => '3',
                                        "lable" => "1"
                                    ];
                                    $this->getInsertDataTable($data_video_data);
                                }
                            }
                        }
                        if (count($video_detail) > 0) {
                            foreach ($video_detail as $video) {
                                $type[] = $video['item_type'];
                            }
                        }
                        $flag = $this->getFlag($type);
                    }
                    if (count($video_detail) > 0) {
                        
                        $array_merge = array_merge($item_old_value, $video_detail);
                        $new_value_array = json_encode($array_merge, true);

                        if (isset($extra_values['is_widen_cdn']) && $extra_values['is_widen_cdn'] == true) {
                            $update_details = [
                                'widen_multi_img' => $new_value_array,
                                'use_widen_cdn' => 1
                            ];
                        } else {
                            $update_details = [
                                'widen_multi_img' => $new_value_array
                            ];
                        }
                        $this->productAction->updateAttributes(
                            [$product_ids],
                            $update_details,
                            $storeId
                        );
                        $this->productAction->updateAttributes(
                            [$product_ids],
                            ['widen_isMain' => $flag],
                            $storeId
                        );
                    }
                } else {
                    foreach ($get_data as $data_value) {
                        if ($data_value['Type'] == 'video') {
                            $product_sku_key = "";
                            $data_img_url = $this->getPerfectVideoUrl($data_value["Image_Url"]);
                            $video_detail[] = [
                                "item_url" => $data_img_url,
                                "altText" => !empty($data_value['Alt_Text'])?$data_value['Alt_Text']:"",
                                "image_role" => null,
                                "item_type" => $data_value['Type'],
                                "thum_url" => $data_img_url,
                                "selected_template_url" => $data_img_url,
                                "height" => "",
                                "width"=> "",
								"asset_order" => $data_value['asset_order'],
                                "is_import" => "0"
                            ];
                            $data_video_data = [
                                'sku' => $sku,
                                'message' => $data_img_url,
                                'data_type' => '3',
                                "lable" => "1"
                            ];
                            $this->getInsertDataTable($data_video_data);
                        }
                    }
                    foreach ($video_detail as $video) {
                        $type[] = $video['item_type'];
                    }
                    $flag = $this->getFlag($type);
                    $new_value_array = json_encode($video_detail, true);

                    if (isset($extra_values['is_widen_cdn']) && $extra_values['is_widen_cdn'] == true) {
                        $update_details = [
                            'widen_multi_img' => $new_value_array,
                            'use_widen_cdn' => 1
                        ];
                    } else {
                        $update_details = [
                            'widen_multi_img' => $new_value_array
                        ];
                    }
                    $this->productAction->updateAttributes(
                        [$product_ids],
                        $update_details,
                        $storeId
                    );
                    $this->productAction->updateAttributes(
                        [$product_ids],
                        ['widen_isMain' => $flag],
                        $storeId
                    );
                    
                }
            } else {
                if (empty($doc_value)) {
                    $doc_detail=[];
                    foreach ($get_data as $data_value) {
                        // make this variable dynamic - pending
                        $product_sku_key = "";
                        if ($data_value['Type'] == 'pdf' || $data_value['Type'] == 'office') {
                            $data_doc_url = $this->getPerfectVideoUrl($data_value["Image_Url"]);
                            $doc_detail[] = [
                                "item_url" => $data_doc_url,
                                "item_type" => $data_value['Type'],
                                "altText" => $data_value['Alt_Text'],
                                "doc_name" => $data_value['Alt_Text'],
								"asset_order" => $data_value['asset_order'],
								
                            ];
                            $data_doc_data = [
                                'sku' => $sku,
                                'message' => $data_doc_url,
                                'data_type' => '2',
                                "lable" => "1"
                            ];
                            $this->getInsertDataTable($data_doc_data);
                        }
                    }
                    $new_value_array = json_encode($doc_detail, true);
                    $this->productAction->updateAttributes(
                        [$product_ids],
                        ['widen_document' => $new_value_array],
                        $storeId
                    );
                } else {
                    /**Not empty means need to add all new Documents */
                    $old_value = json_decode($doc_value, true);
                    $doc_detail = [];
                    $existing_urls = [];
                    foreach ($old_value as $existing_doc_val) {
                        $existing_urls[] = $existing_doc_val["item_url"];
                    }

                    foreach ($get_data as $all_new_urls) {
                        if ($all_new_urls["Type"] == "pdf" || $all_new_urls["Type"] == "office") {
                            $new_link = $this->getPerfectVideoUrl($all_new_urls['Image_Url']);
                            if (!in_array($new_link, $existing_urls)) {
                                $doc_detail[] = [
                                    "item_url" => $new_link,
                                    "item_type" => $all_new_urls['Type'],
                                    "altText" => $all_new_urls['Alt_Text'],
                                    "doc_name" => $all_new_urls['Alt_Text'],
									"asset_order" => $all_new_urls['asset_order'],
                                ];
                                $data_doc_data = [
                                    'sku' => $sku,
                                    'message' => $new_link,
                                    'data_type' => '2',
                                    "lable" => "1"
                                ];
                                $this->getInsertDataTable($data_doc_data);
                            }
                        }
                    }
                    if (count($doc_detail) > 0) {
                        $array_merge = array_merge($old_value, $doc_detail);
                        $new_value_array = json_encode($array_merge, true);
                        $this->productAction->updateAttributes(
                            [$product_ids],
                            ['widen_document' => $new_value_array],
                            $storeId
                        );
                    }
                }
            }
        }
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
        if(in_array("ALL",$widen_role_array['image_roles'])){
            $img_role = ["image","small_image","thumbnail"];
        }
        else if($widen_role_array['image_roles'] == "BASE"){
            $img_role = ["image"];
        }
        else if($widen_role_array['image_roles'] == "SMALL"){
            $img_role = ["small_image"];
        }
        else if($widen_role_array['image_roles'] == "THUMB"){
            $img_role = ["thumbnail"];
        }
        else if($widen_role_array['image_roles'] == "PLP ROLLOVER"){
            $img_role = ["category_page_list_rollover"];
        }
        else{
            $img_role = [];
        }
        return $img_role;
    }
}
