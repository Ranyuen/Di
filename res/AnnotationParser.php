<?php
/**
 * Annotation based simple DI & AOP at PHP.
 *
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2015 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 * @link      https://github.com/Ranyuen/Di
 */
namespace Ranyuen\Di\Reflection;

require_once 'vendor/hafriedlander/php-peg/autoloader.php';

use hafriedlander\Peg\Parser;

/**
 * Annotation parser.
 *
 * @SuppressWarnings(PHPMD)
 */
class AnnotationParser extends Parser\Basic
{
/* Main: '(' > Array > ')' */
protected $match_Main_typestack = array('Main');
function match_Main ($stack = array()) {
	$matchrule = "Main"; $result = $this->construct($matchrule, $matchrule, null);
	$_5 = NULL;
	do {
		if (substr($this->string,$this->pos,1) == '(') {
			$this->pos += 1;
			$result["text"] .= '(';
		}
		else { $_5 = FALSE; break; }
		if (( $subres = $this->whitespace(  ) ) !== FALSE) { $result["text"] .= $subres; }
		$matcher = 'match_'.'Array'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_5 = FALSE; break; }
		if (( $subres = $this->whitespace(  ) ) !== FALSE) { $result["text"] .= $subres; }
		if (substr($this->string,$this->pos,1) == ')') {
			$this->pos += 1;
			$result["text"] .= ')';
		}
		else { $_5 = FALSE; break; }
		$_5 = TRUE; break;
	}
	while(0);
	if( $_5 === TRUE ) { return $this->finalise($result); }
	if( $_5 === FALSE) { return FALSE; }
}

public function Main_Array (&$result, $sub) {
        $result['val'] = $sub['val'];
    }

/* Array: Element ( > ',' > Element ) * ( > ',' ) ? */
protected $match_Array_typestack = array('Array');
function match_Array ($stack = array()) {
	$matchrule = "Array"; $result = $this->construct($matchrule, $matchrule, null);
	$_18 = NULL;
	do {
		$matcher = 'match_'.'Element'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_18 = FALSE; break; }
		while (true) {
			$res_13 = $result;
			$pos_13 = $this->pos;
			$_12 = NULL;
			do {
				if (( $subres = $this->whitespace(  ) ) !== FALSE) { $result["text"] .= $subres; }
				if (substr($this->string,$this->pos,1) == ',') {
					$this->pos += 1;
					$result["text"] .= ',';
				}
				else { $_12 = FALSE; break; }
				if (( $subres = $this->whitespace(  ) ) !== FALSE) { $result["text"] .= $subres; }
				$matcher = 'match_'.'Element'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_12 = FALSE; break; }
				$_12 = TRUE; break;
			}
			while(0);
			if( $_12 === FALSE) {
				$result = $res_13;
				$this->pos = $pos_13;
				unset( $res_13 );
				unset( $pos_13 );
				break;
			}
		}
		$res_17 = $result;
		$pos_17 = $this->pos;
		$_16 = NULL;
		do {
			if (( $subres = $this->whitespace(  ) ) !== FALSE) { $result["text"] .= $subres; }
			if (substr($this->string,$this->pos,1) == ',') {
				$this->pos += 1;
				$result["text"] .= ',';
			}
			else { $_16 = FALSE; break; }
			$_16 = TRUE; break;
		}
		while(0);
		if( $_16 === FALSE) {
			$result = $res_17;
			$this->pos = $pos_17;
			unset( $res_17 );
			unset( $pos_17 );
		}
		$_18 = TRUE; break;
	}
	while(0);
	if( $_18 === TRUE ) { return $this->finalise($result); }
	if( $_18 === FALSE) { return FALSE; }
}

public function Array_Element (&$result, $sub) {
        if (!isset($result['val'])) {
            $result['val'] = [];
        }
        if (isset($sub['key'])) {
            $result['val'][$sub['key']] = $sub['val'];
        } else {
            $result['val'][] = $sub['val'];
        }
    }

