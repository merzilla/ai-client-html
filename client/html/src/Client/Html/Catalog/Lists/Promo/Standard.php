<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2013
 * @copyright Aimeos (aimeos.org), 2015-2022
 * @package Client
 * @subpackage Html
 */


namespace Aimeos\Client\Html\Catalog\Lists\Promo;


/**
 * Default implementation of catalog list item section for HTML clients.
 *
 * @package Client
 * @subpackage Html
 */
class Standard
	extends \Aimeos\Client\Html\Catalog\Base
	implements \Aimeos\Client\Html\Common\Client\Factory\Iface
{
	/** client/html/catalog/lists/promo/subparts
	 * List of HTML sub-clients rendered within the catalog list promo section
	 *
	 * The output of the frontend is composed of the code generated by the HTML
	 * clients. Each HTML client can consist of serveral (or none) sub-clients
	 * that are responsible for rendering certain sub-parts of the output. The
	 * sub-clients can contain HTML clients themselves and therefore a
	 * hierarchical tree of HTML clients is composed. Each HTML client creates
	 * the output that is placed inside the container of its parent.
	 *
	 * At first, always the HTML code generated by the parent is printed, then
	 * the HTML code of its sub-clients. The order of the HTML sub-clients
	 * determines the order of the output of these sub-clients inside the parent
	 * container. If the configured list of clients is
	 *
	 *  array( "subclient1", "subclient2" )
	 *
	 * you can easily change the order of the output by reordering the subparts:
	 *
	 *  client/html/<clients>/subparts = array( "subclient1", "subclient2" )
	 *
	 * You can also remove one or more parts if they shouldn't be rendered:
	 *
	 *  client/html/<clients>/subparts = array( "subclient1" )
	 *
	 * As the clients only generates structural HTML, the layout defined via CSS
	 * should support adding, removing or reordering content by a fluid like
	 * design.
	 *
	 * @param array List of sub-client names
	 * @since 2014.03
	 * @category Developer
	 */
	private $subPartPath = 'client/html/catalog/lists/promo/subparts';
	private $subPartNames = [];


	/**
	 * Returns the HTML code for insertion into the body.
	 *
	 * @param string $uid Unique identifier for the output if the content is placed more than once on the same page
	 * @return string HTML code
	 */
	public function body( string $uid = '' ) : string
	{
		$view = $this->view();

		$html = '';
		foreach( $this->getSubClients() as $subclient ) {
			$html .= $subclient->setView( $view )->body( $uid );
		}
		$view->promoBody = $html;

		/** client/html/catalog/lists/promo/template-body
		 * Relative path to the HTML body template of the catalog list promotion client.
		 *
		 * The template file contains the HTML code and processing instructions
		 * to generate the result shown in the body of the frontend. The
		 * configuration string is the path to the template file relative
		 * to the templates directory (usually in client/html/templates).
		 *
		 * You can overwrite the template file configuration in extensions and
		 * provide alternative templates. These alternative templates should be
		 * named like the default one but suffixed by
		 * an unique name. You may use the name of your project for this. If
		 * you've implemented an alternative client class as well, it
		 * should be suffixed by the name of the new class.
		 *
		 * It's also possible to create a specific template for each type, e.g.
		 * for the grid, list or whatever view you want to offer your users. In
		 * that case, you can configure the template by adding "-<type>" to the
		 * configuration key. To configure an alternative list view template for
		 * example, use the key
		 *
		 * client/html/catalog/lists/promo/template-body-list = catalog/lists/promo-body-list.php
		 *
		 * The argument is the relative path to the new template file. The type of
		 * the view is determined by the "l_type" parameter (allowed characters for
		 * the types are a-z and 0-9), which is also stored in the session so users
		 * will keep the view during their visit. The catalog list type subpart
		 * contains the template for switching between list types.
		 *
		 * @param string Relative path to the template creating code for the HTML page body
		 * @since 2014.03
		 * @category Developer
		 * @see client/html/catalog/lists/promo/template-header
		 * @see client/html/catalog/lists/type/template-body
		 */
		$tplconf = 'client/html/catalog/lists/promo/template-body';
		$default = 'catalog/lists/promo-body';

		return $view->render( $this->getTemplatePath( $tplconf, $default, 'aimeos/catalog/lists/type' ) );
	}


	/**
	 * Returns the HTML string for insertion into the header.
	 *
	 * @param string $uid Unique identifier for the output if the content is placed more than once on the same page
	 * @return string|null String including HTML tags for the header on error
	 */
	public function header( string $uid = '' ) : ?string
	{
		$view = $this->view();

		$html = '';
		foreach( $this->getSubClients() as $subclient ) {
			$html .= $subclient->setView( $view )->header( $uid );
		}
		$view->promoHeader = $html;

		/** client/html/catalog/lists/promo/template-header
		 * Relative path to the HTML header template of the catalog list promotion client.
		 *
		 * The template file contains the HTML code and processing instructions
		 * to generate the HTML code that is inserted into the HTML page header
		 * of the rendered page in the frontend. The configuration string is the
		 * path to the template file relative to the templates directory (usually
		 * in client/html/templates).
		 *
		 * You can overwrite the template file configuration in extensions and
		 * provide alternative templates. These alternative templates should be
		 * named like the default one but suffixed by
		 * an unique name. You may use the name of your project for this. If
		 * you've implemented an alternative client class as well, it
		 * should be suffixed by the name of the new class.
		 *
		 * It's also possible to create a specific template for each type, e.g.
		 * for the grid, list or whatever view you want to offer your users. In
		 * that case, you can configure the template by adding "-<type>" to the
		 * configuration key. To configure an alternative list view template for
		 * example, use the key
		 *
		 * client/html/catalog/lists/promo/template-header-list = catalog/lists/promo-header-list.php
		 *
		 * The argument is the relative path to the new template file. The type of
		 * the view is determined by the "l_type" parameter (allowed characters for
		 * the types are a-z and 0-9), which is also stored in the session so users
		 * will keep the view during their visit. The catalog list type subpart
		 * contains the template for switching between list types.
		 *
		 * @param string Relative path to the template creating code for the HTML page head
		 * @since 2014.03
		 * @category Developer
		 * @see client/html/catalog/lists/promo/template-body
		 * @see client/html/catalog/lists/type/template-body
		 */
		$tplconf = 'client/html/catalog/lists/promo/template-header';
		$default = 'catalog/lists/promo-header';

		return $view->render( $this->getTemplatePath( $tplconf, $default, 'aimeos/catalog/lists/type' ) );
	}


	/**
	 * Returns the sub-client given by its name.
	 *
	 * @param string $type Name of the client type
	 * @param string|null $name Name of the sub-client (Default if null)
	 * @return \Aimeos\Client\Html\Iface Sub-client object
	 */
	public function getSubClient( string $type, string $name = null ) : \Aimeos\Client\Html\Iface
	{
		/** client/html/catalog/lists/promo/decorators/excludes
		 * Excludes decorators added by the "common" option from the catalog list promo html client
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
		 *  client/html/catalog/lists/promo/decorators/excludes = array( 'decorator1' )
		 *
		 * This would remove the decorator named "decorator1" from the list of
		 * common decorators ("\Aimeos\Client\Html\Common\Decorator\*") added via
		 * "client/html/common/decorators/default" to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2015.08
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/catalog/lists/promo/decorators/global
		 * @see client/html/catalog/lists/promo/decorators/local
		 */

		/** client/html/catalog/lists/promo/decorators/global
		 * Adds a list of globally available decorators only to the catalog list promo html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap global decorators
		 * ("\Aimeos\Client\Html\Common\Decorator\*") around the html client.
		 *
		 *  client/html/catalog/lists/promo/decorators/global = array( 'decorator1' )
		 *
		 * This would add the decorator named "decorator1" defined by
		 * "\Aimeos\Client\Html\Common\Decorator\Decorator1" only to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2015.08
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/catalog/lists/promo/decorators/excludes
		 * @see client/html/catalog/lists/promo/decorators/local
		 */

		/** client/html/catalog/lists/promo/decorators/local
		 * Adds a list of local decorators only to the catalog list promo html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap local decorators
		 * ("\Aimeos\Client\Html\Catalog\Decorator\*") around the html client.
		 *
		 *  client/html/catalog/lists/promo/decorators/local = array( 'decorator2' )
		 *
		 * This would add the decorator named "decorator2" defined by
		 * "\Aimeos\Client\Html\Catalog\Decorator\Decorator2" only to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2015.08
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/catalog/lists/promo/decorators/excludes
		 * @see client/html/catalog/lists/promo/decorators/global
		 */

		return $this->createSubClient( 'catalog/lists/promo/' . $type, $name );
	}


	/**
	 * Returns the list of sub-client names configured for the client.
	 *
	 * @return array List of HTML client names
	 */
	protected function getSubClientNames() : array
	{
		return $this->context()->config()->get( $this->subPartPath, $this->subPartNames );
	}


	/**
	 * Sets the necessary parameter values in the view.
	 *
	 * @param \Aimeos\MW\View\Iface $view The view object which generates the HTML output
	 * @param array &$tags Result array for the list of tags that are associated to the output
	 * @param string|null &$expire Result variable for the expiration date of the output (null for no expiry)
	 * @return \Aimeos\MW\View\Iface Modified view object
	 */
	public function data( \Aimeos\MW\View\Iface $view, array &$tags = [], string &$expire = null ) : \Aimeos\MW\View\Iface
	{
		$context = $this->context();
		$config = $context->config();

		if( isset( $view->listCurrentCatItem ) ) {
			$catId = $view->listCurrentCatItem->getId();
		} else {
			$catId = $config->get( 'client/html/catalog/lists/catid-default', '' );
		}

		if( $catId )
		{
			/** client/html/catalog/lists/promo/size
			 * The maximum number of products that should be shown in the promotion section
			 *
			 * Each product list can render a list of promoted products on
			 * top if there are any products associated to that category whose
			 * list type is "promotion". This option limits the maximum number
			 * of products that are displayed. It takes only effect if more
			 * promotional products are added to this category than the set
			 * value.
			 *
			 * @param integer Number of promotion products
			 * @since 2014.03
			 * @category User
			 * @category Developer
			 */
			$size = $config->get( 'client/html/catalog/lists/promo/size', 6 );
			$domains = $config->get( 'client/html/catalog/lists/domains', ['media', 'price', 'text'] );
			$level = $config->get( 'client/html/catalog/lists/levels', \Aimeos\MW\Tree\Manager\Base::LEVEL_ONE );

			$products = \Aimeos\Controller\Frontend::create( $context, 'product' )
				->category( $catId, 'promotion', $level )
				->sort( 'relevance' )->slice( 0, $size )
				->uses( $domains )
				->search();

			$this->addMetaItems( $products, $expire, $tags );


			if( !empty( $products ) && (bool) $config->get( 'client/html/catalog/lists/stock/enable', true ) === true ) {
				$view->promoStockUrl = $this->getStockUrl( $view, $products );
			}

			$view->promoItems = $products;
		}

		return parent::data( $view, $tags, $expire );
	}
}
