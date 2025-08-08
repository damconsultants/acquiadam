<?php

namespace DamConsultants\AcquiaDam\Cron;

use \Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\Product\Action;
use DamConsultants\AcquiaDam\Model\AcquiaDamSycDataFactory;
use DamConsultants\AcquiaDam\Model\ResourceModel\Collection\MetaPropertyCollectionFactory;

class AutoAddFromMagento
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
     * @var \DamConsultants\AcquiaDam\Model\AcquiaDamAutoReplaceDataFactory
     */
    protected $_acquiadamAutoReplaceData;

    /**
     * Featch Null Data To Magento
     * @param LoggerInterface $logger
     * @param ProductRepository $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManagerInterface
     * @param \DamConsultants\AcquiaDam\Helper\Data $DataHelper
     * @param \DamConsultants\AcquiaDam\Model\AcquiaDamAutoReplaceDataFactory $acquiadamAutoReplaceData
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
        \DamConsultants\AcquiaDam\Model\AcquiaDamAutoReplaceDataFactory $acquiadamAutoReplaceData,
        Action $action,
        MetaPropertyCollectionFactory $metaPropertyCollectionFactory,
        AcquiaDamSycDataFactory $acquiadamsycData
    ) {

        $this->logger = $logger;
        $this->_productRepository = $productRepository;
        $this->collectionFactory = $collectionFactory;
        $this->datahelper = $DataHelper;
        $this->_acquiadamAutoReplaceData = $acquiadamAutoReplaceData;
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
        $enable = $this->datahelper->getAutoCronEnable();
        if (!$enable) {
            return false;
        }
        $product_collection = $this->collectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->addAttributeToFilter(
                [
                    ['attribute' => 'widen_multi_img', 'notnull' => true]
                ]
            )
            ->addAttributeToFilter(
                [
                    ['attribute' => 'widen_auto_replace', 'null' => true]
                ]
            )
            ->load();
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
            foreach ($productSku_array as $sku) {
                $get_data =  $this->datahelper->getAcquiaDamImageSyncWithProperties($sku, $properties_details);
                $get_data_json_decode = json_decode($get_data, true);
                $fetch_details = $get_data_json_decode['data'];
                if (count($fetch_details) > 0) {
                    try {
                        $this->getDataItem($fetch_details, $all_properties_slug, $sku);
                    } catch (Exception $e) {
                        $insert_data = [
                            "sku" => $sku,
                            "message" => $e->getMessage(),
                            "data_type" => ""
                        ];
                        $updated_values = [
                            'widen_auto_replace' => 1
                        ];
                        $storeId = $this->getMyStoreId();
                        $_product = $this->_productRepository->get($sku);
                        $product_ids = $_product->getId();

                        $this->action->updateAttributes(
                            [$product_ids],
                            $updated_values,
                            $storeId
                        );
                        $this->getInsertDataTable($insert_data);
                    }
                } else {
                    $insert_data = [
                        "sku" => $sku,
                        "message" => 'Something went wrong from API side, Please contact to support team!',
                        "data_type" => ""
                    ];
                    $this->getInsertDataTable($insert_data);

                    $updated_values = [
                        'widen_auto_replace' => 1
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
        } else {
            $product_collection = $this->collectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->addAttributeToFilter(
                [
                    ['attribute' => 'widen_auto_replace', 'notnull' => true]
                ]
            )
            ->load();
            $id = [];
            foreach ($product_collection as $product) {
                $id[] = $product->getId();
            }
            $storeId = $this->storeManagerInterface->getStore()->getId();
            $this->action->updateAttributes(
                $id,
                ['widen_auto_replace' => ""],
                $storeId
            );
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
        $diff_image_detail =[];
        $all_item_url = [];
        $imgs = [];
        $type = [];
        $new_image_detail = [];
        $image =[];
        $images =[];
        $_product = $this->_productRepository->get($sku);
        $storeId = $this->storeManagerInterface->getStore()->getId();
        $product_ids = $_product->getId();
        $image_value = $_product->getWidenMultiImg();
        $auto_replace = $_product->getWidenAutoReplace();
        $model = $this->_acquiadamsycData->create();
        $type = [];
        $flag = 0;
        $item_img_url = "";
        if (!empty($get_data)) {
            if (!empty($image_value) && $auto_replace == null) {
                $item_old_value = json_decode($image_value, true);
                if (count($item_old_value) > 0) {
                    foreach ($item_old_value as $img) {
                        /*** Code by Jayendra ******/
                        if ($img['item_type'] == 'image') {
                            $item_img_url = $img['item_url'];
                            $all_item_url[] = $item_img_url;
                        }
                    }
                    foreach ($get_data as $data_value) {
                        if ($data_value['Type'] == 'image') {
                            $image_url_new = $data_value["Image_Url"];
                            $width = '';
                            $height = '';
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
                                    "image_role" => $data_value['image_roles'],
                                    "item_type" => $data_value['Type'],
                                    "thum_url" => $item_url[0],
                                    "selected_template_url" => $item_url[0],
                                    "height" => $height,
                                    "width"=> $width,
                                    "is_import" => "0"
                                ];
                            } else {
                                $image_detail[] = [
                                    "item_url" => $item_url[0],
                                    "altText" => $data_value['Alt_Text'],
                                    "image_role" => $data_value['image_roles'],
                                    "item_type" => $data_value['Type'],
                                    "thum_url" => $image_url_new,
                                    "selected_template_url" => $image_url_new,
                                    "height" => $height,
                                    "width"=> $width,
                                    "is_import" => "0"
                                ];
                            }
                        }
                    }
                    if (count($image_detail) > 0) {
                        foreach ($image_detail as $img) {
                            $image[] = $img['item_url'];
                        }
                    }
                    foreach ($item_old_value as $key1 => $img) {
                        if ($img['item_type'] == 'image') {
                            $item_img_url = $img['item_url'];
                        }
                        if (in_array($item_img_url, $image)) {
                            $item_key = array_search($img['item_url'], array_column($image_detail, "item_url"));
                            $new_image_detail[] = [
                                "item_url" => $item_img_url,
                                "altText" => $image_detail[$item_key]['altText'],
                                "image_role" => $image_detail[$item_key]['image_role'],
                                "item_type" => $img['item_type'],
                                "thum_url" => $img['thum_url'],
                                "selected_template_url" => $img['selected_template_url'],
                                "height" => $img['height'],
                                "width"=> $img['width'],
                                "is_import" => $img['is_import']
                            ];
                        }
                    }
                }
                $images = [];
                $array_merge = array_merge($new_image_detail, $diff_image_detail);
                if (count($diff_image_detail) > 0) {
                    foreach ($diff_image_detail as $diff_image) {
                        $images[] = $diff_image['item_url'];
                        $data_image_data = [
                            'sku' => $sku,
                            'message' => $diff_image['item_url'],
                            'data_type' => '1'
                        ];
                        $this->getInsertDataTable($data_image_data);
                    }
                }
                foreach ($array_merge as $merge) {
                    $type[] = $merge['item_type'];
                }
                $new_value_array = json_encode($array_merge, true);
                $image_value_array = implode(',', $images);
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
                $data_image_data = [
                    'sku' => $sku,
                    'message' => $image_value_array,
                    'data_type' => '1'
                ];
                $this->getInsertDataTable($data_image_data);
                $this->action->updateAttributes(
                    [$product_ids],
                    $update_details,
                    $storeId
                );
                $this->action->updateAttributes(
                    [$product_ids],
                    ['widen_isMain' => $flag],
                    $storeId
                );
                
                $updated_values = [
                    'widen_auto_replace' => 1
                ];
                $this->action->updateAttributes(
                    [$product_ids],
                    $updated_values,
                    $storeId
                );
            }
        }
    }
    /**
     * Is Json
     *
     * @param array $insert_data
     * @return $this
     */
    public function getInsertDataTable($insert_data)
    {
        $model = $this->_acquiadamAutoReplaceData->create();
        $data_image_data = [
            'sku' => $insert_data['sku'],
            'widen_data' =>$insert_data['message'],
            'widen_data_type' => $insert_data['data_type']
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
}
