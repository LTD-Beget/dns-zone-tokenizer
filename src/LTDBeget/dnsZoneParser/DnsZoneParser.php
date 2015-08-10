<?php
/**
 * @author: Viskov Sergey
 * @date: 8/4/15
 * @time: 9:31 PM
 */


namespace LTDBeget\dnsZoneParser;


/**
 * FiniteStateMachine for parsing dns zone files
 *
 * Class
 * @package beget\lib\dns\lib\parser
 */
class DnsZoneParser
{
    /**
     * Machine will parse plain data in array of records
     * which filling depends on record type
     * @param String $data content of dns zone file as string
     * @return array result of parsing
     * @throws ResourceRecordParseException
     */
    public static function parse($data)
    {
        return self::getInstance()->analyze($data);
    }

    /**
     * Spited by chars plain string
     * @var Array
     */
    protected $data;

    /**
     * Result of parsed data
     * @var array
     */
    protected $parsedRecords = [];

    /**
     * Here store result of parsing single record
     * while it does not end
     * @var array
     */
    protected $parsedRecord;

    /**
     * Here store result of parsing single record RDATA
     * @var array
     */
    protected $parsedRData;

    /**
     * @internal
     * Flag for store info, that multiple lines record starts
     * @var bool
     */
    protected $multiLineOpened = false;

    /**
     * singleton
     * @var DnsZoneParser
     */
    protected static $instance = null;

    /**
     * singleton init and store in $instance
     * @return $this
     */
    protected static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * @param String $data
     * @return array
     */
    protected function analyze($data)
    {
        // init FiniteStateMachine
        $finiteStateMachine = self::getInstance();

        $finiteStateMachine->defineData($data);

        // Run analyze
        $finiteStateMachine->extractRecords();

        $result = $this->parsedRecords;
        $this->parsedRecords = [];
        return $result;
    }

    /**
     * Split data on array of chars
     * @param String $data
     */
    protected function defineData($data)
    {
        $this->data = str_split($data, 1);
    }

    /**
     * fill parsedRecord and parsedRData
     * with all possible keys
     *
     * @return void
     */
    protected function prepare()
    {
        $this->parsedRecord = [
            "NAME"  => "",
            "TTL"   => "",
            "IN"    => "",
            "TYPE"  => ""
        ];
        $this->parsedRData = [
            "ADDRESS" => "",
            "CNAME"   => "",
            "PREFERENCE" => "",
            "EXCHANGE"  => "",
            "NSDNAME"  => "",
            "PTRDNAME" => "",
            "MNAME" => "",
            "RNAME" => "",
            "SERIAL" => "",
            "REFRESH" => "",
            "RETRY" => "",
            "EXPIRE" => "",
            "MINIMUM" => "",
            "SERVICE" => "",
            "PROTOCOL" => "",
            "PRIORITY" => "",
            "WEIGHT" => "",
            "PORT" => "",
            "TARGET" => "",
            "TXTDATA" => ""
        ];
    }

    /**
     * Save result of parsing single record in array of parsedRecords
     */
    protected function saveParsed()
    {
        $this->parsedRecords[] = $this->getParsedData();
        // Clear data before end
        $this->clear();
        return;
    }

    /**
     * clear data of parsedRecord and parsedRData
     * @return void
     */
    protected function clear()
    {
        $this->parsedRecord = null;
        $this->parsedRData = null;
    }

    /**
     * get result of parsing single record
     * @return array
     */
    protected function getParsedData()
    {
        // getting result;
        $parsedRecord = $this->parsedRecord;

        // remove empty fields
        $parsedRecord["RDATA"] = array_filter($this->parsedRData, function($value){return ($value !== NULL && $value !== FALSE && $value !== '');});

        return $parsedRecord;
    }

    /**
     * Start of parsing algorithm
     * @return array
     */
    protected function extractRecords()
    {
        $current = true;
        while($current) { // while char in data not end of file
            $this->extractRecord();
            $current = ord(current($this->data));
        }
    }

