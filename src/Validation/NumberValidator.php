<?php

namespace Wikibase\DataModel\Services\Validation;

use ValueValidators\Error;
use ValueValidators\Result;
use ValueValidators\ValueValidator;

/**
 * @license GPL 2+
 * @author Katie Filbert < aude.wiki@gmail.com >
 */
class NumberValidator implements ValueValidator {

	/**
	 * @see ValueValidator::validate()
	 *
	 * @param mixed $value The value to validate
	 *
	 * @return Result
	 */
	public function validate( $value ) {
		$isValid = is_int( $value ) || is_float( $value );

		if ( $isValid ) {
			return Result::newSuccess();
		}

		return Result::newError( array(
			Error::newError(
				'Bad type, expected an integer or float value',
				null,
				'bad-type',
				array( 'number', gettype( $value ) )
			)
		) );
	}

	/**
	 * @see ValueValidator::setOptions()
	 *
	 * @param array $options
	 */
	public function setOptions( array $options ) {
		// Do nothing. This method shouldn't even be in the interface.
	}

}
