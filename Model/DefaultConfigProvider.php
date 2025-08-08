<?php
namespace DamConsultants\AcquiaDam\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartItemRepositoryInterface as QuoteItemRepository;

class DefaultConfigProvider
{
    /**
     * @var $checkoutSession
     */
    private $checkoutSession;
    /**
     * @var $quoteItemRepository
     */
    private $quoteItemRepository;
    /**
     * @var $scopeConfig
     */
    protected $scopeConfig;
    /**
     * Get
     *
     * @param CheckoutSession $checkoutSession
     * @param QuoteItemRepository $quoteItemRepository
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        QuoteItemRepository $quoteItemRepository,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->quoteItemRepository = $quoteItemRepository;
        $this->product = $product;
    }
    /**
     * AfterGetConfig
     *
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param array $result
     */
    public function afterGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, $result)
    {
        $quoteId = $this->checkoutSession->getQuote()->getId();
        $imageDataDetails = [];

        if ($quoteId) {
            $quoteItems = $this->quoteItemRepository->getList($quoteId);
            foreach ($quoteItems as $index => $quoteItem) {
                $productId = $quoteItem['product_id'];
                $imageDataDetails = $this->getProductDetails($productId);
                $item_id = $quoteItem['item_id'];
                if (count($imageDataDetails) > 0 && is_array($imageDataDetails)) {
                    $result['imageData'][$item_id] = [
                        'src' => $imageDataDetails['src'],
                        'alt' => $imageDataDetails['alt'],
                        'width' => 150,
                        'height' => 150
                    ];
                }
            }
        }
        return $result;
    }
    /**
     * Get
     *
     * @param array $productId
     */
    public function getProductDetails($productId)
    {

        $product = $this->product->load($productId);
        $acquiadamImage = $product->getData('acquiadam_multi_img');
        $data = [];
        if ($acquiadamImage != "") {
            $json_value = json_decode($acquiadamImage, true);
            $thumbnail = 'thumbnail';
            if (!empty($json_value)) {
                foreach ($json_value as $values) {
                    if (isset($values['image_role'])) {
                        foreach ($values['image_role'] as $image_role) {
                            if ($image_role == $thumbnail) {
                                $image_values = trim($values['thum_url']);
                                $data['src'] = $image_values;
                                $altText = trim($values['altText']);
                                $data['alt'] = $altText;
                            }
                        }
                    }

                }
            }
        }
        return $data;
    }
}
