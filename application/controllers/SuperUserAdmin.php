<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class SuperUserAdmin extends REST_Controller {

  public function __construct(){
    parent::__construct();
    $this->load->model('SuperUserAdminModel');
  }

  /************************************************
                    CONTROLLER MAP
                    --------------
    - isSU: __GET
    - changeIsSUState : __POST
    - getBelongingUsers: __GET
    - removeBelongingUser: __POST
    - userExists: __POST

   ************************************************/

  //Check if user is a superuser
  public function isSU_get() {
    $this->response(
        [
            'isSU' => ($this->session->userdata('isSU') == '1') ? true : false
        ],

        REST_Controller::HTTP_OK);
  }

  public function hasSuperUser_get() {
      $this->response(
          [
              'isValid' => $this->SuperUserAdminModel
                           ->hasSuperUser($this->session->userdata('idsu'))
          ],
          REST_Controller::HTTP_OK
      );
  }

  public function resetUserAfterRegister_post() {
      $this->session->set_userdata('idusu', $this->post('idusu'));
      $this->session->set_userdata('idsu',  $this->post('idsu'));

      $this->response([], REST_Controller::HTTP_OK);
  }

  /*
   * Change SuperUser State and return new value
   * @param newState: update isSU value
   *                  - if (true) => enable isSU
   *                  - if (false) => disable isSU
   */
  public function changeIsSUState_post(){
      //Seting new cookie value
      $this->session->set_userdata('isSU',
                                    ($this->post('newState')) ? '1' : '0');
      $this->SuperUserAdminModel->updateIsSUState(
                                    $this->post('newState'),
                                    $this->session->userdata('idsu')
                                );

      $this->response(
          [
              'newState' => $this->post('newState')
          ],

          REST_Controller::HTTP_OK
      );
  }

  //Response belonging Users
  public function getBelongingUsers_get() {
      $this->response(
          [
              'belongingUsers' => $this->SuperUserAdminModel
                                  ->getBelongingUsers($this->session->userdata('idsu'))
          ],
          REST_Controller::HTTP_OK);
  }
  
  //Remove (only a link) between user and superuser 
  public function removeBelongingUser_post() {

      $this->response(
          [
              'successMessage' => $this->SuperUserAdminModel
                                  ->removeBelongingUser(
                                        $this->post('user_to_remove'),
                                        $this->session->userdata('idsu')
                                    )
          ],
          REST_Controller::HTTP_OK);
  }

  //Return if user alredy exists
  public function userExists_post() {

      $this->response(
          [
              'userExists' => $this->SuperUserAdminModel
                             ->userExists(
                                 $this->post('user'),
                                 $this->post('password')
                             )
          ],
          REST_Controller::HTTP_OK
    );
  }

  public function addBelongingUser_post() {
      $this->response(
          [
              'statusCode' => $this->SuperUserAdminModel
                              ->addBelongingUser(
                                  $this->session->userdata('idsu'), //SuperUser ID
                                  $this->post('idUser') //User ID
                              )
          ],
          REST_CONTROLLER::HTTP_OK
      );
  }

}