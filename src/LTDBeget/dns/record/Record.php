<?php
/**
 * @author: Viskov Sergey
 * @date  : 14.04.16
 * @time  : 2:33
 */

namespace LTDBeget\dns\record;

use LTDBeget\ascii\AsciiChar;
use LTDBeget\dns\SyntaxErrorException;
use LTDBeget\stringstream\StringStream;

/**
 * Class Record
 *
 * @package LTDBeget\dns\record
 */
class Record
{
    /**
     * @var StringStream
     */
    private $stream;
    /**
     * @var string
     */
    private $globalOrigin;
    /**
     * @var string
     */
    private $globalTtl;

    /**
     * @var array
     */
    private $tokens = [];

    /**
     * Record constructor.
     *
     * @param StringStream $stream
     * @param string       $globalOrigin
     * @param string       $globalTtl
     */
    public function __construct(StringStream $stream, string $globalOrigin = NULL, string $globalTtl = NULL)
    {
        $this->stream       = $stream;
        $this->globalOrigin = $globalOrigin;
        $this->globalTtl    = $globalTtl;
    }

    /**
     * @return array
     */
    public function tokenize() : array
    {
        if ($this->isRecordClass()) {
            if (!empty($this->globalOrigin)) {
                $this->tokens["NAME"] = $this->globalOrigin;
            } else {
                throw new SyntaxErrorException($this->stream);
            }

            if (!empty($this->globalTtl)) {
                $this->tokens["TTL"] = $this->globalTtl;
            } else {
                throw new SyntaxErrorException($this->stream);
            }
            goto in;
        }

        $this->defaultExtractor('NAME');
        $this->stream->ignoreHorizontalSpace();

        if ($this->isRecordClass()) {
            if (!empty($this->globalTtl)) {
                $this->tokens["TTL"] = $this->globalTtl;
            } elseif (!empty($this->globalOrigin)) {
                $this->tokens["TTL"]  = $this->tokens['NAME'];
                $this->tokens["NAME"] = $this->globalOrigin;
            } else {
                throw new SyntaxErrorException($this->stream);
            }
            goto in;
        }

        $this->defaultExtractor('TTL');
        $this->stream->ignoreHorizontalSpace();
        in:
        $this->ignoreIn();
        $this->stream->ignoreHorizontalSpace();
        $this->defaultExtractor('TYPE');
        $this->stream->ignoreHorizontalSpace();
        $this->extractRData();

        return $this->tokens;
    }

    /**
     * @return bool
     */
    private function isRecordClass() : bool
    {
        if ($this->stream->currentAscii()->is(AsciiChar::I_UPPERCASE())) {
            $this->stream->next();
            if ($this->stream->currentAscii()->is(AsciiChar::N_UPPERCASE())) {
                $this->stream->next();
                if ($this->stream->currentAscii()->isHorizontalSpace()) {
                    $this->stream->previous();
                    $this->stream->previous();

                    return true;
                }
            } else {
                $this->stream->previous();
            }
        }

        return false;
    }

    /**
     * @param string $tokenName
     */
    private function defaultExtractor(string $tokenName)
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
            return;
        } else {
            throw new SyntaxErrorException($this->stream);
        }
    }

    private function ignoreIn()
    {
        if ($this->stream->currentAscii()->is(AsciiChar::I_UPPERCASE())) {
            $this->stream->next();
        } else {
            throw new SyntaxErrorException($this->stream);
        }

        if ($this->stream->currentAscii()->is(AsciiChar::N_UPPERCASE())) {
            $this->stream->next();
        } else {
            throw new SyntaxErrorException($this->stream);
        }
    }

    private function extractRData()
    {
        $this->tokens['RDATA'] = (new RData($this->stream, $this->tokens['TYPE']))->tokenize();
    }
}