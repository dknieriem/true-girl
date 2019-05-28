<?php
/**
 Plugin Name: Better Bandsintown
 Plugin URI: http://kayvanbree.nl/
 Description: Embed Tour Dates from Bandsintown.com without having to deal with CSS (or an ugly widget).
 Version: 0.4.2
 Author: Kay van Bree
 Author URI: 
 Text Domain: bbit-widget
 License: GPL3
 
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/gpl-3.0.html>.
 */

new BBIT();

class BBIT {
    public function __construct(){
        $this->do_includes();
    }
    
    private function do_includes(){
        include('bbit-widget.php');
        include('bbit-shortcode.php');
    }
}