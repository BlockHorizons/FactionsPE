<?php
namespace localizer;

use pocketmine\event\TextContainer;

class Translatable extends TextContainer {
  
  /**
   * ISO-639-1 language code
   * @type string 
   */
  public $locale;
   
  /** @type array */
  public $params;
  
  /** @type string|null */
  public $default;
    
   /** @type string */
  public $key;
  
  public function __construct(string $key, array $params = [], string $default = null, string $locale = Localizer::DEFAULT_LOCALE) {
    $this->key = $key;
    $this->params = $params;
    $this->default = $default;
    $this->locale = $locale;
    return $this;
  }
  
  /*
   * ------------------------------------------------------------
   * FLUENT SETTERS
   * -----------------------------------------------------------
   *
   */
  
  public function setLocale(string $locale) : Translatable {
    $this->locale = $locale;
    return $this;
  }
  
  public function setParams(array $params) : Translatable {
    $this->params = $params; 
    return $this;
  }
  
  public function setParam(string $name, $value) : Translatable {
    $this->params[$name] = $value;
    return $this;
  }
  
  public function setDefault($default) : Translatable {
    $this->default = $default; 
    return $this;
  }

  public function getKey() : string {
    return $this->key;
  }
  
  /*
   * ------------------------------------------------------------
   * TRANSLATE
   * -----------------------------------------------------------
   *
   */
  
  public function get(string $locale = null) {
    $locale = $locale ?? $this->locale;
    return Localizer::{$locale}($this->key, $this->params, $this->default);
  }
  
  public function getText() {
   return $this->get(); 
  }
  
  public function __toString() {
    return $this->getText(); 
  }
  
}
