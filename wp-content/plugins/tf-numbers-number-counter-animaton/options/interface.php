<?php
interface TFNumbersOpsInterface {
    // section data array
    function get_section($prefix);

    // options array
    function get_options();

    // initialization call
    function init($prefix);
}
