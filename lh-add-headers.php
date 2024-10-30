<?php 
/*
Plugin Name: LH Add Headers
Plugin URI: https://lhero.org/portfolio/lh-add-headers/
Description: Adds simple cacheing headers to wordpress
Author: Peter Shaw
Version: 1.04
Author URI: https://shawfactor.com
*/

if (!class_exists('LH_add_headers_plugin')) {

class LH_add_headers_plugin {

private function remove_html_comments($content = '') {
	return preg_replace('/<!--(.|\s)*?-->/', '', $content);
}







public function start_buffer(){

global $wp_customize;
if ( !isset( $wp_customize ) and ! is_admin()) {


ob_start();


add_action('shutdown', array($this,"print_buffer"), 0);


}


}









public function print_buffer() {




$output = ob_get_contents();

ob_end_clean();

$output = $this->remove_html_comments($output);




$output = apply_filters( 'lh_cache_headers_filter', $output);

$etag  = wp_hash($output);

 //set etag-header
  header( "Etag: ".$etag );




global $wpdb;


$wp_last_modified_date = $wpdb->get_var("SELECT GREATEST(post_modified_gmt, post_date_gmt) d FROM $wpdb->posts WHERE post_status = 'publish' ORDER BY d DESC LIMIT 1");
$wp_last_modified_date = max($wp_last_modified_date, get_lastcommentmodified('GMT'));
			
$last_modified = mysql2date('D, d M Y H:i:s', $wp_last_modified_date, 0) . ' GMT';




//set last-modified header
header( "Last-Modified: ".$last_modified);

//make sure it has already expired so that the browser won't send cached output
header("Expires: ".$last_modified);




if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) ==  $last_modified || 
    @trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag) { 

header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache"); 


header("HTTP/1.1 304 Not Modified");

exit; 


} else {




echo $output;
die;

}





}



public function __construct() {

add_action('template_redirect', array($this,"start_buffer"));


}


}

$lh_add_headers_instance = new LH_add_headers_plugin();

}


?>