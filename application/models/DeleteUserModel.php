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
        $isSuperUserOf = $this->db->query(
                "SELECT SuperUserIs FROM User WHERE ID_USU = ?", $superUserid);
        $users_superUserIs = $this->db->query(
                "SELECT ID_User FROM User WHERE SuperUserIs = ?", $superUserid);
        $superusers_superUserIs = $this->db->query(
                "SELECT ID_USU FROM User WHERE SuperUserIs = ?", $superUserid);
        
        if ($users_superUserIs->num_rows() > 1) {
        /* CAMBIAR CUANDO RAUL IMPLEMENTE SUPERUSUARIOS */
        //if ($isSuperUserOf->num_rows() > 1) {//ES SUPERUSUARIO
            foreach ($users_superUserIs->result() as $row) {
                /* Relate all the Users related to the SuperUser
                 * and the SuperUser (itself).
                 */
                $this->deleteUserPictograms($userid);
                $this->deleteUser($row->ID_User);
            }
            //Delete SuperUsers Related to our SuperUser and their Images
            foreach ($superusers_superUserIs->result() as $row) {
                $this->deleteSuperUserImages($row->ID_USU);
                $this->deleteSuperUser($row->ID_USU);
            }
        }else {// Only is SuperUser of himself.
               $this->deleteUserPictograms($userid);
               $this->deleteUser($userid);
               $this->deleteSuperUserImages($superUserid);
               $this->deleteSuperUser($superUserid);
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
                "SELECT imgPicto FROM pictograms WHERE ID_PUser = ?", $idUser);
                foreach($imgPictoPaths->result() as $row){
                    $img = strval($row->imgPicto);
                    unlink('img/pictos/'.$img);
                }
                $this->db->query("DELETE FROM pictograms WHERE ID_PUser = ?", $idUser);    
    }
    
}
