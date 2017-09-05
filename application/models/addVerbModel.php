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
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.224 Safari/534.10');
        $html = curl_exec($curl); curl_close($curl);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->substituteEntities = TRUE;
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        //file_put_contents('C:\Users\Hector\html.html', $dom->saveHTML($dom->documentElement));
        $xpath = new DOMXPath($dom);
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
        $verb->presente->ps1 = $xpath->query('//*[@id="main"]/div[3]/div[1]/div[3]/div/div[1]/table/tr[1]/td[2]/span')->item(0)->nodeValue;
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