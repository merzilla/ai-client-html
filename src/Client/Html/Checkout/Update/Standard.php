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
	/** client/html/checkout/update/name
	 * Class name of the used checkout update client implementation
	 *
	 * Each default HTML client can be replace by an alternative imlementation.
	 * To use this implementation, you have to set the last part of the class
	 * name as configuration value so the client factory knows which class it
	 * has to instantiate.
	 *
	 * For example, if the name of the default class is
	 *
	 *  \Aimeos\Client\Html\Checkout\Update\Standard
	 *
	 * and you want to replace it with your own version named
	 *
	 *  \Aimeos\Client\Html\Checkout\Update\Myupdate
	 *
	 * then you have to set the this configuration option:
	 *
	 *  client/html/checkout/update/name = Myupdate
	 *
	 * The value is the last part of your own class name and it's case sensitive,
	 * so take care that the configuration value is exactly named like the last
	 * part of the class name.
	 *
	 * The allowed characters of the class name are A-Z, a-z and 0-9. No other
	 * characters are possible! You should always start the last part of the class
	 * name with an upper case character and continue only with lower case characters
	 * or numbers. Avoid chamel case names like "MyUpdate"!
	 *
	 * @param string Last part of the class name
	 * @since 2014.03
	 */


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
	 * @see client/html/checkout/update/template-header
	 */

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
	 * @see client/html/checkout/update/template-body
	 */
}
