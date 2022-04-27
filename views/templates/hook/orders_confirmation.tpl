{*
* 2007-2016 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2016 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
    <div class="row">
        <div class="col-lg-6">
            <div class="panel">
                <div class="panel-heading">
                    <img src="{$base_url|escape:'htmlall':'UTF-8'}modules/{$module_name|escape:'htmlall':'UTF-8'}/logo.gif" alt="" /> 
                    {l s='Tipsa Carrier Origen' mod='tipsacarrier'}
                </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="col-xs-12">
                                <div id="message" class="form-horizontal">
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">{l s='Client Code' mod='tipsacarrier'}</label>
                                        <div class="col-lg-4">
                                            <input type="text" value="{Configuration::get('TIPSA_CODIGO_CLIENTE')}" disabled="disabled">
                                        </div>
                                        <label class="control-label col-lg-2">{l s='Agency Code' mod='tipsacarrier'}</label>
                                        <div class="col-lg-4">
                                            <input type="text" value="{Configuration::get('TIPSA_CODIGO_AGENCIA')}" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">{l s='Ref.' mod='tipsacarrier'}</label>
                                        <div class="col-lg-10">
                                            <input type="text" value="{$TIPSACARRIER_REFERENCE}" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">{l s='Sender' mod='tipsacarrier'}</label>
                                        <div class="col-lg-10">
                                            <input type="text" value="{$TIPSACARRIER_SHOPNAME}"  disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">{l s='Phone' mod='tipsacarrier'}</label>
                                        <div class="col-lg-4">
                                            <input type="text" value="{$TIPSACARRIER_SHOPPHONE}"  disabled="disabled">
                                        </div>
                                        <label class="control-label col-lg-2">{l s='Postal Code' mod='tipsacarrier'}</label>
                                        <div class="col-lg-4">
                                            <input type="text" value="{$TIPSACARRIER_CP}" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">{l s='City' mod='tipsacarrier'}</label>
                                        <div class="col-lg-10">
                                            <input type="text" value="{$TIPSACARRIER_CITY}" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">{l s='Address' mod='tipsacarrier'}</label>
                                        <div class="col-lg-10">
                                            <input type="text" value="{$TIPSACARRIER_ADDRESS}" disabled="disabled">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>    
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel">
                <div class="panel-heading">
                    <img src="{$base_url|escape:'htmlall':'UTF-8'}modules/{$module_name|escape:'htmlall':'UTF-8'}/logo.gif" alt="" /> 
                    {l s='Tipsa Carrier Delivery' mod='tipsacarrier'}
                </div>
                <form method="post" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}#tipsa_show_message">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="col-xs-12">
                                <div id="message" class="form-horizontal">
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">{l s='Contact Info' mod='tipsacarrier'}</label>
                                        <div class="col-lg-10">
                                            <input type="text" value="{$TIPSACARRIER_DEST_CONTACT_INFO}" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">{l s='Phone' mod='tipsacarrier'}</label>
                                        <div class="col-lg-4">
                                            <input type="text" value="{$TIPSACARRIER_DEST_PHONE}" disabled="disabled">
                                        </div>
                                        <label class="control-label col-lg-2">{l s='Postal Code' mod='tipsacarrier'}</label>
                                        <div class="col-lg-4">
                                            <input type="text" value="{$TIPSACARRIER_DEST_CP}" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">{l s='City' mod='tipsacarrier'}</label>
                                        <div class="col-lg-10">
                                            <input type="text" value="{$TIPSACARRIER_DEST_CITY}" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">{l s='Address' mod='tipsacarrier'}</label>
                                        <div class="col-lg-10">
                                            <input type="text" value="{$TIPSACARRIER_DEST_ADDRESS}" disabled="disabled">
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">{l s='Comments' mod='tipsacarrier'}</label>
                                        <div class="col-lg-10">
                                            <input type="text" value="{$TIPSACARRIER_ORDER_COMMENTS}" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">{l s='Packages' mod='tipsacarrier'}</label>
                                        <div class="col-lg-4">
                                            <input type="text" value="{$TIPSACARRIER_PACKAGES}" id="packages" name="packages">
                                        </div>
                                        <label class="control-label col-lg-2">{l s='Cash on delivery' mod='tipsacarrier'}</label>
                                        <div class="col-lg-4">
                                            <input type="text" value="{convertPrice price=$TIPSACARRIER_DEST_COD}" disabled="disabled">
                                        </div>
                                    </div>
                                    <input type="hidden" id="TIPSA_ID_ENVIO" name="TIPSA_ID_ENVIO" value="{$TIPSACARRIER_ID_ENVIO}">
                                    <button type="submit" id="submitUpdateBultostipsa_envios" class="btn btn-primary " name="updateBultostipsa_envios" onclick="if (!confirm('{l s='Are you sure?' mod='tipsacarrier'}'))
                                            return false;">
                                        {l s='Force parcel items' mod='tipsacarrier'}
                                    </button>
                                    {if ($showLabelButton)}
                                        <button type="submit" id="submitGenerateLabeltipsa_envios" class="btn btn-primary pull-right" name="generateLabeltipsa_envios" onclick="if (!confirm('{l s='Are you sure?' mod='tipsacarrier'}'))
                                                return false;">
                                            {l s='Generate Label' mod='tipsacarrier'}
                                        </button>
                                    {else}
                                        <a class="btn btn-default pull-right"  target="_blank" href="{$linkLabel}">
                                            {l s='Download label' mod='tipsacarrier'}
                                            <i class="icon-external-link"></i>
                                        </a>
                                    {/if}
                                    &nbsp;&nbsp;&nbsp;
                                </div>
                            </div>
                        </div>
                    </div>    
                </form>
            </div>
        </div>
    </div>

