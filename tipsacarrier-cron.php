<?php

/**
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
 *  @author    eComm360 SL <info@ecomm360.es>
 *  @copyright 2012-2017 eComm360 SL
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of eComm360 SL
 */

/*
 * This file can be called using a cron to update the Order Statuses
 */
include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
/* Check to security tocken */

if (substr(Tools::encrypt('tipsacarrier/cron'), 0, 10) != Tools::getValue('token') || !Module::isInstalled('tipsacarrier'))
  die('Bad token');

$tipsacarrier = Module::getInstanceByName('tipsacarrier');

/* Check if the module is enabled */
if ($tipsacarrier->active) {
  $orders = Db::getInstance()->executeS('SELECT id_envio FROM '._DB_PREFIX_.'tipsa_envios WHERE `num_albaran`!= "" ORDER BY id_envio DESC');
  foreach ($orders as $order) {
    $tipsacarrier->updateStatus((int) $order['id_envio']);
  }
} 
