<?php
namespace DamConsultants\AcquiaDam\Plugin\Checkout\CustomerData;

use Magento\Checkout\CustomerData\DefaultItem;
use Magento\Quote\Model\Quote\Item;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ItemPlugin
{
    protected $productRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    public function afterGetItemData(DefaultItem $subject, array $result, Item $item)
    {
        if ($item->getProductType() === 'configurable') {
            $childItem = $item->getOptionByCode('simple_product');

            if ($childItem && $childItem->getProduct()) {
                $childId = $childItem->getProduct()->getId();

                try {
                    // Reload child product with all attributes (including widen_multi_img)
                    $childProduct = $this->productRepository->getById($childId);

                    $widenImages = $childProduct->getData('widen_multi_img');
                    if ($widenImages) {
                        $decodedImages = json_decode($widenImages, true);

                        if (is_array($decodedImages) && count($decodedImages)) {
                            // Default: first image
                            $imageUrl = $decodedImages[0]['item_url'] ?? '';

                            // Prefer Base role if available
                            foreach ($decodedImages as $img) {
                                if (isset($img['image_role']) && in_array('Base', $img['image_role'])) {
                                    $imageUrl = $img['item_url'];
                                    break;
                                }
                            }

                            if ($imageUrl) {
                                $result['product_image']['src'] = $imageUrl;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Log or ignore if product not found
                }
            }
        }

        return $result;
    }
}
