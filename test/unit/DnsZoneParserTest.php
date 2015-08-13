<?php
/**
 * @author: voksiv
 * @date:   13.08.15
 * @time:   13:51
 */

namespace LTDBeget\dnsZoneParser\test\unit;

use LTDBeget\dnsZoneParser\DnsZoneParser;
use PHPUnit_Framework_TestCase;

class DnsZoneParserTest extends PHPUnit_Framework_TestCase
{
    public function testParse()
    {
        $plain_data = $this->getZoneFileData("exampleZone");
        $expected_parsed_data = array (
            0 =>
                array (
                    'NAME' => '@',
                    'TTL' => '300',
                    'IN' => 'IN',
                    'TYPE' => 'SOA',
                    'RDATA' =>
                        array (
                            'MNAME' => 'ns1.beget.ru.',
                            'RNAME' => 'hostmaster.beget.ru.',
                            'SERIAL' => '2015060403',
                            'REFRESH' => '300',
                            'RETRY' => '600',
                            'EXPIRE' => '86400',
                            'MINIMUM' => '300',
                        ),
                ),
            1 =>
                array (
                    'NAME' => '@',
                    'TTL' => '600',
                    'IN' => 'IN',
                    'TYPE' => 'A',
                    'RDATA' =>
                        array (
                            'ADDRESS' => '5.101.153.38',
                        ),
                ),
            2 =>
                array (
                    'NAME' => '@',
                    'TTL' => '600',
                    'IN' => 'IN',
                    'TYPE' => 'AAAA',
                    'RDATA' =>
                        array (
                            'ADDRESS' => '2607:f0d0:1002:0051:0000:0000:0000:0004',
                        ),
                ),
            3 =>
                array (
                    'NAME' => '@',
                    'TTL' => '300',
                    'IN' => 'IN',
                    'TYPE' => 'NS',
                    'RDATA' =>
                        array (
                            'NSDNAME' => 'ns1.beget.ru.',
                        ),
                ),
            4 =>
                array (
                    'NAME' => '_xmpp-server._tcp.icq.beget.ru',
                    'TTL' => '600',
                    'IN' => 'IN',
                    'TYPE' => 'SRV',
                    'RDATA' =>
                        array (
                            'PRIORITY' => '10',
                            'WEIGHT' => '0',
                            'PORT' => '5247',
                            'TARGET' => 'jabber',
                        ),
                ),
            5 =>
                array (
                    'NAME' => 'autoconfig',
                    'TTL' => '600',
                    'IN' => 'IN',
                    'TYPE' => 'CNAME',
                    'RDATA' =>
                        array (
                            'CNAME' => 'autoconfig.beget.ru.',
                        ),
                ),
            6 =>
                array (
                    'NAME' => 'arhangelsk',
                    'TTL' => '600',
                    'IN' => 'IN',
                    'TYPE' => 'MX',
                    'RDATA' =>
                        array (
                            'PREFERENCE' => '10',
                            'EXCHANGE' => 'mx2.beget.ru.',
                        ),
                ),
            7 =>
                array (
                    'NAME' => '@',
                    'TTL' => '600',
                    'IN' => 'IN',
                    'TYPE' => 'TXT',
                    'RDATA' =>
                        array (
                            'TXTDATA' => 'Some text another Some text',
                        ),
                ),
            8 =>
                array (
                    'NAME' => 'test',
                    'TTL' => '600',
                    'IN' => 'IN',
                    'TYPE' => 'TXT',
                    'RDATA' =>
                        array (
                            'TXTDATA' => 'Some text another Some text',
                        ),
                ),
        );
        $this->runParse($plain_data, $expected_parsed_data);
    }

    /**
     * @param String $plain_data
     * @param array $expected_parsed_data
     */
    protected function runParse($plain_data, array $expected_parsed_data)
    {
        /**
         * @var DnsZoneParser $class
         */
        $class = DnsZoneParser::class;
        $this->assertEquals($expected_parsed_data, $class::parse($plain_data));
    }

    /**
     * @return string
     */
    private function getZoneDir()
    {
        return dirname(__DIR__).DIRECTORY_SEPARATOR."zone";
    }

    /**
     * @param $fileName
     * @return string
     */
    private function getZoneFileData($fileName)
    {
        return file_get_contents($this->getZoneDir().DIRECTORY_SEPARATOR.$fileName);
    }
}