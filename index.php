<?php

/*
Plugin Name: GT-Geo Targeting
Plugin URI: http://pranav.me/plugins/gt-geotargeting
Description: Ability to show content based on country. Show content if a visitor is from a list of countries, show content if a visitor is not from the list of countries, show custom message to visitors, and more!
Version: 1.0.0
Author: Pranav Rastogi
Author URI: http://www.pranav.me
*/

/*  Copyright 2010  Pranav Rastogi  (email : i@pranav.me)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
	
*/

function install_database() {

$content = file_get_contents(dirname(__FILE__) . '/ip2nation1of5.sql');
$content .= file_get_contents(dirname(__FILE__) . '/ip2nation2of5.sql');
$content .= file_get_contents(dirname(__FILE__) . '/ip2nation3of5.sql');
$content .= file_get_contents(dirname(__FILE__) . '/ip2nation4of5.sql');
$content .= file_get_contents(dirname(__FILE__) . '/ip2nation5of5.sql');
$content = trim($content);
$con2 = $content;
$con2 = explode(';', $con2);
$length = sizeof($con2);

foreach($con2 as $key => $con)
{
if($key < $length)
{
$con = trim($con);
$result = mysql_query("$con;");
if(!$result)
{
echo "Error: " . $key . mysql_error();
}
}
}

}

function uninstall_settings() {
$result = mysql_query("DROP TABLE IF EXISTS ip2nation;");
if(!$result)
{
echo "Error: " . $key . mysql_error();
}
$result = mysql_query("DROP TABLE IF EXISTS ip2nationCountries;");
if(!$result)
{
echo "Error: " . $key . mysql_error();
}
}


function getIP() {
$ip_user = preg_replace( '/[^0-9a-fA-F:., ]/', '',$_SERVER['REMOTE_ADDR'] );
return $ip_user;
}

function GetUserCountry() {

if(function_exists('geoip_continent_code_by_name'))
{
$user_country = geoip_continent_code_by_name(getIP());
return $user_country;
}
else {
        global $wpdb, $catch;
            
            $ip = getIP();            
            $sql_query = "SELECT c.country, c.code
                          FROM ip2nationCountries c, ip2nation i
                          WHERE i.ip < INET_ATON('$ip')
                          AND c.code = i.country
                          ORDER BY i.ip
                          DESC LIMIT 1";
                          
            $catch = $wpdb->get_row($sql_query);

$array = (array) $catch;
return $array;
    }
}


function WPGeo_IsFromCountry($country)
{
$IP = getIP();
$user_country = getUserCountry();
$country = explode(",", $country);
foreach($country as $countries)
{
$countries = trim($countries);
if($user_country['country'] == $countries || $user_country['code'] == $countries)
{
return true;
}
}
}

function WPGeo_IsNotFromCountry($country)
{
$IP = getIP();
$user_country = getUserCountry();
$country = explode(",", $country);
foreach($country as $countries)
{
$countries = trim($countries);
if($user_country['country'] == $countries || $user_country['code'] == $countries)
{
$country_equals = 1;
}
}

if(!isset($country_equals))
{
return true;
}
}

add_shortcode( 'geo-in', 'geo_country' );

function geo_country( $attr, $content = null ) {
extract( shortcode_atts( array( 
'country' => 'No money, no honey. i.e. You forgot' ,
'note' => 'Content not available for your country'
), $attr ) );
if ( $country && WPGeo_IsFromCountry($country) == true )
{
return $content;
}
else {
return $note;
}
}

add_shortcode( 'geo-out', 'geo_country_out' );

function geo_country_out( $attr, $content = null ) {
extract( shortcode_atts( array( 
'country' => '' ,
'note' => 'Content not available for your country'
), $attr ) );
if ( $country && WPGeo_IsNotFromCountry($country) == true )
{
return $content;
}
else {
return $note;
}
}

register_activation_hook( __FILE__, 'install_database' );
register_deactivation_hook( __FILE__, 'uninstall_settings' );
?>