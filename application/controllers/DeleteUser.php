<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class DeleteUser extends REST_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->library('session');
        $this->load->model('DeleteUserModel');
        $this->load->model('PanelInterface');
        $this->load->model('Lexicon');
        $this->load->model('BoardInterface');
        $this->load->model('AddWordInterface');
    }

    public function deleteUser_get() {
        // CHECK COOKIES
        /*
        if (!$this->session->userdata('uname')) {
            redirect(base_url(), 'location');
        } else {
            if (!$this->session->userdata('cfguser')) {
                $this->BoardInterface->loadCFG($this->session->userdata('uname'));
                $this->load->view('MainBoard', true);
            } else {
                $this->load->view('MainBoard', true);
            }
        }
    */    
        
        //$postdata = file_get_contents("php://input");
        //$request = json_decode($postdata);
        //$superUserId = $request->superUserId;
        //$userId = $request->userId;
        
        $result = $this->DeleteUserModel->deleteUserBD();
        $response = [
            'result' => $result
        ];
        $response = $result;
        $this->response($response, REST_Controller::HTTP_OK);
    }

}
