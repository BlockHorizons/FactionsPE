<?php
namespace factions\utils;

use factions\FactionsPE;

class Settings {

    const YAML = 0x01;
    const JSON = 0x02;
    
    private static $instance = NULL;

    /**
     * Decoded data
     * @var string[]
     */
    protected $data = [];

    protected $file = "config.file";
    protected $format = self::YAML;
    
    public function __construct(string $file, $format = self::YAML) {
        self::$instance = $this;
        $data = file_get_contents($file);
        $this->format = $format;
        $this->file = $file;
        try {
            switch ($format) {
                case self::YAML:

                    $decoded = yaml_parse($data);
                    if (!$decoded) throw new \RuntimeException("data decode failed");

                    $this->data = $decoded;
                    break;
                case self::JSON:
                    $decoded = json_decode($data, true);
                    if(!$decoded) throw new \RuntimeException("data decode failed");

                    $this->data = $decoded;

                    break;
                default:
                    throw new \InvalidArgumentException("unsupported format type ('" . $format . "')");
            }
        } catch (\Exception $e) {
            FactionsPE::get()->getLogger()->warning("Settings failed to load. Error: ".$e->getMessage());
        }

        FactionsPE::get()->getLogger()->debug("Settings loaded");
    }

    /**
     * Don't use '.' inside of key unless you want to use an array inside of config
     *
     * @param string $key
     * @param null $default
     *
     * @return mixed
     */
    public static function get(string $key, $default = NULL) {
        $data = self::$instance->getAll();
        if(strpos($key, ".")) {
            $keys = explode(".", $key);
            $i = 0;
            while(isset($data[$keys[$i]])) {
                $data = $data[$keys[$i]];
                if(!is_array($data)) return $data;
                $i++;
                if(!isset($keys[$i])) return $data;
            }
            return $default;
        }
        if(isset($data[$key])) return $data[$key];
        return $default;
    }

    public function getFile() : string {
        return $this->file;
    }

    public function setFile(string $file) {
        $this->file = $file;
    }

    public function setFormat($format) {
        $this->format = $format;
    }

    public function getAll(){
        return $this->data;
    }


    public function save() {
        switch ($this->format) {
            case self::YAML:
                $encoded = yaml_emit($this->data);
                if(!$encoded) {
                    throw new \Exception("failed to encode data to yaml");
                }
                break;
            case self::JSON:
                $encoded = json_encode($this->data);
                if(!$encoded) {
                    throw new \Exception("failed to decode data to json");
                }
                break;
            default: 
            throw new \InvalidArgumentException("unsupported format type ('" . $this->format . "')");
        }
        file_put_contents( $this->file, $encoded );
    }
}