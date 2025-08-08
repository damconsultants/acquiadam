<?php
namespace DamConsultants\AcquiaDam\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class ReSyncData extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var urlBuilder
     */
    public $urlBuilder;
    /**
     * @var productRepository
     */
    public $_productRepository;
    /**
     * Closed constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->_productRepository = $productRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $sku = $item["sku"];
                $_product = $this->_productRepository->get($sku);
                $product_acquiadam_cron_val = $_product->getAcquiaDamCronSync();
                
                if (isset($item['id'])) {
                    $viewUrlPath = $this->getData('config/viewUrlPath');
                    $urlEntityParamName = $this->getData('config/urlEntityParamName');
                    if ($item['status'] == 2 && $product_acquiadam_cron_val != null) {
                        $item[$this->getData('name')] = [
                            'view' => [
                                'href' => $this->urlBuilder->getUrl(
                                    $viewUrlPath,
                                    [
                                        $urlEntityParamName => $item['id'],
                                    ]
                                ),
                                'label' => __('Re-Sync'),
                                'class' => 'action-primary',
                            ],
                        ];
                    }
                }
            }
        }

        return $dataSource;
    }
}
