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
                            'ADDRESS' => '::ffff:a.b.c.d',
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
                            'CNAME' => 'cf-ssl00000-protected.example.com.',
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
            9 =>
                array (
                    'NAME' => 'testtxt',
                    'TTL' => '600',
                    'IN' => 'IN',
                    'TYPE' => 'TXT',
                    'RDATA' =>
                        array (
                            'TXTDATA' => 'v=DKIM1; k=rsa; p=MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDZKI3U+9acu3NfEy0NJHIPydxnPLPpnAJ7k2JdrsLqAK1uouMudHI20pgE8RMldB/TeWKXYoRidcGCZWXleUzldDTwZAMDQNpdH1uuxym0VhoZpPbI1RXwpgHRTbCk49VqlC',
                        ),
                ),
            10 =>
                array (
                    'NAME' => 'testmx',
                    'TTL' => '600',
                    'IN' => 'IN',
                    'TYPE' => 'MX',
                    'RDATA' =>
                        array (
                            'PREFERENCE' => '20',
                            'EXCHANGE' => '@',
                        ),
                ),
            11 =>
                array (
                    'NAME' => 'testmx2',
                    'TTL' => '600',
                    'IN' => 'IN',
                    'TYPE' => 'MX',
                    'RDATA' =>
                        array (
                            'PREFERENCE' => '20',
                            'EXCHANGE' => '.',
                        ),
                ),
            12 =>
                array (
                    'NAME' => '_domainkeytxt',
                    'TTL' => '600',
                    'IN' => 'IN',
                    'TYPE' => 'TXT',
                    'RDATA' =>
                        array (
                            'TXTDATA' => 't=y;o=~;\0',
                        ),
                ),
            13 =>
                array (
                    'NAME' => 'www',
                    'TTL' => '600',
                    'IN' => 'IN',
                    'TYPE' => 'TXT',
                    'RDATA' =>
                        array (
                            'TXTDATA' => '@ A 79.125.10.157 ',
                        ),
                ),
            14 =>
                array (
                    'NAME' => 'txt3',
                    'TTL' => '600',
                    'IN' => 'IN',
                    'TYPE' => 'TXT',
                    'RDATA' =>
                        array (
                            'TXTDATA' => 'www CNAME ;;;;\'werwerwer\'\0010',
                        ),
                ),
            15 =>
                array (
                    'NAME' => 'nstest',
                    'TTL' => '300',
                    'IN' => 'IN',
                    'TYPE' => 'NS',
                    'RDATA' =>
                        array (
                            'NSDNAME' => 'ns1',
                        ),
                ),
            16 =>
                array (
                    'NAME' => 'nstest2',
                    'TTL' => '300',
                    'IN' => 'IN',
                    'TYPE' => 'NS',
                    'RDATA' =>
                        array (
                            'NSDNAME' => '85.249.229.194',
                        ),
                ),
            17 =>
                array (
                    'NAME' => 'xn----7sbfndkfpirgcajeli2a4pnc.xn----7sbbfcqfo2cfcagacemif0ap5q',
                    'TTL' => '300',
                    'IN' => 'IN',
                    'TYPE' => 'NS',
                    'RDATA' =>
                        array (
                            'NSDNAME' => '1.1.1.1',
                        ),
                ),
            18 =>
                array (
                    'NAME' => 'casino',
                    'TTL' => '600',
                    'IN' => 'IN',
                    'TYPE' => 'NS',
                    'RDATA' =>
                        array (
                            'NSDNAME' => '@',
                        ),
                ),
            19 =>
                array (
                    'NAME' => 'bonus',
                    'TTL' => '600',
                    'IN' => 'IN',
                    'TYPE' => 'CNAME',
                    'RDATA' =>
                        array (
                            'CNAME' => '@',
                        ),
                ),
            20 =>
                array (
                    'NAME' => '*',
                    'TTL' => '600',
                    'IN' => 'IN',
                    'TYPE' => 'CNAME',
                    'RDATA' =>
                        array (
                            'CNAME' => 's',
                        ),
                ),
            21 =>
                array (
                    'NAME' => '@',
                    'TTL' => '600',
                    'IN' => 'IN',
                    'TYPE' => 'SRV',
                    'RDATA' =>
                        array (
                            'PRIORITY' => '10',
                            'WEIGHT' => '0',
                            'PORT' => '5269',
                            'TARGET' => '@',
                        ),
                ),

            22 =>
                array (
                    'NAME' => 'xmpp',
                    'TTL' => '600',
                    'IN' => 'IN',
                    'TYPE' => 'SRV',
                    'RDATA' =>
                        array (
                            'PRIORITY' => '10',
                            'WEIGHT' => '0',
                            'PORT' => '5222',
                            'TARGET' => '81.211.107.230.',
                        ),
                ),
            23 =>
                array (
                    'NAME' => 'www222',
                    'IN' => 'IN',
                    'TTL' => '0',
                    'TYPE' => 'CNAME',
                    'RDATA' =>
                        array (
                            'CNAME' => 'lifun.ru.',
                        ),
                ),
            24 =>
                array (
                    'NAME' => '46.20.191.35',
                    'TTL' => '3600',
                    'IN' => 'IN',
                    'TYPE' => 'PTR',
                    'RDATA' =>
                        array (
                            'PTRDNAME' => 'office',
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