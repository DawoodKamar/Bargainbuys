<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class BargainBuys extends Module
{
    // Constructor function
    public function __construct()
    {
        $this->name = 'bargainbuys';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Dawood Kamar';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => '8.99.99',
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Bargain buys', [], 'Modules.Bargainbuys.Admin');
        $this->description = $this->trans('Enhance your store with Bargain Buys, displaying the three most affordable items for great deals and easy shopping. Perfect for attracting budget-conscious customers and boosting sales', [], 'Modules.Bargainbuys.Admin');
        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall?', [], 'Modules.Bargainbuys.Admin');

        if (!Configuration::get('BARGAINBUYS_NAME')) {
            $this->warning = $this->trans('No name provided', [], 'Modules.Bargainbuys.Admin');
        }
    }

    // Function to get the 3 cheapest products
    public function getCheapestProducts()
    {
        $context = Context::getContext();
        $id_lang = (int) $context->language->id;

        $query = new DbQuery();
        $query->select('p.id_product, pl.name, p.price');
        $query->from('product', 'p');
        $query->leftJoin('product_lang', 'pl', 'p.id_product = pl.id_product AND pl.id_lang = ' . $id_lang);
        $query->where('p.active = 1');
        $query->orderBy('p.price ASC');
        $query->limit(3);
        $products = Db::getInstance()->executeS($query);
        $locale = $context->currentLocale; // Get the current locale
        $iso_code = $context->currency->iso_code;

        // Fetch the images for each product
        foreach ($products as $key => $product) {
            $id_image = Product::getCover($product['id_product']);
            $prodObj = new Product((int) $product['id_product']);
            $price = $prodObj->getPrice();

            // Format the price using the locale
            $products[$key]['formatted_price'] = $locale->formatPrice($price, $iso_code);
            if ($id_image) {
                $image = new Image($id_image['id_image']);
                $products[$key]['image'] = _PS_BASE_URL_ . _THEME_PROD_DIR_ . $image->getExistingImgPath() . '.jpg';
            }
        }

        return $products;
    }

    // Right column hook function
    public function hookDisplayRightColumn($params)
    {
        $cheapestProducts = $this->getCheapestProducts();
        $this->context->smarty->assign('cheapestProducts', $cheapestProducts);

        return $this->display(__FILE__, 'views/templates/hook/bargainbuys.tpl');
    }

    // Left column hook function
    public function hookDisplayLeftColumn($params)
    {
        $cheapestProducts = $this->getCheapestProducts();
        $this->context->smarty->assign('cheapestProducts', $cheapestProducts);

        // Render the template
        return $this->display(__FILE__, 'views/templates/hook/bargainbuys.tpl');
    }

    // Hook to add header to enable the ability to add css files
    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'bargainbuys-style',
            'modules/' . $this->name . '/views/css/bargainbuys.css',
            [
                'media' => 'all',
                'priority' => 1000,
            ]
        );

        $this->context->controller->registerJavascript(
            'bargainbuys-javascript',
            'modules/' . $this->name . '/views/js/bargainbuys.js',
            [
                'position' => 'bottom',
                'priority' => 1000,
            ]
        );
    }

    // Install function
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        return parent::install()
            && $this->registerHook('displayLeftColumn')
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->registerHook('displayRightColumn')
            && Configuration::updateValue('BARGAIN_BUYS', 'bargain buys');
    }

    // Uninstall function
    public function uninstall()
    {
        return
            parent::uninstall()
            && Configuration::deleteByName('BARGAIN_BUYS');
    }
}
