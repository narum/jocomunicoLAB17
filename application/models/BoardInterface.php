<?php
class BoardInterface extends CI_Model {
    function __construct() {
        // Call the Model constructor
        parent::__construct();
        $this->load->library('Myword');
    }
    /*
     * Load the user config
     */
    function loadCFG($user) {
        $newdata = array(
            'cfguser' => 1,
            'cfgExpansionOnOff' => 1,
            'cfgPredOnOff' => 1,
            'cfgPredBarVertHor' => 0,
            'cfgSentenceBarUpDown' => 1
        );
        $this->session->set_userdata($newdata);
    }
    /*
     * Get the board struct (columns, rows, name...)
     */
    function getBoardStruct($id) {
        $output = array();
        $idusu = $this->session->userdata('idusu');
        $this->db->where('ID_GBUser', $idusu);
        $this->db->where('ID_Board', $id);
        $this->db->join('GroupBoards', 'GroupBoards.ID_GB = Boards.ID_GBBoard');
        $query = $this->db->get('Boards');
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;
        return $output;
    }
    /*
     * Change the board struct (columns and rows)
     */
    function updateNumCR($c, $r, $id) {
        $output = array();
        $this->db->where('ID_Board', $id);
        $query = $this->db->update('Boards', array('width' => $c, 'height' => $r));
        return $output;
    }
    /*
     * Change the name of one board from ID of the board
     */
    function updateName($Name, $id) {
        $output = array();
        $this->db->where('ID_Board', $id);
        $query = $this->db->update('Boards', array('Bname' => $Name));
        return $output;
    }
    /*
     * Return all pictograms from board
     */
    function getCellsBoard($id) {
        $output = array();
        $idlang = $this->session->userdata('uinterfacelangauge');
        $lang = $this->session->userdata('ulangabbr');
        $this->db->where('R_BoardCell.ID_RBoard', $id);
        $this->db->group_by('R_BoardCell.posInBoard');
        $this->db->order_by('R_BoardCell.posInBoard', 'asc');
        $this->db->join('Cell', 'R_BoardCell.ID_RCell = Cell.ID_Cell');
        //Este tiene que ser left, si pictograms.picto id = null significa que esta vacia
        $this->db->join('Pictograms', 'Cell.ID_CPicto = Pictograms.pictoid', 'left');
        $this->db->join('PictogramsLanguage', 'Pictograms.pictoid = PictogramsLanguage.pictoid AND PictogramsLanguage.languageid = "' . $idlang . '"', 'left');
        $this->db->join('Function', 'Cell.ID_CFunction = Function.ID_Function', 'left');
        $this->db->join('S_Folder', 'S_Folder.ID_Folder = Cell.sentenceFolder', 'left');
        $this->db->join('S_Sentence', 'S_Sentence.ID_SSentence = Cell.ID_CSentence', 'left');
        $this->db->join('Boards', 'Boards.ID_Board = Cell.boardLink', 'left');
        $this->db->select('*, functName' . $lang . ' as textFunction');
        $query = $this->db->get('R_BoardCell');
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;
        return $output;
    }
    /*
     * Return one pictogram from the board with the given position in this board
     */
    function getCell($pos, $idboard) {
        $output = array();
        $idlang = $this->session->userdata('uinterfacelangauge');
        $this->db->where('R_BoardCell.ID_RBoard', $idboard);
        $this->db->where('R_BoardCell.posInBoard', $pos);
        $this->db->join('Cell', 'R_BoardCell.ID_RCell = Cell.ID_Cell');
        //Este tiene que ser left, si pictograms.picto id = null significa que esta vacia
        $this->db->join('Pictograms', 'Cell.ID_CPicto = Pictograms.pictoid', 'left');
        $this->db->join('PictogramsLanguage', 'Pictograms.pictoid = PictogramsLanguage.pictoid AND PictogramsLanguage.languageid = "' . $idlang . '"', 'left');
        $this->db->join('Function', 'Cell.ID_CFunction = Function.ID_Function', 'left');
        // $this->db->group_by('Pictograms.pictoid');
        $query = $this->db->get('R_BoardCell');
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else {
            $output = null;
        }
        return $output;
    }
    /*
     * Change one pictogram from the board to another position
     */
    function updatePosCell($oldPos, $newPos, $idBoard) {
        $output = array();
        $this->db->where('posInBoard', $oldPos);
        $this->db->where('ID_RBoard', $idBoard);
        $this->db->update('R_BoardCell', array('posInBoard' => $newPos));
        return $output;
    }
    /*
     * Change the values of a cell from cell database table
     */
    function updateMetaCell($id, $visible, $textInCell, $isFixed, $idFunc, $idboard, $idpicto, $idSentence, $idSFolder, $cellType, $color, $imgCell) {
        $output = array();
        $data = array(
            'activeCell' => $visible,
            'textInCell' => $textInCell,
            'isFixedInGroupBoards' => $isFixed,
            'ID_CFunction' => $idFunc,
            'boardLink' => $idboard,
            'ID_CPicto' => $idpicto,
            'ID_CSentence' => $idSentence,
            'sentenceFolder' => $idSFolder,
            'cellType' => $cellType,
            'color' => $color,
            'imgCell' => $imgCell
        );
        $this->db->where('ID_Cell', $id);
        $this->db->update('Cell', $data);
        return $output;
    }
    /*
     * Change scan values by the output scan values
     */
    function updateScanCell($id, $num1, $text1, $num2, $text2) {
        $output = array();
        $data = array('customScanBlock1' => $num1,
            'customScanBlockText1' => $text1,
            'customScanBlock2' => $num2,
            'customScanBlockText2' => $text2);
        $this->db->where('ID_RCell', $id);
        $this->db->update('R_BoardCell', $data);
        return $output;
    }
    /*
     * Change the cell pictogram by another pictogram
     */
    function updatePictoCell($id, $idPicto) {
        $output = array();
        $this->db->where('ID_Cell', $id);
        $this->db->update('Cell', array('ID_CPicto' => $idPicto));
        return $output;
    }
    /*
     * Create a NULL cell (blank cell) in the position ($Pos)
     * and add the cell to the board ($idBoard)
     */
    function newCell($Pos, $idBoard) {
        $output = array();
        $data = array(
            'ID_Cell' => 'NULL'
                /* MODIF: Probar despues del lunes por si acaso falla que no nos pille en la presentacion
                  ,
                  'color' => 'fff' */
        );
        $this->db->insert('Cell', $data);
        $id = $this->db->insert_id();
        $data = array(
            'ID_RBoard' => $idBoard,
            'ID_RCell' => $id,
            'posInBoard' => $Pos
        );
        $this->db->insert('R_BoardCell', $data);
    }
    /*
     * Return the cell ID in position ($Pos) from the board ($idBoard)
     */
    function getIDCell($Pos, $idBoard) {
        $output = array();
        $this->db->where('posInBoard', $Pos);
        $this->db->where('ID_RBoard', $idBoard);
        $query = $this->db->get('R_BoardCell');
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;
        return $output;
    }
    /*
     * Change the data of one pictogram ($cell) from the board ($idpicto)
     */
    function updateDataCell($idpicto, $cell) {
        $output = array();
        $data = array(
            'imgCell' => NULL,
            'textInCellTextOnOff' => '1',
            'textInCell' => NULL,
            'ID_CFunction' => NULL,
            'boardLink' => NULL,
            'ID_CPicto' => $idpicto,
            'ID_CSentence' => NULL,
            'sentenceFolder' => NULL,
            'cellType' => 'picto',
        );
        $this->db->where('ID_Cell', $cell);
        $this->db->update('Cell', $data);
        return $output;
    }
    /*
     * Remove the data of one pictogram ($cell) from the board ($idpicto)
     */
    function removeDataCell($cell) {
        $output = array();
        $data = array(
            'imgCell' => NULL,
            'activeCell' => '1',
            'textInCellTextOnOff' => '1',
            'textInCell' => NULL,
            'isFixedInGroupBoards' => NULL,
            'ID_CFunction' => NULL,
            'boardLink' => NULL,
            'ID_CPicto' => NULL,
            'ID_CSentence' => NULL,
            'sentenceFolder' => NULL,
            'cellType' => 'other',
            'color' => 'fff'
        );
        $this->db->where('ID_Cell', $cell);
        $this->db->update('Cell', $data);
        $data = array(
            'isMenu' => 0,
            'customScanBlock1' => 1,
            'customScanBlockText1' => NULL,
            'customScanBlock2' => NULL,
            'customScanBlockText2' => NULL
        );
        $this->db->where('ID_RCell', $cell);
        $this->db->update('R_BoardCell', $data);
        return $output;
    }
    /*
     * Remove the cell ($id) from the board ($idBoard). Remove the link too
     */
    function removeCell($id, $idBoard) {
        $this->db->where('ID_RBoard', $idBoard);
        $this->db->where('ID_RCell', $id);
        $this->db->delete('R_BoardCell');
        $this->db->where('ID_Cell', $id);
        $this->db->delete('Cell');
    }
    /*
     * Init a DB transaction
     */
    function initTrans() {
        $this->db->trans_start();
    }
    /*
     * Ends a DB transaction. Commit change if nothing gone worng. Otherwise
     * makes a rollback
     */
    function commitTrans() {
        $this->db->trans_complete();
    }
    /*
     * Return true if the last end transaction was a commit, else return false
     */
    function statusTrans() {
        return $this->db->trans_status();
    }
    /*
     * Return the last word added to the sentence
     */
    function getLastWord($idusu) {
        $output = array();
        $this->db->where('ID_RSTPUser', $idusu);
        $this->db->order_by('ID_RSTPSentencePicto', 'desc');
        $query = $this->db->get('R_S_TempPictograms');
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;
        return $output[0];
    }

