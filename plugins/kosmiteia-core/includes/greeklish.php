<?php
defined( 'ABSPATH' ) || exit;

function kosmiteia_greeklish( $text ) {
	static $pairs = null;

	if ( null === $pairs ) {
		$pairs = array(
			'ου' => 'ou', 'ού' => 'ou',
			'αυ' => 'av', 'αύ' => 'av', 'ευ' => 'ev', 'εύ' => 'ev',
			'α' => 'a', 'ά' => 'a', 'β' => 'v', 'γ' => 'g', 'δ' => 'd',
			'ε' => 'e', 'έ' => 'e', 'ζ' => 'z', 'η' => 'i', 'ή' => 'i',
			'θ' => 'th', 'ι' => 'i', 'ί' => 'i', 'ϊ' => 'i', 'ΐ' => 'i',
			'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => 'x',
			'ο' => 'o', 'ό' => 'o', 'π' => 'p', 'ρ' => 'r', 'σ' => 's',
			'ς' => 's', 'τ' => 't', 'υ' => 'y', 'ύ' => 'y', 'ϋ' => 'y',
			'ΰ' => 'y', 'φ' => 'f', 'χ' => 'ch', 'ψ' => 'ps', 'ω' => 'o',
			'ώ' => 'o',
		);
	}

	if ( ! preg_match( '/[\x{0370}-\x{03FF}\x{1F00}-\x{1FFF}]/u', $text ) ) {
		return $text;
	}

	return strtr( mb_strtolower( $text, 'UTF-8' ), $pairs );
}

function kosmiteia_greeklish_title( $title, $raw_title = '', $context = 'save' ) {
	// Lookups keep the original slug, so existing Greek URLs still resolve.
	if ( 'query' === $context ) {
		return $title;
	}

	return kosmiteia_greeklish( $title );
}
add_filter( 'sanitize_title', 'kosmiteia_greeklish_title', 5, 3 );

function kosmiteia_greeklish_file_name( $filename ) {
	return kosmiteia_greeklish( $filename );
}
add_filter( 'sanitize_file_name', 'kosmiteia_greeklish_file_name', 5 );
