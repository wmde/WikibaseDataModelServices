<?php

namespace Wikibase\DataModel\Services\Validation;

use ValueValidators\ValueValidator;

/**
 * UrlSchemeValidators is a collection of validators for some commonly used URL schemes.
 * This is intended for conveniently supplying a map of validators to UrlValidator.
 *
 * @license GPL 2+
 * @author Daniel Kinzler
 * @author Thiemo Mättig
 */
class UrlSchemeValidators {

	/**
	 * @var string
	 */
	private $linkUrlClass;

	/**
	 * @param string $linkUrlClass
	 */
	public function __construct( $linkUrlClass ) {
		$this->linkUrlClass = $linkUrlClass;
	}

	/**
	 * Returns a validator for the given URL scheme, or null if
	 * no validator is defined for that scheme.
	 *
	 * @todo 'bitcoin', 'geo', 'magnet', 'news', 'sip', 'sips', 'sms', 'tel', 'urn', 'xmpp'.
	 * @todo protocol relative '//'.
	 *
	 * @param string $scheme e.g. 'http'.
	 *
	 * @return ValueValidator|null
	 */
	public function getValidator( $scheme ) {
		switch ( $scheme ) {
			case 'ftp':
			case 'ftps':
			case 'git':
			case 'gopher':
			case 'http':
			case 'https':
			case 'irc':
			case 'ircs':
			case 'mms':
			case 'nntp':
			case 'redis':
			case 'sftp':
			case 'ssh':
			case 'svn':
			case 'telnet':
			case 'worldwind':
				$regex = '!^' . preg_quote( $scheme, '!' ) . '://(' . $this->linkUrlClass . ')+$!i';
				break;

			case 'mailto':
				$regex = '!^mailto:(' . $this->linkUrlClass . ')+@(' . $this->linkUrlClass . ')+$!i';
				break;

			case '*':
			case 'any':
				$regex = '!^([a-z][a-z\d+.-]*):(' . $this->linkUrlClass . ')+$!i';
				break;

			default:
				return null;
		}

		return new RegexValidator( $regex, false, 'bad-url' );
	}

	/**
	 * Given a list of schemes, this function returns a mapping for each supported
	 * scheme to a corresponding ValueValidator. If the schema isn't supported,
	 * no mapping is created for it.
	 *
	 * @param string[] $schemes a list of scheme names, e.g. 'http'.
	 *
	 * @return ValueValidator[] a map of scheme names to ValueValidator objects.
	 */
	public function getValidators( array $schemes ) {
		$validators = array();

		foreach ( $schemes as $scheme ) {
			$validator = $this->getValidator( $scheme );

			if ( $validator !== null ) {
				$validators[$scheme] = $validator;
			}
		}

		return $validators;
	}

}
