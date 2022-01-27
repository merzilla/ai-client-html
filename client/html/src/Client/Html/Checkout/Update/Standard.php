<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2013
 * @copyright Aimeos (aimeos.org), 2015-2022
 * @package Client
 * @subpackage Html
 */


namespace Aimeos\Client\Html\Checkout\Update;


/**
 * Default implementation of update checkout HTML client.
 *
 * @package Client
 * @subpackage Html
 */
class Standard
	extends \Aimeos\Client\Html\Common\Client\Factory\Base
	implements \Aimeos\Client\Html\Common\Client\Factory\Iface
{
	/** client/html/checkout/update/subparts
	 * List of HTML sub-clients rendered within the checkout update section
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
	private $subPartPath = 'client/html/checkout/update/subparts';
	private $subPartNames = [];
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
		$view = $this->view();

		try
		{
			$view = $this->view = $this->view ?? $this->object()->data( $view );

			$html = '';
			foreach( $this->getSubClients() as $subclient ) {
				$html .= $subclient->setView( $view )->body( $uid );
			}
			$view->updateBody = $html;
		}
		catch( \Aimeos\Client\Html\Exception $e )
		{
			$error = array( $context->translate( 'client', $e->getMessage() ) );
			$view->updateErrorList = array_merge( $view->get( 'updateErrorList', [] ), $error );
		}
		catch( \Aimeos\Controller\Frontend\Exception $e )
		{
			$error = array( $context->translate( 'controller/frontend', $e->getMessage() ) );
			$view->updateErrorList = array_merge( $view->get( 'updateErrorList', [] ), $error );
		}
		catch( \Aimeos\MShop\Exception $e )
		{
			$error = array( $context->translate( 'mshop', $e->getMessage() ) );
			$view->updateErrorList = array_merge( $view->get( 'updateErrorList', [] ), $error );
		}
		catch( \Exception $e )
		{
			$error = array( $context->translate( 'client', 'A non-recoverable error occured' ) );
			$view->updateErrorList = array_merge( $view->get( 'updateErrorList', [] ), $error );
			$this->logException( $e );
		}

		/** client/html/checkout/update/template-body
		 * Relative path to the HTML body template of the checkout update client.
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
		 * @param string Relative path to the template creating code for the HTML page body
		 * @since 2014.03
		 * @category Developer
		 * @see client/html/checkout/update/template-header
		 */
		$tplconf = 'client/html/checkout/update/template-body';
		$default = 'checkout/update/body';

		return $view->render( $view->config( $tplconf, $default ) );
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

		try
		{
			$view = $this->view = $this->view ?? $this->object()->data( $view );

			$html = '';
			foreach( $this->getSubClients() as $subclient ) {
				$html .= $subclient->setView( $view )->header( $uid );
			}
			$view->updateHeader = $html;

			/** client/html/checkout/update/template-header
			 * Relative path to the HTML header template of the checkout update client.
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
			 * @param string Relative path to the template creating code for the HTML page head
			 * @since 2014.03
			 * @category Developer
			 * @see client/html/checkout/update/template-body
			 */
			$tplconf = 'client/html/checkout/update/template-header';
			$default = 'checkout/update/header';

			return $view->render( $view->config( $tplconf, $default ) );
		}
		catch( \Exception $e )
		{
			$this->logException( $e );
		}

		return null;
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
		/** client/html/checkout/update/decorators/excludes
		 * Excludes decorators added by the "common" option from the checkout update html client
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
		 *  client/html/checkout/update/decorators/excludes = array( 'decorator1' )
		 *
		 * This would remove the decorator named "decorator1" from the list of
		 * common decorators ("\Aimeos\Client\Html\Common\Decorator\*") added via
		 * "client/html/common/decorators/default" to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2014.05
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/checkout/update/decorators/global
		 * @see client/html/checkout/update/decorators/local
		 */

		/** client/html/checkout/update/decorators/global
		 * Adds a list of globally available decorators only to the checkout update html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap global decorators
		 * ("\Aimeos\Client\Html\Common\Decorator\*") around the html client.
		 *
		 *  client/html/checkout/update/decorators/global = array( 'decorator1' )
		 *
		 * This would add the decorator named "decorator1" defined by
		 * "\Aimeos\Client\Html\Common\Decorator\Decorator1" only to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2014.05
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/checkout/update/decorators/excludes
		 * @see client/html/checkout/update/decorators/local
		 */

		/** client/html/checkout/update/decorators/local
		 * Adds a list of local decorators only to the checkout update html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap local decorators
		 * ("\Aimeos\Client\Html\Checkout\Decorator\*") around the html client.
		 *
		 *  client/html/checkout/update/decorators/local = array( 'decorator2' )
		 *
		 * This would add the decorator named "decorator2" defined by
		 * "\Aimeos\Client\Html\Checkout\Decorator\Decorator2" only to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2014.05
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/checkout/update/decorators/excludes
		 * @see client/html/checkout/update/decorators/global
		 */

		return $this->createSubClient( 'checkout/update/' . $type, $name );
	}


	/**
	 * Processes the input, e.g. store given values.
	 *
	 * A view must be available and this method doesn't generate any output
	 * besides setting view variables.
	 */
	public function init()
	{
		$view = $this->view();
		$context = $this->context();

		try
		{
			$cntl = \Aimeos\Controller\Frontend::create( $context, 'service' );
			$cntl->updatePush( $view->request(), $view->response(), $view->param( 'code', '' ) );

			parent::init();
		}
		catch( \Exception $e )
		{
			$view->response()->withStatus( 500, 'Error updating order status' );
			$view->response()->getBody()->write( $e->getMessage() );

			$body = (string) $view->request()->getBody();
			$str = "Updating order status failed: %1\$s\n%2\$s\n%3\$s";
			$msg = sprintf( $str, $e->getMessage(), print_r( $view->param(), true ), $body );
			$context->logger()->error( $msg, 'client/html' );
		}
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
	 * Returns the URL to the confirm page.
	 *
	 * @param \Aimeos\MW\View\Iface $view View object
	 * @param array $params Parameters that should be part of the URL
	 * @param array $config Default URL configuration
	 * @return string URL string
	 */
	protected function getUrlConfirm( \Aimeos\MW\View\Iface $view, array $params, array $config ) : string
	{
		$target = $view->config( 'client/html/checkout/confirm/url/target' );
		$cntl = $view->config( 'client/html/checkout/confirm/url/controller', 'checkout' );
		$action = $view->config( 'client/html/checkout/confirm/url/action', 'confirm' );
		$config = $view->config( 'client/html/checkout/confirm/url/config', $config );

		return $view->url( $target, $cntl, $action, $params, [], $config );
	}


	/**
	 * Returns the URL to the update page.
	 *
	 * @param \Aimeos\MW\View\Iface $view View object
	 * @param array $params Parameters that should be part of the URL
	 * @param array $config Default URL configuration
	 * @return string URL string
	 */
	protected function getUrlUpdate( \Aimeos\MW\View\Iface $view, array $params, array $config ) : string
	{
		$target = $view->config( 'client/html/checkout/update/url/target' );
		$cntl = $view->config( 'client/html/checkout/update/url/controller', 'checkout' );
		$action = $view->config( 'client/html/checkout/update/url/action', 'update' );
		$config = $view->config( 'client/html/checkout/update/url/config', $config );

		return $view->url( $target, $cntl, $action, $params, [], $config );
	}
}
