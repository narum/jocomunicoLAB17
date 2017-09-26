<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class DeleteTemp extends CI_Controller {

  public function __construct(){
      parent::__construct();
      $this->load->helper('file');
  }

  public function index(){
    $dir="Temp";
    delete_files('Temp', TRUE);
  }

}
