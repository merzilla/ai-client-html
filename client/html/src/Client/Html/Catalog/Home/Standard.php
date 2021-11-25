<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2020-2021
 * @package Client
 * @subpackage Html
 */


namespace Aimeos\Client\Html\Catalog\Home;

/**
 * Implementation of catalog home section HTML clients for a configurable list of homes.
 *
 * @package Client
 * @subpackage Html
 */
class Standard
	extends \Aimeos\Client\Html\Catalog\Base
	implements \Aimeos\Client\Html\Common\Client\Factory\Iface
{
	/** client/html/catalog/home/subparts
	 * List of HTML sub-clients rendered within the catalog home section
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
	 * @since 2020.10
	 * @category Developer
	 */
	private $subPartPath = 'client/html/catalog/home/subparts';
	private $subPartNames = [];

	private $tags = [];
	private $expire;
	private $view;


	/**
	 * Returns the HTML code for insertion into the body.
	 *
	 * @param string $uid Unique identifier for the output if the content is placed more than once on the same page
	 * @return string HTML code
	 */
	public function body( string $uid = '' ) : string
	{
		$context = $this->context();

		/** client/html/catalog/home/cache
		 * Enables or disables caching only for the catalog home component
		 *
		 * Disable caching for components can be useful if you would have too much
		 * entries to cache or if the component contains non-cacheable parts that
		 * can't be replaced using the modifyBody() and modifyHeader() methods.
		 *
		 * @param boolean True to enable caching, false to disable
		 * @category Developer
		 * @category User
		 * @see client/html/catalog/detail/cache
		 * @see client/html/catalog/filter/cache
		 * @see client/html/catalog/stage/cache
		 * @see client/html/catalog/list/cache
		 */

		/** client/html/catalog/home
		 * All parameters defined for the catalog home component and its subparts
		 *
		 * Please refer to the single settings for details.
		 *
		 * @param array Associative list of name/value settings
		 * @category Developer
		 * @see client/html/catalog#home
		 */
		$confkey = 'client/html/catalog/home';

		if( ( $html = $this->getCached( 'body', $uid, [], $confkey ) ) === null )
		{
			$view = $this->view();
			$config = $this->context()->getConfig();

			/** client/html/catalog/home/template-body
			 * Relative path to the HTML body template of the catalog home client.
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
			 * @since 2020.10
			 * @category Developer
			 * @see client/html/catalog/home/template-header
			 */
			$tplconf = 'client/html/catalog/home/template-body';
			$default = 'catalog/home/body-standard';

			try
			{
				$html = '';

				if( !isset( $this->view ) ) {
					$view = $this->view = $this->object()->data( $view, $this->tags, $this->expire );
				}

				foreach( $this->getSubClients() as $subclient ) {
					$html .= $subclient->setView( $view )->body( $uid );
				}
				$view->listBody = $html;

				$html = $view->render( $config->get( $tplconf, $default ) );
				$this->setCached( 'body', $uid, [], $confkey, $html, $this->tags, $this->expire );

				return $html;
			}
			catch( \Aimeos\Client\Html\Exception $e )
			{
				$error = array( $context->translate( 'client', $e->getMessage() ) );
				$view->homeErrorList = array_merge( $view->get( 'homeErrorList', [] ), $error );
			}
			catch( \Aimeos\Controller\Frontend\Exception $e )
			{
				$error = array( $context->translate( 'controller/frontend', $e->getMessage() ) );
				$view->homeErrorList = array_merge( $view->get( 'homeErrorList', [] ), $error );
			}
			catch( \Aimeos\MShop\Exception $e )
			{
				$error = array( $context->translate( 'mshop', $e->getMessage() ) );
				$view->homeErrorList = array_merge( $view->get( 'homeErrorList', [] ), $error );
			}
			catch( \Exception $e )
			{
				$error = array( $context->translate( 'client', 'A non-recoverable error occured' ) );
				$view->homeErrorList = array_merge( $view->get( 'homeErrorList', [] ), $error );
				$this->logException( $e );
			}

			$html = $view->render( $config->get( $tplconf, $default ) );
		}
		else
		{
			$html = $this->modifyBody( $html, $uid );
		}

		return $html;
	}


	/**
	 * Returns the HTML string for insertion into the header.
	 *
	 * @param string $uid Unique identifier for the output if the content is placed more than once on the same page
	 * @return string|null String including HTML tags for the header on error
	 */
	public function header( string $uid = '' ) : ?string
	{
		$confkey = 'client/html/catalog/home';

		if( ( $html = $this->getCached( 'header', $uid, [], $confkey ) ) === null )
		{
			$view = $this->view();
			$config = $this->context()->getConfig();

			/** client/html/catalog/home/template-header
			 * Relative path to the HTML header template of the catalog home client.
			 *
			 * The template file contains the HTML code and processing instructions
			 * to generate the HTML code that is inserted into the HTML page header
			 * of the rendered page in the frontend. The configuration string is the
			 * path to the template file relative to the templates directory (usually
			 * in client/html/templates).
			 *
			 * You can overwrite the template file configuration in extensions and
			 * provide alternative templates. These alternative templates should be
			 * named like the default one but with the string "standard" replaced by
			 * an unique name. You may use the name of your project for this. If
			 * you've implemented an alternative client class as well, "standard"
			 * should be replaced by the name of the new class.
			 *
			 * @param string Relative path to the template creating code for the HTML page head
			 * @since 2020.10
			 * @category Developer
			 * @see client/html/catalog/home/template-body
			 */
			$tplconf = 'client/html/catalog/home/template-header';
			$default = 'catalog/home/header-standard';

			try
			{
				$html = '';

				if( !isset( $this->view ) ) {
					$view = $this->view = $this->object()->data( $view, $this->tags, $this->expire );
				}

				foreach( $this->getSubClients() as $subclient ) {
					$html .= $subclient->setView( $view )->header( $uid );
				}
				$view->listHeader = $html;

				$html = $view->render( $config->get( $tplconf, $default ) );
				$this->setCached( 'header', $uid, [], $confkey, $html, $this->tags, $this->expire );

				return $html;
			}
			catch( \Exception $e )
			{
				$this->logException( $e );
			}
		}
		else
		{
			$html = $this->modifyHeader( $html, $uid );
		}

		return $html;
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
		/** client/html/catalog/home/decorators/excludes
		 * Excludes decorators added by the "common" option from the catalog home html client
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
		 *  client/html/catalog/home/decorators/excludes = array( 'decorator1' )
		 *
		 * This would remove the decorator named "decorator1" from the list of
		 * common decorators ("\Aimeos\Client\Html\Common\Decorator\*") added via
		 * "client/html/common/decorators/default" to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2020.10
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/catalog/home/decorators/global
		 * @see client/html/catalog/home/decorators/local
		 */

		/** client/html/catalog/home/decorators/global
		 * Adds a list of globally available decorators only to the catalog home html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap global decorators
		 * ("\Aimeos\Client\Html\Common\Decorator\*") around the html client.
		 *
		 *  client/html/catalog/home/decorators/global = array( 'decorator1' )
		 *
		 * This would add the decorator named "decorator1" defined by
		 * "\Aimeos\Client\Html\Common\Decorator\Decorator1" only to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2020.10
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/catalog/home/decorators/excludes
		 * @see client/html/catalog/home/decorators/local
		 */

		/** client/html/catalog/home/decorators/local
		 * Adds a list of local decorators only to the catalog home html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap local decorators
		 * ("\Aimeos\Client\Html\Catalog\Decorator\*") around the html client.
		 *
		 *  client/html/catalog/home/decorators/local = array( 'decorator2' )
		 *
		 * This would add the decorator named "decorator2" defined by
		 * "\Aimeos\Client\Html\Catalog\Decorator\Decorator2" only to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2020.10
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/catalog/home/decorators/excludes
		 * @see client/html/catalog/home/decorators/global
		 */

		return $this->createSubClient( 'catalog/home/' . $type, $name );
	}


	/**
	 * Processes the input, e.g. store given values.
	 *
	 * A view must be available and this method doesn't generate any output
	 * besides setting view variables if necessary.
	 */
	public function init()
	{
		$context = $this->context();
		$view = $this->view();

		try
		{
			parent::init();
		}
		catch( \Aimeos\Client\Html\Exception $e )
		{
			$error = array( $context->translate( 'client', $e->getMessage() ) );
			$view->homeErrorList = array_merge( $view->get( 'homeErrorList', [] ), $error );
		}
		catch( \Aimeos\Controller\Frontend\Exception $e )
		{
			$error = array( $context->translate( 'controller/frontend', $e->getMessage() ) );
			$view->homeErrorList = array_merge( $view->get( 'homeErrorList', [] ), $error );
		}
		catch( \Aimeos\MShop\Exception $e )
		{
			$error = array( $context->translate( 'mshop', $e->getMessage() ) );
			$view->homeErrorList = array_merge( $view->get( 'homeErrorList', [] ), $error );
		}
		catch( \Exception $e )
		{
			$error = array( $context->translate( 'client', 'A non-recoverable error occured' ) );
			$view->homeErrorList = array_merge( $view->get( 'homeErrorList', [] ), $error );
			$this->logException( $e );
		}
	}


	/**
	 * Returns the list of sub-client names configured for the client.
	 *
	 * @return array List of HTML client names
	 */
	protected function getSubClientNames() : array
	{
		return $this->context()->getConfig()->get( $this->subPartPath, $this->subPartNames );
	}


	/**
	 * Modifies the cached body content to replace content based on sessions or cookies.
	 *
	 * @param string $content Cached content
	 * @param string $uid Unique identifier for the output if the content is placed more than once on the same page
	 * @return string Modified body content
	 */
	public function modifyBody( string $content, string $uid ) : string
	{
		$content = parent::modifyBody( $content, $uid );

		return $this->replaceSection( $content, $this->view()->csrf()->formfield(), 'catalog.lists.items.csrf' );
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
		$config = $context->getConfig();

		/** client/html/catalog/home/domains
		 * A list of domain names whose items should be available in the catalog home view template
		 *
		 * The templates rendering home lists usually add the images, prices
		 * and texts associated to each home item. If you want to display additional
		 * content like the home attributes, you can configure your own list of
		 * domains (attribute, media, price, home, text, etc. are domains)
		 * whose items are fetched from the storage. Please keep in mind that
		 * the more domains you add to the configuration, the more time is required
		 * for fetching the content!
		 *
		 * This configuration option overwrites the "client/html/catalog/domains"
		 * option that allows to configure the domain names of the items fetched
		 * for all catalog related data.
		 *
		 * @param array List of domain names
		 * @since 2020.10
		 * @category Developer
		 * @see client/html/catalog/domains
		 * @see client/html/catalog/detail/domains
		 * @see client/html/catalog/stage/domains
		 * @see client/html/catalog/lists/domains
		 */
		$domains = ['media', 'media/property', 'price', 'text', 'product' => ['promotion']];
		$domains = $config->get( 'client/html/catalog/domains', $domains );
		$domains = $config->get( 'client/html/catalog/home/domains', $domains );

		/** client/html/catalog/home/basket-add
		 * Display the "add to basket" button for each product item in the catalog home component
		 *
		 * Enables the button for adding products to the basket for the listed products.
		 * This works for all type of products, even for selection products with product
		 * variants and product bundles. By default, also optional attributes are
		 * displayed if they have been associated to a product.
		 *
		 * @param boolean True to display the button, false to hide it
		 * @since 2020.10
		 * @category Developer
		 * @category User
		 * @see client/html/catalog/lists/basket-add
		 * @see client/html/catalog/detail/basket-add
		 * @see client/html/basket/related/basket-add
		 * @see client/html/catalog/product/basket-add
		 */
		if( $config->get( 'client/html/catalog/home/basket-add', false ) ) {
			$domains = array_merge_recursive( $domains, ['attribute' => ['variant', 'custom', 'config']] );
		}

		$tree = \Aimeos\Controller\Frontend::create( $context, 'catalog' )->uses( $domains )
			->getTree( \Aimeos\Controller\Frontend\Catalog\Iface::LIST );


		$articles = map();
		$products = $tree->getRefItems( 'product', null, 'promotion' );

		foreach( $tree->getChildren() as $child ) {
			$products->union( $child->getRefItems( 'product', null, 'promotion' ) );
		}

		if( $config->get( 'client/html/catalog/home/basket-add', false ) )
		{
			foreach( $products as $product )
			{
				if( $product->getType() === 'select' ) {
					$articles->union( $product->getRefItems( 'product', 'default', 'default' ) );
				}
			}
		}

		/** client/html/catalog/home/stock/enable
		 * Enables or disables displaying product stock levels in product list views
		 *
		 * This configuration option allows shop owners to display product
		 * stock levels for each product in list views or to disable
		 * fetching product stock information.
		 *
		 * The stock information is fetched via AJAX and inserted via Javascript.
		 * This allows to cache product items by leaving out such highly
		 * dynamic content like stock levels which changes with each order.
		 *
		 * @param boolean Value of "1" to display stock levels, "0" to disable displaying them
		 * @since 2020.10
		 * @category User
		 * @category Developer
		 * @see client/html/catalog/detail/stock/enable
		 * @see client/html/catalog/stock/url/target
		 * @see client/html/catalog/stock/url/controller
		 * @see client/html/catalog/stock/url/action
		 * @see client/html/catalog/stock/url/config
		 */
		if( !$products->isEmpty() && (bool) $config->get( 'client/html/catalog/home/stock/enable', true ) === true ) {
			$view->homeStockUrl = $this->getStockUrl( $view, $products->union( $articles ) );
		}

		// Delete cache when products are added or deleted even when in "tag-all" mode
		$this->addMetaItems( $tree, $expire, $tags, ['catalog', 'product'] );

		$view->homeTree = $tree;

		return parent::data( $view, $tags, $expire );
	}
}
