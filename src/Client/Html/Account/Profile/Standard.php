<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016-2025
 * @package Client
 * @subpackage Html
 */


namespace Aimeos\Client\Html\Account\Profile;


/**
 * Default implementation of account profile HTML client.
 *
 * @package Client
 * @subpackage Html
 */
class Standard
	extends \Aimeos\Client\Html\Common\Client\Factory\Base
	implements \Aimeos\Client\Html\Iface
{
	/** client/html/account/profile/name
	 * Class name of the used account profile client implementation
	 *
	 * Each default HTML client can be replace by an alternative imlementation.
	 * To use this implementation, you have to set the last part of the class
	 * name as configuration value so the client factory knows which class it
	 * has to instantiate.
	 *
	 * For example, if the name of the default class is
	 *
	 *  \Aimeos\Client\Html\Account\Profile\Standard
	 *
	 * and you want to replace it with your own version named
	 *
	 *  \Aimeos\Client\Html\Account\Profile\Myprofile
	 *
	 * then you have to set the this configuration option:
	 *
	 *  client/html/account/profile/name = Myprofile
	 *
	 * The value is the last part of your own class name and it's case sensitive,
	 * so take care that the configuration value is exactly named like the last
	 * part of the class name.
	 *
	 * The allowed characters of the class name are A-Z, a-z and 0-9. No other
	 * characters are possible! You should always start the last part of the class
	 * name with an upper case character and continue only with lower case characters
	 * or numbers. Avoid chamel case names like "MyProfile"!
	 *
	 * @param string Last part of the class name
	 * @since 2016.10
	 */


	/**
	 * Sets the necessary parameter values in the view.
	 *
	 * @param \Aimeos\Base\View\Iface $view The view object which generates the HTML output
	 * @param array &$tags Result array for the list of tags that are associated to the output
	 * @param string|null &$expire Result variable for the expiration date of the output (null for no expiry)
	 * @return \Aimeos\Base\View\Iface Modified view object
	 */
	public function data( \Aimeos\Base\View\Iface $view, array &$tags = [], ?string &$expire = null ) : \Aimeos\Base\View\Iface
	{
		$context = $this->context();
		$config = $context->config();

		/** client/html/common/address/salutations
		 * List of salutions the customers can select from in the HTML frontend
		 *
		 * The following salutations are available:
		 *
		 * * empty string for "unknown"
		 * * company
		 * * mr
		 * * ms
		 *
		 * You can modify the list of salutation codes and remove the ones
		 * which shouldn't be used or add new ones.
		 *
		 * @param array List of available salutation codes
		 * @since 2021.04
		 * @see client/html/account/profile/address/salutations
		 */
		$salutations = $config->get( 'client/html/common/address/salutations', ['', 'company', 'mr', 'ms'] );

		/** client/html/account/profile/domains
		 * A list of domain names whose items should be available in the account profile view template
		 *
		 * The templates rendering customer details can contain additional
		 * items. If you want to display additional content, you can configure
		 * your own list of domains (attribute, media, price, product, text,
		 * etc. are domains) whose items are fetched from the storage.
		 *
		 * @param array List of domain names
		 * @since 2016.10
		 */
		$domains = $config->get( 'client/html/account/profile/domains', ['customer/address'] );

		/** common/countries
		 * A list of ISO country codes which should be available in the checkout address step.
		 *
		 * If you want to ship your products to several countries or you need
		 * to know from which countries your customers are, you have to enable
		 * the country selection in the address page of the checkout process.
		 *
		 * @param array List of two letter ISO country codes
		 * @since 2023.04
		 */
		$countries = $view->config( 'common/countries', [] );

		/** common/states
		 * A list of ISO country codes which should be available in the checkout address step.
		 *
		 * For each country you can freely define a list of states or regions
		 * that can be used afterwards to calculate the final price for each
		 * delivery option.
		 *
		 * To define states or regions use something like this:
		 *
		 *  [
		 *		'US' => [
		 *			'CA' => 'California',
		 *			'NY' => 'New York',
		 *			// ...
		 *		'EU' => [
		 *			'W' => 'Western Europe',
		 *			'C' => 'Central Europe',
		 *			// ...
		 *		],
		 *	],
		 *
		 * @param array List of two letter ISO country codes
		 * @since 2023.04
		 */
		$states = $view->config( 'common/states', [] );

		$item = \Aimeos\Controller\Frontend::create( $context, 'customer' )->uses( $domains )->get();

		$localeManager = \Aimeos\MShop::create( $context, 'locale' );
		$languages = $localeManager->search( $localeManager->filter( true ) )
			->col( 'locale.languageid', 'locale.languageid' );

		$deliveries = [];
		$addr = $item->getPaymentAddress();

		if( !$addr->getLanguageId() ) {
			$addr->setLanguageId( $context->locale()->getLanguageId() );
		}

		$payment = $addr->toArray();
		$payment['string'] = $this->call( 'getAddressString', $view, $addr );

		foreach( $item->getAddressItems() as $pos => $address )
		{
			$delivery = $address->toArray();
			$delivery['string'] = $this->call( 'getAddressString', $view, $address );
			$deliveries[$pos] = $delivery;
		}

		$view->profileItem = $item;
		$view->addressPayment = $payment;
		$view->addressDelivery = $deliveries;
		$view->addressPaymentCss = $this->cssPayment();
		$view->addressDeliveryCss = $this->cssDelivery();
		$view->addressCountries = $countries;
		$view->addressLanguages = $languages;
		$view->addressSalutations = $salutations;
		$view->addressStates = $states;

		return parent::data( $view, $tags, $expire );
	}


	/**
	 * Processes the input, e.g. store given values.
	 *
	 * A view must be available and this method doesn't generate any output
	 * besides setting view variables if necessary.
	 */
	public function init()
	{
		$view = $this->view();

		if( !$view->param( 'address/save' ) && !$view->param( 'address/delete' ) ) {
			return;
		}

		$data = $view->param( 'address/payment', [] );
		$map = $view->param( 'address/delivery', [] );

		if( !empty( $data ) && ( $view->addressPaymentError = $this->checkFields( $data, 'payment' ) ) !== [] ) {
			throw new \Aimeos\Client\Html\Exception( sprintf( 'At least one payment address part is missing or invalid' ) );
		}

		$cntl = \Aimeos\Controller\Frontend::create( $this->context(), 'customer' );
		$addrItems = $cntl->uses( ['customer/address'] )->get()->getAddressItems();
		$cntl->add( $view->param( 'address/payment', [] ) );

		if( $pos = $view->param( 'address/delete' ) )
		{
			if( isset( $addrItems[$pos] ) ) {
				$cntl->deleteAddressItem( $addrItems[$pos] );
			}

			unset( $map[$pos] );
		}

		foreach( $map as $pos => $data )
		{
			if( ( $view->addressDeliveryError = $this->checkFields( $data, 'delivery' ) ) !== [] ) {
				throw new \Aimeos\Client\Html\Exception( sprintf( 'At least one delivery address part is missing or invalid' ) );
			}

			$addrItem = $addrItems->get( $pos ) ?: $cntl->createAddressItem();
			$cntl->addAddressItem( $addrItem->fromArray( $data ), $pos );
		}

		$cntl->store();
	}


	/**
	 * Checks the address fields for missing data and sanitizes the given parameter list.
	 *
	 * @param array $params Associative list of address keys (order.address.* or customer.address.*) and their values
	 * @return array List of missing field names
	 */
	protected function checkFields( array $params, string $type ) : array
	{
		$view = $this->view();
		$prefix = $type === 'payment' ? 'customer.' : 'customer.address.';

		$mandatory = $view->config( 'client/html/common/address/delivery/mandatory', [] );
		$optional = $view->config( 'client/html/common/address/delivery/optional', [] );
		$hidden = $view->config( 'client/html/common/address/delivery/hidden', [] );

		$allFields = array_flip( array_merge( $mandatory, $optional, $hidden ) );
		$invalid = $this->validateFields( $params, $allFields );
		$this->checkSalutation( $params, $mandatory );

		$msg = match( $type ) {
			'delivery' => $view->translate( 'client', 'Delivery address part "%1$s" is invalid' ),
			'payment' => $view->translate( 'client', 'Payment address part "%1$s" is invalid' ),
			default => $view->translate( 'client', 'Address part "%1$s" is invalid' )
		};

		foreach( $invalid as $key => $name ) {
			$invalid[$key] = sprintf( $msg, $name );
		}

		$msg = match( $type ) {
			'delivery' => $view->translate( 'client', 'Delivery address part "%1$s" is missing' ),
			'payment' => $view->translate( 'client', 'Payment address part "%1$s" is missing' ),
			default => $view->translate( 'client', 'Address part "%1$s" is missing' )
		};

		foreach( $mandatory as $key )
		{
			if( !isset( $params[$prefix . $key] ) || $params[$prefix . $key] == '' ) {
				$invalid[$key] = sprintf( $msg, $key );
			}
		}

		return $invalid;
	}


	/**
	 * Additional checks for the salutation
	 *
	 * @param array $params Associative list of address keys (order.address.*) and their values
	 * @param array &$mandatory List of mandatory field names
	 */
	protected function checkSalutation( array $params, array &$mandatory )
	{
		if( isset( $params['order.address.salutation'] )
				&& $params['order.address.salutation'] === 'company'
				&& in_array( 'company', $mandatory ) === false
		) {
			$mandatory[] = 'company';
		}
	}


	/**
	 * Returns the CSS classes for the delivery address fields
	 *
	 * @return array Associative list of CSS classes for the delivery address fields
	 */
	protected function cssDelivery() : array
	{
		$config = $this->context()->config();

		$mandatory = $config->get( 'client/html/common/address/delivery/mandatory', [] );
		$optional = $config->get( 'client/html/common/address/delivery/optional', [] );
		$hidden = $config->get( 'client/html/common/address/delivery/hidden', [] );

		$css = [];

		foreach( $mandatory as $name ) {
			$css[$name][] = 'mandatory';
		}

		foreach( $optional as $name ) {
			$css[$name][] = 'optional';
		}

		foreach( $hidden as $name ) {
			$css[$name][] = 'hidden';
		}

		return $css;
	}


	/**
	 * Returns the CSS classes for the payment address fields
	 *
	 * @return array Associative list of CSS classes for the payment address fields
	 */
	protected function cssPayment() : array
	{
		$config = $this->context()->config();

		$mandatory = $config->get( 'client/html/common/address/payment/mandatory', [] );
		$optional = $config->get( 'client/html/common/address/payment/optional', [] );
		$hidden = $config->get( 'client/html/common/address/payment/hidden', [] );

		$css = [];

		foreach( $mandatory as $name ) {
			$css[$name][] = 'mandatory';
		}

		foreach( $optional as $name ) {
			$css[$name][] = 'optional';
		}

		foreach( $hidden as $name ) {
			$css[$name][] = 'hidden';
		}

		return $css;
	}


	/**
	 * Returns the address as string
	 *
	 * @param \Aimeos\Base\View\Iface $view The view object which generates the HTML output
	 * @param \Aimeos\MShop\Common\Item\Address\Iface $addr Order address item
	 * @return string Address as string
	 */
	protected function getAddressString( \Aimeos\Base\View\Iface $view, \Aimeos\MShop\Common\Item\Address\Iface $addr )
	{
		return preg_replace( "/\n+/m", "\n", trim( sprintf(
			/// Address format with company (%1$s), salutation (%2$s), title (%3$s), first name (%4$s), last name (%5$s),
			/// address part one (%6$s, e.g street), address part two (%7$s, e.g house number), address part three (%8$s, e.g additional information),
			/// postal/zip code (%9$s), city (%10$s), state (%11$s), country (%12$s), language (%13$s),
			/// e-mail (%14$s), phone (%15$s), facsimile/telefax (%16$s), mobile (%17$s), web site (%18$s), vatid (%19$s)
			$view->translate( 'client', '%1$s
%2$s %3$s %4$s %5$s
%6$s %7$s
%8$s
%9$s %10$s
%11$s
%12$s
%13$s
%14$s
%15$s
%16$s
%17$s
%18$s
%19$s
'
			),
			$addr->getCompany(),
			$view->translate( 'mshop/code', (string) $addr->getSalutation() ),
			$addr->getTitle(),
			$addr->getFirstName(),
			$addr->getLastName(),
			$addr->getAddress1(),
			$addr->getAddress2(),
			$addr->getAddress3(),
			$addr->getPostal(),
			$addr->getCity(),
			$addr->getState(),
			$view->translate( 'country', (string) $addr->getCountryId() ),
			$view->translate( 'language', (string) $addr->getLanguageId() ),
			$addr->getEmail(),
			$addr->getTelephone(),
			$addr->getTelefax(),
			$addr->getMobile(),
			$addr->getWebsite(),
			$addr->getVatID()
		) ) );
	}


	/**
	 * Validate the address key/value pairs using regular expressions
	 *
	 * @param array &$params Associative list of address keys (order.address.*) and their values
	 * @param array $fields List of field names to validate
	 * @return array List of invalid address keys
	 */
	protected function validateFields( array $params, array $fields ) : array
	{
		$invalid = [];
		$config = $this->context()->config();

		foreach( $params as $key => $value )
		{
			$name = ( $pos = strrpos( $key, '.' ) ) ? substr( $key, $pos + 1 ) : $key;

			if( isset( $fields[$name] ) )
			{
				$regex = $config->get( 'client/html/common/address/validate/' . $name );

				if( $regex && preg_match( '/' . $regex . '/', $value ) !== 1 ) {
					$invalid[$name] = $name;
				}
			}
		}

		return $invalid;
	}


	/** client/html/account/profile/template-body
	 * Relative path to the HTML body template of the account profile client.
	 *
	 * The template file contains the HTML code and processing instructions
	 * to generate the result shown in the body of the frontend. The
	 * configuration string is the path to the template file relative
	 * to the templates directory (usually in templates/client/html).
	 *
	 * You can overwrite the template file configuration in extensions and
	 * provide alternative templates. These alternative templates should be
	 * named like the default one but suffixed by
	 * an unique name. You may use the name of your project for this. If
	 * you've implemented an alternative client class as well, it
	 * should be suffixed by the name of the new class.
	 *
	 * @param string Relative path to the template creating code for the HTML page body
	 * @since 2016.10
	 * @see client/html/account/profile/template-header
	 */

	/** client/html/account/profile/template-header
	 * Relative path to the HTML header template of the account profile client.
	 *
	 * The template file contains the HTML code and processing instructions
	 * to generate the HTML code that is inserted into the HTML page header
	 * of the rendered page in the frontend. The configuration string is the
	 * path to the template file relative to the templates directory (usually
	 * in templates/client/html).
	 *
	 * You can overwrite the template file configuration in extensions and
	 * provide alternative templates. These alternative templates should be
	 * named like the default one but suffixed by
	 * an unique name. You may use the name of your project for this. If
	 * you've implemented an alternative client class as well, it
	 * should be suffixed by the name of the new class.
	 *
	 * @param string Relative path to the template creating code for the HTML page head
	 * @since 2016.10
	 * @see client/html/account/profile/template-body
	 */

	/** client/html/account/profile/decorators/excludes
	 * Excludes decorators added by the "common" option from the account profile html client
	 *
	 * Decorators extend the functionality of a class by adding new aspects
	 * (e.g. log what is currently done), executing the methods of the underlying
	 * class only in certain conditions (e.g. only for logged in users) or
	 * modify what is returned to the caller.
	 *
	 * This option allows you to remove a decorator added via
	 * "client/html/common/decorators/default" before they are wrapped
	 * around the html client.
	 *
	 *  client/html/account/profile/decorators/excludes = array( 'decorator1' )
	 *
	 * This would remove the decorator named "decorator1" from the list of
	 * common decorators ("\Aimeos\Client\Html\Common\Decorator\*") added via
	 * "client/html/common/decorators/default" to the html client.
	 *
	 * @param array List of decorator names
	 * @see client/html/common/decorators/default
	 * @see client/html/account/profile/decorators/global
	 * @see client/html/account/profile/decorators/local
	 */

	/** client/html/account/profile/decorators/global
	 * Adds a list of globally available decorators only to the account profile html client
	 *
	 * Decorators extend the functionality of a class by adding new aspects
	 * (e.g. log what is currently done), executing the methods of the underlying
	 * class only in certain conditions (e.g. only for logged in users) or
	 * modify what is returned to the caller.
	 *
	 * This option allows you to wrap global decorators
	 * ("\Aimeos\Client\Html\Common\Decorator\*") around the html client.
	 *
	 *  client/html/account/profile/decorators/global = array( 'decorator1' )
	 *
	 * This would add the decorator named "decorator1" defined by
	 * "\Aimeos\Client\Html\Common\Decorator\Decorator1" only to the html client.
	 *
	 * @param array List of decorator names
	 * @see client/html/common/decorators/default
	 * @see client/html/account/profile/decorators/excludes
	 * @see client/html/account/profile/decorators/local
	 */

	/** client/html/account/profile/decorators/local
	 * Adds a list of local decorators only to the account profile html client
	 *
	 * Decorators extend the functionality of a class by adding new aspects
	 * (e.g. log what is currently done), executing the methods of the underlying
	 * class only in certain conditions (e.g. only for logged in users) or
	 * modify what is returned to the caller.
	 *
	 * This option allows you to wrap local decorators
	 * ("\Aimeos\Client\Html\Account\Decorator\*") around the html client.
	 *
	 *  client/html/account/profile/decorators/local = array( 'decorator2' )
	 *
	 * This would add the decorator named "decorator2" defined by
	 * "\Aimeos\Client\Html\Account\Decorator\Decorator2" only to the html client.
	 *
	 * @param array List of decorator names
	 * @see client/html/common/decorators/default
	 * @see client/html/account/profile/decorators/excludes
	 * @see client/html/account/profile/decorators/global
	 */
}
