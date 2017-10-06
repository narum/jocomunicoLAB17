<?php

class addVerbModel extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library("session");
    }

     function conjugateVerb($verb) {
        $verb = str_replace(' ', '', $verb);
        $language = $this->session->userdata('ulangabbr');//CA or ES
        $infinitive = $this->getInfinitive($language, $verb)[0]->verb;
        return $this->getConjugations($language, $infinitive);
    }

     function getInfinitive($language, $verb){
        if ($language == 'CA'){
            $language = 'cat';
        }else if ($language == 'ES'){
            $language = 'spa';
        }
         return $this->getInfinitiveVerb($verb, $language);
    }

     function getInfinitiveVerb($verb, $language){
         $url = 'http://api.verbix.com/finder/json/eba16c29-e22e-11e5-be88-00089be4dcbc/v2/'.$language.'/'.$verb;
         $ch = curl_init();
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($ch, CURLOPT_URL, $url);
         $result = curl_exec($ch);
         curl_close($ch);
         $obj = json_decode($result);
         echo $obj->access_token;
         return $obj;
     }

     function getConjugations($language, $infinitive){
         $verb = new Verb();
         if ($language == 'CA'){
             $url = 'http://www.verbix.com/webverbix/go.php?T1='.$infinitive.'&Submit=Go&D1=7&H1=107';
             $xpath = $this->searchInfinitive($url);
             $this->getPresentCAT($verb, $xpath);
             $this->getPerfectoCAT($verb, $xpath);
             $this->getImperfectoCAT($verb, $xpath);
             $this->getPluscuamperfectoCAT($verb, $xpath);
             $this->getPasadoCAT($verb, $xpath);
             $this->getFuturoCAT($verb, $xpath);
             $this->getPrsubjCAT($verb, $xpath);
             $this->getImpsubjCAT($verb, $xpath);
             $this->getImperativoCAT($verb, $xpath);
             $this->getFormasNoPersonalesCAT($verb, $xpath);
         }else if ($language == 'ES'){
             $url = 'http://www.verbix.com/webverbix/Spanish/'.$infinitive.'.html';
             $xpath = $this->searchInfinitive($url);
             return $xpath;
             $this->getPresentESP($verb, $xpath);
             $this->getPerfectoESP($verb, $xpath);
             $this->getImperfectoESP($verb, $xpath);
             $this->getPluscuamperfectoESP($verb, $xpath);
             $this->getPasadoESP($verb, $xpath);
             $this->getFuturoESP($verb, $xpath);
             $this->getPrsubjESP($verb, $xpath);
             $this->getImpsubjESP($verb, $xpath);
             $this->getImperativoESP($verb, $xpath);
             $this->getFormasNoPersonalesESP($verb, $xpath);
         }
         return $verb;
     }

    function searchInfinitive($url){

        $url ="http://www.verbix.com/webverbix/Spanish/ir.html";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.224 Safari/534.10');
        curl_setopt($curl, CURLOPT_URL,$url);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        $html = curl_exec($curl); curl_close($curl);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->substituteEntities = TRUE;
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        file_put_contents('C:\Users\Hector\DOMJOCOMUNICO.html', $dom->saveHTML($dom->documentElement));
        $xpath = new DOMXPath($dom);//*[@id="verbixConjugations"]/div[3]/div/div[1]/table/tbody/tr[1]/td[2]/span
        return $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[4]/table/tr[1]/td[2]/span/text()')->item(0)->nodeValue;
        return $xpath;
    }

    function getPresentCAT($verb, $xpath){
        $verb->presente->ps1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[1]/table/tr[1]/td[2]/span/text()')->item(0)->nodeValue;
        $verb->presente->ps2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[1]/table/tr[2]/td[2]/span/text()')->item(0)->nodeValue;
        $verb->presente->ps3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[1]/table/tr[3]/td[2]/span/text()')->item(0)->nodeValue;
        $verb->presente->pp1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[1]/table/tr[4]/td[2]/span/text()')->item(0)->nodeValue;
        $verb->presente->pp2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[1]/table/tr[5]/td[2]/span/text()')->item(0)->nodeValue;
        $verb->presente->pp3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[1]/table/tr[6]/td[2]/span/text()')->item(0)->nodeValue;
    }

    function getPresentESP($verb, $xpath){
        $verb->presente->ps1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[1]/table/tr[1]/td[2]/span/text()')->item(0)->nodeValue;
        $verb->presente->ps2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[1]/table/tr[2]/td[2]/span')->item(0)->nodeValue;
        $verb->presente->ps3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[1]/table/tr[3]/td[2]/span')->item(0)->nodeValue;
        $verb->presente->pp1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[1]/table/tr[4]/td[2]/span')->item(0)->nodeValue;
        $verb->presente->pp2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[1]/table/tr[5]/td[2]/span')->item(0)->nodeValue;
        $verb->presente->pp3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[1]/table/tr[6]/td[2]/span')->item(0)->nodeValue;
    }

    function getPerfectoCAT($verb, $xpath){
        $verb->perfecto->ps1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[2]/table/tr[1]/td[2]/span')->item(0)->nodeValue;
        $verb->perfecto->ps2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[2]/table/tr[2]/td[2]/span')->item(0)->nodeValue;
        $verb->perfecto->ps3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[2]/table/tr[3]/td[2]/span')->item(0)->nodeValue;
        $verb->perfecto->pp1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[2]/table/tr[4]/td[2]/span')->item(0)->nodeValue;
        $verb->perfecto->pp2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[2]/table/tr[5]/td[2]/span')->item(0)->nodeValue;
        $verb->perfecto->pp3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[2]/table/tr[6]/td[2]/span')->item(0)->nodeValue;
    }

    function getPerfectoESP($verb, $xpath){
        $verb->perfecto->ps1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[2]/table/tr[1]/td[2]/span')->item(0)->nodeValue;
        $verb->perfecto->ps2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[2]/table/tr[2]/td[2]/span')->item(0)->nodeValue;
        $verb->perfecto->ps3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[2]/table/tr[3]/td[2]/span')->item(0)->nodeValue;
        $verb->perfecto->pp1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[2]/table/tr[4]/td[2]/span')->item(0)->nodeValue;
        $verb->perfecto->pp2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[2]/table/tr[5]/td[2]/span')->item(0)->nodeValue;
        $verb->perfecto->pp3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[2]/table/tr[6]/td[2]/span')->item(0)->nodeValue;
    }

    function getImperfectoCAT($verb, $xpath){
        $verb->imperfecto->ps1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[3]/table/tr[1]/td[2]/span')->item(0)->nodeValue;
        $verb->imperfecto->ps2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[3]/table/tr[2]/td[2]/span')->item(0)->nodeValue;
        $verb->imperfecto->ps3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[3]/table/tr[3]/td[2]/span')->item(0)->nodeValue;
        $verb->imperfecto->pp1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[3]/table/tr[4]/td[2]/span')->item(0)->nodeValue;
        $verb->imperfecto->pp2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[3]/table/tr[5]/td[2]/span')->item(0)->nodeValue;
        $verb->imperfecto->pp3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[3]/table/tr[6]/td[2]/span')->item(0)->nodeValue;
    }

    function getImperfectoESP($verb, $xpath){
        $verb->imperfecto->ps1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[3]/table/tr[1]/td[2]/span')->item(0)->nodeValue;
        $verb->imperfecto->ps2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[3]/table/tr[2]/td[2]/span')->item(0)->nodeValue;
        $verb->imperfecto->ps3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[3]/table/tr[3]/td[2]/span')->item(0)->nodeValue;
        $verb->imperfecto->pp1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[3]/table/tr[4]/td[2]/span')->item(0)->nodeValue;
        $verb->imperfecto->pp2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[3]/table/tr[5]/td[2]/span')->item(0)->nodeValue;
        $verb->imperfecto->pp3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[3]/table/tr[6]/td[2]/span')->item(0)->nodeValue;
    }

    function getPluscuamperfectoCAT($verb, $xpath){
        $verb->pluscuamperfecto->ps1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[4]/table/tr[1]/td[2]/span')->item(0)->nodeValue;
        $verb->pluscuamperfecto->ps2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[4]/table/tr[2]/td[2]/span')->item(0)->nodeValue;
        $verb->pluscuamperfecto->ps3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[4]/table/tr[3]/td[2]/span')->item(0)->nodeValue;
        $verb->pluscuamperfecto->pp1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[4]/table/tr[4]/td[2]/span')->item(0)->nodeValue;
        $verb->pluscuamperfecto->pp2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[4]/table/tr[5]/td[2]/span')->item(0)->nodeValue;
        $verb->pluscuamperfecto->pp3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[4]/table/tr[6]/td[2]/span')->item(0)->nodeValue;
    }

    function getPluscuamperfectoESP($verb, $xpath){
        $verb->pluscuamperfecto->ps1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[4]/table/tr[1]/td[2]/span')->item(0)->nodeValue;
        $verb->pluscuamperfecto->ps2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[4]/table/tr[2]/td[2]/span')->item(0)->nodeValue;
        $verb->pluscuamperfecto->ps3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[4]/table/tr[3]/td[2]/span')->item(0)->nodeValue;
        $verb->pluscuamperfecto->pp1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[4]/table/tr[4]/td[2]/span')->item(0)->nodeValue;
        $verb->pluscuamperfecto->pp2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[4]/table/tr[5]/td[2]/span')->item(0)->nodeValue;
        $verb->pluscuamperfecto->pp3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[4]/table/tr[6]/td[2]/span')->item(0)->nodeValue;
    }

    function getPasadoCAT($verb, $xpath){
        $verb->pasado->ps1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[7]/table/tr[1]/td[2]/span')->item(0)->nodeValue;
        $verb->pasado->ps2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[7]/table/tr[2]/td[2]/span')->item(0)->nodeValue;
        $verb->pasado->ps3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[7]/table/tr[3]/td[2]/span')->item(0)->nodeValue;
        $verb->pasado->pp1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[7]/table/tr[4]/td[2]/span')->item(0)->nodeValue;
        $verb->pasado->pp2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[7]/table/tr[5]/td[2]/span')->item(0)->nodeValue;
        $verb->pasado->pp3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[7]/table/tr[6]/td[2]/span')->item(0)->nodeValue;
    }

    function getPasadoESP($verb, $xpath){
        $verb->pasado->ps1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[5]/table/tr[1]/td[2]/span')->item(0)->nodeValue;
        $verb->pasado->ps2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[5]/table/tr[2]/td[2]/span')->item(0)->nodeValue;
        $verb->pasado->ps3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[5]/table/tr[3]/td[2]/span')->item(0)->nodeValue;
        $verb->pasado->pp1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[5]/table/tr[4]/td[2]/span')->item(0)->nodeValue;
        $verb->pasado->pp2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[5]/table/tr[5]/td[2]/span')->item(0)->nodeValue;
        $verb->pasado->pp3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[5]/table/tr[6]/td[2]/span')->item(0)->nodeValue;
    }

    function getFuturoCAT($verb, $xpath){
        $verb->futuro->ps1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[5]/table/tr[1]/td[2]/span')->item(0)->nodeValue;
        $verb->futuro->ps2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[5]/table/tr[2]/td[2]/span')->item(0)->nodeValue;
        $verb->futuro->ps3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[5]/table/tr[3]/td[2]/span')->item(0)->nodeValue;
        $verb->futuro->pp1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[5]/table/tr[4]/td[2]/span')->item(0)->nodeValue;
        $verb->futuro->pp2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[5]/table/tr[5]/td[2]/span')->item(0)->nodeValue;
        $verb->futuro->pp3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/div/div[5]/table/tr[6]/td[2]/span')->item(0)->nodeValue;
    }

    function getFuturoESP($verb, $xpath){
        $verb->futuro->ps1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[7]/table/tr[1]/td[2]/span')->item(0)->nodeValue;
        $verb->futuro->ps2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[7]/table/tr[2]/td[2]/span')->item(0)->nodeValue;
        $verb->futuro->ps3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[7]/table/tr[3]/td[2]/span')->item(0)->nodeValue;
        $verb->futuro->pp1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[7]/table/tr[4]/td[2]/span')->item(0)->nodeValue;
        $verb->futuro->pp2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[7]/table/tr[5]/td[2]/span')->item(0)->nodeValue;
        $verb->futuro->pp3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[7]/table/tr[6]/td[2]/span')->item(0)->nodeValue;
    }

    function getPrsubjCAT($verb, $xpath){
        $verb->prsubj->ps1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[1]/table/tr[1]/td[2]/span')->item(0)->nodeValue;
        $verb->prsubj->ps2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[1]/table/tr[2]/td[2]/span')->item(0)->nodeValue;
        $verb->prsubj->ps3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[1]/table/tr[3]/td[2]/span')->item(0)->nodeValue;
        $verb->prsubj->pp1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[1]/table/tr[4]/td[2]/span')->item(0)->nodeValue;
        $verb->prsubj->pp2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[1]/table/tr[5]/td[2]/span')->item(0)->nodeValue;
        $verb->prsubj->pp3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[1]/table/tr[6]/td[2]/span')->item(0)->nodeValue;
    }

    function getPrsubjESP($verb, $xpath){
        $verb->prsubj->ps1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[4]/div/div[1]/table/tr[1]/td[2]/span')->item(0)->nodeValue;
        $verb->prsubj->ps2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[4]/div/div[1]/table/tr[2]/td[2]/span')->item(0)->nodeValue;
        $verb->prsubj->ps3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[4]/div/div[1]/table/tr[3]/td[2]/span')->item(0)->nodeValue;
        $verb->prsubj->pp1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[4]/div/div[1]/table/tr[4]/td[2]/span')->item(0)->nodeValue;
        $verb->prsubj->pp2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[4]/div/div[1]/table/tr[5]/td[2]/span')->item(0)->nodeValue;
        $verb->prsubj->pp3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[4]/div/div[1]/table/tr[6]/td[2]/span')->item(0)->nodeValue;
    }

    function getImpsubjCAT($verb, $xpath){
        $verb->impsubj->ps1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[3]/table/tr[1]/td[2]/span')->item(0)->nodeValue;
        $verb->impsubj->ps2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[3]/table/tr[2]/td[2]/span')->item(0)->nodeValue;
        $verb->impsubj->ps3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[3]/table/tr[3]/td[2]/span')->item(0)->nodeValue;
        $verb->impsubj->pp1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[3]/table/tr[4]/td[2]/span')->item(0)->nodeValue;
        $verb->impsubj->pp2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[3]/table/tr[5]/td[2]/span')->item(0)->nodeValue;
        $verb->impsubj->pp3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[3]/table/tr[6]/td[2]/span')->item(0)->nodeValue;
    }
    function getImpsubjESP($verb, $xpath){
        $verb->impsubj->ps1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[4]/div/div[3]/table[1]/tr[1]/td[2]/span')->item(0)->nodeValue;
        $verb->impsubj->ps2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[4]/div/div[3]/table[1]/tr[2]/td[2]/span')->item(0)->nodeValue;
        $verb->impsubj->ps3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[4]/div/div[3]/table[1]/tr[3]/td[2]/span')->item(0)->nodeValue;
        $verb->impsubj->pp1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[4]/div/div[3]/table[1]/tr[4]/td[2]/span')->item(0)->nodeValue;
        $verb->impsubj->pp2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[4]/div/div[3]/table[1]/tr[5]/td[2]/span')->item(0)->nodeValue;
        $verb->impsubj->pp3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[4]/div/div[3]/table[1]/tr[6]/td[2]/span')->item(0)->nodeValue;
    }

    function getImperativoCAT($verb, $xpath){
        $verb->imperativo->ps2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[4]/table/tr[1]/td[2]/span')->item(0)->nodeValue;
        $verb->imperativo->ps3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[4]/table/tr[2]/td[2]/span')->item(0)->nodeValue;
        $verb->imperativo->pp1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[4]/table/tr[3]/td[2]/span')->item(0)->nodeValue;
        $verb->imperativo->pp2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[4]/table/tr[4]/td[2]/span')->item(0)->nodeValue;
        $verb->imperativo->pp3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[4]/table/tr[5]/td[2]/span')->item(0)->nodeValue;
    }

    function getImperativoESP($verb, $xpath){
        $verb->imperativo->ps2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[6]/div/div[1]/table/tr[1]/td[2]/span')->item(0)->nodeValue;
        $verb->imperativo->ps3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[6]/div/div[1]/table/tr[2]/td[2]/span')->item(0)->nodeValue;
        $verb->imperativo->pp1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[6]/div/div[1]/table/tr[3]/td[2]/span')->item(0)->nodeValue;
        $verb->imperativo->pp2 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[6]/div/div[1]/table/tr[4]/td[2]/span')->item(0)->nodeValue;
        $verb->imperativo->pp3 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[6]/div/div[1]/table/tr[5]/td[2]/span')->item(0)->nodeValue;
    }

    function getFormasNoPersonalesCAT($verb, $xpath){
        $verb->infinitivo = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[1]/p/span[1]')->item(0)->nodeValue;
        $verb->gerundio = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[1]/p/span[2]')->item(0)->nodeValue;
        $verb->participio = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[1]/p/span[3]')->item(0)->nodeValue;
    }

    function getFormasNoPersonalesESP($verb, $xpath){
        $verb->infinitivo = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/p/span[1]')->item(0)->nodeValue;
        $verb->gerundio = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/p/span[3]')->item(0)->nodeValue;
        $verb->participio = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[2]/p/span[2]')->item(0)->nodeValue;
    }

    //Check If the verb is already on the DB.
    function verbExist($verb){
        $verb = str_replace(' ', '', $verb);
        $language = $this->session->userdata('ulangabbr');//CA or ES
        $userid = $this->session->userdata('idusu');
        if ($language == 'CA'){
            $verbTable = 'verbca';
        }else if ($language == 'ES'){
            $verbTable = 'verbes';
        }
        $sql = "SELECT verbid FROM ".$verbTable." JOIN pictograms ON verbid = pictoid WHERE ID_PUser = ? AND verbtext ='".$verb."';";
        $query = $this->db->query($sql, $userid);
        $res = $query->result();
        if(count($res) < 1){
            return false;
        }else {
            return true;
        }
    }

    function insertData($request){
        $imgPicto = $request->img;
        $pictotext = $request->verb;
        $pictoid = $this->insertPictogram($imgPicto);
        $this->insertPictogramsLanguage($pictoid, $pictotext);
        $this->insertVerb($pictoid, $pictotext);
        $this->insertVerbConjugation($pictoid, $request->conjugations);
        return $this->insertVerbPatterns($pictoid, $request->patterns, $request->pronominal);
    }

    function insertPictogram($imgPicto){
        $userid = $this->session->userdata('idusu');
        $data = array(
            'ID_PUser' => $userid,
            'pictoType' => 'verb',
            'supportsExpansion' => '1',
            'imgPicto' => $imgPicto
        );
        $this->db->insert('Pictograms', $data);
        $pictoid = $this->db->insert_id();
        return $pictoid;

    }

    function insertPictogramsLanguage($pictoid, $pictotext){
        $languageid = ($this->session->userdata('ulangabbr') == 'CA' ? 1:  2);
        $data = array(
            'pictoid' => $pictoid,
            'languageid' => $languageid,
            'insertdate' => mdate("%Y/%m/%d", time()),
            'pictotext' => $pictotext,
            'pictofreq' => 10.000
        );
        $this->db->insert('PictogramsLanguage', $data);
    }

    function insertVerb($verbid, $verbtext){
        $table = ($this->session->userdata('ulangabbr') == 'CA' ? 'verbca': 'verbes');
        $data = array(
            'verbid' => $verbid,
            'verbtext' => $verbtext,
            'actiu' => '1'
        );
        $this->db->insert($table, $data);
    }

    function insertVerbConjugation($verbid, $conjugations){
        $this->insertConjugations($verbid, $conjugations->presente);
        $this->insertConjugations($verbid, $conjugations->perfecto);
        $this->insertConjugations($verbid, $conjugations->imperfecto);
        $this->insertConjugations($verbid, $conjugations->pluscuamperfecto);
        $this->insertConjugations($verbid, $conjugations->pasado);
        $this->insertConjugations($verbid, $conjugations->futuro);
        $this->insertConjugations($verbid, $conjugations->prsubj);
        $this->insertConjugations($verbid, $conjugations->impsubj);
        $this->insertImperatiuConjugations($verbid, $conjugations->imperativo);
        $this->insertFormasNoPersonales($verbid, $conjugations->formasNoPersonales);
    }
    function insertConjugations($verbid, $conj){
        $this->insertTemp($verbid, $conj->name, 1, 'sing', $conj->persona->ps1);
        $this->insertTemp($verbid, $conj->name, 2, 'sing', $conj->persona->ps2);
        $this->insertTemp($verbid, $conj->name, 3, 'sing', $conj->persona->ps3);
        $this->insertTemp($verbid, $conj->name, 1, 'pl', $conj->persona->pp1);
        $this->insertTemp($verbid, $conj->name, 2, 'pl', $conj->persona->pp2);
        $this->insertTemp($verbid, $conj->name, 3, 'pl', $conj->persona->pp3);
    }
    function insertImperatiuConjugations($verbid, $conj){
        $this->insertTemp($verbid, $conj->name, 2, 'sing', $conj->persona->ps2);
        $this->insertTemp($verbid, $conj->name, 3, 'sing', $conj->persona->ps3);
        $this->insertTemp($verbid, $conj->name, 1, 'pl', $conj->persona->pp1);
        $this->insertTemp($verbid, $conj->name, 2, 'pl', $conj->persona->pp2);
        $this->insertTemp($verbid, $conj->name, 3, 'pl', $conj->persona->pp3);
    }
    function insertTemp($verbid, $tense, $pers, $singpl, $verbConj){
        $table = ($this->session->userdata('ulangabbr') == 'CA' ? 'verbconjugationca': 'verbconjugationes');
        $data = array(
            'verbid' => $verbid,
            'tense' => $tense,
            'pers' => $pers,
            'singpl' => $singpl,
            'verbconj' => $verbConj
        );
        $this->db->insert($table, $data);
    }
    function insertFormasNoPersonales($verbid, $conj){
        $table = ($this->session->userdata('ulangabbr') == 'CA' ? 'verbconjugationca': 'verbconjugationes');
        $data = array(
            'verbid' => $verbid,
            'tense' => 'infinitiu',
            'pers' => 0,
            'singpl' => 'sing',
            'verbconj' => $conj->infinitivo
        );
        $this->db->insert($table, $data);
        $data = array(
            'verbid' => $verbid,
            'tense' => 'gerundi',
            'pers' => 0,
            'singpl' => 'sing',
            'verbconj' => $conj->gerundio
        );
        $this->db->insert($table, $data);
        $data = array(
            'verbid' => $verbid,
            'tense' => 'participi',
            'pers' => 0,
            'singpl' => 'sing',
            'verbconj' => $conj->participio
        );
        $this->db->insert($table, $data);
    }

    function insertVerbPatterns($verbid, $patterns, $pronominal){
        foreach ($patterns as $pattern){
            $this->insertPattern($verbid, $pattern, $pronominal);
        }
    }
    function insertPattern($verbid, $pattern, $pronominal){
        $table = ($this->session->userdata('ulangabbr') == 'CA' ? 'patternca': 'patternes');
        $pronominal = ($pronominal == 0 ? '0': '1');
        $subverb = $this->subVerb($pattern);
        $data = array(
            'verbid' => $verbid,
            'pronominal' => $pronominal,
            'pseudoimpersonal' => '0',
            'copulatiu' => '0',
            'tipusfrase' => 'enunciativa',
            'defaulttense' => $pattern->Patron->defaulttense,
            'subj' => $pattern->Patron->subj,
            'subjdef' => $pattern->Patron->subjdef,
            'theme' => $pattern->CD->priority,
            'themetipus' => $pattern->CD->type,
            'themeprep' => $pattern->CD->preposition,
            'receiver' => $pattern->Receiver->priority,
            'receiverprep' => $pattern->Receiver->preposition,
            'benef' => $pattern->Beneficiary->priority,
            'beneftipus' => $pattern->Beneficiary->type,
            'benefprep' => $pattern->Beneficiary->preposition,
            'acomp' => $pattern->Acomp->priority,
            'acompprep' => $pattern->Acomp->preposition,
            'tool' => $pattern->Tool->priority,
            'toolprep' => $pattern->Tool->preposition,
            'manera' => $pattern->Modo->priority,
            'maneratipus' => $pattern->Modo->type,
            'locto' => $pattern->Locto->priority,
            'loctotipus' => $pattern->Locto->type,
            'loctoprep' => $pattern->Locto->preposition,
            'subverb' => $subverb,
            'exemple' => $pattern->Patron->exemple,
        );
        $this->db->insert($table, $data);
    }

    function subVerb($pattern){
        if ($pattern->CD->type == 'verb' || $pattern->Beneficiary->type == 'verb' || $pattern->Locto->type == 'verb'){
            return '1';
        }else{
            return '0';
        }
    }

    function getAllData($verbid){
        $result = new stdClass();
        $result->verbText = $this->getVerbText($verbid);
        $result->imgPicto = $this->getPictoImg($verbid);
        $result->pronominal = $this->isPronominal($verbid);
        $result->conjugations = $this->getConjugationsBD($verbid);
        //get Patterns
        $result->patterns = $this->getPatterns($verbid);
        return $result;
    }
    function getVerbText($verbid){
        $table = ($this->session->userdata('ulangabbr') == 'CA' ? 'verbca': 'verbes');
        return $this->db->query("SELECT verbtext FROM ".$table." WHERE verbid = ?", $verbid)->row('verbtext');
    }
    function getPictoImg($verbid){
        return $this->db->query("SELECT imgPicto FROM pictograms WHERE pictoid = ?", $verbid)->row('imgPicto');
    }
    function isPronominal($verbid){
        $table = ($this->session->userdata('ulangabbr') == 'CA' ? 'patternca': 'patternes');
        return $this->db->query("SELECT pronominal FROM ".$table." WHERE verbid = ?", $verbid)->row('pronominal');
    }
    function getConjugationsBD($verbid){
        $verb = new Verb();
        $this->getTimeBD($verb->presente, $verbid, 'present');
        $this->getTimeBD($verb->perfecto, $verbid, 'perfet');
        $this->getTimeBD($verb->imperfecto, $verbid, 'imperfecte');
        $this->getTimeBD($verb->pluscuamperfecto, $verbid, 'perifrastic');
        $this->getTimeBD($verb->pasado, $verbid, 'passat');
        $this->getTimeBD($verb->futuro, $verbid, 'futur');
        $this->getTimeBD($verb->prsubj, $verbid, 'prsubj');
        $this->getTimeBD($verb->impsubj, $verbid, 'impsubj');
        $this->getImperativoBD($verb->imperativo, $verbid);
        $this->getFormasNoPersonalesBD($verb, $verbid);
        return $verb;
    }

    function getTimeBD($verbTime, $verbid, $tense){
        $table = ($this->session->userdata('ulangabbr') == 'CA' ? 'verbconjugationca': 'verbconjugationes');
        $verbTime->ps1 = $this->db->query("SELECT verbconj FROM ".$table." WHERE verbid = ? AND tense = '".$tense."' AND pers = 1 AND singpl = 'sing'", $verbid)->row('verbconj');
        $verbTime->ps2 = $this->db->query("SELECT verbconj FROM ".$table." WHERE verbid = ? AND tense = '".$tense."' AND pers = 2 AND singpl = 'sing'", $verbid)->row('verbconj');
        $verbTime->ps3 = $this->db->query("SELECT verbconj FROM ".$table." WHERE verbid = ? AND tense = '".$tense."' AND pers = 3 AND singpl = 'sing'", $verbid)->row('verbconj');
        $verbTime->pp1 = $this->db->query("SELECT verbconj FROM ".$table." WHERE verbid = ? AND tense = '".$tense."' AND pers = 1 AND singpl = 'pl'", $verbid)->row('verbconj');
        $verbTime->pp2 = $this->db->query("SELECT verbconj FROM ".$table." WHERE verbid = ? AND tense = '".$tense."' AND pers = 2 AND singpl = 'pl'", $verbid)->row('verbconj');
        $verbTime->pp3 = $this->db->query("SELECT verbconj FROM ".$table." WHERE verbid = ? AND tense = '".$tense."' AND pers = 3 AND singpl = 'pl'", $verbid)->row('verbconj');
    }
    function getImperativoBD($verbTime, $verbid){
        $table = ($this->session->userdata('ulangabbr') == 'CA' ? 'verbconjugationca': 'verbconjugationes');
        $verbTime->ps2 = $this->db->query("SELECT verbconj FROM ".$table." WHERE verbid = ? AND tense = 'imperatiu' AND pers = 2 AND singpl = 'sing'", $verbid)->row('verbconj');
        $verbTime->ps3 = $this->db->query("SELECT verbconj FROM ".$table." WHERE verbid = ? AND tense = 'imperatiu' AND pers = 3 AND singpl = 'sing'", $verbid)->row('verbconj');
        $verbTime->pp1 = $this->db->query("SELECT verbconj FROM ".$table." WHERE verbid = ? AND tense = 'imperatiu' AND pers = 1 AND singpl = 'pl'", $verbid)->row('verbconj');
        $verbTime->pp2 = $this->db->query("SELECT verbconj FROM ".$table." WHERE verbid = ? AND tense = 'imperatiu' AND pers = 2 AND singpl = 'pl'", $verbid)->row('verbconj');
        $verbTime->pp3 = $this->db->query("SELECT verbconj FROM ".$table." WHERE verbid = ? AND tense = 'imperatiu' AND pers = 3 AND singpl = 'pl'", $verbid)->row('verbconj');
    }
    function getFormasNoPersonalesBD($verb, $verbid){
        $table = ($this->session->userdata('ulangabbr') == 'CA' ? 'verbconjugationca': 'verbconjugationes');
        $verb->infinitivo = $this->db->query("SELECT verbconj FROM ".$table." WHERE verbid = ? AND tense = 'infinitiu'", $verbid)->row('verbconj');
        $verb->gerundio = $this->db->query("SELECT verbconj FROM ".$table." WHERE verbid = ? AND tense = 'gerundi'", $verbid)->row('verbconj');
        $verb->participio = $this->db->query("SELECT verbconj FROM ".$table." WHERE verbid = ? AND tense = 'participi'", $verbid)->row('verbconj');
    }

    function getPatterns($verbid){
        $table = ($this->session->userdata('ulangabbr') == 'CA' ? 'patternca': 'patternes');
        $sql = "SELECT verbid, pronominal, defaulttense, subj, subjdef, theme, themetipus, themeprep, receiver, 
        receiverprep, benef, beneftipus, benefprep, acomp, acompprep, tool, toolprep, manera, maneratipus,
        locto, loctotipus, loctoprep, exemple FROM ".$table." WHERE verbid = ?";
        return $this->db->query($sql, $verbid)->result_array();
    }
}
class Verb {
    public $presente;
    public $perfecto;
    public $imperfecto;
    public $pluscuamperfecto;
    public $pasado;
    public $futuro;
    public $prsubj;
    public $impsubj;
    public $imperativo;
    public $infinitivo;
    public $gerundio;
    public $participio;

    function __construct(){
        $this->presente = new Persona();
        $this->perfecto = new Persona();
        $this->imperfecto = new Persona();
        $this->pluscuamperfecto = new Persona();
        $this->pasado = new Persona();
        $this->futuro = new Persona();
        $this->prsubj = new Persona();
        $this->impsubj = new Persona();
        $this->imperativo = new Persona();
    }
}
class Persona {
    public $ps1;
    public $ps2;
    public $ps3;
    public $pp1;
    public $pp2;
    public $pp3;

    function __construct(){
    }
}