    /**
     * Start of parsing single record
     */
    protected function extractRecord()
    {
        $char = $this->getMeaningfulSymbol();

        if(ord($char) != 0) {
            //Preparing record data for analyze
            $this->prepare();

            prev($this->data);

            //Run analyze
            $this->extractName();
        }
    }

    /**
     * Ignoring comments, blank lines, end lines at start of resource record
     * @param bool|false $comment_open
     * @return mixed
     */
    protected function getMeaningfulSymbol($comment_open = false)
    {
        $char = current($this->data);
        next($this->data);
        $ord = ord($char);
        if(($ord == 10 || $ord == 32) && !$comment_open) { // if space or line end
            $char = $this->getMeaningfulSymbol();
        } elseif($char === ";") {
            $char = $this->getMeaningfulSymbol(true);
        } elseif($comment_open && $ord == 10) {
            $char = $this->getMeaningfulSymbol();
        } elseif($comment_open && $ord != 10) {
            $char = $this->getMeaningfulSymbol(true);
        }

        return $char;
    }


    /**
     * Parse name of resource record
     * @param bool $starts
     * @throws ResourceRecordParseException
     */
    protected function extractName($starts = false)
    {
        $char = current($this->data);
        next($this->data);

        if(ord($char) == 0) { // if end of record
            return;
        }

        if($char === " " && !$starts) { // ignore space at start
            $this->extractName();
        } elseif($char === " ") { // name ends
            $this->extractTtl();
        } else { // add char to name
            $this->parsedRecord["NAME"] .= $char;
            $this->extractName(true);
        }
    }

    /**
     * Parse ttl from resource record
     * @throws ResourceRecordParseException
     */
    protected function extractTtl()
    {
        $char = current($this->data);
        next($this->data);

        if(ord($char) == 0) { // if end of record
            return;
        }

        if($char === " " && $this->parsedRecord["TTL"] === "") { // ignore space at start
            $this->extractTtl();
        } elseif($char === " ") { // name ends
            $this->extractIn();
        } elseif(ord($char) >= 48 && ord($char) <= 57) { // add char to tll if number
            $this->parsedRecord["TTL"] .= $char;
            $this->extractTtl();
        } else {
            throw new ResourceRecordParseException("Failed parse ttl, digit expects;");
        }
    }

    /**
     * Parse class (IN) from resource record
     * @throws ResourceRecordParseException
     */
    protected function extractIn()
    {
        $char = current($this->data);
        next($this->data);
        if(ord($char) == 0) { // if end of record
            return;
        }

        if($char === " " && $this->parsedRecord["IN"] === "") {
            $this->extractIn();
        } elseif($char === "I") { // name ends
            $this->parsedRecord["IN"] .= $char;
            $this->extractIn();
        } elseif($char === "N" && $this->parsedRecord["IN"] === "I") {
            $this->parsedRecord["IN"] .= $char;
            $this->extractIn();
        } elseif($char === " ") {
            $this->extractType();
        } else {
            throw new ResourceRecordParseException("Failed parse IN");
        }
    }

    /**
     * Parse type of resource record
     * @throws ResourceRecordParseException
     */
    protected function extractType()
    {
        $char = strtoupper(current($this->data));
        next($this->data);

        if(ord($char) == 0) { // if end of record
            return;
        }

        if($char === " " && $this->parsedRecord["TYPE"] === "") { // ignore space at start
            $this->extractType();
        } elseif($char === " ") { // name ends
            $this->extractRData($this->parsedRecord["TYPE"]);
            return;
        } elseif(ord($char) >= 65 && ord($char) <= 90) { // add char to type if uppercase letter
            $this->parsedRecord["TYPE"] .= $char;
            $this->extractType();
        } else {
            throw new ResourceRecordParseException("Failed parse type, letter expects;");
        }
    }

    /**
     * Define type of RDATA
     * @param String $type type of resource record
     * @throws ResourceRecordParseException
     */
    protected function extractRData($type)
    {
        switch($type) {
            case "A":
                $this->extractAData();
                break;
            case "AAAA":
                $this->extractAaaaData();
                break;
            case "CNAME":
                $this->extractCnameData();
                break;
            case "MX":
                $this->extractMxData();
                break;
            case "NS":
                $this->extractNsData();
                break;
            case "PTR":
                $this->extractPtrData();
                break;
            case "SOA":
                $this->extractSoaData();
                break;
            case "SRV":
                $this->extractSrvData();
                break;
            case "TXT":
                $this->extractTxtData();
                break;
            default:
                throw new ResourceRecordParseException("Failed parse RDATA, Unknown type: $type;");
                break;
        }
    }

