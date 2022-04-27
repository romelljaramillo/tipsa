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
if (!defined('_PS_VERSION_'))
  exit;

class Tipsacarrier extends CarrierModule
{

  public $id_carrier;
  private $_html = '';
  public $errors;
  private $_postErrors = array();
  public $_urlDinaPaq = "http://213.236.3.131:8085/dinapaqweb/detalle_envio.php?servicio=@&fecha=";

  public function __construct()
  {
    $this->name = 'tipsacarrier';
    $this->tab = 'shipping_logistics';
    $this->version = '17.1.2';
    $this->author = 'eComm360 SL';
    $this->controllers = array('AdminTipsaCarriers');
    $this->bootstrap = true;

    parent::__construct();

    $this->displayName = $this->l('Transportista TIPSA');
    $this->description = $this->l('Módulo que integra el sistema de envíos con TIPSA');

    //Self verificarion of internal Carriers
    if (self::isInstalled($this->name)) {
      // Getting carrier list
      $carriers = Carrier::getCarriers($this->context->language->id, true, false, false, null, PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);

      // Saving id carrier list
      $id_carrier_list = array();

      foreach ($carriers as $carrier) {
        $id_carrier_list[] .= $carrier['id_carrier'];
      }

      // Testing if Carrier Id exists
      $warning = array();
      if (!Configuration::get('TIPSA_CODIGO_AGENCIA')) {
        $warning[] .= $this->l('"Código de Agencia"') . ' ';
      }
      if (!Configuration::get('TIPSA_CODIGO_CLIENTE')) {
        $warning[] .= $this->l('"Código del cliente"') . ' ';
      }
      if (!Configuration::get('TIPSA_PASSWORD_CLIENTE')) {
        $warning[] .= $this->l('"Password del cliente"') . ' ';
      }
      if (!Configuration::get('TIPSA_URL')) {
        $warning[] .= $this->l('"URL del WS"') . ' ';
      }
      if (count($warning)) {
        $this->warning .= implode(' , ', $warning) . $this->l('debe finalizar la configuración antes de utilizar este módulo.') . ' ';
      }
    }
  }

  /**
   * Installation and creation of tables and carriers
   * 
   * @return boolean
   * 
   */
  public function install()
  {
    //Execution of Queries
    include_once dirname(__FILE__) . '/sql/install.php';

    $url_tracking = 'http://www.tip-sa.com/cliente/datos_prestashop.php?id=@';

    $carrier_list = array('PREMIUM' => 'Tipsa PREMIUM',
        'DELEGACION' => 'Recoge en DELEGACION',
        'TIPSA-MV' => 'Tipsa MASIVO',
        'ECONOMY' => 'Tipsa ECONOMY',
        'TIPSA-10' => 'Tipsa Antes 10 am.',
        'AEREA' => 'Tipsa Carga AEREA',
        'MARITIMA' => 'Tipsa Carga MARITIMA',
        'FARMA' => 'Tipsa FARMA'
    );

    $carrierConfig = array();
    foreach ($carrier_list as $carrierName => $carrierAlias) {
      $carrierConfig[] = array('name' => $carrierName,
          'id_tax_rules_group' => 0,
          'url' => $url_tracking,
          'active' => false,
          'deleted' => 0,
          'shipping_handling' => false,
          'range_behavior' => 0,
          'delay' => $carrierAlias,
          'id_zone' => 1,
          'is_module' => false,
          'shipping_external' => false,
          'external_module_name' => $this->name,
          'need_range' => false
      );
    }

    $id_carrier1 = $this->installExternalCarrier($carrierConfig[0]);
    Configuration::updateValue('TIPSACARRIER1_CARRIER_ID', (int) $id_carrier1);
    $id_carrier2 = $this->installExternalCarrier($carrierConfig[1]);
    Configuration::updateValue('TIPSACARRIER2_CARRIER_ID', (int) $id_carrier2);
    $id_carrier3 = $this->installExternalCarrier($carrierConfig[2]);
    Configuration::updateValue('TIPSACARRIER3_CARRIER_ID', (int) $id_carrier3);
    $id_carrier4 = $this->installExternalCarrier($carrierConfig[3]);
    Configuration::updateValue('TIPSACARRIER4_CARRIER_ID', (int) $id_carrier4);
    $id_carrier5 = $this->installExternalCarrier($carrierConfig[4]);
    Configuration::updateValue('TIPSACARRIER5_CARRIER_ID', (int) $id_carrier5);
    $id_carrier6 = $this->installExternalCarrier($carrierConfig[5]);
    Configuration::updateValue('TIPSACARRIER6_CARRIER_ID', (int) $id_carrier6);
    $id_carrier7 = $this->installExternalCarrier($carrierConfig[6]);
    Configuration::updateValue('TIPSACARRIER7_CARRIER_ID', (int) $id_carrier7);
    $id_carrier8 = $this->installExternalCarrier($carrierConfig[7]);
    Configuration::updateValue('TIPSACARRIER8_CARRIER_ID', (int) $id_carrier8);

    //Tipsa states 
    Configuration::updateValue('TIPSA_TRANSITO', (int) Configuration::get('PS_OS_SHIPPING'));
    Configuration::updateValue('TIPSA_ENTREGADO', (int) Configuration::get('PS_OS_DELIVERED'));
    Configuration::updateValue('TIPSA_INCIDENCIA', (int) Configuration::get('PS_OS_ERROR'));

    //URLs for TIPSA Webservices.
    Configuration::updateValue('TIPSA_URL', 'HTTP://webservices.tipsa-dinapaq.com:8099/SOAP?service=LoginWSservice');
    Configuration::updateValue('TIPSA_URLwb', 'HTTP://webservices.tipsa-dinapaq.com:8099/SOAP?service=WebServService');

    if (!parent::install() || !$this->registerHook('updateCarrier') || !$this->registerHook('adminOrder') || !$this->addTabInBackOffice()) {
      return false;
    }

    return true;
  }

  /**
   * Create a new tab inside the Order Menu to manage the module.
   * 
   * @return boolean
   */
  public function addTabInBackOffice()
  {
    $tab = new Tab();
    $tab->active = 1;
    $tab->class_name = 'AdminTipsacarriers';
    $tab->name = array();
    foreach (Language::getLanguages(true) as $lang) {
        $tab->name[$lang['id_lang']] = "Gestor TIPSA";
    }
    $tab->id_parent = (int) Tab::getIdFromClassName('AdminParentOrders');
    $tab->module = $this->name;

    return $tab->add();
  }

    public function uninstallTab()
    {
        $id_tab = (int) Tab::getIdFromClassName('AdminTipsacarriers');
        $tab = new Tab($id_tab);
        return $tab->delete();
    }