    /*
    * #Jorge: Return the word chosen of the sentence.
    */

    function getWordSelected($idusu, $pos){
      $output = array();
      $this-> db-> where('ID_RSTPUser', $idusu);
      $query = $this->db->get('R_S_TempPictograms');
      if ($query->num_rows() > 0) {
          $output = $query->result();
      } else{
          $output = null;
      }
      return $output[$pos];
    }







    /*
     * Remove the sentence from the tabla temp
     */
    function removeSentence($idusu) {
        $this->db->where('ID_RSTPUser', $idusu);
        $this->db->delete('R_S_TempPictograms');
    }
    /*
     * Return the function information
     */
    function getFunction($id) {
        $this->db->where('ID_Function', $id);
        $query = $this->db->get('Function');
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;
        return $output;
    }
    /*
     * Return all functions
     */
    function getFunctions() {
        $language = $this->session->userdata('ulangabbr');
        $this->db->order_by('name', 'asc');
        $this->db->select('ID_Function, functName' . $language . ' AS name');
        $query = $this->db->get('Function');
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;
        return $output;
    }
    /*
     * Return all user boards in the same group
     */
    function getIDGroupBoards($idboard) {
        $this->db->where('ID_Board', $idboard);
        $query = $this->db->get('Boards');
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = "null";
        return $output;
    }
    /*
     * Return all user boards in the same group
     */
    function getBoards($idgroup) {
        $this->db->order_by('primaryBoard', 'desc');
        $this->db->order_by('Bname', 'asc');
        $this->db->where('ID_GBBoard', $idgroup);
        $query = $this->db->get('Boards');
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;
        return $output;
    }
    /*
     * Return primarygroupboard from a board group
     */
    function getPrimaryGroupBoard() {
        $idusu = $this->session->userdata('idusu');
        $this->db->where('primaryGroupBoard', '1');
        $this->db->where('ID_GBUser', $idusu);
        $query = $this->db->get('GroupBoards');
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;
        return $output;
    }
    function getInfoGroupBoard($idgroup) {
        $this->db->where('ID_GB', $idgroup);
        $query = $this->db->get('GroupBoards');
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;
        return $output;
    }
    /*
     * Return primaryboard from a board group
     */
    function getPrimaryBoard($idgroup) {
        $this->db->where('primaryBoard', '1');
        $this->db->where('ID_GBBoard', $idgroup);
        $query = $this->db->get('Boards');
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;
        return $output;
    }
    /*
     * Set this board the primry
     */
    function setPrimaryBoard($id) {
        $this->db->where('ID_Board', $id);
        $this->db->update('Boards', array(
            'primaryBoard' => '1',
        ));
    }
    /*
     * Return all user boards in the same group
     */
    function getAllBoards() {
        $idusu = $this->session->userdata('idusu');
        $this->db->where('ID_GBUser', $idusu);
        $this->db->join('GroupBoards', 'ID_GB = ID_GBBoard');
        $query = $this->db->get('Boards');
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;
        return $output;
    }
    /*
     * Return all sentences from user
     */
    function getSentences($idusu, $idsearch) {
        $this->db->like('sPreRecText', $idsearch);
        $this->db->where('isPreRec', '1');
        $this->db->where('ID_SSUser', $idusu);
        $query = $this->db->get('S_Sentence');
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;
        return $output;
    }
    /*
     * Return the sentence of the input id sentence
     */
    function getSentence($id) {
        $this->db->where('ID_SSentence', $id);
        $query = $this->db->get('S_Sentence');
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;
        return $output[0];
    }
    /*
     * Return all folders from user
     */
    function getSFolders($idusu, $idsearch) {
        $this->db->like('folderName', $idsearch);
        $this->db->where('ID_SFUser', $idusu);
        $query = $this->db->get('S_Folder');
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;
        return $output;
    }
    /*
     * Return a folder of input id folder
     */
    function getSFolder($id) {
        $this->db->where('ID_Folder', $id);
        $query = $this->db->get('S_Folder');
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;
        return $output[0];
    }
    /*
     * ADD MODIFIER TO A NOUN THAT WAS JUST ENTERED
     */
    function afegirModifNom($modif) {
        $idusu = $this->session->userdata('idusu');
        $this->db->where('ID_RSTPUser', $idusu);
        $query = $this->db->get('R_S_TempPictograms');
        if ($query->num_rows() > 0) {
            $aux = $query->result();
            $nrows = $query->num_rows();
            $identry = $aux[$nrows - 1]->ID_RSTPSentencePicto;
            if ($modif == 'pl') {
                $data = array(
                    'isplural' => '1',
                );
            }
            if ($modif == 'fem') {
                $data = array(
                    'isfem' => '1',
                );
            }
            if ($modif == 'i') {
                $data = array(
                    'coordinated' => '1',
                );
            }
            $this->db->where('ID_RSTPSentencePicto', $identry);
            $this->db->update('R_S_TempPictograms', $data);
        }
    }
    /*
     * Set the board (id) primary in the group (idboard)
     */
    function changePrimaryBoard($id, $idboard) {
        $this->db->where('ID_GBBoard', $idboard);
        $this->db->update('Boards', array(
            'primaryBoard' => '0',
        ));
        $this->db->where('ID_Board', $id);
        $this->db->update('Boards', array(
            'primaryBoard' => '1',
        ));
    }
    /*
     * Change the value of autoreturn from board
     */
    function changeAutoReturn($id, $value) {
        $this->db->where('ID_Board', $id);
        $this->db->update('Boards', array(
            'autoReturn' => $value,
        ));
    }
    /*
     * Get autoreturn value
     */
    function getAutoReturn($id) {
        $this->db->where('ID_Board', $id);
        $this->db->get('Boards');
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;
        return $output[0];
    }
    function changeAutoReadSentence($id, $value) {
        $this->db->where('ID_Board', $id);
        $this->db->update('Boards', array(
            'autoReadSentence' => $value,
        ));
    }
    function getAutoReadSentence($id) {
        $this->db->where('ID_Board', $id);
        $this->db->get('Boards');
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;
        return $output[0];
    }
    function createBoard($IDGboard, $name, $width, $height) {
        $data = array(
            'ID_GBBoard' => $IDGboard,
            'Bname' => $name,
            'width' => $width,
            'height' => $height
        );
        $this->db->insert('Boards', $data);
        $id = $this->db->insert_id();
        return $id;
    }
    function copyBoard($IDGboard, $name, $width, $height, $autoReturn, $autoReadSentence) {
        $data = array(
            'ID_GBBoard' => $IDGboard,
            'Bname' => $name,
            'width' => $width,
            'height' => $height,
            'autoReturn' => $autoReturn,
            'autoReadSentence' => $autoReadSentence
        );
        $this->db->insert('Boards', $data);
        $id = $this->db->insert_id();
        return $id;
    }
    function getBoardTables($idSrc) {
        $this->db->where('ID_RBoard', $idSrc);
        $this->db->join('R_BoardCell', 'R_BoardCell.ID_RCell = Cell.ID_Cell', 'left');
        $query = $this->db->get('Cell');
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;
        return $output;
    }
    public function AddBoards(){
      $this->LaunchClean();
      $GBID=$this->createGroupBoard();
      $this->InsertBoards($GBID);
      $this->InsertCells();
      return $GBID;
    }
    private function LaunchClean(){
      $this->cleanRBoardCell();
      $this->cleanCells();
      $this->cleanBoards();
      $this->cleanGroupBoards();
    }
    private function createGroupBoard(){
      $ID_User=$this->session->idusu;
      $ID_Language=$this->session->uinterfacelangauge;
      if($ID_Language==1){
        $GBname='Exemple';
      }else{
        $GBname='Ejemplo';
      }
       $sql="INSERT INTO `GroupBoards` (`ID_GBUser`, `GBname`, `primaryGroupBoard`, `defWidth`, `defHeight`, `imgGB`)
       VALUES (?,?,?,?,?,?)";
       $a=$this->db->query($sql,array($ID_User,$GBname,0,6,5,NULL));
       $query=$this->db->query("SELECT LAST_INSERT_ID() as s2");
       $res=$query->result();
       return $res[0]->s2;
    }
    private function cleanGroupBoards(){
      $ID_User=$this->session->idusu;
      $ID_Language=$this->session->uinterfacelangauge;
      if($ID_Language==1){
        $GBname='nous Grups';
      }else{
        $GBname='Grupos nuevos';
      }
      $sql="DELETE GroupBoards FROM GroupBoards WHERE GBname=? AND ID_GBUser=?";
      $this->db->query($sql,array($GBname,$ID_User));
    }
    private function cleanRBoardCell(){
      $ID_User=$this->session->idusu;
      $boardkey=$this->getBoardkey();
      for($i=0;$i<count($boardkey);$i++){
        $sql="DELETE R_BoardCell FROM R_BoardCell INNER JOIN Cell ON
        R_BoardCell.ID_RCell = Cell.ID_Cell AND R_BoardCell.ID_RBoard=?";
        $this->db->query($sql,$boardkey[$i]);
      }
    }
    private function cleanCells(){
      $ID_User=$this->session->idusu;
      $boardkey=$this->getBoardkey();
      for($i=0;$i<count($boardkey);$i++){
      $sql="DELETE Cell FROM Cell INNER JOIN R_BoardCell ON
      R_BoardCell.ID_RCell = Cell.ID_Cell AND R_BoardCell.ID_RBoard=?";
      $this->db->query($sql,$boardkey[$i]);
     }
    }
    private function cleanBoards(){
      $ID_User=$this->session->idusu;
      $sql="DELETE Boards FROM Boards INNER JOIN GroupBoards ON GroupBoards.ID_GB = Boards.ID_GBBoard AND GroupBoards.ID_GBUser=?
      AND Boards.Bname NOT LIKE '%T. V.%' AND Boards.Bname NOT LIKE '%T. F.%' AND
      Boards.Bname NOT LIKE '%P. 1%' AND Boards.Bname NOT LIKE '%P. 2%'";
      $this->db->query($sql,$ID_User);
    }
    private function InsertBoards($gbkey){
      $ID_Language=$this->session->uinterfacelangauge;
      if($ID_Language==1){
        $filename='./boards/BoardsC.json';
      }else{
        $filename='./boards/Boards.json';
      }
     $file = file_get_contents($filename);
     $boards=json_decode($file);
     $count=count($boards->ID_Board);
     for($i=0;$i<$count;$i++){
      $sql="INSERT INTO Boards(ID_GBBoard,primaryboard,Bname,width,height,autoReturn,autoReadSentence)
       VALUES (?,?,?,?,?,?,?)";
      $this->db->query($sql,array(
        $gbkey,
        $boards->primaryboard[$i],
        $boards->Bname[$i],
        $boards->width[$i],
        $boards->height[$i],
        $boards->autoReturn[$i],
        $boards->autoReadSentence[$i]
      ));
    }
    return $count;
    }
    private function getBoardkey(){
      $keys=array();
      $ID_User=$this->session->idusu;
      $sql="SELECT * FROM Boards,GroupBoards WHERE GroupBoards.ID_GBUser=? AND
      GroupBoards.ID_GB=Boards.ID_GBBoard AND Boards.Bname NOT LIKE '%T. V.%' AND Boards.Bname NOT LIKE '%T. F.%' AND
      Boards.Bname NOT LIKE '%P. 1%' AND Boards.Bname NOT LIKE '%P. 2%'";
      $query=$this->db->query($sql,$ID_User);
      foreach ($query->result() as $row) {
        array_push($keys,$row->ID_Board);
      }
      return $keys;
    }
    private function InsertCells(){
     $ID_Cell=array();
     $boardkey=$this->getBoardkey();
     $ID_Language=$this->session->uinterfacelangauge;
     if($ID_Language==1){
       $file = file_get_contents("./boards/CellC.json");
       $fileb=file_get_contents("./boards/BoardsC.json");
     }else{
       $file = file_get_contents("./boards/Cell.json");
       $fileb=file_get_contents("./boards/Boards.json");
     }

     $boards=json_decode($fileb);
     $cells=json_decode($file);
     $count=count($cells->ID_Cell);
     for($i=0;$i<$count;$i++){
       if(!(is_null($cells->boardLink[$i]))){
           $posc=array_search($cells->boardLink[$i],$boards->ID_Board);
       }else{
           $posc=null;
       }
        $sql="INSERT INTO Cell(isFixedInGroupBoards,imgCell,ID_CPicto,ID_CSentence,sentenceFolder,boardLink,color,
        ID_CFunction,textInCell,textInCellTextOnOff,cellType,activeCell)VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->db->query($sql,array(
        $cells->isFixedInGroupBoards[$i],
        $cells->imgCell[$i],
        $cells->ID_CPicto[$i],
        $cells->ID_CSentence[$i],
        $cells->sentenceFolder[$i],
        $boardkey[$posc],
        $cells->color[$i],
        $cells->ID_CFunction[$i],
        $cells->textInCell[$i],
        $cells->textInCellTextOnOff[$i],
        $cells->cellType[$i],
        $cells->activeCell[$i]
      ));
      array_push($a,$sentencekey[$poscs]);
        $query=$this->db->query("SELECT LAST_INSERT_ID() as s2");
        $res=$query->result();
        array_push($ID_Cell,$res[0]->s2);
    }
     return $this->InsertRBoardCell($ID_Cell);
    }
    private function InsertRBoardCell($ID_Cell){
       $boardkey=$this->getBoardkey();
       $ID_Language=$this->session->uinterfacelangauge;
       if($ID_Language==1){
         $file = file_get_contents("./boards/R_BoardCellC.json");
       }else{
         $file = file_get_contents("./boards/R_BoardCell.json");
       }

       $rbcell=json_decode($file);
       $count=count($rbcell->ID_RBoard);
       sort($rbcell->ID_RBoard);
       $a=array();
       $posc=-1;
       for($i=0;$i<$count;$i++){
       if($rbcell->ID_RBoard[$i]>$ant){
         $posc++;
         $ant=$rbcell->ID_RBoard[$i];
       }else{
         $ant=$rbcell->ID_RBoard[$i];
       }
        $sql="INSERT INTO R_BoardCell(ID_RBoard,ID_RCell,posInBoard,isMenu,posInMenu,customScanBlock1,customScanBlockText1,customScanBlock2,
          customScanBlockText2)VALUES (?,?,?,?,?,?,?,?,?)";
          array_push($a,$boardkey[$posc]);
        $this->db->query($sql,array(
          $boardkey[$posc],
          $ID_Cell[$i],
          $rbcell->posInBoard[$i],
          $rbcell->isMenu[$i],
          $rbcell->posInMenu[$i],
          $rbcell->customScanBlock1[$i],
          $rbcell->customScanBlockText1[$i],
          $rbcell->customScanBlock2[$i],
          $rbcell->customScanBlockText2[$i]
        ));
      }
      return $boardkey;
    }
    function copyBoardTables(&$idDst, &$sameGroupBoard, &$row) {
        if ($sameGroupBoard === 0) {
            $row->boardLink = null;
        }
        $data = array(
            'isFixedInGroupBoards' => $row->isFixedInGroupBoards,
            'imgCell' => $row->imgCell,
            'ID_CPicto' => $row->ID_CPicto,
            'ID_CSentence' => $row->D_CSentence,
            'sentenceFolder' => $row->sentenceFolder,
            'boardLink' => $row->boardLink,
            'color' => $row->color,
            'ID_CFunction' => $row->ID_CFunction,
            'textInCell' => $row->textInCell,
            'textInCellTextOnOff' => $row->textInCellTextOnOff,
            'cellType' => $row->cellType,
            'activeCell' => $row->activeCell
        );
        $this->db->insert('Cell', $data);
        $id = $this->db->insert_id();
        $data2 = array(
            'ID_RBoard' => $idDst,
            'ID_RCell' => $id,
            'posInBoard' => $row->posInBoard,
            'isMenu' => $row->isMenu,
            'customScanBlock1' => $row->customScanBlock1,
            'customScanBlockText1' => $row->customScanBlockText1,
            'customScanBlock2' => $row->customScanBlock2,
            'customScanBlockText2' => $row->customScanBlockText2
        );
        $this->db->insert('R_BoardCell', $data2);
    }
    function removeBoard($IDboard) {
        $this->db->where('ID_Board', $IDboard);
        $this->db->delete('Boards');
    }
    function removeGoupBoard($IDGB) {
        $this->db->where('ID_GB', $IDGB);
        $this->db->delete('GroupBoards');
    }
    function removeBoardLinks($IDboard) {
        $output = array();
        $data = array(
            /* 'imgCell' => NULL,
              'activeCell' => 1,
              'textInCellTextOnOff' => 1,
              'textInCell' => NULL,
              'isFixedInGroupBoards' => NULL,
              'ID_CFunction' => NULL, */
            'boardLink' => NULL
                /* 'ID_CPicto' => NULL,
                  'ID_CSentence' => NULL,
                  'sentenceFolder' => NULL,
                  'cellType' => NULL,
                  'color' => 'fff' */
        );
        $this->db->where('boardLink', $IDboard);
        $this->db->update('Cell', $data);
    }
    function getMaxScanBlock1($IDboard) {
        $output = array();
        $this->db->order_by('customScanBlock1', 'desc');
        $this->db->where('ID_RBoard', $IDboard);
        $query = $this->db->get('R_BoardCell');
        if ($query->num_rows() > 0) {
            $output = $query->result()[0]->customScanBlock1;
        } else
            $output = null;
        return $output;
    }
    function getColumns($IDboard) {
        $output = array();
        $this->db->select('width');
        $this->db->where('ID_Board', $IDboard);
        $query = $this->db->get('Boards');
        if ($query->num_rows() > 0) {
            $output = $query->result()[0]->width;
        } else
            $output = null;
        return $output;
    }
    function getRows($IDboard) {
        $output = array();
        $this->db->select('height');
        $this->db->where('ID_Board', $IDboard);
        $query = $this->db->get('Boards');
        if ($query->num_rows() > 0) {
            $output = $query->result()[0]->height;
        } else
            $output = null;
        return $output;
    }
    function getMaxScanBlock2($IDboard, $scanGroup) {
        $output = array();
        $this->db->order_by('customScanBlock2', 'desc');
        $this->db->where('customScanBlock1', $scanGroup);
        $this->db->where('ID_RBoard', $IDboard);
        $query = $this->db->get('R_BoardCell');
        if ($query->num_rows() > 0) {
            $output = $query->result()[0]->customScanBlock2;
        } else
            $output = null;
        return $output;
    }
    function getScannedCells($IDboard, $csb1, $csb2) {
        $output = array();
        $this->db->order_by('posInBoard', 'asc');
        $this->db->where('customScanBlock2', $csb2);
        $this->db->where('customScanBlock1', $csb1);
        $this->db->where('ID_RBoard', $IDboard);
        $query = $this->db->get('R_BoardCell');
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;
        return $output;
    }
    function getAudioSentence($md5) {
        $output = array();
        $this->db->where('mp3TSMd5Encoded', $md5);
        $query = $this->db->get('mp3');
        if ($query->num_rows() > 0) {
            $output = $query->result();
        } else
            $output = null;
        return $output;
    }
    function getIdLastSentence($idusu) {
        $this->db->where('ID_SHUser', $idusu);
        $this->db->order_by('ID_SHistoric', 'desc');
        $query = $this->db->get('S_Historic');
        if ($query->num_rows() > 0) {
            $aux = $query->result();
            return $aux[0]->ID_SHistoric;
        } else
            return null;
    }
    function score($id, $score) {
        $data = array('userScore' => $score);
        $this->db->where('ID_SHistoric', $id);
        $this->db->update('S_Historic', $data);
    }
    function modifyColorCell($id, $color) {
        $data = array('color' => $color);
        $this->db->where('ID_Cell', $id);
        $this->db->update('Cell', $data);
    }
    /*
     * Get the last img asociate to the picto
     */
    function getImgCell($id) {
        $idusu = $this->session->userdata('idusu');
        $this->db->where('P_StatsUserPicto.ID_PSUPUser', $idusu);
        $this->db->where('P_StatsUserPicto.pictoid', $id);
        $query = $this->db->get('P_StatsUserPicto');
        if ($query->num_rows() > 0) {
            $aux = $query->result();
            return $aux[0]->imgtemp;
        } else
            return null;
    }
    function updateImgCell($id, $imgCell) {
        $data = array(
            'imgCell' => $imgCell
        );
        $this->db->where('ID_Cell', $id);
        $this->db->update('Cell', $data);
        $this->db->where('ID_Cell', $id);
        $query = $this->db->get('Cell');
        if ($query->num_rows() > 0) {
            $aux = $query->result();
            return $aux[0]->ID_CPicto;
        } else
            return null;
    }
    function getColors() {
        $idLanguage = $this->session->userdata('uinterfacelangauge');
        $this->db->select('tagString, content'); // Seleccionar les columnes
        $this->db->from('Content'); // Seleccionem la taula
        $this->db->where('section', 'color'); // filtrem per columnes
        $this->db->where('ID_CLanguage', $idLanguage); // filtrem per columnes
        $this->db->order_by('Content.content', 'asc');
        $query = $this->db->get(); // Fem la query i la guardem a la variable query
        return $query->result_array(); // retornem l'array query amb els resultats
    }
    function get_errorText($errorID) {
        $idLanguage = $this->session->userdata('uinterfacelangauge');
        $this->db->select('tagString, content'); // Seleccionar les columnes
        $this->db->from('Content'); // Seleccionem la taula
        $this->db->where('tagString', $errorID); // filtrem per columnes
        $this->db->where('ID_CLanguage', $idLanguage); // filtrem per columnes
        $query = $this->db->get(); // Fem la query i la guardem a la variable query
        return $query->result_array();
    }

    function ErrorAudioToDB($errorCode) {
        $idUser = $this->session->userdata('idusu');
        $this->db->set('errorTemp', $errorCode);
        $this->db->where('ID_User', $idUser);
        $this->db->update('User');
    }

}
