<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class SuperUserAdmin extends REST_Controller {

  public function __construct(){
    parent::__construct();
    $this->load->model('SuperUserAdminModel');
  }

  public function getBelongingUsers_get(){
      //Response belonging Users
      $this->response(
          [
              'belongingUsers' => $this->SuperUserAdminModel
                                  ->getBelongingUsers($this->session->userdata('idusu'))
          ],
          REST_Controller::HTTP_OK);
  }

  public function removeBelongingUser_post() {

      $this->response(
          [
              'successMessage' => $this->SuperUserAdminModel
                                  ->removeBelongingUser(
                                                        $this->post('user_to_remove'),
                                                        $this->session->userdata('idusu')
                                                       )
          ]
      );
  }

}