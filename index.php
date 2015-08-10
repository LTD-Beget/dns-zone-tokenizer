<?php

/**
 * @author: Viskov Sergey
 * @date: 8/10/15
 * @time: 2:04 PM
 */
use LTDBeget\dnsZoneParser\DnsZoneParser;

/**
 * @var String $data example file content from dns zone file
 */
$data = '@ 300 IN SOA ns1.beget.ru. hostmaster.beget.ru. 2015060403 300 600 86400 300
@ 300 IN NS ns1.beget.ru.
@ 300 IN NS ns2.beget.ru.
@ 300 IN NS ns1.beget.pro.
@ 300 IN NS ns2.beget.pro.
@ 600 IN A 5.101.153.38
@ 600 IN TXT "v=DKIM1; k=rsa; p=MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDH2TSQumVLiA1af8QQolcQZlNvVPiHDsKcmHZPwpLT+VJfFi7tWHqmoC2vt5Q23dG3FDsqJr8C+Fz+coSnvWoogExv2Rlnb+ujbpWBkHh+Cc/WxPIOtWxIWAaePBrSTE5LZ41inDgLJwqKdiY+WZ+LJI00/A4V45rz6QeRIN0y3wIDAQAB"
@ 600 IN TXT "v=spf1 redirect=_spf.mail.ru"
@ 600 IN MX 10 emx.mail.ru.
mailru._domainkey 600 IN A 5.101.153.38
mailru._domainkey 600 IN TXT "v=DKIM1; k=rsa; p=MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDH2TSQumVLiA1af8QQolcQZlNvVPiHDsKcmHZPwpLT+VJfFi7tWHqmoC2vt5Q23dG3FDsqJr8C+Fz+coSnvWoogExv2Rlnb+ujbpWBkHh+Cc/WxPIOtWxIWAaePBrSTE5LZ41inDgLJwqKdiY+WZ+LJI00/A4V45rz6QeRIN0y3wIDAQAB"
mailru._domainkey 600 IN MX 10 emx.mail.ru.
www.mailru._domainkey 600 IN A 5.101.153.38
abakan 600 IN A 5.101.153.38
abakan 600 IN TXT "v=spf1 redirect=beget.ru"
abakan 600 IN MX 10 mx1.beget.ru.
abakan 600 IN MX 20 mx2.beget.ru.
www.abakan 600 IN A 5.101.153.38
www.abakan 600 IN TXT "v=spf1 redirect=beget.ru"
www.abakan 600 IN MX 10 mx1.beget.ru.
www.abakan 600 IN MX 20 mx2.beget.ru.
anadyr 600 IN A 5.101.153.38
anadyr 600 IN TXT "v=spf1 redirect=beget.ru"
anadyr 600 IN MX 10 mx1.beget.ru.
anadyr 600 IN MX 20 mx2.beget.ru.
www.anadyr 600 IN A 5.101.153.38
www.anadyr 600 IN TXT "v=spf1 redirect=beget.ru"
www.anadyr 600 IN MX 10 mx1.beget.ru.
www.anadyr 600 IN MX 20 mx2.beget.ru.';

$records = DnsZoneParser::parse($data);

print_r($records);