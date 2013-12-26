<?php  
/* 
Plugin Name: Cherkasy Weather
Plugin Script: ck-weather.php
Plugin URI: http://karmeljuk.ga
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
Light Lifting
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
Heavy Lifting
*/
function get_ck_weather_data($woeid, $tempscale){  
  
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