    protected function extractAData()
    {
        $this->extractAddress();
        $this->endRecord();
        $this->saveParsed();
    }

    protected function extractAaaaData()
    {
        $this->extractAddress();
        $this->endRecord();
        $this->saveParsed();
    }

    protected function extractCnameData()
    {
        $this->extractCname();
        $this->endRecord();
        $this->saveParsed();
    }

    protected function extractMxData()
    {
        $this->extractPreference();
        $this->extractExchange();
        $this->endRecord();
        $this->saveParsed();
    }

    protected function extractNsData()
    {
        $this->extractNsDName();
        $this->endRecord();
        $this->saveParsed();
    }

    protected function extractPtrData()
    {
        $this->extractPtrDName();
        $this->endRecord();
        $this->saveParsed();
    }

    protected function extractSoaData()
    {
        $this->extractMName();
        $this->extractRName();
        $this->extractSerial();
        $this->extractRefresh();
        $this->extractRetry();
        $this->extractExpire();
        $this->extractMinimum();
        $this->endRecord();
        $this->saveParsed();
    }

    protected function extractSrvData()
    {
        $this->extractPriority();
        $this->extractWeight();
        $this->extractPort();
        $this->extractTarget();
        $this->endRecord();
        $this->saveParsed();
    }

    protected function extractTxtData()
    {
        $this->extractTxtBlocks();
        $this->endRecord();
        $this->saveParsed();
    }

    protected function extractAddress()
    {
        $this->defaultRDataExtractor("ADDRESS");
    }

    protected function extractCname()
    {
        $this->defaultRDataExtractor("CNAME");
    }

    protected function extractPreference()
    {
        $this->defaultRDataExtractor("PREFERENCE");
    }

    protected function extractExchange()
    {
        $this->defaultRDataExtractor("EXCHANGE");
    }

    protected function extractNsDName()
    {
        $this->defaultRDataExtractor("NSDNAME");
    }

    protected function extractPtrDName()
    {
        $this->defaultRDataExtractor("PTRDNAME");
    }

    protected function extractMName()
    {
        $this->defaultRDataExtractor("MNAME");
    }

    protected function extractRName()
    {
        $this->defaultRDataExtractor("RNAME");
    }

    protected function extractSerial()
    {
        $this->defaultRDataExtractor("SERIAL");
    }

    protected function extractRefresh()
    {
        $this->defaultRDataExtractor("REFRESH");
    }

    protected function extractRetry()
    {
        $this->defaultRDataExtractor("RETRY");
    }

    protected function extractExpire()
    {
        $this->defaultRDataExtractor("EXPIRE");
    }

    protected function extractMinimum()
    {
        $this->defaultRDataExtractor("MINIMUM");
    }

    protected function extractPriority()
    {
        $this->defaultRDataExtractor("PRIORITY");
    }

    protected function extractWeight()
    {
        $this->defaultRDataExtractor("WEIGHT");
    }

    protected function extractPort()
    {
        $this->defaultRDataExtractor("PORT");
    }

    protected function extractTarget()
    {
        $this->defaultRDataExtractor("TARGET");
    }

