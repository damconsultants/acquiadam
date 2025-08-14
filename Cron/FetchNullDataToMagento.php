<?php

namespace DamConsultants\AcquiaDam\Cron;

use \Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\Product\Action;
use DamConsultants\AcquiaDam\Model\AcquiaDamSycDataFactory;
use DamConsultants\AcquiaDam\Model\ResourceModel\Collection\MetaPropertyCollectionFactory;

class FetchNullDataToMagento
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $_productRepository;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var \DamConsultants\AcquiaDam\Helper\Data
     */
    protected $datahelper;
    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $action;
    /**
     * @var \DamConsultants\AcquiaDam\Model\ResourceModel\Collection\MetaPropertyCollectionFactory
     */
    protected $metaPropertyCollectionFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerInterface;
    /**
     * @var \DamConsultants\AcquiaDam\Model\AcquiaDamSycDataFactory
     */
    protected $_acquiadamsycData;

    /**
     * Featch Null Data To Magento
     * @param LoggerInterface $logger
     * @param ProductRepository $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManagerInterface
     * @param \DamConsultants\AcquiaDam\Helper\Data $DataHelper
     * @param Action $action
     * @param MetaPropertyCollectionFactory $metaPropertyCollectionFactory
     * @param AcquiaDamSycDataFactory $acquiadamsycData
     */
    public function __construct(
        LoggerInterface $logger,
        ProductRepository $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManagerInterface,
        \DamConsultants\AcquiaDam\Helper\Data $DataHelper,
        Action $action,
        MetaPropertyCollectionFactory $metaPropertyCollectionFactory,
        AcquiaDamSycDataFactory $acquiadamsycData
    ) {

        $this->logger = $logger;
        $this->_productRepository = $productRepository;
        $this->collectionFactory = $collectionFactory;
        $this->datahelper = $DataHelper;
        $this->action = $action;
        $this->metaPropertyCollectionFactory = $metaPropertyCollectionFactory;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->_acquiadamsycData = $acquiadamsycData;
    }
    /**
     * Execute
     *
     * @return $this
     */
    public function execute()
    {
        $enable = $this->datahelper->getFetchCronEnable();
        if (!$enable) {
            return false;
        }
        $product_collection = $this->collectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->addAttributeToFilter(
                [
                    ['attribute' => 'widen_multi_img', 'null' => true]
                ]
            )
            ->addAttributeToFilter(
                [
                    ['attribute' => 'widen_cron_sync', 'null' => true]
                ]
            )
            ->load();
        $product_sku_limit = $this->datahelper->getProductSkuLimitConfig();
        if (!empty($product_sku_limit)) {
            $product_collection->getSelect()->limit($product_sku_limit);
        } else {
            $product_collection->getSelect()->limit(5);
        }
        $productSku_array = [];
        foreach ($product_collection as $product) {
            $productSku_array[] = $product->getSku();
        }
        
        $collection = $this->metaPropertyCollectionFactory->create();
        $properties_details = [];
        $all_properties_slug = [];
        $collection_data = $collection->getData();
        if (count($collection_data) > 0) {
                
            foreach ($collection_data as $metacollection) {
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

        }
        if (count($productSku_array) > 0) {
			$color_style = [];
            foreach ($productSku_array as $sku) {
				$_product = $this->_productRepository->get($sku);
				if (!empty($_product->getStyle()) || !empty($_product->getColor())) {
                    $color_style = [
                        "color_number" =>  $_product->getAttributeText('color'),
                        "style_number" =>  $_product->getStyle()
                    ];	
				}
                $get_data =  $this->datahelper->getAcquiaDamImageSyncWithProperties($color_style, $properties_details);
                $get_data_json_decode = json_decode($get_data, true);
                $fetch_details = $get_data_json_decode['data'];
                if (count($fetch_details) > 0) {
                    try {
                        $this->getDataItem($fetch_details, $all_properties_slug, $sku);
                    } catch (Exception $e) {
                        $insert_data = [
                            "sku" => $sku,
                            "message" => $e->getMessage(),
                            "data_type" => "",
                            'remove_for_magento' => '',
                            'added_on_cron_compactview' => '',
                            "lable" => "0",
                            "status" => ""
                        ];
                        $this->getInsertDataTable($insert_data);
                    }
                } else {
                    $insert_data = [
                        "sku" => $sku,
                        "message" => 'Something went wrong from API side, Please contact to support team!',
                        "data_type" => "",
                        'remove_for_magento' => '',
                        'added_on_cron_compactview' => '',
                        "lable" => "0",
                        "status" => '2'
                    ];
                    $this->getInsertDataTable($insert_data);

                    $updated_values = [
                        'widen_cron_sync' => 2
                    ];

                    $storeId = $this->getMyStoreId();
                    $_product = $this->_productRepository->get($sku);
                    $product_ids = $_product->getId();

                    $this->action->updateAttributes(
                        [$product_ids],
                        $updated_values,
                        $storeId
                    );
                }

            }
        }
        return true;
    }
    /**
     * Get Data Item
     *
     * @param array $get_data
     * @param array $all_properties_slug
     * @param array $sku
     */
    public function getDataItem($get_data, $all_properties_slug, $sku)
    {
        $image_detail = [];
        $doc_detail = [];
        $_product = $this->_productRepository->get($sku);
        $storeId = $this->storeManagerInterface->getStore()->getId();
        $product_ids = $_product->getId();
        $image_value = $_product->getWidenMultiImg();
        $model = $this->_acquiadamsycData->create();
        $type = [];
        $flag = 0;
        if (!empty($get_data)) {
            if (!empty($image_value)) {
                $all_item_url = [];
                $item_old_value = json_decode($image_value, true);
                if (count($item_old_value) > 0) {
                    foreach ($item_old_value as $img) {
                        $all_item_url[] = $img['item_url'];
                    }
                    foreach ($get_data as $data_value) {
                        $data_img_url = $this->getPerfectVideoUrl($data_value["Image_Url"]);
                        $new_image_role = '';
                        if ($data_value['Type'] == 'image' || $data_value['Type'] == 'video') {
                            $item_url = explode("?", $data_img_url);
                            if (!in_array($item_url[0], $all_item_url)) {
                                if ($data_value['Type'] == 'image') {
                                    $new_image_role = $data_value['image_roles'];
                                } else {
                                    $new_image_role = null;
                                }
                                $width = '';
                                $height = '';
                                $parsedUrl = \parse_url($data_img_url);
                                if (isset($parsedUrl['query'])) {
                                    \parse_str($parsedUrl['query'], $queryParams);
                                    $width = isset($queryParams['w']) ? $queryParams['w'] : '';
                                    $height = isset($queryParams['h']) ? $queryParams['h'] : '';
                                }
                                $image_detail[] = [
                                    "item_url" => $item_url[0],
                                    "altText" => !empty($data_value['Alt_Text'])?$data_value['Alt_Text']:"",
                                    "image_role" => $new_image_role,
                                    "item_type" => $data_value['Type'],
                                    "thum_url" => $item_url[0],
                                    "selected_template_url" => $item_url[0],
                                    "height" => $height,
                                    "width"=> $width,
                                    "is_import" => "0"

                                ];
                                $data_image_data = [
                                    'sku' => $sku,
                                    'message' => $item_url[0],
                                    'data_type' => '1',
                                    'remove_for_magento' => '1',
                                    'added_on_cron_compactview' => '1',
                                    'lable' => 1,
                                    'status' => 1
                                ];
                                $this->getInsertDataTable($data_image_data);
                            }
                        } else {
                            if ($data_value['Type'] == 'pdf' || $data_value['Type'] == 'office') {
                                $doc_detail[] = [
                                    "item_url" => $data_img_url,
                                    "altText" => $data_value['Alt_Text'],
                                ];
                                $data_doc_data = [
                                    'sku' => $sku,
                                    'message' => $data_img_url,
                                    'data_type' => '2',
                                    'remove_for_magento' => '1',
                                    'added_on_cron_compactview' => '1',
                                    'lable' => 1,
                                    'status' => 1
                                ];
                                $this->getInsertDataTable($data_doc_data);
                            }
                        }
                    }
                    foreach ($image_detail as $img) {
                        $type[] = $img['item_type'];
                    }
                    if (in_array("IMAGE", $type) && in_array("VIDEO", $type)) {
                        $flag = 1;
                    } elseif (in_array("IMAGE", $type)) {
                        $flag = 2;
                    } elseif (in_array("VIDEO", $type)) {
                        $flag = 3;
                    }
                    if (count($doc_detail) > 0) {
                        $new_docs_value_array = json_encode($doc_detail, true);
                        $this->action->updateAttributes(
                            [$product_ids],
                            ['widen_document' => $new_docs_value_array],
                            $storeId
                        );
                        $this->action->updateAttributes(
                            [$product_ids],
                            ['widen_cron_sync' => '1'],
                            $storeId
                        );
                    }
                }
                $array_merge = array_merge($item_old_value, $image_detail);
                $new_value_array = json_encode($array_merge, true);
                $this->action->updateAttributes(
                    [$product_ids],
                    ['widen_multi_img' => $new_value_array],
                    $storeId
                );
                $this->action->updateAttributes(
                    [$product_ids],
                    ['widen_isMain' => $flag],
                    $storeId
                );
                $this->action->updateAttributes(
                    [$product_ids],
                    ['widen_cron_sync' => '1'],
                    $storeId
                );
            } else {
                if (isset($get_data)) {
                    foreach ($get_data as $data_value) {
                        $data_img_url = $this->getPerfectVideoUrl($data_value["Image_Url"]);
                        $new_image_role = "";
                        $item_url = explode("?", $data_img_url);
                        if ($data_value['Type'] == 'image' || $data_value['Type'] == 'video') {
                            if ($data_value['Type'] == 'image') {
                                $new_image_role = $data_value['image_roles'];
                            } else {
                                $new_image_role = null;
                            }
                            $width = '';
                            $height = '';
                            $parsedUrl = \parse_url($data_img_url);
                            if (isset($parsedUrl['query'])) {
                                \parse_str($parsedUrl['query'], $queryParams);
                                $width = isset($queryParams['w']) ? $queryParams['w'] : '';
                                $height = isset($queryParams['h']) ? $queryParams['h'] : '';
                            }
                            $image_detail[] = [
                                "item_url" => $item_url[0],
                                "altText" => !empty($data_value['Alt_Text'])?$data_value['Alt_Text']:"",
                                "image_role" => $new_image_role,
                                "item_type" => $data_value['Type'],
                                "thum_url" => $item_url[0],
                                "selected_template_url" => $item_url[0],
                                "height" => $height,
                                "width"=> $width,
                                "is_import" => "0"
        
                            ];
                            $data_image_data = [
                                'sku' => $sku,
                                'message' => $item_url[0],
                                'data_type' => '1',
                                'remove_for_magento' => '1',
                                'added_on_cron_compactview' => '1',
                                'lable' => 1,
                                'status' => 1
                            ];
                            $this->getInsertDataTable($data_image_data);
                        } else {
                            if ($data_value['Type'] == 'pdf' || $data_value['Type'] == 'office') {
                                $doc_detail[] = [
                                    "item_url" => $data_img_url,
                                    "altText" => $data_value['Alt_Text'],
                                ];
                                $data_doc_data = [
                                    'sku' => $sku,
                                    'message' => $data_img_url,
                                    'data_type' => '2',
                                    'remove_for_magento' => '1',
                                    'added_on_cron_compactview' => '1',
                                    'lable' => 1,
                                    'status' => 1
                                ];
                                $this->getInsertDataTable($data_doc_data);
                            }
                        }
                    }
                    if (count($doc_detail) > 0) {
                        $new_docs_value_array = json_encode($doc_detail, true);
                        
                        $this->action->updateAttributes(
                            [$product_ids],
                            ['widen_document' => $new_docs_value_array],
                            $storeId
                        );
                        $this->action->updateAttributes(
                            [$product_ids],
                            ['widen_cron_sync' => '1'],
                            $storeId
                        );
                    }
                }
                foreach ($image_detail as $img) {
                    $type[] = $img['item_type'];
                }
                if (in_array("IMAGE", $type) && in_array("VIDEO", $type)) {
                    $flag = 1;
                } elseif (in_array("IMAGE", $type)) {
                    $flag = 2;
                } elseif (in_array("VIDEO", $type)) {
                    $flag = 3;
                }
                $new_value_array = json_encode($image_detail, true);
                $this->action->updateAttributes(
                    [$product_ids],
                    ['widen_multi_img' => $new_value_array],
                    $storeId
                );
                $this->action->updateAttributes(
                    [$product_ids],
                    ['widen_isMain' => $flag],
                    $storeId
                );
                $this->action->updateAttributes(
                    [$product_ids],
                    ['widen_cron_sync' => '1'],
                    $storeId
                );
            }
        }
    }

    /**
     * Get perfect video url
     *
     * @param string $url
     */
    public function getPerfectVideoUrl($url)
    {
        $new_url = $url;
        if (strlen(trim($url)) > 0) {
            $query_str = parse_url($url, PHP_URL_QUERY);
            parse_str($query_str, $query_params);
            if (isset($query_params['download'])) {
                $new_url = str_replace("&download=true", "", $url);
            }
        }
        return $new_url;
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
            'widen_data' =>$insert_data['message'],
            'widen_data_type' => $insert_data['data_type'],
            'remove_for_magento' => $insert_data['remove_for_magento'],
            'added_on_cron_compactview' => $insert_data['added_on_cron_compactview'],
            'lable' => $insert_data['lable'],
            'status' => $insert_data['status']
        ];
        
        $model->setData($data_image_data);
        $model->save();
    }
    /**
     * Is int
     *
     * @return $this
     */

    public function getMyStoreId()
    {
        $storeId = $this->storeManagerInterface->getStore()->getId();
        return $storeId;
    }
}
