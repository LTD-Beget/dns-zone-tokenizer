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
        ],
        'CAA' => [
            'FLAGS' => 'defaultExtractor',
            'TAG'   => 'defaultExtractor',
            'VALUE' => 'defaultExtractor'
        ],
        'NAPTR' => [
            'ORDER'       => 'defaultExtractor',
            'PREFERENCE'  => 'defaultExtractor',
            'FLAGS'       => 'defaultExtractor',
            'SERVICES'    => 'defaultExtractor',
            'REGEXP'      => 'defaultExtractor',
            'REPLACEMENT' => 'defaultExtractor'
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
     * Is the txt record surrounded by quotes
     * @var bool
     */
    private $txtRecordHasQuotes = false;

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

        if ($this->stream->isEnd()){
            return;
        }
        $ord = $this->stream->ord();

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
        if ($this->stream->isEnd()) {
            return;
        }
        $ord = $this->stream->ord();
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
        if ($this->stream->isEnd()) {
            return;
        }
        $ord = $this->stream->ord();
        $this->stream->next();

        // comment starts
        if($ord === AsciiChar::SEMICOLON) {
            $this->commentOpen = true;
            goto start;
        } elseif($this->commentOpen === true && $ord !== AsciiChar::LINE_FEED) {
            $this->commentOpen = true;
            goto start;
        } elseif($this->commentOpen === true && ($ord === AsciiChar::LINE_FEED || $ord === AsciiChar::NULL)) {
            $this->stream->previous();
            $this->commentOpen = false;
            goto start;
        } else {
            // ignore whitespace
            if($ord === AsciiChar::SPACE || $ord === AsciiChar::HORIZONTAL_TAB) {
                goto start;
            }

            // multi line opened
            if($ord === AsciiChar::OPEN_BRACKET && !$this->commentOpen) {
                $this->multiLineOpened = true;
                goto start;
            }
            // multi line closed
            elseif($this->multiLineOpened && !$this->commentOpen && $ord === AsciiChar::CLOSE_BRACKET) {
                $this->multiLineOpened = false;
                goto start;
            }
            // comment end in multi line TXT record
            elseif($ord === AsciiChar::LINE_FEED && $this->commentOpen && $this->multiLineOpened) {
                goto start;
            }
            // is record ends?
            elseif(!$this->multiLineOpened && ($ord === AsciiChar::LINE_FEED || $ord === AsciiChar::NULL)) {
                return;
            } elseif($this->multiLineOpened && $ord === AsciiChar::LINE_FEED) {
                goto start;
            }
            elseif(!$this->commentOpen) {
                // Double quotes aren't required to start a string, but if they start the string then they must also end the string
                if($ord !== AsciiChar::DOUBLE_QUOTES) {
                    $this->stream->previous();
                    $this->txtRecordHasQuotes = false;
                } else {
                    $this->txtRecordHasQuotes = true;
                }
                $this->extractCharSet($tokenName);
            }
        }
        
        // multi line should no longer be open
        if($this->multiLineOpened) {
            throw new SyntaxErrorException($this->stream);
        }
    }

    /**
     * @param string $tokenName
     */
    private function extractCharSet(string $tokenName)
    {
        $escaping_open = false;
        start:
        if ($this->stream->isEnd()) {
            throw new SyntaxErrorException($this->stream);
        }
        $ord = $this->stream->ord();
        $this->stream->next();

        if(!$escaping_open && $this->txtRecordHasQuotes && $ord === AsciiChar::DOUBLE_QUOTES) {
            $this->txtExtractor($tokenName);
        } elseif(!$this->txtRecordHasQuotes && $ord === AsciiChar::SPACE) {
            // If there aren't quotes around a character string, a space terminates the string
            return;
        } else {
            if($ord === AsciiChar::LINE_FEED || $ord === AsciiChar::VERTICAL_TAB || $ord === AsciiChar::NULL) {
                if($this->txtRecordHasQuotes) {
                    $this->stream->previous();
                    throw new SyntaxErrorException($this->stream);
                }
                else {
                    return;
                }
            }

            $this->tokens[$tokenName] .= chr($ord);

            // Escaping open is only set for one iteration so that it doesn't break on double slashes ie. \\
            if($ord === AsciiChar::BACKSLASH) {
                $escaping_open = true;
            } else {
                $escaping_open = false;
            }

            goto start;
        }
    }
}
