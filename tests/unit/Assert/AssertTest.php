<?php

namespace Wikibase\DataModel\Services\Tests\Assert;

use Wikibase\DataModel\Services\Assert\Assert;
use Wikimedia\Assert\ParameterAssertionException;

/**
 * @covers Wikibase\DataModel\Services\Assert\Assert
 *
 * @license GPL-2.0+
 */
class AssertTest extends \PHPUnit_Framework_TestCase {

	public function provideNotAllowableRepositoryNames() {
		return array(
			array( 'fo:o' ),
			array( 'foo:' ),
			array( ':foo' ),
			array( ':' ),
			array( 123 ),
			array( null ),
			array( false ),
			array( array( 'foo' ) ),
		);
	}

	/**
	 * @dataProvider provideNotAllowableRepositoryNames
	 */
	public function testGivenInvalidValue_parameterIsAllowableRepositoryNameFails( $value ) {
		$this->setExpectedException( ParameterAssertionException::class );
		Assert::parameterIsAllowableRepositoryName( $value, 'test' );
	}

	public function provideAllowableRepositoryNames() {
		return array(
			array( '' ),
			array( 'foo' ),
			array( '123' ),
		);
	}

	/**
	 * @dataProvider provideAllowableRepositoryNames
	 */
	public function testGivenValidValue_parameterIsAllowableRepositoryNamePasses( $value ) {
		Assert::parameterIsAllowableRepositoryName( $value, 'test' );
	}

	public function provideInvalidRepositoryNameIndexedArrays() {
		return array(
			array( 'foo' ),
			array( array( 0 => 'foo' ) ),
			array( array( 'fo:0' => 'bar' ) ),
			array( array( 'foo:' => 'bar' ) ),
			array( array( ':foo' => 'bar' ) ),
		);
	}

	/**
	 * @dataProvider provideInvalidRepositoryNameIndexedArrays
	 */
	public function testGivenInvalidValue_parameterKeyIsAllowableRepositoryNameFails( $value ) {
		$this->setExpectedException( ParameterAssertionException::class );
		Assert::parameterKeyIsAllowableRepositoryName( $value, 'test' );
	}

	public function provideValidRepositoryNameIndexedArrays() {
		return array(
			array( array( 'foo' => 'bar' ) ),
			array( array( '' => 'bar' ) ),
			array( array( '' => 'bar', 'foo' => 'baz' ) ),
			array( array() ),
		);
	}

	/**
	 * @dataProvider provideValidRepositoryNameIndexedArrays
	 */
	public function testGivenValidValue_parameterKeyIsAllowableRepositoryNamePasses( $value ) {
		Assert::parameterKeyIsAllowableRepositoryName( $value, 'test' );
	}

}
