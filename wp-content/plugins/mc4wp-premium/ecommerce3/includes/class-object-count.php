<?php

class MC4WP_Ecommerce_Object_Count {

    /**
     * @var int
     */
    public $all;

    /**
     * @var int
     */
    public $tracked;

    /**
     * @var int
     */
    public $untracked;

    /**
     * @var int
     */
    public $percentage;

    /**
     * MC4WP_Ecommerce_Object_Count constructor.
     *
     * @param int $all
     * @param int $untracked
     */
    public function __construct( $all, $untracked = 0 ) {
        $this->all = (int) $all;
        $this->untracked = (int) $untracked;
        $this->tracked = $this->all - $this->untracked;
        $this->percentage = $this->tracked > 0 ? $this->tracked / $this->all * 100 : 0;
    }
}