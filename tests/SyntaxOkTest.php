<?php
use LTDBeget\dns\Tokenizer;

/**
 * @author: voksiv
 * @date:   13.08.15
 * @time:   13:51
 */

class SyntaxOkTest extends PHPUnit_Framework_TestCase
{

    public function testExampleCom()
    {
        $expected = json_decode('[{"NAME":"example.com.","TTL":"1h","TYPE":"SOA","RDATA":{"MNAME":"ns.example.com.","RNAME":"username.example.com.","SERIAL":"2007120710","REFRESH":"1d","RETRY":"2h","EXPIRE":"4w","MINIMUM":"1h"}},{"NAME":"example.com.","TTL":"1h","TYPE":"NS","RDATA":{"NSDNAME":"ns"}},{"NAME":"example.com.","TTL":"1h","TYPE":"NS","RDATA":{"NSDNAME":"ns.somewhere.example."}},{"NAME":"example.com.","TTL":"1h","TYPE":"MX","RDATA":{"PREFERENCE":"10","EXCHANGE":"mail.example.com."}},{"NAME":"@","TTL":"1h","TYPE":"MX","RDATA":{"PREFERENCE":"20","EXCHANGE":"mail2.example.com."}},{"NAME":"@","TTL":"1h","TYPE":"MX","RDATA":{"PREFERENCE":"50","EXCHANGE":"mail3"}},{"NAME":"example.com.","TTL":"1h","TYPE":"A","RDATA":{"ADDRESS":"192.0.2.1"}},{"NAME":"example.com.","TTL":"1h","TYPE":"AAAA","RDATA":{"ADDRESS":"2001:db8:10::1"}},{"NAME":"ns.example.com.","TTL":"1h","TYPE":"A","RDATA":{"ADDRESS":"192.0.2.2"}},{"NAME":"ns.example.com.","TTL":"1h","TYPE":"AAAA","RDATA":{"ADDRESS":"2001:db8:10::2"}},{"NAME":"www.example.com.","TTL":"1h","TYPE":"CNAME","RDATA":{"CNAME":"example.com."}},{"NAME":"wwwtest.example.com.","TTL":"1h","TYPE":"CNAME","RDATA":{"CNAME":"www"}},{"NAME":"mail.example.com.","TTL":"1h","TYPE":"A","RDATA":{"ADDRESS":"192.0.2.3"}},{"NAME":"mail2.example.com.","TTL":"1h","TYPE":"A","RDATA":{"ADDRESS":"192.0.2.4"}},{"NAME":"mail3.example.com.","TTL":"1h","TYPE":"A","RDATA":{"ADDRESS":"192.0.2.5"}}]', true);
        $config_path = realpath(__DIR__ . "/../zone/syntax_ok/example.com");
        $plain_config = file_get_contents($config_path);
        $this->assertEquals($expected, Tokenizer::tokenize($plain_config));
    }

    public function testInfiniteBugZone()
    {
        $expected = json_decode('[{"NAME":"@","TTL":"3600","TYPE":"SOA","RDATA":{"MNAME":"ns1.beget.ru.","RNAME":"hostmaster.beget.ru.","SERIAL":"2014070701","REFRESH":"3600","RETRY":"600","EXPIRE":"86400","MINIMUM":"3600"}},{"NAME":"@","TTL":"3600","TYPE":"NS","RDATA":{"NSDNAME":"ns1.beget.ru."}},{"NAME":"@","TTL":"3600","TYPE":"NS","RDATA":{"NSDNAME":"ns2.beget.ru."}},{"NAME":"1","TTL":"3600","TYPE":"PTR","RDATA":{"PTRDNAME":"rt01.beget.ru."}},{"NAME":"3","TTL":"3600","TYPE":"PTR","RDATA":{"PTRDNAME":"ns3.beget.ru."}},{"NAME":"5","TTL":"3600","TYPE":"PTR","RDATA":{"PTRDNAME":"ns5.beget.ru."}},{"NAME":"11","TTL":"3600","TYPE":"PTR","RDATA":{"PTRDNAME":"ns1.beget.ru."}},{"NAME":"12","TTL":"3600","TYPE":"PTR","RDATA":{"PTRDNAME":"ns2.beget.com.ua."}}]', true);
        $config_path = realpath(__DIR__ . "/../zone/syntax_ok/infinite.check");
        $plain_config = file_get_contents($config_path);
        $this->assertEquals($expected, Tokenizer::tokenize($plain_config));
    }

