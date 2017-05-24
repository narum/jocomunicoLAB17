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
        $this->load->model('BoardInterface');
        $this->load->library('session');
    }
    //crea la carpeta para los backups
public function index_get(){
  $data=$this->BackupInserts->createBackupFolder();
  $response = [
      'data' => $data
  ];
  $this->response($response, REST_Controller::HTTP_OK);
}
//llama al backup parcial de imagenes
public function images_get(){
  $this->BackupInserts->createParcialBackupFolder_images();
}

//llama al backup parcial del vocabulario
public function vocabulary_get(){
  $this->BackupInserts->createParcialBackupFolder_vocabulary();
}

//llama al backup parcial de la carpeta
public function folder_get(){
  $data=$this->BackupInserts->createParcialBackupFolder_Folder();
  $response = [
      'data' => $data
  ];
  $this->response($response, REST_Controller::HTTP_OK);
}

//llama al backup parcial de la configuracion
public function cfg_get(){
  $this->BackupInserts->createParcialBackupFolder_cfg();
}

//llama al backup parcial de los paneles
public function panels_get(){
  $this->BackupInserts->createParcialBackupFolder_Panels();
}
//recupera las imagenes y las inserta en la nueva base de datos
public function recimages_post(){
  $overwrite=$this->post('overwrite');
  if($overwrite) $this->BackupClean->LaunchParcialClean_images();
  $data=$this->RecoverBackup->LaunchParcialRecover_images();
  $response = [
      'data' => $data
  ];
  $this->response($response, REST_Controller::HTTP_OK);
}

//recupera el vocabulario y las inserta en la nueva base de datos
public function recvocabulary_post(){
  $overwrite=$this->post('overwrite');
  if($overwrite) $this->BackupClean->LaunchParcialClean_vocabulary();
  $this->RecoverBackup->LaunchParcialRecover_vocabulary();
}

//recupera las folder y las inserta en la nueva base de datos
public function recfolder_post(){
  $overwrite=$this->post('overwrite');
  if($overwrite) $this->BackupClean->LaunchParcialClean_Folder();
  $data=$this->RecoverBackup->LaunchParcialRecover_Folder();
  $response = [
      'data' => $overwrite
  ];
  $this->response($response, REST_Controller::HTTP_OK);
}

//recupera las cfg y las inserta en la nueva base de datos
public function reccfg_post(){
  $overwrite=$this->post('overwrite');
  $this->RecoverBackup->LaunchParcialRecover_cfg();

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
  $data=$this->RecoverBackup->LaunchParcialRecover_panels($mainGboard);
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
  $data=$this->RecoverBackup->LaunchTotalRecover();
  $response = [
      'data' => $data
  ];
  $this->response($response, REST_Controller::HTTP_OK);
}
public function probando_get(){
  $data=$this->RecoverBackup->LaunchTotalRecover();
  $response = [
      'data' => $data
  ];
  $this->response($response, REST_Controller::HTTP_OK);
}
}
