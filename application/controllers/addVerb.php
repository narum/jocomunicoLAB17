<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class addVerb extends REST_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->library('session');
        $this->load->model('addVerbModel');
        $this->load->model('PanelInterface');
        $this->load->model('Lexicon');
        $this->load->model('BoardInterface');
        $this->load->model('AddWordInterface');
    }

    public function getConjugations_post() {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $verb = $request->verb;
        $result = $this->addVerbModel->conjugateVerb($verb);
        $this->response($result, REST_Controller::HTTP_OK);
    }

}
