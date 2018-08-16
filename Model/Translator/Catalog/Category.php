<?php
/**
 * @category  Aromicon
 * @package   Aromicon_
 * @author    Stefan Richter <richter@aromicon.de>
 * @copyright 2018 aromicon GmbH (http://www.aromicon.de)
 * @license   Commercial https://www.aromicon.de/magento-download-extensions-modules/de/license
 */
namespace Aromicon\Deepl\Model\Translator\Catalog;

class Category
{
    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;
    
    /**
     * @var \Aromicon\Deepl\Api\TranslatorInterface
     */
    private $translator;

    /**
     * @var \Aromicon\Deepl\Helper\Config
     */
    private $config;

    private $categoryResource;

    public function __construct(
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Aromicon\Deepl\Api\TranslatorInterface $translator,
        \Aromicon\Deepl\Helper\Config $config,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResource
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->translator = $translator;
        $this->config = $config;
        $this->categoryResource = $categoryResource;
    }

    /**
     * @param $productId int
     * @param $fromStoreId int
     * @param $toStoreId int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function translateAndCopy($categoryId, $toStoreId)
    {
        $product = $this->categoryRepository->get($categoryId, $toStoreId);

        $sourceLanguage = $this->config->getSourceLanguage();
        $targetLanguage = $this->config->getLanguageCodeByStoreId($toStoreId);

        $categoryFields = $this->config->getTranslatableCategoryFields();

        foreach ($categoryFields as $field) {
            if ($product->getData($field) == '') {
                continue;
            }

            $translatedText = $this->translator->translate($product->getData($field), $sourceLanguage, $targetLanguage);
            if ($product->getData($field) == $translatedText) {
                continue;
            }

            $product->setData($field, $translatedText);
            $this->categoryResource->saveAttribute($product, $field);
        }
    }
}
