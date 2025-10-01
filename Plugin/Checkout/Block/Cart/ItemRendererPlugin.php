<?php
namespace DamConsultants\AcquiaDam\Plugin\Checkout\Block\Cart;

use Magento\Checkout\Block\Cart\Item\Renderer as ItemRenderer;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ItemRendererPlugin
{
    protected $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function afterGetProductForThumbnail(ItemRenderer $subject, $result)
    {
        $item = $subject->getItem();

        // For configurable products, replace thumbnail product with child simple
        if ($item->getProductType() === 'configurable') {
            $childItem = $item->getOptionByCode('simple_product');
            if ($childItem && $childItem->getProduct()) {
                try {
                    $childId = $childItem->getProduct()->getId();
                    $childProduct = $this->productRepository->getById($childId);
                    return $childProduct; // return child product for thumbnail
                } catch (\Exception $e) {
                    return $result; // fallback parent product
                }
            }
        }

        return $result;
    }
}
