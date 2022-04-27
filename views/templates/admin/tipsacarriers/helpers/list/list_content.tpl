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

{extends file="helpers/list/list_content.tpl"}
{block name="td_content"}
        {if isset($params.type) && $params.type == 'link'}
            {if !empty($tr.codigo_envio)}
                <a href="{$tr.url_track|escape:'html':'UTF-8'}" target="_blank">{l s='Check tracking' mod='tipsacarrier'}</a>
            {else}
                --
            {/if}
	{elseif isset($params.type) && $params.type == 'label'}
            {if !empty($tr.codigo_envio)}
                <a href="{$tr.codigo_barras|escape:'html':'UTF-8'}" target="_blank">{l s='Download label' mod='tipsacarrier'}</a>
            {else}
                --
            {/if}
	{else}
		{$smarty.block.parent}
	{/if}
{/block}
