<?php

class DeleteUserModel extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library("session");
    }

    function deleteUserBD() {
       
        $superUserid = $this->session->userdata('idsu');
        $userid = $this->session->userdata('idusu');
        $usersOfSuperUser = $this->db->query(
                   "SELECT Child FROM SUSU WHERE Parent = ?", $superUserid);
  
        if ($usersOfSuperUser->num_rows() > 0) {//IS SUPERUSER
            foreach ($usersOfSuperUser->result() as $row) {
                /* Delete all the Users related to the SuperUser
                 * and the SuperUser (itself).
                 */
                $idUSUChild = $this->db->query(
                   "SELECT ID_User FROM User WHERE ID_USU = ?", $row->Child)->row(ID_User);
                $this->DeleteAll($row->Child, $idUSUChild);
            }
                $this->DeleteAll($superUserid,$userid);
                $this->deleteParentChilds($superUserid);
            
        }else {// Only is SuperUser of himself.
               $this->DeleteAll($superUserid,$userid);
        }
    }
    
    function deleteUser($idUsuario){
                $this->db->query("DELETE FROM S_Folder WHERE ID_SFUser = ?", $idUsuario);
                $this->db->query("DELETE FROM S_Sentence WHERE ID_SSUser = ?", $idUsuario);
                $this->db->query("DELETE FROM R_S_Sentencepictograms WHERE ID_RSSPUser = ?", $idUsuario);
                $this->db->query("DELETE FROM User WHERE ID_User = ?", $idUsuario);
    }
    
    function deleteSuperUser($idSuperUser){
                $this->db->query("DELETE FROM Superuser WHERE ID_SU = ?", $idSuperUser);
    }
    
    function deleteSuperUserImages($idSuperUser){
                $imgPaths = $this->db->query(
                "SELECT imgPath FROM Images WHERE ID_ISU = ?", $idSuperUser); 
                foreach($imgPaths->result() as $row){
                    unlink($row->imgPath);
                }
                $this->db->query("DELETE FROM Images WHERE ID_ISU = ?", $idSuperUser);     
    }
    
    function deleteUserPictograms($idUser){
                $imgPictoPaths = $this->db->query(
                        "SELECT imgPicto FROM Pictograms WHERE ID_PUser = ?", $idUser);
                        foreach($imgPictoPaths->result() as $row){
                            $img = strval($row->imgPicto);
                            unlink('img/pictos/'.$img);
                        }
                        $this->db->query("DELETE FROM Pictograms WHERE ID_PUser = ?", $idUser);    
    }
    
    function DeleteAll($superUserid,$userid){
               $this->deleteUserPictograms($userid);
               $this->deleteUser($userid);
               $this->deleteSuperUserImages($superUserid);
               $this->deleteSuperUser($superUserid);  
    }
    
    function deleteParentChilds($idSuperUser){
                $this->db->query("DELETE FROM SUSU WHERE Parent = ?", $idSuperUser);
    }
    
}
