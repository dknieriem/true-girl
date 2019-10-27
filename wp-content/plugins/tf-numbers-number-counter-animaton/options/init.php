<?php

Class TFNumbersOptions {

  private $prefix;

  function __construct($prefix) {
    $this->prefix = $prefix;
    $this->includes();

    $this->layouts()->init($prefix);
    $this->numbers()->init($prefix);
    $this->general()->init($prefix);
    $this->numbers_style()->init($prefix);
    $this->additional()->init($prefix);
  }

  private function general() {
    $prefix = $this->prefix;
    return new \TFNumbersOptions\General($prefix);
  }

  private function numbers_style() {
    $prefix = $this->prefix;
    return new \TFNumbersOptions\NumbersStyle($prefix);
  }

  private function layouts() {
    $prefix = $this->prefix;
    return new \TFNumbersOptions\Layouts($prefix);
  }

  private function numbers() {
    $prefix = $this->prefix;
    return new \TFNumbersOptions\Numbers($prefix);
  }

  private function additional() {
    $prefix = $this->prefix;
    return new TFNumbersAdditionalOps;
  }

  private function includes() {
    require_once 'interface.php';
    require_once 'layouts.php';
    require_once 'general.php';
    require_once 'numbers.php';
    require_once 'numbers-style.php';
    require_once 'additional.php';
  }
}
