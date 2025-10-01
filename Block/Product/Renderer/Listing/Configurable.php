<?php

namespace DamConsultants\AcquiaDam\Block\Product\Renderer\Listing;

use Magento\Catalog\Model\Product;
use Magento\Swatches\Model\Swatch;

class Configurable extends \Magento\Swatches\Block\Product\Renderer\Listing\Configurable
{
    /**
     * Override the `getVariationMedia` method.
     *
     * @param string $attributeCode
     * @param string $optionId
     * @return array
     */
    protected function getVariationMedia($attributeCode, $optionId)
    {
        $variationProduct = $this->swatchHelper->loadFirstVariationWithSwatchImage(
            $this->getProduct(),
            [$attributeCode => $optionId]
        );

        if (!$variationProduct) {
            $variationProduct = $this->swatchHelper->loadFirstVariationWithImage(
                $this->getProduct(),
                [$attributeCode => $optionId]
            );
        }

        $variationMediaArray = [];
        if ($variationProduct) {

            if ($this->getRequest()->getFullActionName() == 'catalog_category_view') {
                $widenImage = $variationProduct->getWidenMultiImg();
                $use_widen_both_image = $variationProduct->getUseWidenBothImage();
                $use_widen_cdn = $variationProduct->getUseWidenCdn();
                if ($use_widen_cdn == 1 || $use_widen_both_image == 1) {
                    if ($widenImage) {
                        $decodedwidenImages = json_decode($widenImage, true);
                        if (is_array($decodedwidenImages)) {
                            foreach ($decodedwidenImages as $key => $widenImage) {
                                if ($widenImage['item_type'] == 'image' && isset($widenImage['image_role'])) {
                                    foreach ($widenImage['image_role'] as $image_role) {
                                        if ($image_role == 'Swatch') {
                                            $variationMediaArray = [
                                                'value' => $widenImage['thum_url'],
                                                'thumb' => $widenImage['thum_url']
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        $variationMediaArray = [
                            'value' => $this->getSwatchProductImage($variationProduct, Swatch::SWATCH_IMAGE_NAME),
                            'thumb' => $this->getSwatchProductImage($variationProduct, Swatch::SWATCH_THUMBNAIL_NAME),
                        ];
                    }
                } else {
                    $variationMediaArray = [
                        'value' => $this->getSwatchProductImage($variationProduct, Swatch::SWATCH_IMAGE_NAME),
                        'thumb' => $this->getSwatchProductImage($variationProduct, Swatch::SWATCH_THUMBNAIL_NAME),
                    ];
                }
            } else {
                $variationMediaArray = [
                    'value' => $this->getSwatchProductImage($variationProduct, Swatch::SWATCH_IMAGE_NAME),
                    'thumb' => $this->getSwatchProductImage($variationProduct, Swatch::SWATCH_THUMBNAIL_NAME),
                ];
            }
        }
        return $variationMediaArray;
    }
}