  /**
   * Unistallation of tables and carriers
   * 
   * @return boolean
   * 
   */
  public function uninstall()
  {
    // Uninstall
    if (!parent::uninstall() ||
        !$this->unregisterHook('updateCarrier') ||
        ! $this->uninstallTab()) {
      return false;
    }
    $Carrier = array();
    // Delete External Carrier
    $Carrier['1'] = new Carrier((int) (Configuration::get('TIPSACARRIER1_CARRIER_ID')));
    $Carrier['2'] = new Carrier((int) (Configuration::get('TIPSACARRIER2_CARRIER_ID')));
    $Carrier['3'] = new Carrier((int) (Configuration::get('TIPSACARRIER3_CARRIER_ID')));
    $Carrier['4'] = new Carrier((int) (Configuration::get('TIPSACARRIER4_CARRIER_ID')));
    $Carrier['5'] = new Carrier((int) (Configuration::get('TIPSACARRIER5_CARRIER_ID')));
    $Carrier['6'] = new Carrier((int) (Configuration::get('TIPSACARRIER6_CARRIER_ID')));
    $Carrier['7'] = new Carrier((int) (Configuration::get('TIPSACARRIER7_CARRIER_ID')));
    $Carrier['8'] = new Carrier((int) (Configuration::get('TIPSACARRIER8_CARRIER_ID')));

    // If external carrier is default set other one as default
    if (Configuration::get('PS_CARRIER_DEFAULT') == (int) ($Carrier['1']->id) || Configuration::get('PS_CARRIER_DEFAULT') == (int) ($Carrier['2']->id) || Configuration::get('PS_CARRIER_DEFAULT') == (int) ($Carrier['3']->id) || Configuration::get('PS_CARRIER_DEFAULT') == (int) ($Carrier['4']->id) || Configuration::get('PS_CARRIER_DEFAULT') == (int) ($Carrier['5']->id) || Configuration::get('PS_CARRIER_DEFAULT') == (int) ($Carrier['6']->id) || Configuration::get('PS_CARRIER_DEFAULT') == (int) ($Carrier['7']->id)) {
      $carriersD = Carrier::getCarriers($this->context->language->id, true, false, false, null, PS_CARRIERS_ONLY);
      foreach ($carriersD as $carrierD) {
        if ($carrierD['active'] && !$carrierD['deleted'] && ($carrierD['name'] != $this->_config['name'])) {
          Configuration::updateValue('PS_CARRIER_DEFAULT', $carrierD['id_carrier']);
        }
      }
    }

    foreach ($Carrier as $carrierObj) {
      $carrierObj->deleted = 1;
      if (!$carrierObj->update()) {
        return false;
      }
    }

    //Execution of Queries
    include_once dirname(__FILE__) . '/sql/uninstall.php';

    return true;
  }

  /**
   * Function to install a new carrier from an array.
   * 
   * @param array $config
   * @return boolean | integer New id for carrier
   */
  public static function installExternalCarrier($config)
  {
    $carrier = new Carrier();
    $carrier->name = $config['name'];
    $carrier->id_tax_rules_group = $config['id_tax_rules_group'];
    $carrier->url = $config['url'];
    $carrier->id_zone = $config['id_zone'];
    $carrier->active = $config['active'];
    $carrier->deleted = $config['deleted'];
    $carrier->shipping_handling = $config['shipping_handling'];
    $carrier->range_behavior = $config['range_behavior'];
    $carrier->is_module = $config['is_module'];
    $carrier->shipping_external = $config['shipping_external'];
    $carrier->external_module_name = $config['external_module_name'];
    $carrier->need_range = $config['need_range'];
    $languages = Language::getLanguages(true);
    foreach ($languages as $language) {
      $carrier->delay[(int) $language['id_lang']] = $config['delay'];
    }
    if ($carrier->add()) {
      $groups = Group::getGroups(true);
      foreach ($groups as $group) {
          Db::getInstance()->insert('carrier_group', array('id_carrier' => (int) ($carrier->id), 'id_group' => (int) ($group['id_group'])));
      }
      $rangePrice = new RangePrice();
      $rangePrice->id_carrier = $carrier->id;
      $rangePrice->delimiter1 = '0';
      $rangePrice->delimiter2 = '1000000000';
      $rangePrice->add();
      $rangeWeight = new RangeWeight();
      $rangeWeight->id_carrier = $carrier->id;
      $rangeWeight->delimiter1 = '0';
      $rangeWeight->delimiter2 = '1000000000';
      $rangeWeight->add();
      $zones = Zone::getZones(true);
      foreach ($zones as $zone) {
          Db::getInstance()->insert('carrier_zone', array('id_carrier' => (int) ($carrier->id), 'id_zone' => (int) ($zone['id_zone'])));
          Db::getInstance()->insert('delivery', array('id_carrier' => (int) ($carrier->id), 'id_range_price' => (int) ($rangePrice->id), 'id_range_weight' => null, 'id_zone' => (int) ($zone['id_zone']), 'price' => '0'));
          Db::getInstance()->insert('delivery', array('id_carrier' => (int) ($carrier->id), 'id_range_price' => null, 'id_range_weight' => (int) ($rangeWeight->id), 'id_zone' => (int) ($zone['id_zone']), 'price' => '0'));
      }
      // copy all logos of our service
      if (!Tools::copy(dirname(__FILE__) . '/views/img/' . Tools::strtolower($config['name']) . '.jpg', _PS_SHIP_IMG_DIR_ . '/' . (int) $carrier->id . '.jpg')) {
        PrestaShopLogger::addLog('Image for carrier with id ' . $carrier->id . ' not found when Install /views/img/' . Tools::strtolower($config['name']) . '.jpg');
      }

      // Return ID Carrier
      return (int) ($carrier->id);
    }
    return false;
  }

