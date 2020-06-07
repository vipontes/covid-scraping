<?php 
chdir(__DIR__);
require('./simple_html_dom/HtmlWeb.php');
require('./config.php');

use simplehtmldom\HtmlWeb;

$doc = new HtmlWeb();
$html = $doc->load('https://www.worldometers.info/coronavirus/');

$totdayData = array();
$yesterdayData = array();
foreach($html->find('.tab-pane') as $tabPane) {
    $id = $tabPane->attr['id'];
    if ($id == "nav-today") {
        foreach($tabPane->find('.main_table_countries_div') as $mainTable) {
            foreach($mainTable->find('table') as $table) {
                foreach($table->find('tbody') as $tbody) {
                    foreach($tbody->find('tr') as $tr) {
                        $i = 0;
                        $country = array();
                        foreach($tr->find('td') as $td) {

                            $data = strlen($td->plaintext) != 0 ? $td->plaintext : 0;
                            $data = str_replace(',', '', $data);

                            if ( $i == 0 ) {
                                $country['position'] = $data;
                            }
                            else if ( $i == 1 ) {
                                $country['country'] = $data;
                            }
                            else if ( $i == 2 ) {
                                $country['total_cases'] = $data;
                            }
                            else if ( $i == 3 ) {
                                $country['new_cases'] = $data;
                            }
                            else if ( $i == 4 ) {
                                $country['total_deaths'] = $data;
                            }
                            else if ( $i == 5 ) {
                                $country['new_deaths'] = $data;
                            }
                            else if ( $i == 6 ) {
                                $country['total_recovered'] = $data;
                            }
                            else if ( $i == 7 ) {
                                $country['new_recovered'] = $data;
                            }
                            else if ( $i == 8 ) {
                                $country['active_cases'] =  $data;
                            }
                            else if ( $i == 9 ) {
                                $country['serious_cases'] = $data;
                            }
                            else if ( $i == 10 ) {
                                $country['cases_per_million'] = $data;
                            }
                            else if ( $i == 11 ) {
                                $country['deaths_per_million'] = $data;
                            }
                            else if ( $i == 12 ) {
                                $country['total_tests'] = $data;
                            }
                            else if ( $i == 13 ) {
                                $country['tests_per_million'] = $data;
                            }
                            else if ( $i == 14 ) {
                                $country['population'] = $data;
                            }

                            $i++;
                        }

                        if ( $country['country'] != 'Total:') {
                            $totdayData[] = $country;
                        }
                    }
                }
            }
        }
    }  else if ($id == "nav-yesterday") {
        foreach($tabPane->find('.main_table_countries_div') as $mainTable) {
            foreach($mainTable->find('table') as $table) {
                foreach($table->find('tbody') as $tbody) {
                    foreach($tbody->find('tr') as $tr) {
                        $i = 0;
                        $country = array();
                        foreach($tr->find('td') as $td) {

                            $data = strlen($td->plaintext) != 0 ? $td->plaintext : 0;
                            $data = str_replace(',', '', $data);

                            if ( $i == 0 ) {
                                $country['position'] = $data;
                            }
                            else if ( $i == 1 ) {
                                $country['country'] = $data;
                            }
                            else if ( $i == 2 ) {
                                $country['total_cases'] = $data;
                            }
                            else if ( $i == 3 ) {
                                $country['new_cases'] = $data;
                            }
                            else if ( $i == 4 ) {
                                $country['total_deaths'] = $data;
                            }
                            else if ( $i == 5 ) {
                                $country['new_deaths'] = $data;
                            }
                            else if ( $i == 6 ) {
                                $country['total_recovered'] = $data;
                            }
                            else if ( $i == 7 ) {
                                $country['total_recovered'] =  $data;
                            }
                            else if ( $i == 8 ) {
                                $country['active_cases'] =  $data;
                            }
                            else if ( $i == 9 ) {
                                $country['serious_cases'] = $data;
                            }
                            else if ( $i == 10 ) {
                                $country['cases_per_million'] = $data;
                            }
                            else if ( $i == 11 ) {
                                $country['deaths_per_million'] = $data;
                            }
                            else if ( $i == 12 ) {
                                $country['total_tests'] = $data;
                            }
                            else if ( $i == 13 ) {
                                $country['tests_per_million'] = $data;
                            }
                            else if ( $i == 14 ) {
                                $country['population'] = $data;
                            }

                            $i++;
                        }

                        if ( $country['country'] != 'Total:') {
                            $yesterdayData[] = $country;
                        }
                    }
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

// ********************************************************************
// Update today data
$date = date('Y-m-d');
$query = "SELECT worldometers_id FROM worldometers WHERE measurement_date = :measurement_date AND country = :country";
$sth = $db->prepare($query);
$sth->execute(array(':measurement_date' => $date, ':country' => $totdayData[0]['country']));
$data = $sth->fetch();

if ($data == false) {

    foreach($totdayData as $country) {
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
            deaths_per_million,
            total_tests,
            tests_per_million,
            population
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
            :deaths_per_million,
            :total_tests,
            :tests_per_million,
            :population
        )";
        try {
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
                    ':deaths_per_million' => $country['deaths_per_million'],
                    ':total_tests' => $country['total_tests'],
                    ':tests_per_million' => $country['tests_per_million'],
                    ':population' => $country['population']
                )
            );

        } catch (PDOException $e) {
            $error_array = $sth->errorInfo();
            $error = $error_array[2];
            print_r($error);
        }
    }
} else {
    foreach($totdayData as $country) {
        $query = "SELECT worldometers_id FROM worldometers WHERE measurement_date = :measurement_date AND country = :country";
        $sth = $db->prepare($query);
        $sth->execute(array(':measurement_date' => $date, ':country' => $country['country']));
        $data = $sth->fetch();

        $query = "UPDATE worldometers SET
            total_cases = :total_cases,
            new_cases = :new_cases,
            total_deaths = :total_deaths,
            new_deaths = :new_deaths,
            total_recovered = :total_recovered,
            active_cases = :active_cases,
            serious_cases = :serious_cases,
            cases_per_million = :cases_per_million,
            deaths_per_million = :deaths_per_million,
            total_tests = :total_tests,
            tests_per_million = :tests_per_million,
            population = :population
            WHERE worldometers_id = :worldometers_id";
        try {
            $sth = $db->prepare($query);
            $sth->execute(
                array(
                    ':total_cases' => $country['total_cases'],
                    ':new_cases' => $country['new_cases'],
                    ':total_deaths' => $country['total_deaths'],
                    ':new_deaths' => $country['new_deaths'],
                    ':total_recovered' => $country['total_recovered'],
                    ':active_cases' => $country['active_cases'],
                    ':serious_cases' => $country['serious_cases'],
                    ':cases_per_million' => $country['cases_per_million'],
                    ':deaths_per_million' => $country['deaths_per_million'],
                    ':total_tests' => $country['total_tests'],
                    ':tests_per_million' => $country['tests_per_million'],
                    ':population' => $country['population'],
                    ':worldometers_id' => $data['worldometers_id']
                )
            );
        } catch (PDOException $e) {
            $error_array = $sth->errorInfo();
            $error = $error_array[2];
            print_r($error);
        }
        $data = $sth->rowCount();
    }
}


// ********************************************************************
// Update yesterday data
$date = date('Y-m-d', strtotime("-1 days"));
$query = "SELECT worldometers_id FROM worldometers WHERE measurement_date = :measurement_date AND country = :country";
$sth = $db->prepare($query);
$sth->execute(array(':measurement_date' => $date, ':country' => $yesterdayData[0]['country']));
$data = $sth->fetch();

if ($data == false) {
    foreach($yesterdayData as $country) {
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
            deaths_per_million,
            total_tests,
            tests_per_million,
            population
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
            :deaths_per_million,
            :total_tests,
            :tests_per_million,
            :population
        )";
        try {
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
                    ':deaths_per_million' => $country['deaths_per_million'],
                    ':total_tests' => $country['total_tests'],
                    ':tests_per_million' => $country['tests_per_million'],
                    ':population' => $country['population']
                )
            );

        } catch (PDOException $e) {
            $error_array = $sth->errorInfo();
            $error = $error_array[2];
            print_r($error);
        }
    }
} else {
    foreach($yesterdayData as $country) {
        $query = "SELECT worldometers_id FROM worldometers WHERE measurement_date = :measurement_date AND country = :country";
        $sth = $db->prepare($query);
        $sth->execute(array(':measurement_date' => $date, ':country' => $country['country']));
        $data = $sth->fetch();

        $query = "UPDATE worldometers SET
            total_cases = :total_cases,
            new_cases = :new_cases,
            total_deaths = :total_deaths,
            new_deaths = :new_deaths,
            total_recovered = :total_recovered,
            active_cases = :active_cases,
            serious_cases = :serious_cases,
            cases_per_million = :cases_per_million,
            deaths_per_million = :deaths_per_million,
            total_tests = :total_tests,
            tests_per_million = :tests_per_million,
            population = :population
            WHERE worldometers_id = :worldometers_id";
        try {
            $sth = $db->prepare($query);
            $sth->execute(
                array(
                    ':total_cases' => $country['total_cases'],
                    ':new_cases' => $country['new_cases'],
                    ':total_deaths' => $country['total_deaths'],
                    ':new_deaths' => $country['new_deaths'],
                    ':total_recovered' => $country['total_recovered'],
                    ':active_cases' => $country['active_cases'],
                    ':serious_cases' => $country['serious_cases'],
                    ':cases_per_million' => $country['cases_per_million'],
                    ':deaths_per_million' => $country['deaths_per_million'],
                    ':total_tests' => $country['total_tests'],
                    ':tests_per_million' => $country['tests_per_million'],
                    ':population' => $country['population'],
                    ':worldometers_id' => $data['worldometers_id']
                )
            );
        } catch (PDOException $e) {
            $error_array = $sth->errorInfo();
            $error = $error_array[2];
            print_r($error);
        }
        $data = $sth->rowCount();
    }
}
