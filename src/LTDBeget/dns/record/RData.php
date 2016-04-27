<?php
/**
 * @author: Viskov Sergey
 * @date  : 14.04.16
 * @time  : 4:50
 */

namespace LTDBeget\dns\record;

use LTDBeget\ascii\AsciiChar;
use LTDBeget\dns\SyntaxErrorException;
use LTDBeget\stringstream\StringStream;

/**
 * Class RData
 *
 * @package LTDBeget\dns\record
 */
class RData
{
    /**
     * @var array
     */
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
            'PRIORITY' => 'defaultExtractor',
            'WEIGHT'   => 'defaultExtractor',
            'PORT'     => 'defaultExtractor',
            'TARGET'   => 'defaultExtractor'
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
     * @var bool
     */
    private $commentOpen = false;

    /**
     * @var bool
     */
    private $multiLineOpened = false;

    /**
     * RData constructor.
     *
     * @param StringStream $stream
     * @param string       $type
     */
    public function __construct(StringStream $stream, string $type)
    {
        if (! self::isKnownType($type)) {
            throw new SyntaxErrorException($stream);
        }

        $this->stream = $stream;
        $this->type   = $type;
    }

    /**
     * @param string $type
     * @return bool
     */
    public static function isKnownType(string $type) : bool
    {
        return array_key_exists($type, self::$rdataFormats);
    }

    /**
     * @return array
     */
    public function tokenize() : array
    {
        foreach (self::$rdataFormats[$this->type] as $tokenName => $extractor) {
            $this->$extractor($tokenName);
        }
        
        $this->endRecord();

        return $this->tokens;
    }
    
    /**
     * @param string $tokenName
     */
    protected function defaultExtractor(string $tokenName)
    {
        if($this->multiLineOpened) {
            $this->stream->ignoreWhitespace();
        } else {
            $this->stream->ignoreHorizontalSpace();
        }
        
        $this->commentOpen = false;
        
        if (!array_key_exists($tokenName, $this->tokens)) {
            $this->tokens[$tokenName] = '';
        }
        
        start:

        $ord = $this->stream->ord();

        if($ord === AsciiChar::NULL) {
            return;
        }

        if($ord === AsciiChar::OPEN_BRACKET && !$this->commentOpen) {
            $this->multiLineOpened = true;
            $this->stream->next();
            goto start;
        } elseif($this->multiLineOpened && !$this->commentOpen && $ord === AsciiChar::CLOSE_BRACKET) {
            $this->multiLineOpened = false;
            $this->stream->next();
            goto start;
        } elseif($this->multiLineOpened && !$this->commentOpen && $ord === AsciiChar::LINE_FEED) {
            $this->stream->next();
            goto start;
        } elseif($ord === AsciiChar::LINE_FEED && !$this->commentOpen) {
            return;
        } else {
            if($ord === AsciiChar::SEMICOLON) {
                $this->stream->previous();
                if($this->stream->currentAscii()->isHorizontalSpace()) {

                    $this->commentOpen = true;
                    $this->stream->next();
                    $this->stream->next();
                } else {
                    $this->stream->next();
                    $this->tokens[$tokenName] .= $this->stream->current();
                    $this->stream->next();
                }
                goto start;
            } elseif(($this->stream->currentAscii()->isVerticalSpace() || $ord === AsciiChar::NULL) && $this->commentOpen) {
                $this->stream->next();
                $this->stream->ignoreHorizontalSpace();
                $this->commentOpen = false;
                goto start;
            } elseif($this->commentOpen) {
                $this->commentOpen = true;
                $this->ignoreComment();
                goto start;
            } elseif(!$this->commentOpen) {
                if($ord === AsciiChar::SPACE && $this->tokens[$tokenName] === '') {
                    $this->stream->next();
                    goto start;
                } elseif($this->stream->currentAscii()->isWhiteSpace()) {
                    return;
                } else {
                    $this->tokens[$tokenName] .= $this->stream->current();
                    $this->stream->next();
                    if($this->stream->isEnd()) {
                        $this->tokens[$tokenName] = trim($this->tokens[$tokenName]);
                    }
                    goto start;
                }
            }
        }
    }

    private function ignoreComment()
    {
        start:
        if (!$this->stream->currentAscii()->isVerticalSpace() && !$this->stream->isEnd()) {
            $this->stream->next();
            goto start;
        }
    }
    
    protected function endRecord()
    {
        start:
        $ord = $this->stream->ord();
        if($ord === AsciiChar::NULL) {
            return;
        }
        if($ord === AsciiChar::SEMICOLON) {
            $this->stream->next();
            $this->commentOpen = true;
            goto start;
        } elseif($this->commentOpen) {
            if($ord === AsciiChar::NULL() || $ord === AsciiChar::LINE_FEED) {
                $this->commentOpen = false;
                goto start;
            } else {
                $this->stream->next();
                $this->commentOpen = true;
                goto start;
            }
        } elseif(!$this->commentOpen)  {
            if($this->multiLineOpened) {
                if($ord === AsciiChar::CLOSE_BRACKET) {
                    $this->multiLineOpened = false;
                }
                $this->stream->next();
                goto start;
            } elseif($ord === AsciiChar::NULL() || $ord === AsciiChar::LINE_FEED) {
                return;
            }
        }
    }

    /**
     * @param string $tokenName
     */
    private function txtExtractor(string $tokenName)
    {
        if (!array_key_exists($tokenName, $this->tokens)) {
            $this->tokens[$tokenName] = '';
        }

        start:
        $ord = $this->stream->ord();
        $this->stream->next();

        if($ord === 0) { // if end of record
            return;
        }

        // comment starts
        if($ord === 59) {
            $this->commentOpen = true;
            goto start;
        } elseif($this->commentOpen === true && $ord !== 10) {
            $this->commentOpen = true;
            goto start;
        } elseif($this->commentOpen === true && ($ord === 10 || $ord === 0)) {
            $this->stream->previous();
            $this->commentOpen = false;
            goto start;
        } else {
            // ignore blanck line
            if($ord === 32) {
                goto start;
            }

            // Find starts of char set
            if($ord === 34 && !$this->commentOpen) { // "
                $this->extractCharSet($tokenName);
            }

            // multi line opened
            if($ord === 40 && !$this->commentOpen) {
                $this->multiLineOpened = true;
                goto start;
            }

            // multi line closed
            if($this->multiLineOpened && !$this->commentOpen && $ord === 41) {
                $this->multiLineOpened = false;
                goto start;
            }

            // comment end in multi line TXT record
            if($ord === 10 && $this->commentOpen && $this->multiLineOpened) {
                goto start;
            }

            // is record ends?
            if(!$this->multiLineOpened && ($ord === 10 || $ord === 0)) {
                return;
            } elseif($this->multiLineOpened && $ord === 10) {
                goto start;
            }
        }
    }

    /**
     * @param string $tokenName
     */
    private function extractCharSet(string $tokenName)
    {
        $escaping_open = false;
        start:
        $ord = $this->stream->ord();
        $this->stream->next();

        if($ord === 0) { // if end of record
            return;
        }

        if(!$escaping_open && $ord === 34) {
            $this->txtExtractor($tokenName);
        } else {
            $this->tokens[$tokenName] .= chr($ord);
            $escaping_open = ($ord === 92 && !$escaping_open);
            goto start;
        }
    }
}