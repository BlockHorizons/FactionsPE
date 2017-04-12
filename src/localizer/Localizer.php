<?php
namespace localizer;
/*
 *   Localizer: Easy to use locale controller
 *   Copyright (C) 2016  Chris Prime
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

use localizer\iso\ISO_639_1;

/**
 * Easy to use translate package
 *
 * @author Chris Prime
 */
class Localizer {

	/** @var Localizer[] */
	private static $localizers = [];
	
	/**
	 * The default language
	 */
	const DEFAULT_LOCALE = "en";
  
	/**
	 * Check whetever language exists by country name or code
	 * @return bool
	 */
	public static function checkLanguageExistence(string $identifier) : bool {
		$identifier = strtolower($identifier);
		foreach(ISO_639_1::LANGUAGES as $code => $name) {
			if($identifier === $code || $identifier === strtolower($name)) return true;
		}
		return false;
	}
	/**
	 * Returns the country, owner of the language $code or undefined if unknown code
	 * @return string
	 */
	public static function getCountryByCode(string $code) : string {
		return ISO_639_1::LANGUAGES[$code] ?? "undefined";
	}
	/**
	 * The translate texts
	 * @var array
	 */
	private $data = [];

	/**
	 * The language
	 * @var string
	 */
	private $locale;

	public static $globalLocale = self::DEFAULT_LOCALE;

	/*
	 * Formats
	 */

	public $datetime_short = "";
	public $datetime_long = "";
	public $date_short = "";
	public $date_long = "";
	public $time = "";
	
	private static $parser;

	/**
	 *
	 * @param string $locale ISO-639 code
	 * @param string $directory of languages
	 * @param string $fallbackLocale = null
	 */
	public function __construct(string $locale, string $directory) {
		if(self::checkLanguageExistence($locale)) {
			$this->locale = $locale;
			$this->loadLanguage($directory, $locale);
		} else {
			throw new \InvalidArgumentException("Language '$locale' does not exist");
		}
		self::$localizers[strtolower(trim($this->locale))][] = $this;
	}
	
	/**
	 * Deletes all loaded data
	 */
	public static function clean() {
		self::$localizers = [];	
	}
	
	/**
	 * Loads the language files from directory
	 *
	 * @param string $directory from where
	 * @param string $locale to load
	 */
	public function loadLanguage(string $directory, string $locale) {
		if(($path = realpath($directory))) 
		{
			if( ($pathToLocale = realpath($path . "/" . $locale)) )
			{
				foreach((new \DirectoryIterator($pathToLocale)) as $file) 
				{
					if($file->isDot()) continue;
					if(!$file->isFile()) continue;
					$this->loadLanguageFile($file->getPathname());
				}
			} else 
			{
				throw new \InvalidArgumentException("locale directory '$directory/$locale' doesn't exist");
			}
		} 
		else 
		{
			throw new \InvalidArgumentException("directory '$directory' doesn't exist");
		}
	}
	/**
	 * Loads language file
	 * @param string $file
	 * @return bool
	 */
	public function loadLanguageFile(string $file) : bool {
		if(file_exists($file)) 
		{
			$name = basename($file, substr($file, strpos($file, ".")));
			$data = $this->getDataFromFile($file);
			// Parse the data
			foreach($data as $key => $value) {
				switch($key) {
					case '__datetime_short':
						$this->datetime_short = $value;
						break;
					case '__datetime_long':
						$this->datetime_long = $value;
						break;
					case '__date_short':
						$this->date_short = $value;
						break;
					case '__date_long':
						$this->date_long = $value;
						break;
					case '__time':
						$this->time = $value;
						break;
					default: break;
				}
			}
			$this->data[$name] = $data;
			return true;
		}
		return false;
	}
	
	public static function setParser(callable $parser) {
		self::$parser = $parser;	
	}
	