    public function testLifunRu()
    {
        $expected = json_decode('[{"NAME":"lifun.ru.","TTL":"14400","TYPE":"SOA","RDATA":{"MNAME":"ns1.lifun.ru.","RNAME":"dns-admin.lifun.ru.","SERIAL":"2009082401","REFRESH":"14400","RETRY":"3600","EXPIRE":"2592000","MINIMUM":"600"}},{"NAME":"lifun.ru.","TTL":"14400","TYPE":"NS","RDATA":{"NSDNAME":"ns1.lifun.ru."}},{"NAME":"lifun.ru.","TTL":"14400","TYPE":"NS","RDATA":{"NSDNAME":"ns1.beget.ru."}},{"NAME":"lifun.ru.","TTL":"14400","TYPE":"A","RDATA":{"ADDRESS":"81.222.198.165"}},{"NAME":"lifun.ru.","TTL":"14400","TYPE":"MX","RDATA":{"PREFERENCE":"0","EXCHANGE":"mail.lifun.ru."}},{"NAME":"*.lifun.ru.","TTL":"14400","TYPE":"A","RDATA":{"ADDRESS":"81.222.198.165"}},{"NAME":"localhost.lifun.ru.","TTL":"14400","TYPE":"A","RDATA":{"ADDRESS":"127.0.0.1"}},{"NAME":"ns1.lifun.ru.","TTL":"14400","TYPE":"A","RDATA":{"ADDRESS":"81.222.198.162"}},{"NAME":"ns2.lifun.ru.","TTL":"14400","TYPE":"A","RDATA":{"ADDRESS":"81.222.131.99"}},{"NAME":"www.lifun.ru.","TTL":"14400","TYPE":"CNAME","RDATA":{"CNAME":"lifun.ru.\n"}}]', true);
        $config_path = realpath(__DIR__ . "/../zone/syntax_ok/lifun.ru");
        $plain_config = file_get_contents($config_path);
        $this->assertEquals($expected, Tokenizer::tokenize($plain_config));
    }

