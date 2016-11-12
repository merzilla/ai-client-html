<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2013
 * @copyright Aimeos (aimeos.org), 2015-2016
 */


namespace Aimeos\Client\Html\Checkout\Standard\Summary\Option;


class StandardTest extends \PHPUnit_Framework_TestCase
{
	private $object;
	private $context;


	protected function setUp()
	{
		$this->context = \TestHelperHtml::getContext();

		$paths = \TestHelperHtml::getHtmlTemplatePaths();
		$this->object = new \Aimeos\Client\Html\Checkout\Standard\Summary\Option\Standard( $this->context, $paths );
		$this->object->setView( \TestHelperHtml::getView() );
	}


	protected function tearDown()
	{
		\Aimeos\Controller\Frontend\Basket\Factory::createController( $this->context )->clear();
		unset( $this->object );
	}


	public function testGetBody()
	{
		$controller = \Aimeos\Controller\Frontend\Basket\Factory::createController( $this->context );

		$view = \TestHelperHtml::getView();
		$view->standardBasket = $controller->get();
		$this->object->setView( $view );

		$output = $this->object->getBody();

		$this->assertContains( '<div class="checkout-standard-summary-option container">', $output );
		$this->assertContains( '<div class="checkout-standard-summary-option-account">', $output );
		$this->assertContains( '<div class="checkout-standard-summary-option-terms">', $output );
	}


	public function testGetSubClientInvalid()
	{
		$this->setExpectedException( '\\Aimeos\\Client\\Html\\Exception' );
		$this->object->getSubClient( 'invalid', 'invalid' );
	}


	public function testGetSubClientInvalidName()
	{
		$this->setExpectedException( '\\Aimeos\\Client\\Html\\Exception' );
		$this->object->getSubClient( '$$$', '$$$' );
	}


	public function testProcess()
	{
		$this->object->process();
		$this->assertEquals( null, $this->object->getView()->get( 'standardStepActive' ) );
	}


	public function testProcessOK()
	{
		$view = $this->object->getView();

		$param = array(
			'cs_option_terms' => '1',
			'cs_option_terms_value' => '1',
		);

		$helper = new \Aimeos\MW\View\Helper\Param\Standard( $view, $param );
		$view->addHelper( 'param', $helper );

		$this->object->process();
		$this->assertEquals( null, $view->get( 'standardStepActive' ) );
	}


	public function testProcessInvalid()
	{
		$view = $this->object->getView();

		$param = array(
			'cs_option_terms' => '1',
		);

		$helper = new \Aimeos\MW\View\Helper\Param\Standard( $view, $param );
		$view->addHelper( 'param', $helper );

		$this->object->process();
		$this->assertEquals( 'summary', $view->get( 'standardStepActive' ) );
		$this->assertEquals( true, $view->get( 'termsError' ) );
	}
}