  public function getContent()
  {
    if (((bool) Tools::isSubmit('btnSubmit')) == true) {
      $this->_postValidation();
      if (!count($this->_postErrors))
        $this->_postProcess();
      else
        foreach ($this->_postErrors as $err) {
          $this->_html .= $this->displayError($err);
        }
    }

    if (Tools::getValue('testingConnection')) {
      $this->testConnectionTipsa();
    }

    $fields_form = array(
        'form' => array(
            'legend' => array(
                'title' => $this->l('General configuration'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Codigo Agencia'),
                    'name' => 'tipsa_codigo_agencia',
                    'desc' => $this->l('Codigo de Agencia de TIPSA'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Codigo Cliente'),
                    'desc' => $this->l('Codigo Clientes TIPSA'),
                    'name' => 'tipsa_codigo_cliente',
                ),
                array(
                    'type' => 'text',
                    'name' => 'tipsa_password_cliente',
                    'label' => $this->l('Password Cliente'),
                    'desc' => $this->l('Password Cliente de Tipsa'),
                ),
                array(
                    'type' => 'text',
                    'name' => 'tipsa_url',
                    'label' => $this->l('URL WS'),
                    'desc' => $this->l('WebService por defecto: HTTP://webservices.tipsa-dinapaq.com:8099/SOAP?service=LoginWSservice , si tenéis problemas con puerto 8099 dejar solo el 80'),
                ),
                array(
                    'type' => 'text',
                    'name' => 'tipsa_urlwb',
                    'label' => $this->l('URL WSwb'),
                    'desc' => $this->l('WebService por defecto: HTTP://webservices.tipsa-dinapaq.com:8099/SOAP?service=WebServService , si tenéis problemas con puerto 8099 dejar solo el 80'),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->getTranslator()->trans('Usar bultos dinamicamente', array(), 'Admin.Global'),
                    'name' => 'tipsa_bultos',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Global')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Global')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Bultos por pedido'),
                    'name' => 'tipsa_num_fijo_bultos',
                    'desc' => $this->l('Indique el número de bultos por pedido (no se tiene en cuenta la cantidad de productos)'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Número de bultos por unidad de producto'),
                    'name' => 'tipsa_num_articulos',
                    'desc' => $this->l('Se calcula el número de bultos usando (Nº dinamico x Cantidad de productos)'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Nombre del módulo de contrarreembolso'),
                    'name' => 'tipsa_modulo_contrarreembolso',
                    'desc' => $this->l('Nombre del módulo de contrarreembolso'),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Estado en PrestaShop para TRANSITO'),
                    'name' => 'TIPSA_TRANSITO',
                    'required' => false,
                    'default_value' => (int) Configuration::get('PS_OS_SHIPPING'),
                    'options' => array(
                        'query' => OrderState::getOrderStates($this->context->language->id),
                        'id' => 'id_order_state',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Estado en PrestaShop para ENTREGADO'),
                    'name' => 'TIPSA_ENTREGADO',
                    'required' => false,
                    'default_value' => (int) Configuration::get('PS_OS_DELIVERED'),
                    'options' => array(
                        'query' => OrderState::getOrderStates($this->context->language->id),
                        'id' => 'id_order_state',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Estado en PrestaShop para INCIDENCIA'),
                    'name' => 'TIPSA_INCIDENCIA',
                    'required' => false,
                    'default_value' => (int) Configuration::get('PS_OS_ERROR'),
                    'options' => array(
                        'query' => OrderState::getOrderStates($this->context->language->id),
                        'id' => 'id_order_state',
                        'name' => 'name'
                    )
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        ),
    );
    $helper = new HelperForm();
    $helper->show_toolbar = true;
    $helper->table = $this->table;
    $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
    $helper->default_form_language = $lang->id;
    $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
    $helper->identifier = $this->identifier;
    $helper->submit_action = 'btnSubmit';
    $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');
    $helper->tpl_vars = array(
        'fields_value' => $this->getConfigFieldsValues(),
        'languages' => $this->context->controller->getLanguages(),
        'id_language' => $this->context->language->id
    );

    $this->context->smarty->assign(array(
        'back_link' => $helper->currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
        'tipsacarrier_cron' => _PS_BASE_URL_ . _MODULE_DIR_ . 'tipsacarrier/tipsacarrier-cron.php?token=' . substr(Tools::encrypt('tipsacarrier/cron'), 0, 10),
    ));
    $this->_html .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
    $this->_html .=$helper->generateForm(array($fields_form));
    return $this->_html;
  }

  public function getConfigFieldsValues()
  {
    return array(
        'tipsa_codigo_agencia' => Tools::getValue('tipsa_codigo_agencia', Configuration::get('TIPSA_CODIGO_AGENCIA')),
        'tipsa_codigo_cliente' => Tools::getValue('tipsa_codigo_cliente', Configuration::get('TIPSA_CODIGO_CLIENTE')),
        'tipsa_password_cliente' => Tools::getValue('tipsa_password_cliente', Configuration::get('TIPSA_PASSWORD_CLIENTE')),
        'tipsa_url' => Tools::getValue('tipsa_url', Configuration::get('TIPSA_URL')),
        'tipsa_urlwb' => Tools::getValue('tipsa_urlwb', Configuration::get('TIPSA_URLwb')),
        'tipsa_servicio_14' => Tools::getValue('envio_servicio_14', Configuration::get('TIPSA_14H')),
        'tipsa_servicio_20' => Tools::getValue('envio_servicio_20', Configuration::get('DELEGACION')),
        'tipsa_servicio_MV' => Tools::getValue('envio_servicio_MV', Configuration::get('TIPSA_MV')),
        'tipsa_servicio_48' => Tools::getValue('envio_servicio_48', Configuration::get('TIPSA_48H')),
        'tipsa_servicio_06' => Tools::getValue('envio_servicio_06', Configuration::get('AEREA')),
        'tipsa_servicio_96' => Tools::getValue('envio_servicio_96', Configuration::get('MARITIMA')),
        'tipsa_bultos' => Tools::getValue('tipsa_bultos', Configuration::get('TIPSA_BULTOS')),
        'tipsa_num_fijo_bultos' => Tools::getValue('tipsa_num_fijo_bultos', Configuration::get('TIPSA_FIJO_BULTOS')),
        'tipsa_num_articulos' => Tools::getValue('tipsa_num_articulos', Configuration::get('TIPSA_NUM_BULTOS')),
        'tipsa_modulo_contrarreembolso' => Tools::getValue('tipsa_modulo_contrarreembolso', Configuration::get('TIPSA_MOD_CONTRA')),
        'TIPSA_TRANSITO' => Tools::getValue('TIPSA_TRANSITO', Configuration::get('TIPSA_TRANSITO')),
        'TIPSA_ENTREGADO' => Tools::getValue('TIPSA_ENTREGADO', Configuration::get('TIPSA_ENTREGADO')),
        'TIPSA_INCIDENCIA' => Tools::getValue('TIPSA_INCIDENCIA', Configuration::get('TIPSA_INCIDENCIA')),
    );
  }

  private function _postValidation()
  {
    // Check configuration values
    if (Tools::getValue('tipsa_codigo_agencia') == '' && Tools::getValue('tipsa_codigo_cliente') == '' && Tools::getValue('tipsa_password_cliente') == '' && Tools::getValue('tipsa_url') == '')
      $this->_postErrors[] = $this->l('Necesita configurar correctamente: código agencia, código cliente, password cliente y URL del Web Service.');
  }

  private function _postProcess()
  {
    // Saving new configurations
    if (Configuration::updateValue('TIPSA_CODIGO_AGENCIA', Tools::getValue('tipsa_codigo_agencia')) &&
            Configuration::updateValue('TIPSA_CODIGO_CLIENTE', Tools::getValue('tipsa_codigo_cliente')) &&
            Configuration::updateValue('TIPSA_PASSWORD_CLIENTE', Tools::getValue('tipsa_password_cliente')) &&
            Configuration::updateValue('TIPSA_URL', Tools::getValue('tipsa_url')) &&
            Configuration::updateValue('TIPSA_URLwb', Tools::getValue('tipsa_urlwb')) &&
            Configuration::updateValue('TIPSA_ENVIO_GRAT', Tools::getValue('tipsa_envio_gratuito')) &&
            Configuration::updateValue('TIPSA_SERVICIO_GRAT', Tools::getValue('tipsa_servicio_envio_gratuito')) &&
            Configuration::updateValue('TIPSA_IMP_MIN_ENVIO_GRA', Tools::getValue('tipsa_importe_minimo_envio_gratuito')) &&
            Configuration::updateValue('TIPSA_RESTO', Tools::getValue('tipsa_mostrar_todo')) &&
            Configuration::updateValue('TIPSA_14H', Tools::getValue('tipsa_servicio_14')) &&
            Configuration::updateValue('DELEGACION', Tools::getValue('tipsa_servicio_20')) &&
            Configuration::updateValue('TIPSA_MV', Tools::getValue('tipsa_servicio_MV')) &&
            Configuration::updateValue('TIPSA_48H', Tools::getValue('tipsa_servicio_48')) &&
            Configuration::updateValue('AEREA', Tools::getValue('tipsa_servicio_06')) &&
            Configuration::updateValue('MARITIMA', Tools::getValue('tipsa_servicio_96')) &&
            Configuration::updateValue('TIPSA_BULTOS', Tools::getValue('tipsa_bultos')) &&
            Configuration::updateValue('TIPSA_FIJO_BULTOS', Tools::getValue('tipsa_num_fijo_bultos')) &&
            Configuration::updateValue('TIPSA_NUM_BULTOS', Tools::getValue('tipsa_num_articulos')) &&
            Configuration::updateValue('TIPSA_MOD_CONTRA', Tools::getValue('tipsa_modulo_contrarreembolso')) &&
            Configuration::updateValue('TIPSA_CALCULAR_PRECIO', Tools::getValue('tipsa_precio_por')) &&
            Configuration::updateValue('TIPSA_IMPUESTO', Tools::getValue('tipsa_impuesto_agregado')) &&
            Configuration::updateValue('TIPSA_COSTE_FIJO_ENVIO', Tools::getValue('tipsa_coste_fijo_envio')) &&
            Configuration::updateValue('TIPSA_MANIPULACION', Tools::getValue('tipsa_manipulacion')) &&
            Configuration::updateValue('TIPSA_COSTE_MANIPULACION', Tools::getValue('tipsa_coste_manipulacion')) &&
            Configuration::updateValue('TIPSA_MARGEN_COSTE_ENVIO', Tools::getValue('tipsa_margen_coste_envio')) &&
            Configuration::updateValue('TIPSA_SOBRE_CP', Tools::getValue('tipsa_sobreescribir_cp')) &&
            Configuration::updateValue('TIPSA_TRANSITO', Tools::getValue('TIPSA_TRANSITO')) &&
            Configuration::updateValue('TIPSA_ENTREGADO', Tools::getValue('TIPSA_ENTREGADO')) &&
            Configuration::updateValue('TIPSA_INCIDENCIA', Tools::getValue('TIPSA_INCIDENCIA'))
    )
      $this->_html .= $this->displayConfirmation($this->l('Configuración actualizada'));
    else
      $this->_html .= $this->displayError($this->l('Error al actualizar la configuración'));

    $this->_postErrors[] = $this->l('Necesita configurar correctamente: código agencia, código cliente, password cliente y URL del Web Service.');
  }

  /**
   * Required function called when any carrier is Update.
   * 
   * @param array $params From hook call.
   */
  public function hookupdateCarrier($params)
  {
    if ((int) ($params['id_carrier']) == (int) (Configuration::get('TIPSACARRIER1_CARRIER_ID'))) {
      Configuration::updateValue('TIPSACARRIER1_CARRIER_ID', (int) ($params['carrier']->id));
    }
    if ((int) ($params['id_carrier']) == (int) (Configuration::get('TIPSACARRIER2_CARRIER_ID'))) {
      Configuration::updateValue('TIPSACARRIER2_CARRIER_ID', (int) ($params['carrier']->id));
    }
    if ((int) ($params['id_carrier']) == (int) (Configuration::get('TIPSACARRIER3_CARRIER_ID'))) {
      Configuration::updateValue('TIPSACARRIER3_CARRIER_ID', (int) ($params['carrier']->id));
    }
    if ((int) ($params['id_carrier']) == (int) (Configuration::get('TIPSACARRIER4_CARRIER_ID'))) {
      Configuration::updateValue('TIPSACARRIER4_CARRIER_ID', (int) ($params['carrier']->id));
    }
    if ((int) ($params['id_carrier']) == (int) (Configuration::get('TIPSACARRIER5_CARRIER_ID'))) {
      Configuration::updateValue('TIPSACARRIER5_CARRIER_ID', (int) ($params['carrier']->id));
    }
    if ((int) ($params['id_carrier']) == (int) (Configuration::get('TIPSACARRIER6_CARRIER_ID'))) {
      Configuration::updateValue('TIPSACARRIER6_CARRIER_ID', (int) ($params['carrier']->id));
    }
    if ((int) ($params['id_carrier']) == (int) (Configuration::get('TIPSACARRIER7_CARRIER_ID'))) {
      Configuration::updateValue('TIPSACARRIER7_CARRIER_ID', (int) ($params['carrier']->id));
    }
    if ((int) ($params['id_carrier']) == (int) (Configuration::get('TIPSACARRIER8_CARRIER_ID'))) {
      Configuration::updateValue('TIPSACARRIER8_CARRIER_ID', (int) ($params['carrier']->id));
    }
  }

  public function hookAdminOrder($params)
  {
    $output = '';
    $order = new Order((int) $params['id_order']);
    $carrier = new Carrier((int) $order->id_carrier);

    $this->initializeShippings();

    if ($carrier->external_module_name == 'tipsacarrier') {

      $sql = 'SELECT codigo_barras '
              . 'FROM ' . _DB_PREFIX_ . 'tipsa_envios '
              . 'WHERE id_envio_order = ' . (int) $order->id . ' AND codigo_barras IS NOT NULL ';
      $showLabelButton = (bool) Db::getInstance()->getValue($sql);
      $linkLabel = Db::getInstance()->getValue($sql);

      if (Tools::isSubmit('generateLabeltipsa_envios') && !$showLabelButton) {
        $this->printLabel((int) Tools::getValue('TIPSA_ID_ENVIO'));
        $output .= $this->displayConfirmation('<span id="tipsa_show_message">' . $this->l('Label has been created.') . '</span>');
      }

      if (Tools::isSubmit('updateBultostipsa_envios')) {
        Db::getInstance()->update('tipsa_envios',array('bultos' => (int) Tools::getValue('packages')),'id_envio = '.Tools::getValue('TIPSA_ID_ENVIO'));
        $output .= $this->displayConfirmation('<span id="tipsa_show_message">' . $this->l('Status Updated.') . '</span>');
      }

      $delivery = new Address((int) $order->id_address_delivery);
      $messageList = Message::getMessagesByOrderId((int) $order->id);

      $this->context->smarty->assign(
              array(
                  'base_url' => _PS_BASE_URL_ . __PS_BASE_URI__,
                  'module_name' => $this->name,
                  'TIPSACARRIER_ID_ENVIO' => (int) Db::getInstance()->getValue('SELECT id_envio FROM ' . _DB_PREFIX_ . 'tipsa_envios WHERE id_envio_order = ' . (int) $order->id),
                  'TIPSACARRIER_PACKAGES' => (int) Db::getInstance()->getValue('SELECT bultos FROM ' . _DB_PREFIX_ . 'tipsa_envios WHERE id_envio_order = ' . (int) $order->id),
                  'ps_version' => _PS_VERSION_,
                  'showLabelButton' => !$showLabelButton,
                  'linkLabel' => $linkLabel,
                  'TIPSACARRIER_REFERENCE' => $order->reference,
                  'TIPSACARRIER_SHOPNAME' => Configuration::get('PS_SHOP_NAME'),
                  'TIPSACARRIER_SHOPPHONE' => Configuration::get('PS_SHOP_PHONE'),
                  'TIPSACARRIER_CP' => Configuration::get('PS_SHOP_CODE'),
                  'TIPSACARRIER_CITY' => Configuration::get('PS_SHOP_CITY'),
                  'TIPSACARRIER_ADDRESS' => Configuration::get('PS_SHOP_ADDR1'),
                  'TIPSACARRIER_ORDER_COMMENTS' => (isset($messageList['0']) && $messageList['0']['id_customer'] > 0) ? $messageList['0']['message'] : '',
                  'TIPSACARRIER_DEST_CONTACT_INFO' => $delivery->firstname . ' ' . $delivery->lastname,
                  'TIPSACARRIER_DEST_PHONE' => $delivery->phone . ' - ' . $delivery->phone_mobile,
                  'TIPSACARRIER_DEST_CP' => $delivery->postcode,
                  'TIPSACARRIER_DEST_CITY' => $delivery->city,
                  'TIPSACARRIER_DEST_ADDRESS' => $delivery->address1,
                  'TIPSACARRIER_DEST_COD' => ($order->total_paid_real),
              )
      );
      $output .= $this->display(__FILE__, 'orders_confirmation.tpl');
    }
    return $output;
  }

  /**
   * Returns the value from carrier if this carrier is Ready and Acvive.
   * 
   * @param array $params
   * @param float $shipping_cost
   * 
   * @return float Shipping price.
   */
  public function getOrderShippingCost($params, $shipping_cost)
  {
    return $shipping_cost;
  }

  public function getOrderShippingCostExternal($params)
  {
    PrestaShopLogger::addLog($this->l('Some method calls the external carrierCost, not do actions.'));
    return false;
  }

  /**
   * Initialize the Shipping values for each order if carrier is Tipsa.
   * 
   */
  public function initializeShippings()
  {
    // check if there is orders without register of new order
    $sql = 'SELECT o.id_order '
            . 'FROM ' . _DB_PREFIX_ . 'orders o '
            . 'LEFT JOIN ' . _DB_PREFIX_ . 'carrier c ON c.id_carrier = o.id_carrier '
            . 'LEFT OUTER JOIN ' . _DB_PREFIX_ . 'tipsa_envios tp ON tp.id_envio_order = o.id_order '
            . 'WHERE c.external_module_name = "tipsacarrier" AND tp.id_envio_order is NULL';
    $shippings = Db::getInstance()->executeS($sql);

    foreach ($shippings as $shipping) {
      Db::getInstance()->execute(
              'INSERT INTO ' . _DB_PREFIX_ . 'tipsa_envios (id_envio_order,codigo_envio,url_track,num_albaran) '
              . 'VALUES ("' . $shipping['id_order'] . '","","","")'
      );
    }
  }

  /**
   * Check if Connection data is ok and return the Curl Object.
   * 
   * @return string Curl Object.
   */
  public function loginTipsaUser()
  {
    $URL = Configuration::get('TIPSA_URL');
    $tipsaCodigoAgencia = Configuration::get('TIPSA_CODIGO_AGENCIA');
    $tipsaCodigoCliente = Configuration::get('TIPSA_CODIGO_CLIENTE');
    $tipsaPasswordCliente = Configuration::get('TIPSA_PASSWORD_CLIENTE');
    $XML = '<?xml version="1.0" encoding="utf-8"?>
                <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
                <soap:Body>
                  <LoginWSService___LoginCli>
                    <strCodAge>' . $tipsaCodigoAgencia . '</strCodAge>
                    <strCod>' . $tipsaCodigoCliente . '</strCod>
                    <strPass>' . $tipsaPasswordCliente . '</strPass>
                  </LoginWSService___LoginCli>
                </soap:Body>
                </soap:Envelope>';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($ch, CURLOPT_URL, $URL);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $XML);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));

    $postResult = curl_exec($ch);

    if ($postResult === false) {
      PrestaShopLogger::addLog('Curl error in TipdsaCarrier Module LoginWSService___LoginCli: ' . curl_error($ch));
    }
    curl_close($ch);

    return $postResult;
  }

  public function testConnectionTipsa()
  {
    $postResult = $this->loginTipsaUser();
    $dom = new DOMDocument;
    $dom->loadXML($postResult);
    $note = $dom->getElementsByTagName("Envelope");

    foreach ($note as $value) {
      $details = $value->getElementsByTagName("Result");
      $result_status = $details->item(0)->nodeValue;
    }

    // Saving new configurations
    if ($result_status == 'true') {
      $this->_html .= $this->displayConfirmation($this->l('Conexión correcta y comprobada.'));
    } else
      $this->_html .= $this->displayError($this->l('Error: Compruebe los valores de código agencia, código cliente, password cliente y URL del Web Service.'));
  }

  /**
   * Create the label for a shipping, recover the information from webservices
   * 
   * @param int $id_envio Id Envio inside PrestaShop Tipsa Table.
   * 
   * @return string Message of Success.
   */
  public function printLabel($id_envio = 0)
  {
    if ($id_envio) {
      $sql = 'SELECT codigo_barras '
              . 'FROM ' . _DB_PREFIX_ . 'tipsa_envios '
              . 'WHERE id_envio = ' . (int) $id_envio . ' AND codigo_barras IS NOT NULL ';
      $trackingCode = (bool) Db::getInstance()->getValue($sql);
    } else {
      //TODO Come back to the list.
    }

    if (!$trackingCode) {

      $URL = Configuration::get('TIPSA_URL');
      $tipsaCodigoAgencia = Configuration::get('TIPSA_CODIGO_AGENCIA');
      $tipsaCodigoCliente = Configuration::get('TIPSA_CODIGO_CLIENTE');

      $postResult = $this->loginTipsaUser();
      $dom = new DOMDocument;
      $dom->loadXML($postResult);
      $note = $dom->getElementsByTagName("Envelope");

      foreach ($note as $value) {
        $details = $value->getElementsByTagName("strSesion");
        $id_sesion_cliente = $details->item(0)->nodeValue;
        $details2 = $value->getElementsByTagName("strURLDetSegEnv");
        $tipsa_url_seguimiento = $details2->item(0)->nodeValue;
      }

      // we have the id_sesion
      // lets get to all data for do the order
      $shippingDetails = Db::getInstance()->getRow(
              'SELECT o.id_order, o.module, o.total_paid_real, o.reference,
                      c.name,
                      u.email,
                      a.firstname,a.lastname,a.address1,a.address2,a.postcode,a.other,a.city,a.phone,a.phone_mobile,
                      z.iso_code,
					  m.message
              FROM ' . _DB_PREFIX_ . 'orders AS o 
              LEFT JOIN ' . _DB_PREFIX_ . 'tipsa_envios AS tp ON tp.id_envio_order = o.id_order 
              LEFT JOIN ' . _DB_PREFIX_ . 'carrier AS c ON c.id_carrier = o.id_carrier 
              LEFT JOIN ' . _DB_PREFIX_ . 'customer AS u ON u.id_customer = o.id_customer 
              LEFT JOIN ' . _DB_PREFIX_ . 'address AS a ON a.id_address = o.id_address_delivery 
              LEFT JOIN ' . _DB_PREFIX_ . 'country AS z ON a.id_country = z.id_country 
			  LEFT JOIN ' . _DB_PREFIX_ . 'message AS m ON m.id_order = o.id_order
              WHERE tp.id_envio = ' . (int) $id_envio
      );
      //Assign id_order
      $id_order = $shippingDetails['id_order'];
      // Convert type of service to an speciffic value.
      switch ($shippingDetails['name']) {
        case 'PREMIUM':
          $tipsa_tipo_servicio = '24';
          break;
        case 'DELEGACION':
          $tipsa_tipo_servicio = '20';
          break;
        case 'TIPSA-MV':
          $tipsa_tipo_servicio = 'MV';
          break;
        case 'ECONOMY':
          $tipsa_tipo_servicio = '48';
          break;
        case 'AEREA':
          $tipsa_tipo_servicio = '06';
          break;
        case 'MARITIMA':
          $tipsa_tipo_servicio = '96';
          break;
        case 'FARMA':
          $tipsa_tipo_servicio = '25';
          break;
        case 'TIPSA-10':
          $tipsa_tipo_servicio = '10';
          break;
        default:
          PrestaShopLogger::addLog($this->l('Service name not found (please check if names of carriers have been modified. Name Carrier: ' . $shippingDetails['name']), 3);
          break;
      }
      //get the weihgt and quantity of the product
      $productos = Db::getInstance()->ExecuteS(
              'SELECT product_quantity, product_weight '
              . 'FROM ' . _DB_PREFIX_ . 'order_detail '
              . 'WHERE id_order = ' . (int) $id_order
      );

      $peso = 0;
      $num_productos = 0;
      foreach ($productos as $producto) {
        $peso += (float) ($producto['product_quantity'] * $producto['product_weight']);
        $num_productos += $producto['product_quantity'];
      }
      if ($peso < 1)
        $peso = 1;
      $tipsa_peso_origen = $peso;

      // calculated the number of parcels for the number of articles
      $tipsa_numero_paquetes = 1;
      $bultos = Configuration::get('TIPSA_BULTOS');
      //regular parcels
      if ($bultos == 0) {
        $num_articulos = Configuration::get('TIPSA_FIJO_BULTOS');
        if ($num_articulos == '' || $num_articulos == 0)
          $num_articulos = 1;
        $tipsa_numero_paquetes = (int) ($num_articulos);
      }
      //parcels by articles
      if ($bultos == 1) {
        $num_articulos = Configuration::get('TIPSA_NUM_BULTOS');
        if ($num_articulos == '' || $num_articulos == 0)
          $num_articulos = 1;
        $tipsa_numero_paquetes = Tools::ceilf($num_productos / $num_articulos); //$num_articulos : num bultos variables en la config
		$tipsa_numero_paquetes = (int) $tipsa_numero_paquetes;
      }
      $forcedBultos = (int) Db::getInstance()->getValue('SELECT bultos FROM ' . _DB_PREFIX_ . 'tipsa_envios WHERE id_envio_order = ' . (int) $id_order);
      if($forcedBultos > 0) {
        $tipsa_numero_paquetes = $forcedBultos;
      }
      //Order Information
      $tipsa_referencia = $shippingDetails['reference'];
      $tipsa_importe_servicio = $shippingDetails['total_paid_real'];
      //Address Information
      $tipsa_nombre_destinatario = $shippingDetails['firstname'] . ' ' . $shippingDetails['lastname'];
      $tipsa_nombre_via_destinatario = $shippingDetails['address1'] . '/' . $shippingDetails['address2'];
      $tipsa_poblacion_destinatario = $shippingDetails['city'];
      $tipsa_CP_destinatario = $shippingDetails['postcode'];
      if(!empty($shippingDetails['phone_mobile'])) {
        $tipsa_telefono_destinatario = $shippingDetails['phone_mobile'];
      }else {
        $tipsa_telefono_destinatario = $shippingDetails['phone'];
      }
      $tipsa_telefono_destinatarioMV = $shippingDetails['phone_mobile'];
      $tipsa_email_destinatario = $shippingDetails['email'];
      $tipsa_pais = $shippingDetails['iso_code'];
      $observaciones = $shippingDetails['message'] .'/'. $shippingDetails['other'];

      //Check for Cash On Delivery methods.
      $tipsa_reembolso = 0;
      if (in_array($shippingDetails['module'], explode(',', Configuration::get('TIPSA_MOD_CONTRA')))) {
        $tipsa_reembolso = (float) ($tipsa_importe_servicio);
      }

      //check the country of addresse
      if ($tipsa_pais != 'ES' && $tipsa_pais != 'PT' && $tipsa_pais != 'AD') {
        $tipsa_pais = '<strCodPais>' . $tipsa_pais . '</strCodPais>';
      }

      //Modification if PT CP is used.
      if ($tipsa_pais == 'PT') {
        $tipsa_port = explode('-', $tipsa_CP_destinatario);
        $tipsa_CP_destinatario = '6' . $tipsa_port[0];
      }

      //Store Information
      $vendedor = Configuration::getMultiple(array(
                  'PS_SHOP_NAME', 'PS_SHOP_EMAIL',
                  'PS_SHOP_ADDR1', 'PS_SHOP_ADDR2',
                  'PS_SHOP_CODE', 'PS_SHOP_CITY',
                  'PS_SHOP_COUNTRY_ID', 'PS_SHOP_STATE_ID',
                  'PS_SHOP_PHONE', 'PS_SHOP_FAX')
      );

      $tipsa_nombre_remitente = $vendedor['PS_SHOP_NAME'];
      $tipsa_nombre_via_remitente = $vendedor['PS_SHOP_ADDR1'];
      $tipsa_poblacion_remitente = $vendedor['PS_SHOP_CITY'];
      $tipsa_telefono_remitente = $vendedor['PS_SHOP_PHONE'];

      if (Configuration::get('TIPSA_SOBRE_CP'))
        $tipsa_CP_remitente = Configuration::get('TIPSA_SOBRE_CP');
      else
        $tipsa_CP_remitente = $vendedor['PS_SHOP_CODE'];

      $URL = Configuration::get('TIPSA_URLwb');
      //Realizamos el pedido
      $XML = '<?xml version="1.0" encoding="utf-8"?>
                <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
                  <soap:Header>
                    <ROClientIDHeader xmlns="http://tempuri.org/">
                      <ID>' . $id_sesion_cliente . '</ID>
                    </ROClientIDHeader>
                  </soap:Header>
                  <soap:Body>
                    <WebServService___GrabaEnvio4 xmlns="http://tempuri.org/">
                      <strCodAgeCargo>' . $tipsaCodigoAgencia . '</strCodAgeCargo>
                      <strCodAgeOri>' . $tipsaCodigoAgencia . '</strCodAgeOri>
                      <dtFecha>' . date('Y/m/d') . '</dtFecha>
                      <strCodTipoServ>' . $tipsa_tipo_servicio . '</strCodTipoServ>
                      <strCodCli>' . $tipsaCodigoCliente . '</strCodCli>
                      <strNomOri>' . $tipsa_nombre_remitente . '</strNomOri>
                      <strDirOri>' . $tipsa_nombre_via_remitente . '</strDirOri>
                      <strPobOri>' . $tipsa_poblacion_remitente . '</strPobOri>
                      <strCPOri>' . $tipsa_CP_remitente . '</strCPOri>
                      <strTlfOri>' . $tipsa_telefono_remitente . '</strTlfOri>
                      <strNomDes>' . $tipsa_nombre_destinatario . '</strNomDes>
                      <strDirDes>' . $tipsa_nombre_via_destinatario . '</strDirDes>
                      <strPobDes>' . $tipsa_poblacion_destinatario . '</strPobDes>
                      <strCPDes>' . $tipsa_CP_destinatario . '</strCPDes>
                      <strTlfDes>' . $tipsa_telefono_destinatario . '</strTlfDes>
                      <intPaq>' . $tipsa_numero_paquetes . '</intPaq>
                      <dPesoOri>' . $tipsa_peso_origen . '</dPesoOri>
                      <dReembolso>' . $tipsa_reembolso . '</dReembolso>
                      <strRef>' . $tipsa_referencia . '</strRef>
                      <strObs>' . $observaciones . '</strObs>
                      <boDesSMS>0</boDesSMS>
                      <boDesEmail>1</boDesEmail>
                      <strDesMoviles>' . $tipsa_telefono_destinatarioMV . '</strDesMoviles>
                      <strDesDirEmails>' . $tipsa_email_destinatario . '</strDesDirEmails>
                      <boInsert>' . true . '</boInsert>
                    </WebServService___GrabaEnvio4>
                  </soap:Body>
                </soap:Envelope>';

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_HEADER, false);
      curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
      curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
      curl_setopt($ch, CURLOPT_URL, $URL);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $XML);
      curl_setopt($ch, CURLOPT_HTTPHEADER, Array('Content-Type: text/xml'));

      $postResult = curl_exec($ch);
      if ($postResult === false) {
        PrestaShopLogger::addLog('Curl error in TipdsaCarrier Module LoginWSService___LoginCli: ' . curl_error($ch));
      }
      curl_close($ch);

      $dom = new DOMDocument;

      $dom->loadXML($postResult);
      $note = $dom->getElementsByTagName("Envelope");
      foreach ($note as $value) {
        if (!isset($value->getElementsByTagName("faultstring")->item(0)->nodeValue)) {
          $details = $value->getElementsByTagName("strAlbaranOut");
          $albaran = $details->item(0)->nodeValue;
          $numseg = $value->getElementsByTagName("strGuidOut");
          $tipsa_num_seguimiento = $numseg->item(0)->nodeValue;
        } else {
          $error = $value->getElementsByTagName("faultstring")->item(0)->nodeValue;
          $errorcode = $value->getElementsByTagName("faultcode")->item(0)->nodeValue;
        }

        //Error Information
        if (isset($errorcode) && $errorcode == 'EROSessionNotFound') {
          $albaran = 'Agencia, usuario o password incorrecto';
        }

        if (isset($id_sesion_cliente)) {
          if (isset($error)) {
            $albaran = explode(":", $error);
            $albaran = $albaran[0];
            switch ($albaran) {
              case '9':
                $albaran = 'CP incorrecto';
                break;
              case '6':
                $albaran = 'Tipo servicio no existe';
                break;
              case '28':
                $albaran = 'Servicio incorrecto para el destino seleccionado';
                break;
              case '100':
                $albaran = 'Agencia, usuario o password incorrecto';
                break;
            }
            PrestaShopLogger::addLog($this->l('TIPSA ERROR : ') . $error . 'Error: ' . $albaran, 3);
          }
        }

        $tipsa_num_albaran = $albaran;
        // Transform the url detail
        $cod_tracking = Tools::substr($tipsa_num_seguimiento, 1, 36);
        if (!$cod_tracking) {
          PrestaShopLogger::addLog($this->l('TIPSA ERROR : no hay codigo de Tracking'), 3);
        }
      }

      $XML = '<?xml version="1.0" encoding="utf-8"?>
                <soap:Envelope 
                  xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" 	
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
                  xmlns:xsd="http://www.w3.org/2001/XMLSchema">
                  <soap:Header>
                          <ROClientIDHeader xmlns="http://tempuri.org/">
                                  <ID>' . $id_sesion_cliente . '</ID>
                          </ROClientIDHeader>
                  </soap:Header>
                  <soap:Body>
                          <WebServService___ConsEtiquetaEnvio2>
                                  <strAlbaran>' . $tipsa_num_albaran . '</strAlbaran>
                                  <intIdRepDet>233</intIdRepDet>
                          </WebServService___ConsEtiquetaEnvio2>
                  </soap:Body>
                </soap:Envelope>';

      //Call to create Label.
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_HEADER, false);
      curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
      curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
      curl_setopt($ch, CURLOPT_URL, $URL);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $XML);
      curl_setopt($ch, CURLOPT_HTTPHEADER, Array('Content-Type: text/xml'));
      $postResult = curl_exec($ch);

      if ($postResult === false) {
        PrestaShopLogger::addLog('Curl error in TipdsaCarrier Module LoginWSService___LoginCli: ' . curl_error($ch));
      }
      curl_close($ch);

      $dom = new DOMDocument;
      $dom->loadXML($postResult);
      $note = $dom->getElementsByTagName("Envelope");
      foreach ($note as $value) {
        $details = $value->getElementsByTagName("strEtiqueta");
        $tipsa_etiqueta = $details->item(0)->nodeValue;
      }

      if ($ruta = $this->saveTipsaShipping($id_order, $cod_tracking, $tipsa_url_seguimiento, $tipsa_num_albaran, $tipsa_etiqueta)) {
        return $this->displayConfirmation($this->l('Label generated successfully'));
      } else {
        $ruta = '../modules/tipsacarrier/pdf';
        $permisos = Tools::substr(sprintf('%o', fileperms($ruta)), -4);
        $this->errors[] = sprintf(Tools::displayError('Error: Please check log in your Shop Logs. Permission errors %s .'), $permisos);
        PrestaShopLogger::addLog('Error: Please check log in your Shop Logs. Permission errors ' . $permisos, 3);
      }
    }
  }

  /**
   * Calls the Webservice to recover the actual status from a Shipping
   * 
   * @param int $id_envio Id Envio inside PrestaShop Tipsa Table.
   */
  public function updateStatus($id_envio)
  {
    $sql = 'SELECT id_envio_order, num_albaran '
            . 'FROM ' . _DB_PREFIX_ . 'tipsa_envios '
            . 'WHERE id_envio = ' . (int) $id_envio;
    $envioObj = Db::getInstance()->getRow($sql);
    $tipsa_num_albaran = $envioObj['num_albaran'];
    $id_order = $envioObj['id_envio_order'];

    $URL = Configuration::get('TIPSA_URL');
    $tipsaCodigoAgencia = Configuration::get('TIPSA_CODIGO_AGENCIA');

    $postResult = $this->loginTipsaUser();

    $dom = new DOMDocument;
    $dom->loadXML($postResult);
    $note = $dom->getElementsByTagName("Envelope");
    foreach ($note as $value) {
      $details = $value->getElementsByTagName("strSesion");
      $idsesion = $details->item(0)->nodeValue;
    }

    $URL = Configuration::get('TIPSA_URLwb');
    $XML = '<?xml version="1.0" encoding = "utf-8"?>';
    $XML .= '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">';
    $XML .= '<soap:Header>';
    $XML .= '	<ROClientIDHeader xmlns="http://tempuri.org/">';
    $XML .= '		<ID>' . $idsesion . '</ID>';
    $XML .= '	</ROClientIDHeader>';
    $XML .= '</soap:Header>';
    $XML .= '<soap:Body>';
    $XML .= '	<WebServService___ConsEnvEstados xmlns="http://tempuri.org/">';
    $XML .= '		<strCodAgeCargo>' . $tipsaCodigoAgencia . '</strCodAgeCargo>';
    $XML .= '		<strCodAgeOri>' . $tipsaCodigoAgencia . '</strCodAgeOri>';
    $XML .= '		<strAlbaran>' . $tipsa_num_albaran . '</strAlbaran>';
    $XML .= '	</WebServService___ConsEnvEstados>';
    $XML .= '</soap:Body>';
    $XML .= '</soap:Envelope>';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($ch, CURLOPT_URL, $URL);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $XML);
    curl_setopt($ch, CURLOPT_HTTPHEADER, Array('Content-Type: text/xml'));

    $postResult = curl_exec($ch);

    if ($postResult === false) {
      PrestaShopLogger::addLog('Curl error in TipdsaCarrier Module WebServService___ConsEnvEstados: ' . curl_error($ch));
    }
    curl_close($ch);

    $dom = new DOMDocument;
    $dom->loadXML($postResult);
    $note = $dom->getElementsByTagName("Envelope");
    foreach ($note as $value) {
      $estado = $value->getElementsByTagName("strEnvEstados")->item(0)->nodeValue;
      $error = (isset($value->getElementsByTagName("faultstring")->item(0)->nodeValue)) ? $value->getElementsByTagName("faultstring")->item(0)->nodeValue : null;
    }

    if (isset($error) && $error != null) {
      PrestaShopLogger::addLog('Error Obtaining the states from Webservice : ' . $error);
    }

    $estenv = explode('V_COD_TIPO_EST', $estado);
    $elements = count($estenv);
    $estado = explode('"', $estenv[$elements - 1]);
    $estado = (int) $estado[1];

    //assing for the states in  with information of dinapaq
    switch ($estado) {
      case 2:
        $this->updateOrderStatus(Configuration::get('TIPSA_TRANSITO'), $id_order);
        break;
      case 3:
        $this->updateOrderStatus(Configuration::get('TIPSA_ENTREGADO'), $id_order);
        break;
      case 4:
        $this->updateOrderStatus(Configuration::get('TIPSA_INCIDENCIA'), $id_order);
        break;
      default:
        PrestaShopLogger::addLog('Order State not exists in PrestaShop or not found. ID: ' . $estado);
    }
  }

  /**
   * Change order Status in PrestaShop.
   * 
   * @param int $id_order_state Id of Order status.
   * @param int $id_order Order to change status.
   */
  public function updateOrderStatus($id_order_state, $id_order)
  {
    $order_state = new OrderState($id_order_state);

    if (!Validate::isLoadedObject($order_state)) {
      $this->errors[] = sprintf(Tools::displayError('Order status #%d cannot be loaded'), $id_order_state);
    } else {
      $order = new Order((int) $id_order);
      if (!Validate::isLoadedObject($order)) {
        $this->errors[] = sprintf(Tools::displayError('Order #%d cannot be loaded'), $id_order);
      } else {
        $current_order_state = $order->getCurrentOrderState();
        if ($current_order_state->id == $order_state->id) {
          $this->errors[] = $this->displayWarning(sprintf('Order #%d has already been assigned this status.', $id_order));
        } else {
          $history = new OrderHistory();
          $history->id_order = $order->id;
          $history->id_employee = (int) (isset(Context::getContext()->employee->id)) ? Context::getContext()->employee->id : 0;

          $use_existings_payment = !$order->hasInvoice();
          $history->changeIdOrderState((int) $order_state->id, $order, $use_existings_payment);

          $carrier = new Carrier($order->id_carrier, $order->id_lang);
          $templateVars = array();
          if ($history->id_order_state == Configuration::get('PS_OS_SHIPPING') && $order->shipping_number) {
            $templateVars = array('{followup}' => str_replace('@', $order->shipping_number, $carrier->url));
          }

          if ($history->addWithemail(true, $templateVars)) {
            if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
              foreach ($order->getProducts() as $product) {
                if (StockAvailable::dependsOnStock($product['product_id'])) {
                  StockAvailable::synchronize($product['product_id'], (int) $product['id_shop']);
                }
              }
            }
          } else {
            $this->errors[] = sprintf(Tools::displayError('Cannot change status for order #%d.'), $id_order);
          }
        }
      }
    }
  }

