<?php

class SuperUserAdminModel extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
        $this->load->database();
    }

    function getBelongingUsers($idSU) {

        return $this->db->query('SELECT s.SUname, s.ID_SU
                                 FROM SuperUser s
                                 WHERE s.ID_SU IN (SELECT Child
                                                     FROM SUSU s
                                                     WHERE s.Parent = ?   
                                                    )',
                                 array($idSU))
                                 ->result();
    }

    function removeBelongingUser($user_to_remove, $superuser){
        
        $this->db->query('DELETE
                          FROM SUSU
                          WHERE Parent = ?
                          AND Child = ?',
                          array($superuser, $user_to_remove));
        
        return 'Success!';
    }

}