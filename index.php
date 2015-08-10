<?php

require 'vendor/autoload.php';

/**
 * @author: Viskov Sergey
 * @date: 8/10/15
 * @time: 2:04 PM
 */
use LTDBeget\dnsZoneParser\DnsZoneParser;

/**
 * @var String $data example file content from dns zone file
 */
$data = file_get_contents(__DIR__."/exampleZone");

$records = DnsZoneParser::parse($data);

print_r($records);