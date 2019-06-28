<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2019
 * @package Client
 * @subpackage Html
 */


namespace Aimeos\Client\Html\Account\Profile\Address;


/**
 * Default implementation of acount profile address HTML client.
 *
 * @package Client
 * @subpackage Html
 */
class Standard
	extends \Aimeos\Client\Html\Common\Client\Summary\Base
	implements \Aimeos\Client\Html\Common\Client\Factory\Iface
{
	/** client/html/account/profile/address/standard/subparts
	 * List of HTML sub-clients rendered within the account profile address section
	 *
	 * The output of the frontend is composed of the code generated by the HTML
	 * clients. Each HTML client can consist of serveral (or none) sub-clients
	 * that are responsible for rendering certain sub-parts of the output. The
	 * sub-clients can contain HTML clients themselves and therefore a
	 * hierarchical tree of HTML clients is composed. Each HTML client creates
	 * the output that is placed inside the container of its parent.
	 *
	 * At first, always the HTML code generated by the parent is printed, then
	 * the HTML code of its sub-clients. The address of the HTML sub-clients
	 * determines the address of the output of these sub-clients inside the parent
	 * container. If the configured list of clients is
	 *
	 *  array( "subclient1", "subclient2" )
	 *
	 * you can easily change the address of the output by readdressing the subparts:
	 *
	 *  client/html/<clients>/subparts = array( "subclient1", "subclient2" )
	 *
	 * You can also remove one or more parts if they shouldn't be rendered:
	 *
	 *  client/html/<clients>/subparts = array( "subclient1" )
	 *
	 * As the clients only generates structural HTML, the layout defined via CSS
	 * should support adding, removing or readdressing content by a fluid like
	 * design.
	 *
	 * @param array List of sub-client names
	 * @since 2019.07
	 * @category Developer
	 */
	private $subPartPath = 'client/html/account/profile/address/standard/subparts';
	private $subPartNames = [];


	/**
	 * Returns the HTML code for insertion into the body.
	 *
	 * @param string $uid Unique identifier for the output if the content is placed more than once on the same page
	 * @return string HTML code
	 */
	public function getBody( $uid = '' )
	{
		$view = $this->getView();

		$html = '';
		foreach( $this->getSubClients() as $subclient ) {
			$html .= $subclient->setView( $view )->getBody( $uid );
		}
		$view->addressBody = $html;

		/** client/html/account/profile/address/standard/template-body
		 * Relative path to the HTML body template of the account profile address client.
		 *
		 * The template file contains the HTML code and processing instructions
		 * to generate the result shown in the body of the frontend. The
		 * configuration string is the path to the template file relative
		 * to the templates directory (usually in client/html/templates).
		 *
		 * You can overwrite the template file configuration in extensions and
		 * provide alternative templates. These alternative templates should be
		 * named like the default one but with the string "standard" replaced by
		 * an unique name. You may use the name of your project for this. If
		 * you've implemented an alternative client class as well, "standard"
		 * should be replaced by the name of the new class.
		 *
		 * @param string Relative path to the template creating code for the HTML page body
		 * @since 2019.07
		 * @category Developer
		 * @see client/html/account/profile/address/standard/template-header
		 */
		$tplconf = 'client/html/account/profile/address/standard/template-body';
		$default = 'account/profile/address-body-standard';

		return $view->render( $view->config( $tplconf, $default ) );
	}


	/**
	 * Returns the sub-client given by its name.
	 *
	 * @param string $type Name of the client type
	 * @param string|null $name Name of the sub-client (Default if null)
	 * @return \Aimeos\Client\Html\Iface Sub-client object
	 */
	public function getSubClient( $type, $name = null )
	{
		/** client/html/account/profile/address/decorators/excludes
		 * Excludes decorators added by the "common" option from the account profile address html client
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
		 *  client/html/account/profile/address/decorators/excludes = array( 'decorator1' )
		 *
		 * This would remove the decorator named "decorator1" from the list of
		 * common decorators ("\Aimeos\Client\Html\Common\Decorator\*") added via
		 * "client/html/common/decorators/default" to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2019.07
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/account/profile/address/decorators/global
		 * @see client/html/account/profile/address/decorators/local
		 */

		/** client/html/account/profile/address/decorators/global
		 * Adds a list of globally available decorators only to the account profile address html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap global decorators
		 * ("\Aimeos\Client\Html\Common\Decorator\*") around the html client.
		 *
		 *  client/html/account/profile/address/decorators/global = array( 'decorator1' )
		 *
		 * This would add the decorator named "decorator1" defined by
		 * "\Aimeos\Client\Html\Common\Decorator\Decorator1" only to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2019.07
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/account/profile/address/decorators/excludes
		 * @see client/html/account/profile/address/decorators/local
		 */

		/** client/html/account/profile/address/decorators/local
		 * Adds a list of local decorators only to the account profile address html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap local decorators
		 * ("\Aimeos\Client\Html\Account\Decorator\*") around the html client.
		 *
		 *  client/html/account/profile/address/decorators/local = array( 'decorator2' )
		 *
		 * This would add the decorator named "decorator2" defined by
		 * "\Aimeos\Client\Html\Account\Decorator\Decorator2" only to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2019.07
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/account/profile/address/decorators/excludes
		 * @see client/html/account/profile/address/decorators/global
		 */

		return $this->createSubClient( 'account/profile/address/' . $type, $name );
	}


	/**
	 * Processes the input, e.g. store given values.
	 * A view must be available and this method doesn't generate any output
	 * besides setting view variables.
	 */
	public function process()
	{
		$view = $this->getView();
		$cntl = \Aimeos\Controller\Frontend::create( $this->getContext(), 'customer' );

		$addrItems = $cntl->uses( ['customer/address'] )->get()->getAddressItems();
		$cntl->add( $view->param( 'address/payment', [] ) );
		$map = [];

		foreach( $view->param( 'address/delivery/customer.address.id', [] ) as $pos => $id )
		{
			foreach( $view->param( 'address/delivery', [] ) as $key => $list )
			{
				if( isset( $list[$pos] ) ) {
					$map[$pos][$key] = $list[$pos];
				}
			}
		}

		foreach( $map as $pos => $data )
		{
			if( !isset( $addrItems[$pos] ) ) {
				$addrItem = $cntl->createAddressItem()->fromArray( $data );
			} else {
				$addrItem = $addrItems[$pos]->fromArray( $data );
			}

			$cntl->addAddressItem( $addrItem, $pos );
			unset( $addrItems[$pos] );
		}

		foreach( $addrItems as $addrItem ) {
			$cntl->deleteAddressItem( $addrItem );
		}

		$cntl->store();

		parent::process();
	}


	/**
	 * Returns the list of sub-client names configured for the client.
	 *
	 * @return array List of HTML client names
	 */
	protected function getSubClientNames()
	{
		return $this->getContext()->getConfig()->get( $this->subPartPath, $this->subPartNames );
	}


	/**
	 * Sets the necessary parameter values in the view.
	 *
	 * @param \Aimeos\MW\View\Iface $view The view object which generates the HTML output
	 * @param array &$tags Result array for the list of tags that are associated to the output
	 * @param string|null &$expire Result variable for the expiration date of the output (null for no expiry)
	 * @return \Aimeos\MW\View\Iface Modified view object
	 */
	public function addData( \Aimeos\MW\View\Iface $view, array &$tags = [], &$expire = null )
	{
		$context = $this->getContext();
		$cntl = \Aimeos\Controller\Frontend::create( $context, 'customer' );

		$localeManager = \Aimeos\MShop::create( $context, 'locale' );
		$locales = $localeManager->searchItems( $localeManager->createSearch( true ) );

		$languages = [];
		foreach( $locales as $locale ) {
			$languages[$locale->getLanguageId()] = $locale->getLanguageId();
		}

		$view->addressCustomer = $cntl->uses( ['customer/address'] )->get();
		$view->addressCountries = $view->config( 'client/html/checkout/standard/address/countries', [] );
		$view->addressStates = $view->config( 'client/html/checkout/standard/address/states', [] );
		$view->addressSalutations = array( 'company', 'mr', 'mrs' );
		$view->addressLanguages = $languages;

		return parent::addData( $view, $tags, $expire );
	}
}
