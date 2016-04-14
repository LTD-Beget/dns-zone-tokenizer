<?php
/**
 * @author: Viskov Sergey
 * @date  : 8/4/15
 * @time  : 9:31 PM
 */

namespace LTDBeget\dns;

use LTDBeget\ascii\AsciiChar;
use LTDBeget\dns\record\Record;
use LTDBeget\stringstream\StringStream;

/**
 * Class Tokenizer
 *
 * @package LTDBeget\dns
 */
final class Tokenizer
{
    /**
     * @var StringStream
     */
    private $stream;
    /**
     * Result of tokenize input string
     *
     * @var array
     */
    private $tokens = [];
    /**
     * @var string
     */
    private $ttl = NULL;
    /**
     * @var null
     */
    private $origin = NULL;
    /**
     * @var array
     */
    private $allowedGlobalVariables = [
        'origin' => true,
        'ttl'    => true
    ];

    /**
     * Tokenizer constructor.
     *
     * @param string $string
     */
    private function __construct(string $string)
    {
        $this->stream = new StringStream($string);
    }

    /**
     * @param string $plainData
     * @return array
     */
    public static function tokenize(string $plainData) : array
    {
        return (new self($plainData))->tokenizeInternal()->tokens;
    }

    /**
     * @return Tokenizer
     */
    private function tokenizeInternal() : Tokenizer
    {
        do {
            $this->stream->ignoreWhitespace();
            if ($this->stream->currentAscii()->is(AsciiChar::DOLLAR)) {
                $this->stream->next();
                $this->extractGlobalVariable();
            } elseif ($this->stream->currentAscii()->is(AsciiChar::SEMICOLON)) {
                $this->ignoreComment();
            } else {
                $this->extractRecord();
            }
        } while (!$this->stream->isEnd());

        return $this;
    }

    private function extractGlobalVariable()
    {
        $variableName = "";
        start:
        if ($this->stream->currentAscii()->isLetter()) {
            $variableName .= $this->stream->current();
            $this->stream->next();
            goto start;
        } elseif ($this->stream->currentAscii()->isHorizontalSpace()) {
            $variableName = mb_strtolower($variableName);
            if (!array_key_exists($variableName, $this->allowedGlobalVariables)) {
                throw new SyntaxErrorException($this->stream);
            }
            $this->stream->ignoreHorizontalSpace();
            $this->extractGlobalVariableValue($variableName);
        } else {
            throw new SyntaxErrorException($this->stream);
        }
    }

    /**
     * @param string $variableName
     */
    private function extractGlobalVariableValue(string $variableName)
    {
        start:
        $char = $this->stream->currentAscii();
        $this->{$variableName} .= '';
        if ($char->isLetter() ||
            $char->isDigit() ||
            $char->is(AsciiChar::UNDERSCORE) ||
            $char->is(AsciiChar::DOT) ||
            $char->is(AsciiChar::HYPHEN) ||
            $char->is(AsciiChar::AT_SYMBOL) ||
            $char->is(AsciiChar::ASTERISK)
        ) {
            $this->{$variableName} .= $this->stream->current();
            $this->stream->next();
            goto start;
        } elseif ($char->isWhiteSpace()) {
            return;
        } else {
            throw new SyntaxErrorException($this->stream);
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

    private function extractRecord()
    {
        $this->tokens[] = (new Record($this->stream, $this->origin, $this->ttl))->tokenize();
    }
}