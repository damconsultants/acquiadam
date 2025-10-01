<?php
namespace DamConsultants\AcquiaDam\Block\Product\Renderer;

use Magento\Catalog\Model\Product;

class Configurable extends \Magento\Swatches\Block\Product\Renderer\Configurable
{
    protected function getSwatchProductImage(Product $childProduct, $imageType)
    {
        
        $widenImage = $childProduct->getWidenMultiImg();
        $use_widen_both_image = $childProduct->getUseWidenBothImage();
        $use_widen_cdn = $childProduct->getUseWidenCdn();
        if($this->getRequest()->getFullActionName() == 'catalog_product_view') {
            if ($use_widen_cdn == 1 || $use_widen_both_image == 1) {
                if ($widenImage) {
                    $decodedwidenImages = json_decode($widenImage, true);
                    if (is_array($decodedwidenImages)) {
                        foreach ($decodedwidenImages as $key => $widenImage) {
                            if ($widenImage['item_type'] == 'image' && isset($widenImage['image_role'])) {
                                foreach ($widenImage['image_role'] as $image_role) {
                                    if ($image_role == 'Swatch') {
                                        return $widenImage['thum_url'];
                                    }
                                }
                            }
                        }
                    }
                } else {
                    return parent::getSwatchProductImage($childProduct, $imageType);
                }
            } else {
                return parent::getSwatchProductImage($childProduct, $imageType);
            }
        } else {
            return parent::getSwatchProductImage($childProduct, $imageType);
        }
    }
}