    public function testHell()
    {
        $expected = unserialize('a:25:{i:0;a:4:{s:4:"NAME";s:1:"@";s:3:"TTL";s:3:"300";s:4:"TYPE";s:3:"SOA";s:5:"RDATA";a:7:{s:5:"MNAME";s:13:"ns1.beget.ru.";s:5:"RNAME";s:20:"hostmaster.beget.ru.";s:6:"SERIAL";s:10:"2015060403";s:7:"REFRESH";s:3:"300";s:5:"RETRY";s:3:"600";s:6:"EXPIRE";s:5:"86400";s:7:"MINIMUM";s:3:"300";}}i:1;a:4:{s:4:"NAME";s:1:"@";s:3:"TTL";s:3:"600";s:4:"TYPE";s:1:"A";s:5:"RDATA";a:1:{s:7:"ADDRESS";s:12:"5.101.153.38";}}i:2;a:4:{s:4:"NAME";s:1:"@";s:3:"TTL";s:3:"600";s:4:"TYPE";s:4:"AAAA";s:5:"RDATA";a:1:{s:7:"ADDRESS";s:14:"::ffff:a.b.c.d";}}i:3;a:4:{s:4:"NAME";s:1:"@";s:3:"TTL";s:3:"300";s:4:"TYPE";s:2:"NS";s:5:"RDATA";a:1:{s:7:"NSDNAME";s:13:"ns1.beget.ru.";}}i:4;a:4:{s:4:"NAME";s:30:"_xmpp-server._tcp.icq.beget.ru";s:3:"TTL";s:3:"600";s:4:"TYPE";s:3:"SRV";s:5:"RDATA";a:4:{s:8:"PRIORITY";s:2:"10";s:6:"WEIGHT";s:1:"0";s:4:"PORT";s:4:"5247";s:6:"TARGET";s:6:"jabber";}}i:5;a:4:{s:4:"NAME";s:10:"autoconfig";s:3:"TTL";s:3:"600";s:4:"TYPE";s:5:"CNAME";s:5:"RDATA";a:1:{s:5:"CNAME";s:34:"cf-ssl00000-protected.example.com.";}}i:6;a:4:{s:4:"NAME";s:10:"arhangelsk";s:3:"TTL";s:3:"600";s:4:"TYPE";s:2:"MX";s:5:"RDATA";a:2:{s:10:"PREFERENCE";s:2:"10";s:8:"EXCHANGE";s:13:"mx2.beget.ru.";}}i:7;a:4:{s:4:"NAME";s:1:"@";s:3:"TTL";s:3:"600";s:4:"TYPE";s:3:"TXT";s:5:"RDATA";a:1:{s:7:"TXTDATA";s:27:"Some text another Some text";}}i:8;a:4:{s:4:"NAME";s:4:"test";s:3:"TTL";s:3:"600";s:4:"TYPE";s:3:"TXT";s:5:"RDATA";a:1:{s:7:"TXTDATA";s:27:"Some text another Some text";}}i:9;a:4:{s:4:"NAME";s:7:"testtxt";s:3:"TTL";s:3:"600";s:4:"TYPE";s:3:"TXT";s:5:"RDATA";a:1:{s:7:"TXTDATA";s:188:"v=DKIM1; k=rsa; p=MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDZKI3U+9acu3NfEy0NJHIPydxnPLPpnAJ7k2JdrsLqAK1uouMudHI20pgE8RMldB/TeWKXYoRidcGCZWXleUzldDTwZAMDQNpdH1uuxym0VhoZpPbI1RXwpgHRTbCk49VqlC";}}i:10;a:4:{s:4:"NAME";s:6:"testmx";s:3:"TTL";s:3:"600";s:4:"TYPE";s:2:"MX";s:5:"RDATA";a:2:{s:10:"PREFERENCE";s:2:"20";s:8:"EXCHANGE";s:8:"@;345345";}}i:11;a:4:{s:4:"NAME";s:7:"testmx2";s:3:"TTL";s:3:"600";s:4:"TYPE";s:2:"MX";s:5:"RDATA";a:2:{s:10:"PREFERENCE";s:2:"20";s:8:"EXCHANGE";s:18:".;23\'\'33\'123;;123;";}}i:12;a:4:{s:4:"NAME";s:13:"_domainkeytxt";s:3:"TTL";s:3:"600";s:4:"TYPE";s:3:"TXT";s:5:"RDATA";a:1:{s:7:"TXTDATA";s:10:"t=y;o=~;\0";}}i:13;a:4:{s:4:"NAME";s:3:"www";s:3:"TTL";s:3:"600";s:4:"TYPE";s:3:"TXT";s:5:"RDATA";a:1:{s:7:"TXTDATA";s:18:"@ A 79.125.10.157 ";}}i:14;a:4:{s:4:"NAME";s:4:"txt3";s:3:"TTL";s:3:"600";s:4:"TYPE";s:3:"TXT";s:5:"RDATA";a:1:{s:7:"TXTDATA";s:30:"www CNAME ;;;;\'werwerwer\'\0010";}}i:15;a:4:{s:4:"NAME";s:6:"nstest";s:3:"TTL";s:3:"300";s:4:"TYPE";s:2:"NS";s:5:"RDATA";a:1:{s:7:"NSDNAME";s:3:"ns1";}}i:16;a:4:{s:4:"NAME";s:7:"nstest2";s:3:"TTL";s:3:"300";s:4:"TYPE";s:2:"NS";s:5:"RDATA";a:1:{s:7:"NSDNAME";s:18:"85.249.229.194;111";}}i:17;a:4:{s:4:"NAME";s:63:"xn----7sbfndkfpirgcajeli2a4pnc.xn----7sbbfcqfo2cfcagacemif0ap5q";s:3:"TTL";s:3:"300";s:4:"TYPE";s:2:"NS";s:5:"RDATA";a:1:{s:7:"NSDNAME";s:7:"1.1.1.1";}}i:18;a:4:{s:4:"NAME";s:6:"casino";s:3:"TTL";s:3:"600";s:4:"TYPE";s:2:"NS";s:5:"RDATA";a:1:{s:7:"NSDNAME";s:5:"@;234";}}i:19;a:4:{s:4:"NAME";s:5:"bonus";s:3:"TTL";s:3:"600";s:4:"TYPE";s:5:"CNAME";s:5:"RDATA";a:1:{s:5:"CNAME";s:5:"@;111";}}i:20;a:4:{s:4:"NAME";s:1:"*";s:3:"TTL";s:3:"600";s:4:"TYPE";s:5:"CNAME";s:5:"RDATA";a:1:{s:5:"CNAME";s:5:"s;111";}}i:21;a:4:{s:4:"NAME";s:1:"@";s:3:"TTL";s:3:"600";s:4:"TYPE";s:3:"SRV";s:5:"RDATA";a:4:{s:8:"PRIORITY";s:2:"10";s:6:"WEIGHT";s:1:"0";s:4:"PORT";s:4:"5269";s:6:"TARGET";s:1:"@";}}i:22;a:4:{s:4:"NAME";s:4:"xmpp";s:3:"TTL";s:3:"600";s:4:"TYPE";s:3:"SRV";s:5:"RDATA";a:4:{s:8:"PRIORITY";s:2:"10";s:6:"WEIGHT";s:1:"0";s:4:"PORT";s:4:"5222";s:6:"TARGET";s:15:"81.211.107.230.";}}i:23;a:4:{s:4:"NAME";s:6:"www222";s:3:"TTL";s:1:"0";s:4:"TYPE";s:5:"CNAME";s:5:"RDATA";a:1:{s:5:"CNAME";s:9:"lifun.ru.";}}i:24;a:4:{s:4:"NAME";s:12:"46.20.191.35";s:3:"TTL";s:4:"3600";s:4:"TYPE";s:3:"PTR";s:5:"RDATA";a:1:{s:8:"PTRDNAME";s:11:"office;1231";}}}');
        $config_path = realpath(__DIR__ . "/../zone/syntax_ok/hell");
        $plain_config = file_get_contents($config_path);
        $this->assertEquals($expected, Tokenizer::tokenize($plain_config));
    }

    public function testWrongSoa()
    {
        $expected = json_decode('[{"NAME":"@","TTL":"3600","TYPE":"SOA","RDATA":{"MNAME":"ns1.beget.ru.","RNAME":"hostmaster.beget.ru.","SERIAL":"2013041517","REFRESH":"3600","RETRY":"600","EXPIRE":"86400","MINIMUM":"3600"}},{"NAME":"@","TTL":"3600","TYPE":"NS","RDATA":{"NSDNAME":"ns1.beget.ru."}},{"NAME":"@","TTL":"3600","TYPE":"NS","RDATA":{"NSDNAME":"ns2.beget.ru."}}]', true);
        $config_path = realpath(__DIR__ . "/../zone/syntax_ok/wrong.soa.bug");
        $plain_config = file_get_contents($config_path);
        $this->assertEquals($expected, Tokenizer::tokenize($plain_config));
    }
}