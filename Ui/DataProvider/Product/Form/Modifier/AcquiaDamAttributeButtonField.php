<?php
namespace DamConsultants\AcquiaDam\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\Stdlib\ArrayManager;

class AcquiaDamAttributeButtonField extends AbstractModifier
{
    /**
     * @var LocatorInterface
     * @since 101.0.0
     */
    protected $locator;

    /**
     * @var UrlInterface
     * @since 101.0.0
     */
    protected $urlBuilder;

    /**
     * @var ArrayManager
     * @since 101.0.0
     */
    protected $arrayManager;
    /**
     * @var LayoutFactory
     * @since 101.0.0
     */
    protected $layoutFactory;

    /**
     * @param LocatorInterface $locator
     * @param UrlInterface $urlBuilder
     * @param LayoutFactory $layoutFactory
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        LayoutFactory $layoutFactory,
        ArrayManager $arrayManager
    ) {
        $this->locator = $locator;
        $this->urlBuilder = $urlBuilder;
        $this->layoutFactory = $layoutFactory;
        $this->arrayManager = $arrayManager;
    }
    /**
     * @inheritdoc
     * @since 101.0.0
     */
    public function modifyMeta(array $meta)
    {
       
        $meta = $this->customizeAcquiaDamCategoriesField($meta);
        $meta = $this->getAcquiaDamModalConfig($meta);
        $meta = $this->docAcquiaDamcustomizeCategoriesField($meta);
        $meta = $this->getAcquiaDamDocModalConfig($meta);

        return $meta;
    }
    /**
     * @inheritdoc
     * @since 101.0.0
     */
    public function modifyData(array $data)
    {
        return $data;
    }
    /**
     * Get Modal Config
     *
     * @param array $meta
     */
    protected function getAcquiaDamModalConfig($meta)
    {
       
        return $this->arrayManager->set(
            'widen_url_modal',
            $meta,
            [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => \Magento\Ui\Component\Modal::NAME,
                        'dataScope' => '',
                        'provider' => static::FORM_NAME . '.product_form_data_source',
                        'additionalClasses' => 'widen_gallery',
                        'ns' => static::FORM_NAME,
                        'options' => [
                            'title' => __('Acquia DAM Gallery'),
                            'buttons' => [
                                [
                                    'text' => __('Save'),
                                    'class' => 'action-primary save_image',
                                    'actions' => [
                                        
                                        'closeModal',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'children' => [
                'widen_modal' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'autoRender' => false,
                                'componentType' => 'container',
                                'component' => 'Magento_Ui/js/form/components/html',
                                'dataScope' => 'data.product',
                                'externalProvider' => 'data.product_data_source',
                                'ns' => static::FORM_NAME,
                                'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                'realTimeLink' => true,
                                'behaviourType' => 'edit',
                                "content" => $this->layoutFactory->create()->createBlock(
                                    \DamConsultants\AcquiaDam\Block\Adminhtml\Catalog\Product\Form\Gallery::class
                                )->toHtml(),
                                'externalFilterMode' => true,
                                'currentProductId' => $this->locator->getProduct()->getId(),
                            ],
                        ],
                    ],
                ],
            ],
            ]
        );
    }
    /**
     * Customize Categories field
     *
     * @param array $meta
     * @return array
     * @throws LocalizedException
     * @since 101.0.0
     */
    protected function customizeAcquiaDamCategoriesField(array $meta)
    {
        $fieldCode = 'widen_multi_img';
        $widen = 'widen_isMain';
        $cronSync = 'widen_cron_sync';
        $autoReplace = 'widen_auto_replace';
        $path = $this->arrayManager->findPath($widen, $meta, null, 'children');
        $pathcron = $this->arrayManager->findPath($cronSync, $meta, null, 'children');
        $pathauto = $this->arrayManager->findPath($autoReplace, $meta, null, 'children');
        $meta = $this->arrayManager->set("{$path}/arguments/data/config/visible", $meta, false);
        $meta = $this->arrayManager->set("{$pathcron}/arguments/data/config/visible", $meta, false);
        $meta = $this->arrayManager->set("{$pathauto}/arguments/data/config/visible", $meta, false);
        $elementPath = $this->arrayManager->findPath($fieldCode, $meta, null, 'children');
        $containerPath = $this->arrayManager->findPath(static::CONTAINER_PREFIX . $fieldCode, $meta, null, 'children');
        $fieldIsDisabled = $this->locator->getProduct()->isLockedAttribute($fieldCode);

        if (!$elementPath) {
            return $meta;
        }
        $value = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => false,
                        'required' => false,
                        'dataScope' => '',
                        'breakLine' => false,
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'component' => 'Magento_Ui/js/form/components/group',
                        'disabled' => $this->locator->getProduct()->isLockedAttribute($fieldCode),
                    ],
                ],
            ],
            'children' => [
                $fieldCode => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => \Magento\Ui\Component\Form\Field::NAME,
                                'componentType' => \Magento\Ui\Component\Form\Element\Textarea::NAME,
                                'dataScope' => $fieldCode,
                                'dataType' => \Magento\Ui\Component\Form\Element\DataType\Text::NAME,
                                'sortOrder' => 10,
                                'additionalClasses' => 'admin__field widen_gallery_text_url',
                                'visible' => true,
                                'disabled' => false,
                            ],
                        ],
                    ],
                ],
            ]
        ];
        $value['children']['widen_image'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'title' => __('Acquia DAM Image'),
                            'formElement' => 'container',
                            'additionalClasses' => 'admin__field-small widen_url',
                            'componentType' => 'container',
                            'disabled' => $fieldIsDisabled,
                            'component' => 'Magento_Ui/js/form/components/button',
                            'template' => 'ui/form/components/button/container',
                            'actions' => [
                                [
                                    'targetName' => 'product_form.product_form.widen_url_modal',
                                    'actionName' => 'openModal',
                                ],
                            ],
                            'additionalForGroup' => true,
                            'displayArea' => 'insideGroup',
                            'sortOrder' => 20,
                            'dataScope'  => $fieldCode,
                        ],
                    ],
                ]
            ];
        //}
        $meta = $this->arrayManager->merge($containerPath, $meta, $value);

        return $meta;
    }
    /**
     * Get Modal Config
     *
     * @param array $meta
     */
    protected function getAcquiaDamDocModalConfig($meta)
    {
       
        return $this->arrayManager->set(
            'widen_doc_modal',
            $meta,
            [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => \Magento\Ui\Component\Modal::NAME,
                        'dataScope' => '',
                        'provider' => static::FORM_NAME . '.product_form_data_source',
                        'additionalClasses' => 'widen_doc_gallery',
                        'ns' => static::FORM_NAME,
                        'options' => [
                            'title' => __('Acquia DAM Document Gallery'),
                            'buttons' => [
                                [
                                    'text' => __('Save'),
                                    'class' => 'action-primary save_doc',
                                    'actions' => [
                                        
                                        'closeModal',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'children' => [
                'widen_modal' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'autoRender' => false,
                                'componentType' => 'container',
                                'component' => 'Magento_Ui/js/form/components/html',
                                'dataScope' => 'data.product',
                                'externalProvider' => 'data.product_data_source',
                                'ns' => static::FORM_NAME,
                                'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                'realTimeLink' => true,
                                'behaviourType' => 'edit',
                                "content" => $this->layoutFactory->create()->createBlock(
                                    \DamConsultants\AcquiaDam\Block\Adminhtml\Catalog\Product\Form\AcquiaDamDoc::class
                                )->toHtml(),
                                'externalFilterMode' => true,
                                'currentProductId' => $this->locator->getProduct()->getId(),
                            ],
                        ],
                    ],
                ],
            ],
            ]
        );
    }
    /**
     * Customize Categories field
     *
     * @param array $meta
     * @return array
     * @throws LocalizedException
     * @since 101.0.0
     */
    protected function docAcquiaDamcustomizeCategoriesField(array $meta)
    {
        $fieldCode = 'widen_document';
        $elementPath = $this->arrayManager->findPath($fieldCode, $meta, null, 'children');
        $containerPath = $this->arrayManager->findPath(static::CONTAINER_PREFIX . $fieldCode, $meta, null, 'children');
        $fieldIsDisabled = $this->locator->getProduct()->isLockedAttribute($fieldCode);

        if (!$elementPath) {
            return $meta;
        }

        $value = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => false,
                        'required' => false,
                        'dataScope' => '',
                        'breakLine' => false,
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'component' => 'Magento_Ui/js/form/components/group',
                        'disabled' => $this->locator->getProduct()->isLockedAttribute($fieldCode),
                    ],
                ],
            ],
            'children' => [
                $fieldCode => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => \Magento\Ui\Component\Form\Field::NAME,
                                'componentType' => \Magento\Ui\Component\Form\Element\Textarea::NAME,
                                'dataScope' => $fieldCode,
                                'dataType' => \Magento\Ui\Component\Form\Element\DataType\Text::NAME,
                                'sortOrder' => 10,
                                'additionalClasses' => 'admin__field widen_gallery_doc_url',
                                'visible' => true,
                                'disabled' => false,
                            ],
                        ],
                    ],
                ],
            ]
        ];
        $value['children']['widen_image'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'title' => __('Acquia DAM Doc'),
                            'formElement' => 'container',
                            'additionalClasses' => 'admin__field-small widen_url_doc',
                            'componentType' => 'container',
                            'disabled' => $fieldIsDisabled,
                            'component' => 'Magento_Ui/js/form/components/button',
                            'template' => 'ui/form/components/button/container',
                            'actions' => [
                                [
                                    'targetName' => 'product_form.product_form.widen_doc_modal', // Element selector
                                    'actionName' => 'openModal',
                                ],
                            ],
                            'additionalForGroup' => true,
                            'displayArea' => 'insideGroup',
                            'sortOrder' => 20,
                            'dataScope'  => $fieldCode,
                        ],
                    ],
                ]
            ];
        $meta = $this->arrayManager->merge($containerPath, $meta, $value);

        return $meta;
    }
}
