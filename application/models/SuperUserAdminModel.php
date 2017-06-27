<?php

class SuperUserAdminModel extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
        $this->load->database();
    }

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
                                                  )',
                                 array($idSU))
                                 ->result();
    }

    /*
     * Remove link SuperUser <-> User
     *
     * @param user_to_remove: user to unlinked from Superuser
     * @param superuser: user who unlink user
     */
    function removeBelongingUser($user_to_remove, $superuser){
        
        $this->db->query('DELETE
                          FROM SUSU
                          WHERE Parent = ?
                          AND Child = ?',
                          array($superuser, $user_to_remove));
        
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
        if(!$this->userIsValidCandidate($userID_Child))
            return 'already_added_or_superuser';

        //Link user
        $this->db->query(
            'INSERT INTO SUSU
            VALUES (?, ?)',
            array($userID_Parent, $userID_Child)
        );

        //Copy images from User to
        /*******************************************************
                          /!\  IMPORTANT  /!\
         *******************************************************
         Hello Jocomunico maintainer!
         SuperUser administration is a tricky topic...
         Don't worry, I'll explaining you about it:

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

        //Check if user is a superuser
        $isSU = $this->db->query(
            'SELECT isSU
             FROM SuperUser s
             WHERE s.ID_SU = ?',
             array($userID_Child)
        );

        if($isSU->result()[0]->isSU == '0')
            return false;

        //Check if user already has a superuser
        $alredy_has_su = $this->db->query(
            'SELECT Parent, Child
             FROM SUSU s
             WHERE s.Child = ?',
             array($userID_Child)
        );

        if($alredy_has_su->num_rows() > 0)
            return false;

        //ALL is OK
        return true;

    }

}