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
class AdminTipsacarriersController extends ModuleAdminController
{

  public function __construct()
  {
    $this->bootstrap = true;
    $this->display = 'list';

    parent::__construct();
    $this->meta_title = $this->trans('Gestor TIPSA', array(), 'Modules.Tipsacarrier.Admin');

    if (!$this->module->active) {
      Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
    }

    $this->identifier = 'id_envio';
    $this->table = 'tipsa_envios';

    $this->addRowAction('generatelabel');
    $this->addRowAction('updatestatus');
    $this->addRowAction('delete');

    $this->bulk_actions = array(
        'generateLabel' => array(
            'text' => $this->trans('Generate labels', array(), 'Modules.Tipsacarrier.Admin'),
            'confirm' => $this->trans('Genereate labels for selected items?', array(), 'Modules.Tipsacarrier.Admin'),
            'icon' => 'icon-edit'
        ),
        'updateStatus' => array(
            'text' => $this->trans('Update Status', array(), 'Modules.Tipsacarrier.Admin'),
            'confirm' => $this->trans('Update status for selected items?', array(), 'Modules.Tipsacarrier.Admin'),
            'icon' => 'icon-edit'
        ),
        'delete' => array(
            'text' => $this->trans('Delete selected', array(), 'Modules.Tipsacarrier.Admin'),
            'confirm' => $this->trans('Delete selected items?', array(), 'Modules.Tipsacarrier.Admin'),
            'icon' => 'icon-trash'
        )
    );

    $this->context = Context::getContext();

    $this->default_form_language = $this->context->language->id;

    $this->_select = '
                o.id_order AS `id_order`,
                o.reference,
                o.total_paid_tax_incl,
                o.payment,
                o.date_add,
                oc.tracking_number,
		CONCAT(c.`firstname`, \' \', c.`lastname`) AS `customer`,
		osl.`name` AS `osname`,
		os.`color`,
		IF((SELECT so.id_order FROM `' . _DB_PREFIX_ . 'orders` so WHERE so.id_customer = o.id_customer AND so.id_order < o.id_order LIMIT 1) > 0, 0, 1) as new,
		country_lang.name as cname,
		IF(o.valid, 1, 0) badge_success';

    $this->_join = '
                LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.`id_order` = a.`id_envio_order`)
                LEFT JOIN `' . _DB_PREFIX_ . 'order_carrier` oc ON (o.`id_order` = oc.`id_order`)
		LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = o.`id_customer`)
		LEFT JOIN `' . _DB_PREFIX_ . 'address` address ON address.id_address = o.id_address_delivery
		LEFT JOIN `' . _DB_PREFIX_ . 'country` country ON address.id_country = country.id_country
		LEFT JOIN `' . _DB_PREFIX_ . 'country_lang` country_lang ON (country.`id_country` = country_lang.`id_country` AND country_lang.`id_lang` = ' . (int) $this->context->language->id . ')
		LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON (os.`id_order_state` = o.`current_state`)
		LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = ' . (int) $this->context->language->id . ')';

    $this->_orderBy = 'id_envio';
    $this->_orderWay = 'DESC';

    $statuses = OrderState::getOrderStates((int) $this->context->language->id);
    foreach ($statuses as $status) {
      $this->statuses_array[$status['id_order_state']] = $status['name'];
    }

    $this->fields_list = array(
        'id_envio' => array(
            'title' => $this->trans('ID Tipsa', array(), 'Modules.Tipsacarrier.Admin'),
            'align' => 'center',
            'class' => 'fixed-width-xs',
            'remove_onclick' => true,
        ),
        'reference' => array(
            'title' => $this->trans('Reference', array(), 'Modules.Tipsacarrier.Admin'),
            'remove_onclick' => true,
        ),
        'date_add' => array(
            'title' => $this->trans('Date Order', array(), 'Modules.Tipsacarrier.Admin'),
            'align' => 'text-right',
            'type' => 'datetime',
            'filter_key' => 'o!date_add',
            'remove_onclick' => true,
        ),
        'customer' => array(
            'title' => $this->trans('Customer', array(), 'Modules.Tipsacarrier.Admin'),
            'havingFilter' => true,
            'remove_onclick' => true,
        ),
        'cname' => array(
            'title' => $this->trans('Country', array(), 'Modules.Tipsacarrier.Admin'),
            'havingFilter' => true,
            'remove_onclick' => true,
        ),
        'total_paid_tax_incl' => array(
            'title' => $this->trans('Total', array(), 'Modules.Tipsacarrier.Admin'),
            'align' => 'text-right',
            'type' => 'price',
            'currency' => true,
            'callback' => 'setOrderCurrency',
            'badge_success' => true,
            'remove_onclick' => true,
        ),
        'payment' => array(
            'title' => $this->trans('Payment', array(), 'Modules.Tipsacarrier.Admin'),
            'remove_onclick' => true,
        ),
        'osname' => array(
            'title' => $this->trans('Status In PrestaShop', array(), 'Modules.Tipsacarrier.Admin'),
            'type' => 'select',
            'color' => 'color',
            'list' => $this->statuses_array,
            'filter_key' => 'os!id_order_state',
            'filter_type' => 'int',
            'order_key' => 'osname',
            'remove_onclick' => true,
        ),
        'fecha' => array(
            'title' => $this->trans('Date Shipping', array(), 'Modules.Tipsacarrier.Admin'),
            'align' => 'text-right',
            'type' => 'datetime',
            'remove_onclick' => true,
        ),
        'url_track' => array(
            'title' => $this->trans('URL Tracking', array(), 'Modules.Tipsacarrier.Admin'),
            'type' => 'link',
            'remove_onclick' => true,
        ),
        'num_albaran' => array(
            'title' => $this->trans('Shipping Number', array(), 'Modules.Tipsacarrier.Admin'),
            'remove_onclick' => true,
        ),
        'codigo_barras' => array(
            'title' => $this->trans('BarCode Number', array(), 'Modules.Tipsacarrier.Admin'),
            'type' => 'label',
            'remove_onclick' => true,
        ),
    );
  }

  public static function setOrderCurrency($echo, $tr)
  {
    $order = new Order($tr['id_order']);
    return Tools::displayPrice($echo, (int) $order->id_currency);
  }

  public function initProcess()
  {
    //Initialize the Shippings.
    $this->module->initializeShippings();
    parent::initProcess();
  }

  public function postProcess()
  {
    if (Tools::isSubmit('generateLabel' . $this->table)) {
      $this->module->printLabel((int) Tools::getValue($this->identifier));
    }

    if (Tools::isSubmit('updateStatus' . $this->table)) {
      $this->module->updateStatus((int) Tools::getValue($this->identifier));
    }

    if (Tools::isSubmit('deleteShipping' . $this->table)) {
      $this->deleteShipping((int) Tools::getValue($this->identifier));
    }

    return parent::postProcess();
  }

  /**
   * @param string $token
   * @param int $id
   * @param string $name
   * @return mixed
   */
  public function displayGeneratelabelLink($token = null, $id, $name = null)
  {
    $tpl = $this->createTemplate('helpers/list/list_action_default.tpl');
    if (!array_key_exists('Bad SQL query', self::$cache_lang)) {
      self::$cache_lang['Generatelabel'] = $this->trans('Generate Label', array(), 'Modules.Tipsacarrier.Admin');
    }

    $tpl->assign(array(
        'href' => self::$currentIndex . '&' . $this->identifier . '=' . $id . '&generateLabel' . $this->table . '&token=' . ($token != null ? $token : $this->token),
        'action' => self::$cache_lang['Generatelabel'],
    ));

    return $tpl->fetch();
  }

  /**
   * @param string $token
   * @param int $id
   * @param string $name
   * @return mixed
   */
  public function displayUpdatestatusLink($token = null, $id, $name = null)
  {
    $tpl = $this->createTemplate('helpers/list/list_action_default.tpl');
    if (!array_key_exists('Bad SQL query', self::$cache_lang)) {
      self::$cache_lang['Updatestatus'] = $this->trans('Update Tipsa Status', array(), 'Modules.Tipsacarrier.Admin');
    }

    $tpl->assign(array(
        'href' => self::$currentIndex . '&' . $this->identifier . '=' . $id . '&updateStatus' . $this->table . '&token=' . ($token != null ? $token : $this->token),
        'action' => self::$cache_lang['Updatestatus'],
    ));

    return $tpl->fetch();
  }

  /**
   * @param string $token
   * @param int $id
   * @param string $name
   * @return mixed
   */
  public function displayDeleteLink($token = null, $id, $name = null)
  {
    $tpl = $this->createTemplate('helpers/list/list_action_delete.tpl');

    $order = new Order($id);
    $name = '\n\n' . $this->trans('Reference:', array(), 'Modules.Tipsacarrier.Admin') . ' ' . $order->reference;

    $tpl->assign(array(
        'href' => self::$currentIndex . '&' . $this->identifier . '=' . $id . '&deleteShipping' . $this->table . '&token=' . ($token != null ? $token : $this->token),
        'confirm' => $this->trans('Delete the selected item?', array(), 'Modules.Tipsacarrier.Admin') . $name,
        'action' => $this->trans('Delete', array(), 'Modules.Tipsacarrier.Admin'),
        'id' => $id,
    ));

    return $tpl->fetch();
  }

  public function deleteShipping($id_order)
  {
    $result = Db::getInstance()->execute(
            'DELETE FROM ' . _DB_PREFIX_ . 'tipsa_envios '
            . 'WHERE id_envio = ' . (int) $id_order
    );
    //Initialize the Shippings.
    $this->module->initializeShippings();
    return $result;
  }

  public function renderList()
  {
    return parent::renderList();
  }

  protected function processBulkGenerateLabel()
  {
    foreach (Tools::getValue($this->table . 'Box') as $id_envio) {
      $this->module->printLabel((int) $id_envio);
    }
  }

  protected function processBulkUpdateStatus()
  {
    foreach (Tools::getValue($this->table . 'Box') as $id_envio) {
      $this->module->updateStatus((int) $id_envio);
    }
    if (!count($this->errors)) {
      Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
    }
  }

  protected function processBulkDelete()
  {
    foreach (Tools::getValue($this->table . 'Box') as $id_envio) {
      $this->deleteShipping($id_envio);
    }
    //Initialize the Shippings.
    $this->module->initializeShippings();
  }

}
