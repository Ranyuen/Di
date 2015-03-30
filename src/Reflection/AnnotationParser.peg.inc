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
/*!* Annotation

Main: '(' whitespace? Array whitespace? ')'
    function Array(&$result, $sub) {
        $result['val'] = $sub['val'];
    }

Array: Element ( whitespace? ',' whitespace? Element ) * ( whitespace? ',' ) ?
    function Element(&$result, $sub) {
        if (!isset($result['val'])) {
            $result['val'] = [];
        }
        if (isset($sub['key'])) {
            $result['val'][$sub['key']] = $sub['val'];
        } else {
            $result['val'][] = $sub['val'];
        }
    }

Element: KeyValue | Value
    function KeyValue(&$result, $sub) {
        $result['key'] = $sub['key'];
        $result['val'] = $sub['val'];
    }
    function Value(&$result, $sub) {
        $result['val'] = $sub['val'];
    }

KeyValue: Key whitespace? '=' whitespace? Value
    function Key(&$result, $sub) {
        $result['key'] = $sub['val'];
    }
    function Value(&$result, $sub) {
        $result['val'] = $sub['val'];
    }

Key: Symbol | String
    function Symbol(&$result, $sub) {
        $result['val'] = $sub['text'];
    }
    function String(&$result, $sub) {
        $result['val'] = substr($sub['text'], 1, -1);
    }

Value: Symbol | String | Number | ( '{' whitespace? Array whitespace? '}' )
    function Symbol(&$result, $sub) {
        $result['val'] = $sub['text'];
    }
    function String(&$result, $sub) {
        $result['val'] = substr($sub['text'], 1, -1);
    }
    function Number(&$result, $sub) {
        $result['val'] = $sub['val'];
    }
    function Array(&$result, $sub) {
        $result['val'] = $sub['val'];
    }

Symbol: /[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(?:)/

String: /"(?:[^\\"]*(?:[^"]|\\.))*"/ | /'(?:[^\\']*(?:[^']|\\.))*'/

Number: HexInt | BinInt | OctInt | Float
    function HexInt(&$result, $sub) {
        $result['val'] = intval(substr($sub['text'], 2), 16);
    }
    function OctInt(&$result, $sub) {
        $result['val'] = intval(substr($sub['text'], 1), 8);
    }
    function BinInt(&$result, $sub) {
        $result['val'] = intval(substr($sub['text'], 2), 2);
    }
    function Float(&$result, $sub) {
        $result['val'] = $sub['text'] - 0;
    }

HexInt: /0x[A-Fa-f0-9]+/

BinInt: /0b[01]+/

OctInt: /0[0-7]+/

Float: /[-+]?\d+(?:\.\d+)?(?:[eE][-+]?\d+)?/

whitespace: /\s+/

*/

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
