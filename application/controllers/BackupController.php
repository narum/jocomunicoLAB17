<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class BackupController extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model("BackupInserts");
        $this->load->model("RecoverBackup");
        $this->load->model("Main_model");
        $this->load->model("BackupClean");
        $this->load->model("BackupInsertsWin");
        $this->load->model("RecoverBackupWin");
        $this->load->model('BoardInterface');
        $this->load->library('session');
    }
    //crea la carpeta para los backups
public function index_get(){
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $data=$this->BackupInsertsWin->createBackupFolder();
  } else {
    $data=$this->BackupInserts->createBackupFolder();
  }
  $response = [
      'data' => $data
  ];
  $this->response($response, REST_Controller::HTTP_OK);
}
//recupera las imagenes y las inserta en la nueva base de datos
public function recimages_post(){
  $overwrite=$this->post('overwrite');
  if($overwrite) $this->BackupClean->LaunchParcialClean_images();
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $data=$this->RecoverBackupWin->LaunchParcialRecover_images();
  } else {
    $data=$this->RecoverBackup->LaunchParcialRecover_images();
  }
  $response = [
      'data' => $data
  ];
  $this->response($response, REST_Controller::HTTP_OK);
}

//recupera el vocabulario y las inserta en la nueva base de datos
public function recvocabulary_post(){
  $overwrite=$this->post('overwrite');
  if($overwrite) $this->BackupClean->LaunchParcialClean_vocabulary();
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $this->RecoverBackupWin->LaunchParcialRecover_vocabulary();
  } else {
    $this->RecoverBackup->LaunchParcialRecover_vocabulary();
  }
}

//recupera las folder y las inserta en la nueva base de datos
public function recfolder_post(){
  $overwrite=$this->post('overwrite');
  if($overwrite) $this->BackupClean->LaunchParcialClean_Folder();
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $this->RecoverBackupWin->LaunchParcialRecover_Folder();
  } else {
    $data=$this->RecoverBackup->LaunchParcialRecover_Folder();
  }
  $response = [
      'data' => $data
  ];
  $this->response($response, REST_Controller::HTTP_OK);
}

//recupera las cfg y las inserta en la nueva base de datos
public function reccfg_post(){
  $overwrite=$this->post('overwrite');
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $this->RecoverBackupWin->LaunchParcialRecover_cfg($overwrite);
  } else {
    $this->RecoverBackup->LaunchParcialRecover_cfg($overwrite);
  }
}
//recupera las panels y las inserta en la nueva base de datos
public function recpanels_post(){
  $overwrite=$this->post('overwrite');
  if($overwrite){
    $mainGboard=true;
    $this->BackupClean->LaunchParcialClean_panels();
  }else{
    $mainGboard=false;
  }
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $data=$this->RecoverBackupWin->LaunchParcialRecover_panels($mainGboard);
  } else {
    $data=$this->RecoverBackup->LaunchParcialRecover_panels($mainGboard);
  }
  $response = [
      'data' => $data
  ];
  $this->response($response, REST_Controller::HTTP_OK);
}
public function checkifparcialexists_get(){
  $data=$this->RecoverBackup->checkifparcialexists();
  $response = [
      'data' => $data
  ];
  $this->response($response, REST_Controller::HTTP_OK);
}
public function checkiftotalexists_get(){
  $data=$this->RecoverBackup->checkiftotalexists();
  $response = [
      'data' => $data
  ];
  $this->response($response, REST_Controller::HTTP_OK);
}
public function recbackup_get(){
  $this->BackupClean->LaunchClean();
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $data=$this->RecoverBackupWin->LaunchTotalRecover();
  } else {
      $data=$this->RecoverBackup->LaunchTotalRecover();
  }
  $response = [
      'data' => $data
  ];
  $this->response($data, REST_Controller::HTTP_OK);
}
}
