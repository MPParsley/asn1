<?php

namespace ASN1\Component;

use ASN1\Decodable;
use ASN1\Encodable;
use ASN1\Exception\DecodeException;


/**
 * Class to represent BER/DER identifier octets
 */
class Identifier implements Decodable, Encodable
{
	/**
	 * Type class enumerations
	 *
	 * @var int
	 */
	const CLASS_UNIVERSAL = 0b00;
	const CLASS_APPLICATION = 0b01;
	const CLASS_CONTEXT_SPECIFIC = 0b10;
	const CLASS_PRIVATE = 0b11;
	
	/**
	 * Human readable type classes
	 * 
	 * @var array
	 */
	private static $_classNames = array(
		self::CLASS_UNIVERSAL => "UNIVERSAL",
		self::CLASS_APPLICATION => "APPLICATION",
		self::CLASS_CONTEXT_SPECIFIC => "CONTEXT SPECIFIC",
		self::CLASS_PRIVATE => "PRIVATE",
	);
	
	/**
	 * P/C enumerations
	 *
	 * @var int
	 */
	const PRIMITIVE = 0b0;
	const CONSTRUCTED = 0b1;
	
	/**
	 * Type class
	 *
	 * @var int
	 */
	private $_class;
	
	/**
	 * Primitive or Constructed
	 *
	 * @var int
	 */
	private $_pc;
	
	/**
	 * Content type
	 *
	 * @var int
	 */
	private $_tag;
	
	/**
	 * Constructor
	 *
	 * @param int $class
	 * @param int $pc
	 * @param int|string $tag
	 */
	public function __construct($class, $pc, $tag) {
		$this->_class = 0b11 & $class;
		$this->_pc = 0b1 & $pc;
		$this->_tag = $tag;
	}
	
	/**
	 * {@inheritDoc}
	 *
	 * @see Decodable::fromDER
	 * @param string $data
	 * @param int $offset
	 * @throws DecodeException
	 * @return self
	 */
	public static function fromDER($data, &$offset = null) {
		assert('is_string($data)', "got " . gettype($data));
		$idx = $offset ? $offset : 0;
		$datalen = strlen($data);
		if ($idx >= $datalen) {
			throw new DecodeException("Invalid offset");
		}
		$byte = ord($data[$idx++]);
		// bits 8 and 7 (class)
		// 0 = universal, 1 = application, 2 = context-specific, 3 = private
		$class = (0b11000000 & $byte) >> 6;
		// bit 6 (0 = primitive / 1 = constructed)
		$pc = (0b00100000 & $byte) >> 5;
		// bits 5 to 1 (tag number)
		$tag = (0b00011111 & $byte);
		// long-form identifier
		if ($tag == 0x1f) {
			$tag = gmp_init(0, 10);
			while (true) {
				if ($idx >= $datalen) {
					throw new DecodeException(
						"Unexpected end of data while decoding".
							" long form identifier");
				}
				$byte = ord($data[$idx++]);
				$tag <<= 7;
				$tag |= 0x7f & $byte;
				// last byte has bit 8 set to zero
				if (!(0x80 & $byte)) {
					break;
				}
			}
			$tag = gmp_strval($tag, 10);
		}
		if (isset($offset)) {
			$offset = $idx;
		}
		return new self($class, $pc, $tag);
	}
	
	/**
	 * {@inheritDoc}
	 *
	 * @see Encodable::toDER()
	 * @return string
	 */
	public function toDER() {
		$bytes = array();
		$byte = $this->_class << 6 | $this->_pc << 5;
		$tag = gmp_init($this->_tag, 10);
		if ($tag < 0x1f) {
			$bytes[] = $byte | $tag;
		} else {
			$bytes[] = $byte | 0x1f;
			$octets = array();
			for (; $tag > 0; $tag >>= 7) {
				array_push($octets, gmp_intval(0x80 | ($tag & 0x7f)));
			}
			// last octet has bit 8 set to zero
			$octets[0] &= 0x7f;
			foreach (array_reverse($octets) as $octet) {
				$bytes[] = $octet;
			}
		}
		return pack("C*", ...$bytes);
	}
	
	/**
	 * Get class of the type
	 *
	 * @return int
	 */
	public function typeClass() {
		return $this->_class;
	}
	
	/**
	 * Get P/C
	 *
	 * @return int
	 */
	public function pc() {
		return $this->_pc;
	}
	
	/**
	 * Get tag number
	 *
	 * @return int
	 */
	public function tag() {
		return $this->_tag;
	}
	
	/**
	 * Whether type is universal class
	 *
	 * @return boolean
	 */
	public function isUniversal() {
		return $this->_class == self::CLASS_UNIVERSAL;
	}
	
	/**
	 * Whether type is application class
	 *
	 * @return boolean
	 */
	public function isApplication() {
		return $this->_class == self::CLASS_APPLICATION;
	}
	
	/**
	 * Whether type is context specific class
	 *
	 * @return boolean
	 */
	public function isContextSpecific() {
		return $this->_class == self::CLASS_CONTEXT_SPECIFIC;
	}
	
	/**
	 * Whether type is private class
	 *
	 * @return boolean
	 */
	public function isPrivate() {
		return $this->_class == self::CLASS_PRIVATE;
	}
	
	/**
	 * Whether content is primitive
	 *
	 * @return boolean
	 */
	public function isPrimitive() {
		return $this->_pc == self::PRIMITIVE;
	}
	
	/**
	 * Whether content is constructed
	 *
	 * @return boolean
	 */
	public function isConstructed() {
		return $this->_pc == self::CONSTRUCTED;
	}
	
	/**
	 * Get copy of Identifier with given class
	 *
	 * @param int $class
	 * @return self
	 */
	public function withClass($class) {
		$obj = clone $this;
		$obj->_class = $class;
		return $obj;
	}
	
	/**
	 * Get copy of Identifier with given tag
	 *
	 * @param int $tag
	 * @return self
	 */
	public function withTag($tag) {
		$obj = clone $this;
		$obj->_tag = $tag;
		return $obj;
	}
	
	/**
	 * Get human readable type class name
	 * 
	 * @param int $class
	 * @return string
	 */
	public static function classToName($class) {
		if (!isset(self::$_classNames[$class])) {
			return "CLASS $class";
		}
		return self::$_classNames[$class];
	}
}