    /**
     * Parsing rdata which syntax is relatively default
     * @param $rdata_name
     * @param bool|false $comment_open
     */
    protected function defaultRDataExtractor($rdata_name, $comment_open = false)
    {
        $char = current($this->data);
        next($this->data);
        $ord = ord($char);

        if($ord == 0) {
            return;
        }

        if($char == "(" && !$comment_open) { // multi line opened
            $this->multiLineOpened = true;
            $this->defaultRDataExtractor($rdata_name);
        } elseif($this->multiLineOpened && !$comment_open && $char == ")") { // multi line closed
            $this->multiLineOpened = false;
            $this->defaultRDataExtractor($rdata_name);
        } elseif($this->multiLineOpened && !$comment_open && $ord == 10) { // // multi line end line
            $this->defaultRDataExtractor($rdata_name);
        } elseif($ord == 10 && !$comment_open) { // if end of record
            return;
        } else { // meaningfull symbol
            if($char == ";") {
                $this->defaultRDataExtractor($rdata_name, true);
            } elseif(($ord == 10 || $ord == 0) && $comment_open) {
                prev($this->data);
                $this->defaultRDataExtractor($rdata_name, false);
            } elseif($comment_open) {
                $this->defaultRDataExtractor($rdata_name, true);
            } elseif(!$comment_open) {
                if($char === " " && $this->parsedRData[$rdata_name] === "") { // ignore space at start
                    $this->defaultRDataExtractor($rdata_name);
                } elseif($char === " ") {
                    return;
                } else {
                    $this->parsedRData[$rdata_name] .= $char;
                    $this->defaultRDataExtractor($rdata_name);
                }
            }
        }
    }

    /**
     * Parsing rdata for TXT resource record
     * @param bool|false $comment_open
     */
    protected function extractTxtBlocks($comment_open = false)
    {
        $char = current($this->data);
        next($this->data);
        $ord = ord($char);
        if(ord($char) == 0) { // if end of record
            return;
        }

        // comment starts
        if($char == ";" ) {
            $this->extractTxtBlocks(true);
        } elseif($comment_open == true && $ord != 10) {
            $this->extractTxtBlocks(true);
        } elseif($comment_open == true && ($ord == 10 || $ord == 0)) {
            prev($this->data);
            $this->extractTxtBlocks(false);
        } else {
            // ignore blanck line
            if($char === " ") {
                $this->extractTxtBlocks($comment_open);
            }

            // Find starts of char set
            if($ord === 34 && !$comment_open) { // "
                $this->extractCharSet();
            }

            // multi line opened
            if($char == "(" && !$comment_open) {
                $this->multiLineOpened = true;
                $this->extractTxtBlocks();
            }

            // multi line closed
            if($this->multiLineOpened && !$comment_open && $char == ")") {
                $this->multiLineOpened = false;
                $this->extractTxtBlocks();
            }

            // comment end in multi line TXT record
            if($ord == 10 && $comment_open && $this->multiLineOpened) {
                $this->extractTxtBlocks();
            }

            // is record ends?
            if(!$this->multiLineOpened && ($ord == 10 || $ord == 0)) {
                return;
            } elseif($this->multiLineOpened && $ord == 10) {
                $this->extractTxtBlocks();
            }
        }
    }

    /**
     * Parsing TXT record char set
     * @param bool|false $escaping_open
     */
    protected function extractCharSet($escaping_open = false)
    {
        $char = current($this->data);
        next($this->data);
        $ord = ord($char);

        if(ord($char) == 0) { // if end of record
            return;
        }

        if(!$escaping_open && $ord == 34) {
            $this->extractTxtBlocks();
        } else {
            $this->parsedRData["TXTDATA"] .= $char;
            $escaping_open = (ord($char) == 92 and !$escaping_open);
            $this->extractCharSet($escaping_open);
        }
    }

    /**
     * End parsing of resource record ignoring comments, blank lines
     * and close multiple line if was opened
     * @param bool|false $comment_open
     */
    protected function endRecord($comment_open = false)
    {
        $char = current($this->data);
        $ord = ord($char);

        if(ord($char) == 0) { // if end of record
            return;
        }

        if($char == ";") {
            next($this->data);
            $this->endRecord(true);
        } elseif($comment_open) {
            if(($ord == 0 || $ord == 10)) {
                $this->endRecord(false);
            } else {
                next($this->data);
                $this->endRecord(true);
            }
        } elseif(!$comment_open)  {
            if($this->multiLineOpened) {
                if($char == ")") {
                    $this->multiLineOpened = false;
                }
                next($this->data);
                $this->endRecord();
            } elseif(($ord == 0 || $ord == 10)) {
                return;
            }
        }
    }

    protected function __construct(){}

    private function __clone(){}
}