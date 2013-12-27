<?php  
/* 
Plugin Name: Cherkasy Weather
Plugin Script: ck-weather.php
Plugin URI: http://karmeljuk.ga/ck-weather/ck-weather.zip
Description: A simple weather plugin Cherkasy Weather
Version: 0.1
License: GPL
Author: karmeljuk
Author URI: karmeljuk.ga
*/ 

/*
The Shortcode
*/
add_filter('widget_text', 'do_shortcode');
add_shortcode('get_ck_weather', 'get_ck_weather_shortcode');  

function get_ck_weather_shortcode($atts){  
  
  $args = shortcode_atts(array('woeid' => '918180', 'tempscale' => 'c'), $atts );     
  return get_ck_weather_display($args['woeid'], $args['tempscale']);  
  
}  


/*
Display weather
*/
function get_ck_weather_display($woeid, $tempscale){    
  $weather_panel = '<div class = "gcw_weather_panel">';        
  if($weather = get_ck_weather_data($woeid, $tempscale)){  
    
    $weather_panel .= '<span>' . $weather['city'] . '</span>'. ' ' ;          
    $weather_panel .= '<span>' . $weather['temp'] . ' ' . strtoupper($tempscale) . '</span>';  
    $weather_panel .= '<img src = "' . $weather['icon_url'] . '" />';  
    $weather_panel .= '<span>' . $weather['conditions'] . '</span>';  
  
  }else{//no weather data  
    
    $weather_panel .= '<span>No weather data!';  
      
  }  
  
  $weather_panel .= '</div>';  
        
  return $weather_panel;    
     
}  

/*
Get weather
*/
function get_ck_weather_data($woeid, $tempscale){  
  
  global $wpdb;

  $query_url = 'http://weather.yahooapis.com/forecastrss?w=' . $woeid . '&u=' . $tempscale;  
    
  if($xml = simplexml_load_file($query_url)){  
        
    $error = strpos(strtolower($xml->channel->description), 'error');//server response but no weather data for woeid  
      
  }else{  
      
    $error = TRUE;//no response from weather server  
      
  }  
    
  if(!$error){  
    
    $weather['city'] = $xml->channel->children('yweather', TRUE)->location->attributes()->city;  
    $weather['temp'] = $xml->channel->item->children('yweather', TRUE)->condition->attributes()->temp;    
    $weather['conditions'] = $xml->channel->item->children('yweather', TRUE)->condition->attributes()->text;  
    
    $description = $xml->channel->item->description;  
      
    $imgpattern = '/src="(.*?)"/i';  
    preg_match($imgpattern, $description, $matches);  
  
    $weather['icon_url']= $matches[1];  
      
    return $weather;  
      
  }  
    
  return 0;  
  
}


/*
Creating Tables with plugin
*/
global $ck_weather_db_version;
$ck_weather_version = "1.0";

function ck_weather_db_install () {
   global $wpdb;
   global $ck_weather_db_version;

   $table_name = $wpdb->prefix . "ck_weather";
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
      
      $sql = "CREATE TABLE " . $table_name . " (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    time bigint(11) DEFAULT '0' NOT NULL,
    city tinytext NOT NULL,
    temp mediumint NOT NULL,
    UNIQUE KEY id (id)
  );";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);

      $rows_affected = $wpdb->update( $table_name, array( 'time' => current_time('mysql'), 'city' => $weather['city'], 'temp' => $weather['temp'] ) );
 
      add_option("ck_weather_db_version", $ck_weather_db_version);

   }
}

register_activation_hook(__FILE__,'ck_weather_db_install');