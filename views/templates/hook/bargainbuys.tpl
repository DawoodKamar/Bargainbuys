<!--
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
-->

{if isset($cheapestProducts) && $cheapestProducts}
    <div class="cheapest-products">
        <h2>Bargain Buys</h2>
        <ul>
            {foreach from=$cheapestProducts item=product}
                <li class="bargain-product">
                    <a href="{$link->getProductLink($product.id_product)}" title="{$product.name|escape:'html':'UTF-8'}">
                        {if isset($product.image)}
                            <img src="{$product.image}" alt="{$product.name|escape:'html':'UTF-8'}">
                        {/if}
                        <h3>{$product.name|escape:'html':'UTF-8'}</h3>
                        <p class="price">{$product.formatted_price}</p>
                    </a>
                </li>
            {/foreach}
        </ul>
    </div>
{else}
    <p>No cheapest products found.</p>
{/if}