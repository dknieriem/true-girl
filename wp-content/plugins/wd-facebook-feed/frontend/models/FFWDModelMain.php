<?php

class FFWDModelMain {
  ////////////////////////////////////////////////////////////////////////////////////////
  // Events                                                                             //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Constants                                                                          //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Variables                                                                          //
  ////////////////////////////////////////////////////////////////////////////////////////
  public $id_object_id_json;
  public $access_token;
  public $graph_url = 'https://graph.facebook.com/{FB_ID}/{EDGE}?{ACCESS_TOKEN}{FIELDS}{LIMIT}{OTHER}';
  public $page_user_group;
  public $options;
  ////////////////////////////////////////////////////////////////////////////////////////
  // Constructor & Destructor                                                           //
  ////////////////////////////////////////////////////////////////////////////////////////
  public function __construct() {

  	
	}
  ////////////////////////////////////////////////////////////////////////////////////////
  // Public Methods                                                                     //
  ////////////////////////////////////////////////////////////////////////////////////////
  public function get_theme_row_data($id) {
    global $wpdb;
    if ($id) {
      $row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wd_fb_theme WHERE id="%d"', $id));
    }
    else {
      $row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wd_fb_theme WHERE default_theme="%d"', 1));
    }

    $row = (object) array_merge((array)$row, (array)json_decode(  $row->params));
    unset($row->params);

    return $row;
  }
	public function get_ffwd_info($id) {
    global $wpdb;
    $row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wd_fb_info WHERE published=1 AND id="%d"', $id),ARRAY_A);
    // set access token
		if($row != NULL) {
      $row["success"] = true;
			$this->access_token = $row['access_token'];

			$graph_url_for_page_info = str_replace (
				array('{FB_ID}', '{EDGE}', '{ACCESS_TOKEN}', '{FIELDS}', '{LIMIT}', '{OTHER}'),
				array($row['from'], '', 'access_token=' . $this->access_token . '&', 'fields=picture,name,link&', '', ''),
				$this->graph_url
			);
			$this->page_user_group = array();//self::decap_do_curl($graph_url_for_page_info);
			$this->page_user_group = json_encode($this->page_user_group);
    }else{
      $row["success"] = false;
    }
    $row["blog_style_likes"] = "0";
    return $row;
  }
  public function page_nav($id, $objects_per_page, $ffwd) {
    global $wpdb;
    $total = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wd_fb_data WHERE fb_id="%d"', $id));
    $page_nav['total'] = $total;


    if (isset($_REQUEST['page_number_' . $ffwd]) && $_REQUEST['page_number_' . $ffwd]) {
      $limit = ((int) $_REQUEST['page_number_' . $ffwd] - 1) * $objects_per_page;
    }
    else {
      $limit = 0;
    }
    $page_nav['limit'] = (int) ($limit / $objects_per_page + 1);
    return $page_nav;
  }
	public static function decap_do_curl($uri) {
    $facebook_graph_results = null;
    $facebook_graph_url = $uri;
    $response = wp_remote_get($facebook_graph_url);
    if(isset($response->errors) && isset($response->errors["http_request_failed"][0])){
      print $response->errors["http_request_failed"][0];
    }elseif( is_array( $response ) && isset($response['body'])) {
      $header = $response['headers']; // array of http header lines
      $facebook_graph_results = $response['body']; // use the content
    }

    $facebook_graph_results = json_decode($facebook_graph_results, true);
    return $facebook_graph_results;
  }
  public function get_ffwd_options() {
    global $wpdb;
    $row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wd_fb_option WHERE id="%d"', 1));
    $this->options = $row;
		return $row;
  }
	public function get_option_json_data() {
		if(isset($this->options) && $this->options != NULL)
			return stripslashes(json_encode($this->options));
  }
  ////////////////////////////////////////////////////////////////////////////////////////
  // Getters & Setters                                                                  //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Private Methods                                                                    //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Listeners                                                                          //
  ////////////////////////////////////////////////////////////////////////////////////////
}
