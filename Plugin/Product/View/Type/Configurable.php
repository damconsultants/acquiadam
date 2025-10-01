<?php

namespace DamConsultants\AcquiaDam\Plugin\Product\View\Type;

use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as ConfigurableBlock;

class Configurable
{
    protected $jsonEncoder;
    protected $jsonDecoder;
    protected $productHelper;
    protected $helper;
	protected $widenhelper;

    public function __construct(
        DecoderInterface $jsonDecoder,
        EncoderInterface $jsonEncoder,
        ProductHelper $productHelper,
        \Magento\ConfigurableProduct\Helper\Data $helper,
		\DamConsultants\AcquiaDam\Helper\Data $widenhelper
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
        $this->helper = $helper;
        $this->productHelper = $productHelper;
		$this->widenhelper = $widenhelper;
    }

    public function afterGetJsonConfig(ConfigurableBlock $subject, $result)
    {
        $result = $this->jsonDecoder->decode($result);
        $result['images'] = $this->getOptionImages($subject);
		//$result['enable'] = $this->widenhelper->byndeimageconfig();
        return $this->jsonEncoder->encode($result);
    }

    protected function getOptionImages(ConfigurableBlock $subject)
    {
        $images = [];
        foreach ($subject->getAllowProducts() as $product) {
            // Get widen images from custom attribute
            $widenImages = $product->getData('widen_multi_img');
            $use_widen_both_image = $product->getUseWidenBothImage();
            $use_widen_cdn = $product->getUseWidenCdn();
            if ($use_widen_both_image == 1) {
                $widenImageData = [];
                if ($widenImages) {
                    $decodedwidenImages = json_decode($widenImages, true);
                    $role_image = false;
                    if (is_array($decodedwidenImages)) {
                        foreach ($decodedwidenImages as $key => $widenImage) {
                            if ($widenImage['item_type'] == 'image' && isset($widenImage['image_role'])) {
                                foreach ($widenImage['image_role'] as $image_role) {
                                    if ($image_role == 'Base') {
                                        $role_image = true;
                                    }
                                }
                            }
                            $widenImageData[] = [
                                'thumb' => $widenImage['thum_url'] ?? '',
                                'img' => $widenImage['item_url'] ?? '',
                                'full' => $widenImage['item_url'] ?? '',
                                'caption' => $widenImage['alt_text'] ?? '',
                                'position' => $key + 1,
                                'isMain' => $role_image,
                                'type' => ($widenImage['item_type'] == 'image') ? 'image' : 'video',
                                'videoUrl' => ($widenImage['item_type'] == 'video') ? $widenImage['item_url'] : null,
                                'src' => ($widenImage['item_type'] == 'video') ? $widenImage['item_url'] : null,
                            ];
                        }
                    }
                }

                // Get product gallery images using the injected product helper
                $productImages = $this->helper->getGalleryImages($product) ?: [];
                $galleryImages = [];
                foreach ($productImages as $image) {
                    $galleryImages[] = [
                        'thumb' => $image->getData('small_image_url'),
                        'img' => $image->getData('medium_image_url'),
                        'full' => $image->getData('large_image_url'),
                        'caption' => $image->getLabel(),
                        'position' => $image->getPosition(),
                        'isMain' => $image->getFile() == $product->getImage(),
                        'type' => $image->getMediaType() ? str_replace('external-', '', $image->getMediaType()) : '',
                        'videoUrl' => $image->getVideoUrl(),
                    ];
                }

                // Merge widen images and gallery images
                $images[$product->getId()] = array_merge($galleryImages, $widenImageData);
            } elseif ($use_widen_cdn == 1) {
                $widenImageData = [];
                $galleryImages = [];
                if ($widenImages) {
                    $decodedwidenImages = json_decode($widenImages, true);
                    $role_image = false;
                    if (is_array($decodedwidenImages)) {
                        foreach ($decodedwidenImages as $key => $widenImage) {
                            if ($widenImage['item_type'] == 'image' && isset($widenImage['image_role'])) {
                                foreach ($widenImage['image_role'] as $image_role) {
                                    if ($image_role == 'Base') {
                                        $role_image = true;
                                    }
                                }
                            }
                            $widenImageData[] = [
                                'thumb' => $widenImage['thum_url'] ?? '',
                                'img' => $widenImage['item_url'] ?? '',
                                'full' => $widenImage['item_url'] ?? '',
                                'caption' => $widenImage['alt_text'] ?? '',
                                'position' => $key + 1,
                                'isMain' => $role_image,
                                'type' => ($widenImage['item_type'] == 'IMAGE') ? 'image' : 'video',
                                'videoUrl' => ($widenImage['item_type'] == 'VIDEO') ? $widenImage['item_url'] : null,
                                'src' => ($widenImage['item_type'] == 'VIDEO') ? $widenImage['item_url'] : null,
                            ];
                        }
                    }
                }
                $images[$product->getId()] = array_merge($galleryImages, $widenImageData);
            } else {
                $productImages = $this->helper->getGalleryImages($product) ?: [];
                $galleryImages = [];
                $widenImageData = [];
                foreach ($productImages as $image) {
                    $galleryImages[] = [
                        'thumb' => $image->getData('small_image_url'),
                        'img' => $image->getData('medium_image_url'),
                        'full' => $image->getData('large_image_url'),
                        'caption' => $image->getLabel(),
                        'position' => $image->getPosition(),
                        'isMain' => $image->getFile() == $product->getImage(),
                        'type' => $image->getMediaType() ? str_replace('external-', '', $image->getMediaType()) : '',
                        'videoUrl' => $image->getVideoUrl(),
                    ];
                }

                // Merge widen images and gallery images
                $images[$product->getId()] = array_merge($galleryImages, $widenImageData);
            }


        }
        return $images;
    }
}