	/**
	 * Read language file. Supported format: php (default), json and yaml.
	 * @param string $file
	 * @return array
	 * @throw \Exception
	 */
	public function getDataFromFile(string $file) : array {
		$ext = @end(explode('.', $file));
		$content = file_get_contents($file);
		switch($ext) {
			default:
			case 'php':
				if(($pos = strpos($content, "return ")) !== false) {
					$content = substr($content, $pos);
				} else {
					throw new \Exception("invalid language file php. no translations/return got");
				}
				$data = eval($content);
				break;
			case 'yml':
			case 'yaml':
				if(extension_loaded("yaml")) {
					$data = yaml_parse($content);
					if(!$data) {
						throw new \Exception("invalid language file yaml. decoding error");
					}
				} else {
					$data = [];
				}
				break;
			case 'json':
				$data = json_decode($content, true);
				if(!$data) {
					throw new \Exception("invalid language file json. decoding error: " . json_last_error());
				}
				break;
		}
		// Validate data
		if(is_array($data)) {
			foreach($data as $k => $t) {
				if(is_array($t) || is_object($t)) throw new \Exception("invalid data type under key '$k'");
			}
		} else {
			throw new \Exception("file '$file' didn't return data in array format");
		}
		return $data;
	}

	public function get(string $identifier, array $params = [], string $default = null) : string {
		$ps = explode(".", $identifier);
		if(count($ps) > 1) {
			$name = $ps[0];
			$key = $ps[1];
		} else {
			// Search automatically
			foreach($this->data as $n => $data) {
				foreach($data as $k => $t) {
					if($identifier === $k) {
						$name = $n;
						break 2;
					}
				}
			}
			$key = $identifier;
		}
		if(!isset($name)) 
			$text = $default ?? $identifier;
		else
			$text = $this->data[$name][$key] ?? ($default ?? $identifier);
		foreach ($params as $name => $value) {
			$text = str_replace(":$name", $value, $text);
		}
		return self::$parser ? call_user_func(self::$parser, $text) : $text;
	}

	public static function trans(string $key, array $params = [], string $default = null, $locale = null) {
		$locale = $locale ?? self::$globalLocale;
		$key = strtolower($key);
		if(isset(self::$localizers[$locale])) {
			foreach(self::$localizers[$locale] as $localizer) {
				if(($r = $localizer->get($key, $params, $default)) !== $key) return $r;
			}
		}
		return $r ?? $key;
	}
	
	public static function translatable(string $key, array $params = [], string $default = null, $locale = self::DEFAULT_LOCALE) : Translatable {
		return new Translatable($key, $params, $default, $locale);	
	}

	/**
	 * Copies $source folder and its contents recursively to $target
	 *
	 * @param string $source
	 * @param string $target
	 */
	public static function transferLanguages(string $source, string $target) {
	    $dir = opendir($source); 
	    @mkdir($target); 
	    while(false !== ( $file = readdir($dir)) ) { 
	        if (( $file != '.' ) && ( $file != '..' )) { 
	            if ( is_dir($source . '/' . $file) ) { 
	            	// recursive
	                self::transferLanguages($source . '/' . $file,$target . '/' . $file); 
	            } 
	            else { 
	                copy($source . '/' . $file,$target . '/' . $file); 
	            } 
	        } 
	    } 
	    closedir($dir); 
	}

	/**
	 * Loads all files from sub-directories of $directory
	 * @param string $directory source
	 */
	public static function loadLanguages(string $directory) {
		if(is_dir($directory)) {
			foreach(new \DirectoryIterator($directory) as $content) {
				if($content->isFile() || $content->isDot()) continue;
				if($content->isDir()) {
					$locale = $content->getFilename();
				}
				// Now let's load the localizator
				if(self::checkLanguageExistence($locale)) {
					$localizator = new self($locale, $directory);
				} else {
					// Not a lang dir, skip
					continue;
				}
			}
		} else {
			throw new InvalidArgumentException("directory '$directory' does not exist");
		}
	}

	// Handle all dynamic calls

	public static function __callStatic(string $name, array $arguments) {
		if(self::checkLanguageExistence($name)) {
			# Localizer::lv('monster', [], null)
			# Localizer::trans('monster', [], null, 'lv')
			$arguments[1] = $arguments[1] ?? [];
			$arguments[2] = $arguments[2] ?? null;
			$arguments[3] = $name;
			return call_user_func_array(__CLASS__."::trans", $arguments);
		}
	}

}
