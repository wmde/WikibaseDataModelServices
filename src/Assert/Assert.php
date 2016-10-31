<?php

namespace Wikibase\DataModel\Services\Assert;

use Wikimedia\Assert\Assert as WikimediaAssert;
use Wikimedia\Assert\ParameterAssertionException;

/**
 * Provides functions to assure values meet certain preconditions relevant
 * in Wikibase.
 *
 * @see Wikimedia\Assert\Assert
 *
 * @since 3.7
 *
 * @license GPL-2.0+
 */
class Assert {

	/**
	 * @param string $value
	 * @param string $name
	 *
	 * @throws ParameterAssertionException if $value is not an allowable repository name.
	 */
	public static function parameterIsAllowableRepositoryName( $value, $name ) {
		if ( !self::isAllowableRepositoryName( $value ) ) {
			throw new ParameterAssertionException( $name, 'must be a string not including colons' );
		}
	}

	/**
	 * @param array $value
	 * @param string $name
	 *
	 * @throws ParameterAssertionException If an element of $value is not
	 *         an allowable repository name.
	 */
	public static function parameterKeyIsAllowableRepositoryName( $value, $name ) {
		WikimediaAssert::parameterType( 'array', $value, $name );
		// TODO: change to Assert::parameterKeyType when the new version of the library is released
		WikimediaAssert::parameterElementType( 'string', array_keys( $value ), "array_keys( $name )" );

		foreach ( array_keys( $value ) as $key ) {
			if ( !self::isAllowableRepositoryName( $key ) ) {
				throw new ParameterAssertionException(
					"array_keys( $name )",
					'must not contain strings including colons'
				);
			}
		}
	}

	/**
	 * @param string $value
	 * @return bool
	 */
	private static function isAllowableRepositoryName( $value ) {
		return is_string( $value ) && strpos( $value, ':' ) === false;
	}

}
