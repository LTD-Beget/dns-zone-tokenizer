<?php
use LTDBeget\dns\Tokenizer;

/**
 * @author: Viskov Sergey
 * @date  : 4/14/16
 * @time  : 8:35 PM
 */
class SyntaxErrorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \LTDBeget\dns\SyntaxErrorException
     */
    public function testWtfZone()
    {
        $config_path = realpath(__DIR__ . "/../zone/syntax_error/wtf.zone");
        $plain_config = file_get_contents($config_path);
        Tokenizer::tokenize($plain_config);
    }
}