  /**
   * Create the label for Tipsa Shipping inside the PDF folder.
   * 
   * @param int $id_order
   * @param int $codigo_envio
   * @param string $url_track
   * @param string $num_albaran
   * @param string $codigo_barras
   * 
   * @return boolean|string Path for PDF file
   */
  public function saveTipsaShipping($id_order, $codigo_envio, $url_track, $num_albaran, $codigo_barras)
  {
    $nombre = 'etiqueta_' . $id_order . '.pdf';
    $ruta = '../modules/tipsacarrier/pdf/' . $nombre;
    $descodificar = base64_decode($codigo_barras);
    if (!$fp2 = fopen($ruta, 'wb+')) {
      $this->errors[] = sprintf(Tools::displayError('Impossible to create file on %s.'), $ruta);
      return false;
    }
    if (!fwrite($fp2, trim($descodificar))) {
      $this->errors[] = sprintf(Tools::displayError('Impossible to write file on %s.'), $ruta . $nombre);
    }
    fclose($fp2);

    $fecha = date('d/m/y');
    $cortar = explode('?', $url_track);
    $url_seguimiento = $cortar[0];
    $enlace = $url_seguimiento . '?servicio=' . $codigo_envio . '&fecha=' . $fecha;
    Db::getInstance()->execute(
            'UPDATE ' . _DB_PREFIX_ . 'tipsa_envios '
            . 'SET codigo_envio = "' . $codigo_envio . '", '
            . 'url_track = "' . $enlace . '", '
            . 'num_albaran = "' . $num_albaran . '", '
            . 'codigo_barras = "' . $ruta . '", '
            . 'fecha = "' . date('Y-m-d H:i:s') . '" '
            . 'WHERE id_envio_order = "' . $id_order . '"');
    $tipsaCodigoAgencia = Configuration::get('TIPSA_CODIGO_AGENCIA');
    Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'orders '
            . 'SET shipping_number="' . $tipsaCodigoAgencia . $tipsaCodigoAgencia . $num_albaran . '" '
            . 'WHERE id_order = ' . $id_order);
    Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'order_carrier '
            . 'SET tracking_number="' . $tipsaCodigoAgencia . $tipsaCodigoAgencia . $num_albaran . '" '
            . 'WHERE id_order = ' . $id_order);

    return $ruta;
  }

}
