<?php 
include_once './simple_html_dom/HtmlWeb.php';
require_once('./config.php');

use simplehtmldom\HtmlWeb;

$doc = new HtmlWeb();
$html = $doc->load('https://www.worldometers.info/coronavirus/');

$countries = array();
foreach($html->find('.main_table_countries_div') as $mainTable) {
    foreach($mainTable->find('table') as $table) {
        foreach($table->find('tbody') as $tbody) {
            foreach($tbody->find('tr') as $tr) {
                $i = 0;
                $country = array();
                foreach($tr->find('td') as $td) {

                    $data = strlen($td->plaintext) != 0 ? $td->plaintext : 0;
                    $data = str_replace(',', '', $data);

                    if ( $i == 0 ) {
                        $country['country'] = $data;
                    }
                    else if ( $i == 1 ) {
                        $country['total_cases'] = $data;
                    }
                    else if ( $i == 2 ) {
                        $country['new_cases'] = $data;
                    }
                    else if ( $i == 3 ) {
                        $country['total_deaths'] = $data;
                    }
                    else if ( $i == 4 ) {
                        $country['new_deaths'] = $data;
                    }
                    else if ( $i == 5 ) {
                        $country['total_recovered'] = $data;
                    }
                    else if ( $i == 6 ) {
                        $country['active_cases'] =  $data;
                    }
                    else if ( $i == 7 ) {
                        $country['serious_cases'] = $data;
                    }
                    else if ( $i == 8 ) {
                        $country['cases_per_million'] = $data;
                    }
                    else if ( $i == 9 ) {
                        $country['deaths_per_million'] = $data;
                    }
                
                   $i++;
                }

                if ( $country['country'] != 'Total:') {
                    $countries[] = $country;
                }
            }
        }
    }
}

$html->clear();
unset($html);

$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$date = date('Y-m-d-');
try{
    foreach($countries as $country) {
        $query = "INSERT INTO worldometers
        (
            measurement_date,
            country,
            total_cases,
            new_cases,
            total_deaths,
            new_deaths,
            total_recovered,
            active_cases,
            serious_cases,
            cases_per_million,
            deaths_per_million
        ) VALUES (
            :measurement_date,
            :country,
            :total_cases,
            :new_cases,
            :total_deaths,
            :new_deaths,
            :total_recovered,
            :active_cases,
            :serious_cases,
            :cases_per_million,
            :deaths_per_million
        )";
    
        $sth = $db->prepare($query);
        $sth->execute(
            array(
                ':measurement_date' => $date,
                ':country' => $country['country'],
                ':total_cases' => $country['total_cases'],
                ':new_cases' => $country['new_cases'],
                ':total_deaths' => $country['total_deaths'],
                ':new_deaths' => $country['new_deaths'],
                ':total_recovered' => $country['total_recovered'],
                ':active_cases' => $country['active_cases'],
                ':serious_cases' => $country['serious_cases'],
                ':cases_per_million' => $country['cases_per_million'],
                ':deaths_per_million' => $country['deaths_per_million']
            ));
    }
} catch (PDOException $e) {
    $error_array = $sth->errorInfo();
    $error = $error_array[2];
    print_r($error);
}

print_r('</br>OK');




