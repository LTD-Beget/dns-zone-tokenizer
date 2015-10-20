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
        $this->data = unpack('C*', $data);
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

        foreach($this->parsedRData as $key => $value) {
            if(!empty($value) || $value == "0") { // do not ask for this
                $parsedRecord["RDATA"][$key] = $value;
            }
        }

        return $parsedRecord;
    }

    /**
     * Start of parsing algorithm
     * @return array
     */
    protected function extractRecords()
    {
        do {
            $this->extractRecord();
            $current = current($this->data);

        } while($current != 0);
    }

    /**
     * Start of parsing single record
     */
    protected function extractRecord()
    {
        $ord = $this->getMeaningfulSymbol();

        if($ord !== 0) {
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
        start:
        $ord = current($this->data);
        next($this->data);

        if(($ord == 10 || $ord == 32) && !$comment_open) { // if space or line end
            goto start;
        } elseif($ord === 59) { // ;
            $comment_open = true;
            goto start;
        } elseif($comment_open && $ord == 10) {
            goto start;
        } elseif($comment_open && $ord != 10) {
            $comment_open = true;
            goto start;
        }

        return $ord;
    }


    /**
     * Parse name of resource record
     * @param bool $starts
     * @throws ResourceRecordParseException
     */
    protected function extractName($starts = false)
    {
        start:
        $ord = current($this->data);
        next($this->data);

        if($ord == 0) { // if end of record
            return;
        }

        if($ord === 32 && !$starts) { // ignore space at start
            goto start;
        } elseif($ord === 32) { // name ends
            $this->extractTtl();
        } else { // add char to name
            $this->parsedRecord["NAME"] .= chr($ord);
            $starts = true;
            goto start;
        }
    }

    /**
     * Parse ttl from resource record
     * @throws ResourceRecordParseException
     */
    protected function extractTtl()
    {
        start:
        $ord = current($this->data);
        next($this->data);

        if($ord == 0) { // if end of record
            return;
        }

        if($ord === 32 && $this->parsedRecord["TTL"] === "") { // ignore space at start
            goto start;
        } elseif($ord === 32) { // name ends
            $this->extractIn();
        } elseif($ord >= 48 && $ord <= 57) { // add char to tll if number
            $this->parsedRecord["TTL"] .= chr($ord);
            goto start;
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
        start:

        $ord = current($this->data);
        next($this->data);

        if($ord == 0) { // if end of record
            return;
        }

        if($ord === 32 && $this->parsedRecord["IN"] === "") {
            goto start;
        } elseif($ord === 73 || $ord === 105) { // 73 = I; 105 = i
            $this->parsedRecord["IN"] .= chr($ord);
            goto start;
        } elseif(($ord === 78 || $ord === 110) && ($this->parsedRecord["IN"] === "I" || $this->parsedRecord["IN"] === "i")) {  // 78 = N; 110 = n
            $this->parsedRecord["IN"] .= chr($ord);
            goto start;
        } elseif($ord === 32) {
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
        start:

        $ord = current($this->data);
        next($this->data);

        if($ord == 0) { // if end of record
            return;
        }

        if($ord === 32 && $this->parsedRecord["TYPE"] === "") { // ignore space at start
            goto start;
        } elseif($ord === 32) { // name ends
            $this->extractRData($this->parsedRecord["TYPE"]);
            return;
        } elseif($ord >= 65 && $ord <= 90) { // add char to type if uppercase letter
            $this->parsedRecord["TYPE"] .= chr($ord);
            goto start;
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
        $this->defaultRDataExtractor("ADDRESS");
        $this->endRecord();
        $this->saveParsed();
    }

    protected function extractAaaaData()
    {
        $this->defaultRDataExtractor("ADDRESS");
        $this->endRecord();
        $this->saveParsed();
    }

    protected function extractCnameData()
    {
        $this->defaultRDataExtractor("CNAME");
        $this->endRecord();
        $this->saveParsed();
    }

    protected function extractMxData()
    {
        $this->defaultRDataExtractor("PREFERENCE");
        $this->defaultRDataExtractor("EXCHANGE");
        $this->endRecord();
        $this->saveParsed();
    }

    protected function extractNsData()
    {
        $this->defaultRDataExtractor("NSDNAME");
        $this->endRecord();
        $this->saveParsed();
    }

    protected function extractPtrData()
    {
        $this->defaultRDataExtractor("PTRDNAME");
        $this->endRecord();
        $this->saveParsed();
    }

    protected function extractSoaData()
    {
        $this->defaultRDataExtractor("MNAME");
        $this->defaultRDataExtractor("RNAME");
        $this->defaultRDataExtractor("SERIAL");
        $this->defaultRDataExtractor("REFRESH");
        $this->defaultRDataExtractor("RETRY");
        $this->defaultRDataExtractor("EXPIRE");
        $this->defaultRDataExtractor("MINIMUM");
        $this->endRecord();
        $this->saveParsed();
    }

    protected function extractSrvData()
    {
        $this->defaultRDataExtractor("PRIORITY");
        $this->defaultRDataExtractor("WEIGHT");
        $this->defaultRDataExtractor("PORT");
        $this->defaultRDataExtractor("TARGET");
        $this->endRecord();
        $this->saveParsed();
    }

    protected function extractTxtData()
    {
        $this->extractTxtBlocks();
        $this->endRecord();
        $this->saveParsed();
    }

    /**
     * Parsing rdata which syntax is relatively default
     * @param $rdata_name
     * @param bool|false $comment_open
     */
    protected function defaultRDataExtractor($rdata_name, $comment_open = false)
    {
        start:

        $ord = current($this->data);
        next($this->data);

        if($ord == 0) {
            return;
        }

        if($ord === 40 && !$comment_open) { // multi line opened ; 40 = (
            $this->multiLineOpened = true;
            goto start;
        } elseif($this->multiLineOpened && !$comment_open && $ord === 41) { // multi line closed; 41 = )
            $this->multiLineOpened = false;
            goto start;
        } elseif($this->multiLineOpened && !$comment_open && $ord === 10) { // // multi line end line
            goto start;
        } elseif($ord === 10 && !$comment_open) { // if end of record
            return;
        } else { // meaningfull symbol
            if($ord === 59) { // 59 = ;
                $comment_open = true;
                goto start;
            } elseif(($ord === 10 || $ord === 0) && $comment_open) {
                prev($this->data);
                $comment_open = false;
                goto start;
            } elseif($comment_open) {
                $comment_open = true;
                goto start;
            } elseif(!$comment_open) {
                if($ord === 32 && $this->parsedRData[$rdata_name] === "") { // ignore space at start
                    goto start;
                } elseif($ord === 32) {
                    return;
                } else {
                    $this->parsedRData[$rdata_name] .= chr($ord);
                    goto start;
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
        start:

        $ord = current($this->data);
        next($this->data);

        if($ord == 0) { // if end of record
            return;
        }

        // comment starts
        if($ord === 59) {
            $comment_open = true;
            goto start;
        } elseif($comment_open == true && $ord !== 10) {
            $comment_open = true;
            goto start;
        } elseif($comment_open == true && ($ord === 10 || $ord === 0)) {
            prev($this->data);
            $comment_open = false;
            goto start;
        } else {
            // ignore blanck line
            if($ord === 32) {
                goto start;
            }

            // Find starts of char set
            if($ord === 34 && !$comment_open) { // "
                $this->extractCharSet();
            }

            // multi line opened
            if($ord === 40 && !$comment_open) {
                $this->multiLineOpened = true;
                goto start;
            }

            // multi line closed
            if($this->multiLineOpened && !$comment_open && $ord === 41) {
                $this->multiLineOpened = false;
                goto start;
            }

            // comment end in multi line TXT record
            if($ord === 10 && $comment_open && $this->multiLineOpened) {
                goto start;
            }

            // is record ends?
            if(!$this->multiLineOpened && ($ord === 10 || $ord === 0)) {
                return;
            } elseif($this->multiLineOpened && $ord === 10) {
                goto start;
            }
        }
    }

    /**
     * Parsing TXT record char set
     * @param bool|false $escaping_open
     */
    protected function extractCharSet($escaping_open = false)
    {
        start:
        $ord = current($this->data);
        next($this->data);

        if($ord == 0) { // if end of record
            return;
        }

        if(!$escaping_open && $ord === 34) {
            $this->extractTxtBlocks();
        } else {
            $this->parsedRData["TXTDATA"] .= chr($ord);
            $escaping_open = ($ord === 92 and !$escaping_open);
            goto start;
        }
    }

    /**
     * End parsing of resource record ignoring comments, blank lines
     * and close multiple line if was opened
     * @param bool|false $comment_open
     */
    protected function endRecord($comment_open = false)
    {
        start:
        $ord = current($this->data);

        if($ord == 0) { // if end of record
            return;
        }

        if($ord === 59) { // 59 = ;
            next($this->data);
            $comment_open = true;
            goto start;
        } elseif($comment_open) {
            if(($ord === 0 || $ord === 10)) {
                $comment_open = false;
                goto start;
            } else {
                next($this->data);
                $comment_open = true;
                goto start;
            }
        } elseif(!$comment_open)  {
            if($this->multiLineOpened) {
                if($ord === 41) { // 41 = )
                    $this->multiLineOpened = false;
                }
                next($this->data);
                goto start;
            } elseif(($ord === 0 || $ord === 10)) {
                return;
            }
        }
    }

    protected function __construct(){}

    private function __clone(){}
}