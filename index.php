<?php
require __DIR__ . '/vendor/autoload.php';

use HungCP\PhpSimpleHtmlDom\HtmlDomParser;

$str = '';

if(sizeof($argv)>1){
    getRates($argv[1]);
}else{
    getRates(date('Ymd'));
}

function getRates($targetDate = ''){
    
    $export_html = HtmlDomParser::file_get_html('http://customsgc.gov.my/cgi-bin/exchange.cgi?FileName=KCFEX_'.$targetDate.'_EXPORT');
    $import_html = HtmlDomParser::file_get_html('http://customsgc.gov.my/cgi-bin/exchange.cgi?FileName=KCFEX_'.$targetDate.'_IMPORT');
    $date = explode(" to ", str_replace("Effective Date: ","",$import_html->find('.styLastUpdate')[0]->text()));

    $data = array(
        'start_date' => $date[0],
        'end_date' => $date[1],
    );
    $rate = array();
    //print_r($export_html->find('.styItemRow0, .styItemRow1'));

    foreach($export_html->find('.styItemRow0, .styItemRow1') as $tr){
        //print_r($export_html->find('.styItemRow0, .styItemRow1'));
       $td = $tr->find('td');
       $rate[$td[3]->text()] = array(
           'export'=>$td[4]->text()
       );
       
    //    foreach ($element->nodes as $key => $value) {
    //        # code...
    //        echo $value->text().';;';
    //    }
    }
    
    foreach($import_html->find('.styItemRow0, .styItemRow1') as $tr){
        $td = $tr->find('td');

        $rate[$td[3]->text()]['import'] =$td[4]->text();
    }
    
    $data['rates']=$rate;
    $fp = fopen('result/kjdm_forex_'.$targetDate.'.json', 'w');
    fwrite($fp, json_encode($data,JSON_PRETTY_PRINT));
    fclose($fp);
    //print_r($data);
}