/* Element: KeyValue | Value */
protected $match_Element_typestack = array('Element');
function match_Element ($stack = array()) {
	$matchrule = "Element"; $result = $this->construct($matchrule, $matchrule, null);
	$_23 = NULL;
	do {
		$res_20 = $result;
		$pos_20 = $this->pos;
		$matcher = 'match_'.'KeyValue'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres );
			$_23 = TRUE; break;
		}
		$result = $res_20;
		$this->pos = $pos_20;
		$matcher = 'match_'.'Value'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres );
			$_23 = TRUE; break;
		}
		$result = $res_20;
		$this->pos = $pos_20;
		$_23 = FALSE; break;
	}
	while(0);
	if( $_23 === TRUE ) { return $this->finalise($result); }
	if( $_23 === FALSE) { return FALSE; }
}

public function Element_KeyValue (&$result, $sub) {
        $result['key'] = $sub['key'];
        $result['val'] = $sub['val'];
    }

public function Element_Value (&$result, $sub) {
        $result['val'] = $sub['val'];
    }

/* KeyValue: Key > '=' > Value */
protected $match_KeyValue_typestack = array('KeyValue');
function match_KeyValue ($stack = array()) {
	$matchrule = "KeyValue"; $result = $this->construct($matchrule, $matchrule, null);
	$_30 = NULL;
	do {
		$matcher = 'match_'.'Key'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_30 = FALSE; break; }
		if (( $subres = $this->whitespace(  ) ) !== FALSE) { $result["text"] .= $subres; }
		if (substr($this->string,$this->pos,1) == '=') {
			$this->pos += 1;
			$result["text"] .= '=';
		}
		else { $_30 = FALSE; break; }
		if (( $subres = $this->whitespace(  ) ) !== FALSE) { $result["text"] .= $subres; }
		$matcher = 'match_'.'Value'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_30 = FALSE; break; }
		$_30 = TRUE; break;
	}
	while(0);
	if( $_30 === TRUE ) { return $this->finalise($result); }
	if( $_30 === FALSE) { return FALSE; }
}

public function KeyValue_Key (&$result, $sub) {
        $result['key'] = $sub['val'];
    }

public function KeyValue_Value (&$result, $sub) {
        $result['val'] = $sub['val'];
    }

/* Key: Symbol | String */
protected $match_Key_typestack = array('Key');
function match_Key ($stack = array()) {
	$matchrule = "Key"; $result = $this->construct($matchrule, $matchrule, null);
	$_35 = NULL;
	do {
		$res_32 = $result;
		$pos_32 = $this->pos;
		$matcher = 'match_'.'Symbol'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres );
			$_35 = TRUE; break;
		}
		$result = $res_32;
		$this->pos = $pos_32;
		$matcher = 'match_'.'String'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres );
			$_35 = TRUE; break;
		}
		$result = $res_32;
		$this->pos = $pos_32;
		$_35 = FALSE; break;
	}
	while(0);
	if( $_35 === TRUE ) { return $this->finalise($result); }
	if( $_35 === FALSE) { return FALSE; }
}

public function Key_Symbol (&$result, $sub) {
        $result['val'] = $sub['text'];
    }

public function Key_String (&$result, $sub) {
        $result['val'] = substr($sub['text'], 1, -1);
    }

