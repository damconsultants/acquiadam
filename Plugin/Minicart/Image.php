<?php

namespace DamConsultants\AcquiaDam\Plugin\Minicart;

class Image
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $_productRepository;
    /**
     * Image
     * @param \Magento\Framework\Registry $Registry
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     */
    public function __construct(
        \Magento\Framework\Registry $Registry,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {

        $this->_registry = $Registry;
        $this->product = $product;
        $this->_productRepository = $productRepository;
    }

    /**
     * Around Get Item Data
     *
     * @param \Magento\Checkout\CustomerData\AbstractItem $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote\Item $item
     */
    public function aroundGetItemData(
        \Magento\Checkout\CustomerData\AbstractItem $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Item $item
    ) {

        $data = $proceed($item);
        $productId = $item->getProduct()->getId();
        $product = $this->_productRepository->getById($productId);
        $acquiadamImage = $product->getData('widen_multi_img');
        if ($acquiadamImage != "") {
            $json_value = json_decode($acquiadamImage, true);
            $thumbnail = 'thumbnail';
            if (!empty($json_value)) {
                foreach ($json_value as $values) {
                    if (isset($values['image_role'])) {
                        foreach ($values['image_role'] as $image_role) {
                            if ($image_role == $thumbnail) {
                                $image_values = trim($values['thum_url']);
                                if (($values['height'] != "") && ($values['width'] != "")) {
                                    $image_values = $values['selected_template_url'].'&h='.$values['height'].'&w='.$values['width'];
                                }
                                $data['product_image']['src'] = $image_values;
                                $altText = trim($values['altText']);
                                $data['product_image']['alt'] = $altText;
                            }
                        }
                    }
                }
            } else {
                $data['product_image']['src'];
            }

        } else {

            $data['product_image']['src'];
        }
        return $data;
    }
}
