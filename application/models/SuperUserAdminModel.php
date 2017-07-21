<?php

class SuperUserAdminModel extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
        $this->load->database();
    }

    //TODO: Refactor HTTP Requests from client
    // in order to minify Net Traffic & DB Access

    /****************************************
                  CRUD SU Admin
    ****************************************/

    /*
     * Get users linked to SuperUser
     *
     * @param idSu: SuperUser ID
     */
    function getBelongingUsers($idSU) {

        return $this->db->query('SELECT s.SUname, s.ID_SU
                                 FROM SuperUser s
                                 WHERE s.ID_SU IN (SELECT Child
                                                   FROM SUSU s
                                                   WHERE s.Parent = ?
                                                    AND s.Child <> ?
                                                  )',
                                 array($idSU, $idSU))
                                 ->result();
    }

    /*
     * Remove link SuperUser <-> User
     *
     * @param user_to_remove: user to unlinked from Superuser
     * @param superuser: user who unlink user
     */
    function removeBelongingUser($user_to_remove, $superuser){

        //Delete reference from SUSU
        $this->db->query(
            'DELETE
            FROM SUSU
            WHERE Parent = ?
                AND Child = ?',
            array($superuser, $user_to_remove)
        );
        
        //Update SU_is from removed User
        $this->db->query(
            'UPDATE User
            SET SU_is = 0
            WHERE ID_SU = ?',
            array($user_to_remove)
        );
        
        return 'Success!';
    }

    /*
     * Create link SuperUser <-> User
     *
     * @param userID_Parent: SuperUser ID
     * @param userID_Child: User to link ID
     */
    function addBelongingUser($userID_Parent, $userID_Child) {
        
        //Check if try adding current user
        if($userID_Parent == $userID_Child)
            return 'child_as_parent';

        //Check if user already has a superuser
        if($this->userIsValidCandidate($userID_Child) == false)
            return 'already_added_or_superuser';

        //Link user
        $this->db->query(
            'INSERT INTO SUSU
            VALUES (?, ?)',
            array($userID_Parent, $userID_Child)
        );

        //Updating SU reference on User
        $this->db->query(
            'UPDATE User
            SET SU_is = ?
            WHERE ID_USU = ?',
            array($userID_Parent, $userID_Child)
        );

        //Copy images from User to
        /*******************************************************
                          /!\  IMPORTANT  /!\
         *******************************************************
         Hello Jocomunico maintainer!
         SuperUser administration is a tricky topic...
         Don't worry, I'll explaining you about it:
         
         About SuperUser & User ID's:
            - User has an ID: 
                * TABLE SuperUser (ID_SU)
                    - This ID has a reference in TABLE User (ID_USU)

            - This user could be a superuser:
                * TABLE SuperUser (isSU)
                    - is a superuser: 1
                    - not: 0

            - Also could have a SuperUser
                * TABLE SuperUser (SU_is)
                    - REFEFERENCES TABLE SuperUser (ID_SU)

            - If SU_is == 0 => has no superuser
        
         About images & vocabulary:
          - When a new user is added to superuser account,
            ALL belonging users from superuser can access 
            to ALL vocabulary (from all users).

            That's possible cause querys about images
            & vocabulary references to SUSU table
            
            @see: ./

            /!\ : when user is removed from superuser account
            all images & vocabulary references won't be accessed

        
        ********************************************************/

        return 'user_added';
    }

    /*
     * Update SU Admin
     * - if enable  => update isSU value & add new SUSU reference
     * - if disable => delete references from SUSU & update isSU value
     */
    function updateIsSUState($newState, $idUser) {

        //Update isSU value
        $this->db->query(
            'UPDATE SuperUser
            SET isSU = ?
            WHERE ID_SU = ?',
            array(($newState) ? '1' : '0', $idUser)
        );

        //If enable SU Admin:
        if($newState){
            //Create autoreference in SUSU
            $this->db->query(
                'INSERT INTO SUSU
                VALUES (?, ?)',
                array($idUser, $idUser)
            );

            //Update SU_is
            $this->db->query(
                'UPDATE User
                SET SU_is = ?
                WHERE ID_USU = ?',
                array($idUser, $idUser)
            );
        }

        //If disable SU Admin:
        else{
            //Delete references from SUSU
            $this->db->query(
                'DELETE FROM SUSU
                WHERE Parent = ?',
                array($idUser)
            );

            //Set SU_is to 0
            $this->db->query(
                'UPDATE User
                SET SU_is = 0
                WHERE SU_is = ?',
                array($idUser)
            );
        }

    }

    /***************************************
                AUXILIARY METHODS
    ***************************************/

    /*
     * Check if a new user exists and validation
     *
     * @param user: user name
     * @param password: user password
     * @return: - if exists -> user id
     *          - if not    -> null
     */
    function userExists($user, $password) {

        return $this->db->query(
            'SELECT ID_SU
             FROM User u, SuperUser su
             WHERE su.ID_SU = u.ID_USU 
                AND su.SUname = ?
                AND su.pswd = ?
            ',
            array($user, $password)
        )->result()[0]->ID_SU;

    }

    /*
     * Check if user is a SuperUser or if already has a superuser
     *
     * @param userId: user ID to check
     */
    function userIsValidCandidate($userID_Child) {

        //-> Check if user is a superuser
        $isSU = $this->db->query(
            'SELECT isSU
             FROM SuperUser s
             WHERE s.ID_SU = ?',
             array($userID_Child)
        );

        if($isSU->result()[0]->isSU == '1')
            return false;

        //-> Check if user already has a superuser
        $alredy_has_su = $this->db->query(
            'SELECT Parent, Child
             FROM SUSU s
             WHERE s.Child = ?',
             array($userID_Child)
        );
        
        if(!$this->hasSuperUser($userID_Child))
            return false;

        if($alredy_has_su->num_rows() > 0)
            return false;

        //ALL is OK
        return true;

    }

    function hasSuperUser($userID_Child) {
        //-> Check if user already has a superuser
        $alredy_has_su = $this->db->query(
            'SELECT Parent, Child
             FROM SUSU s
             WHERE s.Child = ?',
             array($userID_Child)
        );

        return ($alredy_has_su->num_rows() > 0) ? false : true;
    }

    /*
     * Return all users associated to Superuser
     * @param groupType: getting usergroup for usage:
            - 'images' -> getting images from all users of group
            - 'pictograms' -> getting all pictograms from all users of group
     * @param idUser: User ID
     */
    function getUserGroupOf($groupType, $idUser) {
        
        $usergroup = array();
        //Getting SuperUser from User (SU_is)
        $superUser = $this->db->query(
            'SELECT Parent
            FROM SUSU
            WHERE Child = ?',
            array($idUser)
        )->first_row()->Parent;
        
        switch($groupType){
            case 'images':
                // -> id's for IMAGES
                //Getting all users from group (by ID_SU)
                $query = $this->db->query(
                    'SELECT Child
                    FROM SUSU s
                    WHERE s.Parent = ?',
                    array($superUser)
                );

                array_push($usergroup, $superUser);
                foreach($query->result() as $row)
                    array_push($usergroup, $row->Child);
            break;

            case'pictograms':
                // -> id's for PICTOGRAMS
                //Getting all users from group (by ID_USU)
                $query = $this->db->query(
                    'SELECT ID_User
                    FROM User
                    WHERE SU_is = ?',
                    array($superUser)
                );

                array_push($usergroup, $idUser);
                foreach ($query->result() as $row)
                    array_push($usergroup, $row->ID_User);
            break;
            
            default:
                break;

        }
        
        //Adding default user & own
        array_push($usergroup, '1');
        
        return $usergroup;
    }

}