/* Value: Symbol | String | Number | ( '{' > Array > '}' ) */
protected $match_Value_typestack = array('Value');
function match_Value ($stack = array()) {
	$matchrule = "Value"; $result = $this->construct($matchrule, $matchrule, null);
	$_54 = NULL;
	do {
		$res_37 = $result;
		$pos_37 = $this->pos;
		$matcher = 'match_'.'Symbol'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres );
			$_54 = TRUE; break;
		}
		$result = $res_37;
		$this->pos = $pos_37;
		$_52 = NULL;
		do {
			$res_39 = $result;
			$pos_39 = $this->pos;
			$matcher = 'match_'.'String'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres );
				$_52 = TRUE; break;
			}
			$result = $res_39;
			$this->pos = $pos_39;
			$_50 = NULL;
			do {
				$res_41 = $result;
				$pos_41 = $this->pos;
				$matcher = 'match_'.'Number'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres );
					$_50 = TRUE; break;
				}
				$result = $res_41;
				$this->pos = $pos_41;
				$_48 = NULL;
				do {
					if (substr($this->string,$this->pos,1) == '{') {
						$this->pos += 1;
						$result["text"] .= '{';
					}
					else { $_48 = FALSE; break; }
					if (( $subres = $this->whitespace(  ) ) !== FALSE) { $result["text"] .= $subres; }
					$matcher = 'match_'.'Array'; $key = $matcher; $pos = $this->pos;
					$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
					if ($subres !== FALSE) { $this->store( $result, $subres ); }
					else { $_48 = FALSE; break; }
					if (( $subres = $this->whitespace(  ) ) !== FALSE) { $result["text"] .= $subres; }
					if (substr($this->string,$this->pos,1) == '}') {
						$this->pos += 1;
						$result["text"] .= '}';
					}
					else { $_48 = FALSE; break; }
					$_48 = TRUE; break;
				}
				while(0);
				if( $_48 === TRUE ) { $_50 = TRUE; break; }
				$result = $res_41;
				$this->pos = $pos_41;
				$_50 = FALSE; break;
			}
			while(0);
			if( $_50 === TRUE ) { $_52 = TRUE; break; }
			$result = $res_39;
			$this->pos = $pos_39;
			$_52 = FALSE; break;
		}
		while(0);
		if( $_52 === TRUE ) { $_54 = TRUE; break; }
		$result = $res_37;
		$this->pos = $pos_37;
		$_54 = FALSE; break;
	}
	while(0);
	if( $_54 === TRUE ) { return $this->finalise($result); }
	if( $_54 === FALSE) { return FALSE; }
}

public function Value_Symbol (&$result, $sub) {
        $result['val'] = $sub['text'];
    }

public function Value_String (&$result, $sub) {
        $result['val'] = substr($sub['text'], 1, -1);
    }

public function Value_Number (&$result, $sub) {
        $result['val'] = $sub['val'];
    }

public function Value_Array (&$result, $sub) {
        $result['val'] = $sub['val'];
    }

/* Symbol: /[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(?:)/ */
protected $match_Symbol_typestack = array('Symbol');
function match_Symbol ($stack = array()) {
	$matchrule = "Symbol"; $result = $this->construct($matchrule, $matchrule, null);
	if (( $subres = $this->rx( '/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(?:)/' ) ) !== FALSE) {
		$result["text"] .= $subres;
		return $this->finalise($result);
	}
	else { return FALSE; }
}


/* String: /"(?:[^\\"]*(?:[^"]|\\.))*"/ | /'(?:[^\\']*(?:[^']|\\.))*'/ */
protected $match_String_typestack = array('String');
function match_String ($stack = array()) {
	$matchrule = "String"; $result = $this->construct($matchrule, $matchrule, null);
	$_60 = NULL;
	do {
		$res_57 = $result;
		$pos_57 = $this->pos;
		if (( $subres = $this->rx( '/"(?:[^\\\\"]*(?:[^"]|\\\\.))*"/' ) ) !== FALSE) {
			$result["text"] .= $subres;
			$_60 = TRUE; break;
		}
		$result = $res_57;
		$this->pos = $pos_57;
		if (( $subres = $this->rx( '/\'(?:[^\\\\\']*(?:[^\']|\\\\.))*\'/' ) ) !== FALSE) {
			$result["text"] .= $subres;
			$_60 = TRUE; break;
		}
		$result = $res_57;
		$this->pos = $pos_57;
		$_60 = FALSE; break;
	}
	while(0);
	if( $_60 === TRUE ) { return $this->finalise($result); }
	if( $_60 === FALSE) { return FALSE; }
}


