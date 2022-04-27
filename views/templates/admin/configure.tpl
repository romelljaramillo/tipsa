{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author eComm360 SL <info@ecomm360.es>
*  @copyright  2012-2017 eComm360 SL
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of eComm360 SL
*}

<div class="panel">
    <h3><i class="icon icon-credit-card"></i> {l s='Tipsa Carrier Shipping Manager' mod='tipsacarrier'}</h3>
    <p>
        <strong>{l s='With this module you can configure in few steps your Carrier Tipsa!' mod='tipsacarrier'}</strong><br />
        {l s='Thanks to this module you can now syncronize your shipping orders and update statuses ans print labels.' mod='tipsacarrier'}<br />
        {l s='Please follow this form to configure it and access to Menu -> Orders -> Tipsa Manager to manage your shippings.' mod='tipsacarrier'}
    </p>
    <br />
    <p>
        {l s='This module is going to save a lot of time in your day. Thanks by choose Tipsa.' mod='tipsacarrier'}
    </p>
    <p>
        {l s='Test Connection' mod='tipsacarrier'}: <a href="{$back_link|escape:'html':'UTF-8'}&testingConnection=true">{l s='Test here' mod='tipsacarrier'}</a>
    </p>
    <p>
        {l s='Cron Url to auto update' mod='tipsacarrier'}: {$tipsacarrier_cron|escape:'html':'UTF-8'}
    </p>
</div>
