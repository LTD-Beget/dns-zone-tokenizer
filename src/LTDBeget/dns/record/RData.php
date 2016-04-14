<?php
/**
 * @author: Viskov Sergey
 * @date: 14.04.16
 * @time: 4:50
 */

namespace LTDBeget\dns\record;


use LTDBeget\dns\SyntaxErrorException;
use LTDBeget\stringstream\StringStream;

class RData
{
    private static $rdataFormats = [
        'SOA'   => [
            'MNAME'   => 'defaultExtractor',
            'RNAME'   => 'defaultExtractor',
            'SERIAL'  => 'defaultExtractor',
            'REFRESH' => 'defaultExtractor',
            'RETRY'   => 'defaultExtractor',
            'EXPIRE'  => 'defaultExtractor',
            'MINIMUM' => 'defaultExtractor',
        ],
        'A'     => [
            'ADDRESS' => 'defaultExtractor'
        ],
        'AAAA'  => [
            'ADDRESS' => 'defaultExtractor'
        ],
        'CNAME' => [
            'CNAME' => 'defaultExtractor'
        ],
        'MX'    => [
            'PREFERENCE' => 'defaultExtractor',
            'EXCHANGE'   => 'defaultExtractor'
        ],
        'NS'    => [
            'NSDNAME' => 'defaultExtractor'
        ],
        'PTR'   => [
            'PTRDNAME' => 'defaultExtractor'
        ],
        'SRV'   => [
            "PRIORITY" => 'defaultExtractor',
            "WEIGHT"   => 'defaultExtractor',
            "PORT"     => 'defaultExtractor',
            "TARGET"   => 'defaultExtractor'
        ],
        'TXT'   => [
            'TXTDATA' => 'txtExtractor'
        ]
    ];
    /**
     * @var string
     */
    private $type;
    /**
     * @var array
     */
    private $tokens = [];

    /**
     * RData constructor.
     * @param StringStream $stream
     * @param string $type
     */
    public function __construct(StringStream $stream, string $type)
    {
        if (!array_key_exists($type, self::$rdataFormats)) {
            throw new SyntaxErrorException($stream);
        }

        $this->stream = $stream;
        $this->type   = $type;
    }

    public function tokenize() : array
    {
        foreach (self::$rdataFormats[$this->type] as $tokenName => $extractor) {
            $this->$extractor($tokenName);
        }
        $this->endRData();

        return $this->tokens;
    }

    /**
     * TODO
     */
    private function endRData()
    {

    }

    /**
     * TODO multiline comments open
     * @param string $tokenName
     */
    protected function defaultExtractor(string $tokenName)
    {
        if (!array_key_exists($tokenName, $this->tokens)) {
            $this->tokens[$tokenName] = '';
        }

        start:
        $char = $this->stream->currentAscii();
        if ($char->isPrintableChar() && !$char->isHorizontalSpace()) {

            $this->tokens[$tokenName] .= $this->stream->current();
            $this->stream->next();
            goto start;
        } elseif ($char->isHorizontalSpace()) {
            $this->stream->ignoreHorizontalSpace();

            return;
        } elseif ($char->isVerticalSpace()) {
            return;
        } else {
            throw new SyntaxErrorException($this->stream);
        }
    }

    /**
     * TODO
     * @param string $tokenName
     */
    protected function txtExtractor(string $tokenName)
    {

    }
    
//    /**
//     * Parsing rdata which syntax is relatively default
//     * @param $rdata_name
//     * @param bool|false $comment_open
//     */
//    private function defaultRDataExtractor($rdata_name, $comment_open = false)
//    {
//        start:
//
//        $ord = current($this->data);
//        next($this->data);
//
//        if($ord == 0) {
//            return;
//        }
//
//        if($ord === 40 && !$comment_open) { // multi line opened ; 40 = (
//            $this->multiLineOpened = true;
//            goto start;
//        } elseif($this->multiLineOpened && !$comment_open && $ord === 41) { // multi line closed; 41 = )
//            $this->multiLineOpened = false;
//            goto start;
//        } elseif($this->multiLineOpened && !$comment_open && $ord === 10) { // // multi line end line
//            goto start;
//        } elseif($ord === 10 && !$comment_open) { // if end of record
//            return;
//        } else { // meaningfull symbol
//            if($ord === 59) { // 59 = ;
//                $comment_open = true;
//                goto start;
//            } elseif(($ord === 10 || $ord === 0) && $comment_open) {
//                prev($this->data);
//                $comment_open = false;
//                goto start;
//            } elseif($comment_open) {
//                $comment_open = true;
//                goto start;
//            } elseif(!$comment_open) {
//                if($ord === 32 && $this->parsedRData[$rdata_name] === "") { // ignore space at start
//                    goto start;
//                } elseif($ord === 32) {
//                    return;
//                } else {
//                    $this->parsedRData[$rdata_name] .= chr($ord);
//                    goto start;
//                }
//            }
//        }
//    }
//
//    /**
//     * Parsing rdata for TXT resource record
//     * @param bool|false $comment_open
//     */
//    private function extractTxtBlocks($comment_open = false)
//    {
//        start:
//
//        $ord = current($this->data);
//        next($this->data);
//
//        if($ord == 0) { // if end of record
//            return;
//        }
//
//        // comment starts
//        if($ord === 59) {
//            $comment_open = true;
//            goto start;
//        } elseif($comment_open == true && $ord !== 10) {
//            $comment_open = true;
//            goto start;
//        } elseif($comment_open == true && ($ord === 10 || $ord === 0)) {
//            prev($this->data);
//            $comment_open = false;
//            goto start;
//        } else {
//            // ignore blanck line
//            if($ord === 32) {
//                goto start;
//            }
//
//            // Find starts of char set
//            if($ord === 34 && !$comment_open) { // "
//                $this->extractCharSet();
//            }
//
//            // multi line opened
//            if($ord === 40 && !$comment_open) {
//                $this->multiLineOpened = true;
//                goto start;
//            }
//
//            // multi line closed
//            if($this->multiLineOpened && !$comment_open && $ord === 41) {
//                $this->multiLineOpened = false;
//                goto start;
//            }
//
//            // comment end in multi line TXT record
//            if($ord === 10 && $comment_open && $this->multiLineOpened) {
//                goto start;
//            }
//
//            // is record ends?
//            if(!$this->multiLineOpened && ($ord === 10 || $ord === 0)) {
//                return;
//            } elseif($this->multiLineOpened && $ord === 10) {
//                goto start;
//            }
//        }
//    }
//
//    /**
//     * Parsing TXT record char set
//     * @param bool|false $escaping_open
//     */
//    private function extractCharSet($escaping_open = false)
//    {
//        start:
//        $ord = current($this->data);
//        next($this->data);
//
//        if($ord == 0) { // if end of record
//            return;
//        }
//
//        if(!$escaping_open && $ord === 34) {
//            $this->extractTxtBlocks();
//        } else {
//            $this->parsedRData["TXTDATA"] .= chr($ord);
//            $escaping_open = ($ord === 92 and !$escaping_open);
//            goto start;
//        }
//    }
}