/* Number: HexInt | BinInt | OctInt | Float */
protected $match_Number_typestack = array('Number');
function match_Number ($stack = array()) {
	$matchrule = "Number"; $result = $this->construct($matchrule, $matchrule, null);
	$_73 = NULL;
	do {
		$res_62 = $result;
		$pos_62 = $this->pos;
		$matcher = 'match_'.'HexInt'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres );
			$_73 = TRUE; break;
		}
		$result = $res_62;
		$this->pos = $pos_62;
		$_71 = NULL;
		do {
			$res_64 = $result;
			$pos_64 = $this->pos;
			$matcher = 'match_'.'BinInt'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres );
				$_71 = TRUE; break;
			}
			$result = $res_64;
			$this->pos = $pos_64;
			$_69 = NULL;
			do {
				$res_66 = $result;
				$pos_66 = $this->pos;
				$matcher = 'match_'.'OctInt'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres );
					$_69 = TRUE; break;
				}
				$result = $res_66;
				$this->pos = $pos_66;
				$matcher = 'match_'.'Float'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres );
					$_69 = TRUE; break;
				}
				$result = $res_66;
				$this->pos = $pos_66;
				$_69 = FALSE; break;
			}
			while(0);
			if( $_69 === TRUE ) { $_71 = TRUE; break; }
			$result = $res_64;
			$this->pos = $pos_64;
			$_71 = FALSE; break;
		}
		while(0);
		if( $_71 === TRUE ) { $_73 = TRUE; break; }
		$result = $res_62;
		$this->pos = $pos_62;
		$_73 = FALSE; break;
	}
	while(0);
	if( $_73 === TRUE ) { return $this->finalise($result); }
	if( $_73 === FALSE) { return FALSE; }
}

public function Number_HexInt (&$result, $sub) {
        $result['val'] = intval(substr($sub['text'], 2), 16);
    }

public function Number_OctInt (&$result, $sub) {
        $result['val'] = intval(substr($sub['text'], 1), 8);
    }

public function Number_BinInt (&$result, $sub) {
        $result['val'] = intval(substr($sub['text'], 2), 2);
    }

public function Number_Float (&$result, $sub) {
        $result['val'] = $sub['text'] - 0;
    }

/* HexInt: /0x[A-Fa-f0-9]+/ */
protected $match_HexInt_typestack = array('HexInt');
function match_HexInt ($stack = array()) {
	$matchrule = "HexInt"; $result = $this->construct($matchrule, $matchrule, null);
	if (( $subres = $this->rx( '/0x[A-Fa-f0-9]+/' ) ) !== FALSE) {
		$result["text"] .= $subres;
		return $this->finalise($result);
	}
	else { return FALSE; }
}


/* BinInt: /0b[01]+/ */
protected $match_BinInt_typestack = array('BinInt');
function match_BinInt ($stack = array()) {
	$matchrule = "BinInt"; $result = $this->construct($matchrule, $matchrule, null);
	if (( $subres = $this->rx( '/0b[01]+/' ) ) !== FALSE) {
		$result["text"] .= $subres;
		return $this->finalise($result);
	}
	else { return FALSE; }
}


/* OctInt: /0[0-7]+/ */
protected $match_OctInt_typestack = array('OctInt');
function match_OctInt ($stack = array()) {
	$matchrule = "OctInt"; $result = $this->construct($matchrule, $matchrule, null);
	if (( $subres = $this->rx( '/0[0-7]+/' ) ) !== FALSE) {
		$result["text"] .= $subres;
		return $this->finalise($result);
	}
	else { return FALSE; }
}


/* Float: /[-+]?\d+(?:\.\d+)?(?:[eE][-+]?\d+)?/ */
protected $match_Float_typestack = array('Float');
function match_Float ($stack = array()) {
	$matchrule = "Float"; $result = $this->construct($matchrule, $matchrule, null);
	if (( $subres = $this->rx( '/[-+]?\d+(?:\.\d+)?(?:[eE][-+]?\d+)?/' ) ) !== FALSE) {
		$result["text"] .= $subres;
		return $this->finalise($result);
	}
	else { return FALSE; }
}




    /**
     * @return array|null
     */
    public function parse()
    {
        $result = $this->match_Main();
        if (isset($result['val'])) {
            return $result['val'];
        }
        return null;
    }
}
// vim:ft=php:
