<?php  
/* 
Plugin Name: Get Current Weather 
Plugin URI: http://woeid.rosselliot.co.nz 
Description: Gets current weather data (temperature, weather icon, conditions) from Yahoo weather API. 
Version: 1.0 
License: GPLv2 
Author: Ross Elliot 
Author URI: http://woeid.rosselliot.co.nz 
*/ 

function get_current_weather_template_tag($woeid = '', $tempscale = 'c'){  
  
  echo get_current_weather_display($woeid, $tempscale);  
  
}  


/*
The Shortcode
*/
add_shortcode('get_current_weather', 'get_current_weather_shortcode');  

function get_current_weather_shortcode($atts){  
  
  $args = shortcode_atts(array('woeid' => '', 'tempscale' => 'c'), $atts );  
       
  $args['tempscale'] = ($args['tempscale']=='c') ? 'c' : 'f';  
    
  return get_current_weather_display($args['woeid'], $args['tempscale']);  
  
}  

$args['tempscale'] = ($args['tempscale'] == 'c') ? 'c' : 'f';


/*
Light Lifting
*/
function get_current_weather_display($woeid, $tempscale){    
  $weather_panel = '<div class = "gcw_weather_panel">';        
  if($weather = get_current_weather_data($woeid, $tempscale)){  
    
    $weather_panel .= '<span>' . $weather['city'] . '</span>';          
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
function get_current_weather_data($woeid, $tempscale){  
  
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
