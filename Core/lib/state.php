<?php
/**
 * 2015-09-02
 * @return string
 */
function df_action_name() {return df_request_o()->getFullActionName();}

/**
 * 2015-09-20
 * @used-by df_is_admin()
 * @return \Magento\Framework\App\State
 */
function df_app_state() {return df_o('Magento\Framework\App\State');}

/**
 * @return \Magento\Framework\App\Action\Action|null
 */
function df_controller() {return df_state()->controller();}

/**
 * 2015-08-14
 * Мы не вправе кэшировать результат работы функции: ведь текущий магазин может меняться.
 * @return bool
 */
function df_is_admin() {
	/**
	 * 2015-09-20
	 * В отличие от Magento 1.x мы не можем использовать код
	 * Magento\Store\Model\Store::ADMIN_CODE === df_store($store)->getCode();
	 * потому что при нахождении в административной части
	 * он вернёт вовсе не административную витрину, а витрину, указанную в MAGE_RUN_CODE.
	 * Более того, @see df_store() учитывает параметры URL
	 * и даже при нахождении в административном интерфейсе
	 * может вернуть вовсе не административную витрину.
	 * Поэтому определяем нахождение в административном интерфейсе другим способом.
	 */
	return 'adminhtml' === df_app_state()->getAreaCode();
}

/**
 * https://mage2.ru/t/94
 * https://mage2.pro/t/59
 * @return bool
 */
function df_is_ajax() {static $r; return !is_null($r) ? $r : $r = df_request_o()->isXmlHttpRequest();}

/** @return bool */
function df_is_it_my_local_pc() {
	/** @var bool $result  */
	static $result;
	if (is_null($result)) {
		$result = df_bool(df_a($_SERVER, 'RM_DEVELOPER'));
	}
	return $result;
}

/**
 * @param string $key
 * @param mixed $value
 * @return void
 */
function df_register($key, $value) {df_registry_o()->register($key, $value);}

/**
 * @param string $key
 * @return mixed
 */
function df_registry($key) {return df_registry_o()->registry($key);}

/**
 * 2015-11-02
 * https://mage2.pro/t/95
 * @return \Magento\Framework\Registry
 */
function df_registry_o() {return df_o('Magento\Framework\Registry');}

/**
 * @param string $key
 * @param string|null $default [optional]
 * @return string
 */
function df_request($key, $default = null) {return df_request_o()->getParam($key, $default);}

/**
 * 2015-08-14
 * https://github.com/magento/magento2/issues/1675
 * @return \Magento\Framework\App\RequestInterface|\Magento\Framework\App\Request\Http
 */
function df_request_o() {return df_o('Magento\Framework\App\RequestInterface');}

/**
 * 2015-08-14
 * @return string
 */
function df_ruri() {static $r; return $r ? $r : $r = df_request_o()->getRequestUri();}

/**
 * 2015-08-14
 * @param string $needle
 * @return bool
 */
function df_ruri_contains($needle) {return df_contains(df_ruri(), $needle);}

/**
 * @return \Df\Core\State
 */
function df_state() {static $r; return $r ? $r : $r = \Df\Core\State::s();}
