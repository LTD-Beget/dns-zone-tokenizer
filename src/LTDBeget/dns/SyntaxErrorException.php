<?php
/**
 * @author: Viskov Sergey
 * @date  : 8/5/15
 * @time  : 1:34 PM
 */

namespace LTDBeget\dns;

use Exception;
use LTDBeget\stringstream\StringStream;

/**
 * Class SyntaxErrorException
 *
 * @package LTDBeget\dns
 */
class SyntaxErrorException extends \RuntimeException
{
    /**
     * @var int
     */
    private $error_line;
    /**
     * @var string
     */
    private $unexpected_char;
    /**
     * @var string
     */
    private $messageTemplate = "Parse error: syntax error, unexpected '%s' on line %d.";

    /**
     * SyntaxErrorException constructor.
     *
     * @param StringStream   $stream
     * @param int            $code
     * @param Exception|null $previous
     */
    public function __construct(StringStream $stream, int $code = 0, Exception $previous = NULL)
    {
        if ($stream->isEnd()) {
            $stream->end();
        }

        $this->unexpected_char = $stream->current();
        $this->error_line      = $this->getParseErrorLineNumber($stream);
        $message               = sprintf($this->messageTemplate, $this->unexpected_char, $this->error_line);
        parent::__construct($message, $code, $previous);
    }

    /**
     * @internal
     * @param StringStream $stream
     * @return int
     */
    private function getParseErrorLineNumber(StringStream $stream) : int
    {
        $parse_error_char_position = $stream->position();
        $plain_data                = $this->getFullString($stream);
        $exploded_by_lines         = explode("\n", $plain_data);
        foreach ($exploded_by_lines as $key => $line) {
            $line_length = strlen($line) + 1;
            $parse_error_char_position -= $line_length;
            if ($parse_error_char_position < 0) {
                return $key + 1;
            }
        }

        return 1;
    }

    /**
     * @param StringStream $stream
     * @return string
     */
    private function getFullString(StringStream $stream) : string
    {
        $stream->start();
        $string = '';
        do {
            $string .= $stream->current();
            $stream->next();
        } while (!$stream->isEnd());

        return $string;
    }

    /**
     * @return int
     */
    public function getErrorLine()
    {
        return $this->error_line;
    }

    /**
     * @return string
     */
    public function getUnexpectedChar()
    {
        return $this->unexpected_char;
    }
}