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
// Check if the PrestaShop version constant is defined; if not, exit the script
if (!defined('_PS_VERSION_')) {
    exit;
}

// Declare a new class named BargainBuys, extending the Module class
class BargainBuys extends Module
{
    // Constructor function of the class
    public function __construct()
    {
        // Set basic module information
        $this->name = 'bargainbuys'; // Unique name for the module
        $this->tab = 'front_office_features'; // Category of the module
        $this->version = '1.0.0'; // Version of the module
        $this->author = 'Dawood Kamar'; // Author of the module
        $this->need_instance = 0; // Whether the module needs to be loaded in the modules page
        $this->ps_versions_compliancy = [ // PrestaShop version compatibility
            'min' => '1.7.0.0', // Minimum version
            'max' => '8.99.99', // Maximum version
        ];
        $this->bootstrap = true; // Use Bootstrap framework

        // Call the parent constructor
        parent::__construct();

        // Set display name and description for the module
        $this->displayName = $this->trans('Bargain buys', [], 'Modules.Bargainbuys.Admin');
        $this->description = $this->trans('Enhance your store with Bargain Buys, displaying the three most affordable items for great deals and easy shopping. Perfect for attracting budget-conscious customers and boosting sales', [], 'Modules.Bargainbuys.Admin');
        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall?', [], 'Modules.Bargainbuys.Admin');

        // Check if a specific configuration value is set, if not set warning message
        if (!Configuration::get('BARGAINBUYS_NAME')) {
            $this->warning = $this->trans('No name provided', [], 'Modules.Bargainbuys.Admin');
        }
    }


    // Function to get the 3 cheapest products from the database
    public function getCheapestProducts()
    {
        // Get the current context of the shop (like language, currency, etc.)
        $context = Context::getContext();
        // Extract the language ID from the context as an integer
        $id_lang = (int) $context->language->id;
    
        // Create a new database query object
        $query = new DbQuery();
        // Define the fields to select: product ID, product name, and product price
        $query->select('p.id_product, pl.name, p.price');
        // Set 'product' as the primary table and alias it as 'p'
        $query->from('product', 'p');
        // Join the 'product_lang' table to fetch product names in the current language
        $query->leftJoin('product_lang', 'pl', 'p.id_product = pl.id_product AND pl.id_lang = ' . $id_lang);
        // Filter the products to only include active ones
        $query->where('p.active = 1');
        // Order the results by price in ascending order to get the cheapest
        $query->orderBy('p.price ASC');
        // Limit the results to the top 3 products
        $query->limit(3);
        // Execute the query and store the result in $products
        $products = Db::getInstance()->executeS($query);
        // Get the current locale from the context (for currency formatting)
        $locale = $context->currentLocale;
        // Get the ISO code of the current currency
        $iso_code = $context->currency->iso_code;
    
        // Iterate over each product in the result
        foreach ($products as $key => $product) {
            // Get the main image of the product
            $id_image = Product::getCover($product['id_product']);
            // Instantiate a Product object for further details
            $prodObj = new Product((int) $product['id_product']);
            // Get the price of the product
            $price = $prodObj->getPrice();
    
            // Format the price in the appropriate currency format
            $products[$key]['formatted_price'] = $locale->formatPrice($price, $iso_code);
            // If an image exists, get the URL for the product's main image
            if ($id_image) {
                $image = new Image($id_image['id_image']);
                // Construct the image URL and add it to the product's array
                $products[$key]['image'] = _PS_BASE_URL_ . _THEME_PROD_DIR_ . $image->getExistingImgPath() . '.jpg';
            }
        }
    
        // Return the array of products, each with its details like ID, name, price, formatted price, and image
        return $products;
    }
    

    // Hook function for displaying content in the right column of a page
    public function hookDisplayRightColumn($params)
    {
        // Fetch the cheapest products and assign them to the template
        $cheapestProducts = $this->getCheapestProducts();
        $this->context->smarty->assign('cheapestProducts', $cheapestProducts);

        // Return the HTML content from the template file
        return $this->display(__FILE__, 'views/templates/hook/bargainbuys.tpl');
    }

// Hook function for displaying content in the left column of a page
public function hookDisplayLeftColumn($params)
{
    // Fetch the cheapest products and assign them to the template
    $cheapestProducts = $this->getCheapestProducts();
    $this->context->smarty->assign('cheapestProducts', $cheapestProducts);

    // Render and return the HTML content from the template file
    return $this->display(__FILE__, 'views/templates/hook/bargainbuys.tpl');
}

// Hook function to include custom CSS and JavaScript files
public function hookActionFrontControllerSetMedia()
{
    // Register a CSS file for the module
    $this->context->controller->registerStylesheet(
        'bargainbuys-style',
        'modules/' . $this->name . '/views/css/bargainbuys.css',
        [
            'media' => 'all',
            'priority' => 1000, // Order of the stylesheet
        ]
    );

    // Register a JavaScript file for the module
    $this->context->controller->registerJavascript(
        'bargainbuys-javascript',
        'modules/' . $this->name . '/views/js/bargainbuys.js',
        [
            'position' => 'bottom', // Position of the script
            'priority' => 1000, // Order of the script
        ]
    );
}

// Install function for the module
public function install()
{
    // Check if the shop feature is active and set context
    if (Shop::isFeatureActive()) {
        Shop::setContext(Shop::CONTEXT_ALL);
    }

    // Install the module and register hooks
    return parent::install()
        && $this->registerHook('displayLeftColumn')
        && $this->registerHook('actionFrontControllerSetMedia')
        && $this->registerHook('displayRightColumn')
        && Configuration::updateValue('BARGAIN_BUYS', 'bargain buys');
}

// Uninstall function for the module
public function uninstall()
{
    // Uninstall the module and delete its configuration
    return
        parent::uninstall()
        && Configuration::deleteByName('BARGAIN_BUYS');
}
}