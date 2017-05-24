<?php
/*Aqui se produce la recuperacion del backup, hay varias funciones que comprueban si los backups existen,
tambien varias funciones auxiliares al final del fichero las cuales cogen las claves recien insertadas para
evitar colisiones entre claves, en la funcion LaunchTotalRecover es muy importante el orden en el que se ejecutan las
funciones*/
class RecoverBackup extends CI_Model {
    public function __construct(){
        parent::__construct();
        $this->load->library('session');
        $this->load->database();
    }
    //lanza una recuperacion total de los datos, llama a todas las recuperaciones parciales
    public function LaunchTotalRecover(){
      $folder=$this->getLastGlobalBackup();
      $this->UpdateSuperUser($folder);
      $this->UpdateUser($folder);
      $this->InsertPictograms($folder);
      $this->InsertPictogramsLanguage($folder);
      $this->InsertNames($folder);
      $this->InsertAdjectives($folder);
      $this->InsertGroupBoards($folder);
      $this->InsertBoards($folder);
      $this->InsertImages($folder);
      $this->InsertSFolder($folder);
      $this->InsertSHistoric($folder);
      $this->InsertSSentence($folder);
      $this->InsertRSSentencePictograms($folder);
      $this->InsertRSHistoricPictograms($folder);
      $a=$this->InsertCells($folder);
      return $a;
    }
    //Comprueba si existe una carpeta con un backup total
    function checkiftotalexists(){
      $exists=true;
      if($this->getLastGlobalBackup()===0)$exists=false;
      return $exists;
    }
    //Comprueba si existen carpetas con backupParcial
    function checkifparcialexists(){
      $keys=array('images','vocabulary','Folder','cfg','Panels');
      $exists=array();
      for($i=0;$i<5;$i++){
        if($this->getLastParcialBackup($keys[$i])===0){
        array_push($exists,false);
         }else{
          array_push($exists,true);
        }
      }
      return $exists;
    }
    function checkifPictogramsexists(){
      $exists=true;
        if(count($this->getPictokeys())===0) $exists=false;
      return $exists;
    }
    //llama a la recuperacion parcial de imagenes
    function LaunchParcialRecover_images(){
      $Fname=$this->getLastGlobalBackup();
      $this->InsertImages($Fname);
    }
    function LaunchParcialRecover_Pictograms(){
      $Fname=$this->getLastGlobalBackup();
      $this->InsertPictograms($Fname);
      $this->InsertPictogramsLanguage($Fname);
    }
      //llama a la recuperacion parcial de imagenes
    function LaunchParcialRecover_vocabulary(){
      $Fname=$this->getLastGlobalBackup();
      if(!$this->checkifPictogramsexists()){
      $this->LaunchParcialRecover_Pictograms();
      }
      $this->InsertAdjectives($Fname);
      $this->InsertNames($Fname);
    }
    function gg(){
      return $this->getLastGlobalBackup();
    }
      //llama a la recuperacion parcial de la carpetas tematicas
    function LaunchParcialRecover_Folder(){
      $Fname=$this->getLastGlobalBackup();
      $this->InsertSFolder($Fname);
      $this->InsertSHistoric($Fname);
      $this->InsertRSSentencePictograms($Fname);
      $this->InsertRSHistoricPictograms($Fname);
      return $Fname;
    }
      //llama a la recuperacion parcial de configuracion
    function LaunchParcialRecover_cfg(){
      $Fname=$this->getLastGlobalBackup();
      $this->UpdateSuperUser($Fname);
      $this->UpdateUser($Fname);
    }
      //llama a la recuperacion parcial de paneles
    function LaunchParcialRecover_panels($mainGboard){
      $Fname=$this->getLastGlobalBackup();
      if(!$this->checkifPictogramsexists()){
      $this->LaunchParcialRecover_Pictograms();
      }
      $this->InsertGroupBoards($Fname,$mainGboard);
      $this->InsertBoards($Fname);
      $this->InsertCells($Fname);
      $this->InsertRBoardCell($Fname);
      return $mainGboard;
    }
    //devuelve el nombre de la capeta del ultimo backup global
    private function getLastGlobalBackup(){
      $ID_User=$this->session->idusu;
      $dates=array();
      $dirs = array_filter(glob("Temp/*" ,GLOB_ONLYDIR), 'is_dir');
      for($i=0;$i<count($dirs);$i++){
         if($ID_User==substr($dirs[$i],25)&&substr($dirs[$i],29)=="")
          array_push($dates,$dirs[$i]);
      }
     return $this->getLastDate($dates);
    }
  //devuelve el nombre de la capeta del ultimo backup parcial por tematica
public function getLastParcialBackup($key){
  $ID_User=$this->session->idusu;
  $dates=array();
  $dirs = array_filter(glob("backups/*" ,GLOB_ONLYDIR), 'is_dir');
  $lenth=count($dirs);
  for($i=0;$i<$lenth;$i++){
     if($ID_User==substr($dirs[$i],28,1)&&substr($dirs[$i],30)==$key)
      array_push($dates,$dirs[$i]);
  }
 return $this->getLastDate($dates);
}
  //funcion auxiliar que converite fechas a ms las compara
private function getLastDate($dates){
$ant=0;
$dateEnc=0;
$lenth=count($dates);
if($lenth==1){
  $dateEnc=$dates[0];
}else{
  for($i=0;$i<$lenth;$i++){
    $dt = DateTime::createFromFormat("d-m-Y H-i-s",substr($dates[$i],5,19));
  if($dt->getTimestamp()>$ant) $dateEnc=$dates[$i];
  $ant=$dt->getTimestamp();
  }
}
return $dateEnc;
}
//Inserta en la base de datos los registros correspondientes a adjectiveClass
private function InsertAdjectivesClass($Folder){
  $ID_Language=$this->session->uinterfacelangauge;
  $pictokey=$this->getPictokeys();
  switch($ID_Language){
    case 1:
    $table="AdjClassCA";
    break;
    case 2:
    $table="AdjClassES";
    break;
  }
 $file = file_get_contents($Folder."/".$table.".json");
 $adjclass=json_decode($file);
 $count=count($adjclass->class);
 for($i=0;$i<$count;$i++){
  $sql="INSERT INTO $table(adjid, class) VALUES (?, ?)";
  $this->db->query($sql,array(
    $pictokey[$i],
    $adjclass->class[$i]
  ));
}
return $pictokey;
}
//Inserta en la base de datos los registros correspondientes a adjectives
private function InsertAdjectives($Folder){
  $ID_Language=$this->session->uinterfacelangauge;
  $pictokey=$this->getPictokeysType('adj');
  switch($ID_Language){
    case 1:
    $table="AdjectiveCA";
    break;
    case 2:
    $table="AdjectiveES";
    break;
  }
 $file = file_get_contents($Folder."/".$table.".json");
 $adj=json_decode($file);
 $count=count($adj->adjid);
 sort($adj->adjid);
 $pos=-1;
 for($i=0;$i<$count;$i++){
   if($adj->adjid[$i]>$ant&&$adj->adjid[$i]!=null){
     $pos++;
     $ant=$adj->adjid[$i];
   }else{
     $ant=$adj->adjid[$i];
   }
  $sql="INSERT INTO $table(adjid,masc,fem,mascpl,fempl,defaultverb,subjdef) VALUES (?,?,?,?,?,?,?)";
  $this->db->query($sql,array(
    $pictokey[$pos],
    $adj->masc[$i],
    $adj->fem[$i],
    $adj->mascpl[$i],
    $adj->fempl[$i],
    $adj->defaultverb[$i],
    $adj->subjdef[$i]
  ));
}
$this->InsertAdjectivesClass($Folder);
}
//Inserta en la base de datos los registros correspondientes a boards
private function InsertBoards($Folder){
 $gbkeys=$this->getGBkeys();
 $file = file_get_contents($Folder."/Boards.json");
 $boards=json_decode($file);
 $count=count($boards->ID_Board);
 sort($boards->ID_GBBoard);
 $posc=-1;
 for($i=0;$i<$count;$i++){
 if($boards->ID_GBBoard[$i]>$ant&&$boards->ID_GBBoard[$i]!=null){
   $posc++;
   $ant=$boards->ID_GBBoard[$i];
 }else{
   $ant=$boards->ID_GBBoard[$i];
 }
  $sql="INSERT INTO Boards(ID_GBBoard,primaryboard,Bname,width,height,autoReturn,autoReadSentence)
   VALUES (?,?,?,?,?,?,?)";
  $this->db->query($sql,array(
    $gbkeys[$posc],
    $boards->primaryboard[$i],
    $boards->Bname[$i],
    $boards->width[$i],
    $boards->height[$i],
    $boards->autoReturn[$i],
    $boards->autoReadSentence[$i]
  ));
}
return $gbkeys;
}
private function InsertSHistoric($Folder){
  $ID_User=$this->session->idusu;
  $file = file_get_contents($Folder."/S_Historic.json");
  $hs=json_decode($file);
  $count=count($hs->isNegative);
  for($i=0;$i<$count;$i++){
    $sql="INSERT INTO `S_Historic`(`ID_SHUser`, `sentenceType`, `isNegative`, `sentenceTense`, `sentenceDate`,
      `sentenceFinished`, `intendedSentence`, `inputWords`, `inputIds`, `parseScore`, `parseString`, `generatorScore`, `generatorString`,
      `comments`, `userScore`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $this->db->query($sql,array(
      $ID_User,
      $hs->sentenceType[$i],
      $hs->isNegative[$i],
      $hs->sentenceTense[$i],
      $hs->sentenceDate[$i],
      $hs->sentenceFinished[$i],
      $hs->intendedSentence[$i],
      $hs->inputWords[$i],
      $hs->inputIds[$i],
      $hs->parseScore[$i],
      $hs->parseString[$i],
      $hs->generatorScore[$i],
      $hs->generatorString[$i],
      $hs->comments[$i],
      $hs->userScore[$i]
    ));
  }
  return $count;
}
//Inserta en la base de datos los registros correspondientes a cells
private function InsertCells($Folder){
 $ID_Cell=array();
 $a=array();
 $sentencekey=$this->getSentencekey();
 $folderkey=$this->getfolderkey();
 $boardkey=$this->getBoardkey();
 $pictokey=$this->getPictokeys();
 $file = file_get_contents($Folder."/Cell.json");
 $cells=json_decode($file);
 $count=count($cells->ID_Cell);
 $boardlink=array_unique(array_filter($cells->boardLink));
 sort($boardlink);
 $IDCsentence=array_unique(array_filter($cells->ID_CSentence));
 sort($IDCsentence);
 $Sentencefolder=array_unique(array_filter($cells->sentenceFolder));
 sort($Sentencefolder);
 $posp=-1;
 for($i=0;$i<$count;$i++){
   if(!(is_null($cells->boardLink[$i]))){
   for($j=0;$j<count($boardlink);$j++){
     if($boardlink[$j]<=$ant){
             $posc=array_search($boardlink[$j],$boardlink)+1;
             $ant=$cells->boardLink[$i];
     }else{
             $ant=$cells->boardLink[$i];
     }
   }
 }else{
   $posc=null;
   $ant=$cells->boardLink[$i];
 }
       if($cells->ID_CPicto[$i]>$ant1&&$cells->ID_CPicto[$i]!=null){
         $posp++;
         $ant1=$cells->ID_CPicto[$i];
       }else{
         $ant1=$cells->ID_CPicto[$i];
       }

       if(!(is_null($cells->ID_CSentence[$i]))){
         $c=count($IDCsentence);
         if($c>1){
           for($z=0;$z<count($IDCsentence);$z++){
             if($IDCsentence[$z]<=$ant2){
                     $poscs=array_search($IDCsentence[$z],$IDCsentence)+1;
                     $ant2=$cells->ID_CSentence[$i];
             }else{
                     $ant2=$cells->ID_CSentence[$i];
             }
           }
         }else{
           $poscs=array_search($IDCsentence[0],$IDCsentence)+1;
         }
       }else{
         $poscs=null;
         $ant2=$cells->ID_CSentence[$i];
       }
       if(!(is_null($cells->sentenceFolder[$i]))){
         $c=count($Sentencefolder);
         if($c>1){
           for($s=0;$s<count($Sentencefolder);$s++){
             if($Sentencefolder[$s]<=$ant3){
                     $posf=array_search($Sentencefolder[$s],$Sentencefolder)+1;
                     $ant3=$cells->sentenceFolder[$i];
             }else{
                     $ant3=$cells->sentenceFolder[$i];
             }
           }
         }else{
           $posf=array_search($Sentencefolder[0],$Sentencefolder)+1;
         }
       }else{
         $posf=null;
         $ant3=$cells->sentenceFolder[$i];
       }
    $sql="INSERT INTO Cell(isFixedInGroupBoards,imgCell,ID_CPicto,ID_CSentence,sentenceFolder,boardLink,color,
    ID_CFunction,textInCell,textInCellTextOnOff,cellType,activeCell)VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
    $this->db->query($sql,array(
    $cells->isFixedInGroupBoards[$i],
    $cells->imgCell[$i],
    $cells->ID_CPicto[$i],
    $sentencekey[$poscs],
    $folderkey[$posf],
    $boardkey[$posc],
    $cells->color[$i],
    $cells->ID_CFunction[$i],
    $cells->textInCell[$i],
    $cells->textInCellTextOnOff[$i],
    $cells->cellType[$i],
    $cells->activeCell[$i]
  ));
    $query=$this->db->query("SELECT LAST_INSERT_ID() as s2");
    $res=$query->result();
    array_push($ID_Cell,$res[0]->s2);
}
 $this->InsertRBoardCell($Folder,$ID_Cell);
}
//Inserta en la base de datos los registros correspondientes a groupboards
private function InsertGroupBoards($Folder,$mainGboard){
 $ID_User=$this->session->idusu;
 $file = file_get_contents($Folder."/GroupBoards.json");
 $gboards=json_decode($file);
 $count=count($gboards->ID_GBUser);
 if(!$mainGboard){
   for($i=0;$i<$count;$i++){
    $sql="INSERT INTO GroupBoards(ID_GBUser,GBname,primaryGroupBoard,defWidth,defHeight,imgGB)VALUES (?,?,?,?,?,?)";
    $this->db->query($sql,
     array(
      $ID_User,
      $gboards->GBname[$i],
      0,
      $gboards->defWidth[$i],
      $gboards->defHeight[$i],
      $gboards->imgGB[$i]
    ));
  }
}else{
  for($i=0;$i<$count;$i++){
   $sql="INSERT INTO GroupBoards(ID_GBUser,GBname,primaryGroupBoard,defWidth,defHeight,imgGB)VALUES (?,?,?,?,?,?)";
   $this->db->query($sql,
    array(
     $ID_User,
     $gboards->GBname[$i],
     $gboards->primaryGroupBoard[$i],
     $gboards->defWidth[$i],
     $gboards->defHeight[$i],
     $gboards->imgGB[$i]
   ));
 }
}

return $count;
}
//Inserta en la base de datos los registros correspondientes a images
private function InsertImages($Folder){
 $ID_User=$this->session->idsu;
 $file = file_get_contents($Folder."/Images.json");
 $images=json_decode($file);
 $count=count($images->ID_Image);
 for($i=0;$i<$count;$i++){
  $sql="INSERT INTO Images(ID_ISU,imgPath,imgName)VALUES (?,?,?)";
  $this->db->query($sql,array(
    $ID_User,
    $images->imgPath[$i],
    $images->imgName[$i]
  ));
  $this->moveImages($images->imgPath[$i],$images->imgName[$i]);
}
return $images;
}
//Inserta en la base de datos los registros correspondientes a nameclass
private function InsertNameClass($Folder){
  $ID_Language=$this->session->uinterfacelangauge;
  $pictokey=$this->getPictokeys();
  switch($ID_Language){
    case 1:
    $table="NameClassCA";
    break;
    case 2:
    $table="NameClassES";
    break;
  }
 $file = file_get_contents($Folder."/".$table.".json");
 $nclass=json_decode($file);
 $count=count($nclass->nameid);
 for($i=0;$i<$count;$i++){
  $sql="INSERT INTO $table(nameid,class)VALUES (?,?)";
  $this->db->query($sql,array(
    $pictokey[$i],
    $nclass->class[$i]
  ));
}
}
//Inserta en la base de datos los registros correspondientes a names
private function InsertNames($Folder){
  $ID_Language=$this->session->uinterfacelangauge;
  $pictokey=$this->getPictokeysType('name');
  switch($ID_Language){
    case 1:
    $table="NameCA";
    break;
    case 2:
    $table="NameES";
    break;
  }
 $file = file_get_contents($Folder."/".$table.".json");
 $names=json_decode($file);
 $count=count($names->nameid);
 sort($names->nameid);
 $pos=-1;
 for($i=0;$i<$count;$i++){
     if($names->nameid[$i]>$ant&&$names->nameid[$i]!=null){
       $pos++;
       $ant=$names->nameid[$i];
     }else{
       $ant=$names->nameid[$i];
     }
  $sql="INSERT INTO $table(nameid,nomtext,mf,singpl,contabincontab,determinat,ispropernoun,defaultverb,plural,femeni,fempl)
  VALUES (?,?,?,?,?,?,?,?,?,?,?)";
  $this->db->query($sql,array(
    $pictokey[$i],
    $names->nomtext[$i],
    $names->mf[$i],
    $names->singpl[$i],
    $names->contabincontab[$i],
    $names->determinat[$i],
    $names->ispropernoun[$i],
    $names->defaultverb[$i],
    $names->plural[$i],
    $names->femeni[$i],
    $names->fempl[$i]
  ));
}
  $this->InsertNameClass($Folder);
}
//Inserta en la base de datos los registros correspondientes a pictograms
private function InsertPictograms($Folder){
  $ID_User=$this->session->idusu;
 $file = file_get_contents($Folder."/Pictograms.json");
 $pictos=json_decode($file);
 $count=count($pictos->pictoid);
 for($i=0;$i<$count;$i++){
  $sql="INSERT INTO Pictograms(ID_PUser,pictoType,supportsExpansion,imgPicto)
  VALUES (?,?,?,?)";
  $this->db->query($sql,array(
    $ID_User,
    $pictos->pictoType[$i],
    $pictos->supportsExpansion[$i],
    $pictos->imgPicto[$i]
  ));
}
}
//Inserta en la base de datos los registros correspondientes a pcitogramslanguage
private function InsertPictogramsLanguage($Folder){
 $pictokey=$this->getPictokeys();
 $file = file_get_contents($Folder."/PictogramsLanguage.json");
 $plang=json_decode($file);
 $count=count($plang->pictoid);
 for($i=0;$i<$count;$i++){
  $sql="INSERT INTO PictogramsLanguage(pictoid,languageid,insertdate,pictotext,pictofreq)
  VALUES (?,?,?,?,?)";
  $this->db->query($sql,array(
  $pictokey[$i],
  $plang->languageid[$i],
  $plang->insertdate[$i],
  $plang->pictotext[$i],
  $plang->pictofreq[$i]
));
}
}
//Inserta en la base de datos los registros correspondientes a R_BoardCell
private function InsertRBoardCell($Folder,$ID_Cell){
   $boardkey=$this->getBoardkey();
   $file = file_get_contents($Folder."/R_BoardCell.json");
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
      array_push($a,$rbcell->posInBoard[$i]);
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
  return $a;
}
//Inserta en la base de datos los registros correspondientes a R_S_HistoricPictograms
private function InsertRSHistoricPictograms($Folder){
 $histokey=$this->getHistorickey();
 $ID_User=$this->session->idusu;
 $file = file_get_contents($Folder."/R_S_HistoricPictograms.json");
 $rshp=json_decode($file);
 $count=count($rshp->ID_RSHPSentencePicto);
 $posh=-1;
 for($i=0;$i<$count;$i++){
   if($rshp->ID_RSHPSentence[$i]>$ant&&$rshp->ID_RSHPSentence[$i]!=null){
     $posh++;
     $ant=$rshp->ID_RSHPSentence[$i];
   }else{
     $ant=$rshp->ID_RSHPSentence[$i];
   }
  $sql="INSERT INTO R_S_HistoricPictograms(ID_RSHPSentence,pictoid,isplural,isfem,coordinated,ID_RSHPUser,imgtemp)
  VALUES (?,?,?,?,?,?,?)";
  $this->db->query($sql,array(
    $histokey[$posh],
    $rshp->pictoid[$i],
    $rshp->isplural[$i],
    $rshp->isfem[$i],
    $rshp->coordinated[$i],
    $ID_User,
    $rshp->imgtemp[$i]
  ));
}
return $histokey;
}
//Inserta en la base de datos los registros correspondientes a R_S_SentencePictograms
private function InsertRSSentencePictograms($Folder){
 $histokey=$this->getSSentencekey();
 $ID_User=$this->session->idusu;
 $file = file_get_contents($Folder."/R_S_SentencePictograms.json");
 $rssp=json_decode($file);
 $count=count($rssp->ID_RSSPSentencePicto);
 $posh=-1;
 for($i=0;$i<$count;$i++){
   if($rssp->ID_RSSPSentence[$i]>$ant&&$rssp->ID_RSSPSentence[$i]!=null){
     $posh++;
     $ant=$rssp->ID_RSSPSentence[$i];
   }else{
     $ant=$rssp->ID_RSSPSentence[$i];
   }
  $sql="INSERT INTO `R_S_SentencePictograms` (`ID_RSSPSentence`, `pictoid`, `isplural`, `isfem`, `coordinated`, `ID_RSSPUser`, `imgtemp`)
  VALUES (?,?,?,?,?,?,?);";
  $this->db->query($sql,array(
    $histokey[$posh],
    $rssp->pictoid[$i],
    $rssp->isplural[$i],
    $rssp->isfem[$i],
    $rssp->coordinated[$i],
    $ID_User,
    $rssp->imgtemp[$i]
  ));
}
return $rssp;
}
//Inserta en la base de datos los registros correspondientes a S_Folder
private function InsertSFolder($Folder){
 $ID_User=$this->session->idusu;
 $file = file_get_contents($Folder."/S_Folder.json");
 $sf=json_decode($file);
 $count=count($sf->ID_Folder);
  for($i=0;$i<$count;$i++){
  $sql="INSERT INTO S_Folder(ID_SFUser,folderName,folderDescr,imgSFolder,folderColor,folderOrder)
  VALUES (?,?,?,?,?,?)";
  $this->db->query($sql,array(
    $ID_User,
    $sf->folderName[$i],
    $sf->folderDescr[$i],
    $sf->imgSFolder[$i],
    $sf->folderColor[$i],
    $sf->folderOrder[$i]
  ));
}
}
//Inserta en la base de datos los registros correspondientes a S_Sentence
private function InsertSSentence($Folder){
 $ID_User=$this->session->idusu;
 $folderkey=$this->getfolderkey();
 $file = file_get_contents($Folder."/S_Sentence.json");
 $ss=json_decode($file);
 $count=count($ss->ID_SSentence);
 sort($ss->ID_SFolder);
 $pos=-1;
 for($i=0;$i<$count;$i++){
   if($ss->ID_SFolder[$i]>$ant&&$ss->ID_SFolder[$i]!=null){
     $pos++;
     $ant=$ss->ID_SFolder[$i];
   }else{
     $ant=$ss->ID_SFolder[$i];
   }
  $sql="INSERT INTO S_Sentence(ID_SSUser,ID_SFolder,posInFolder,sentenceType,isNegative,sentenceTense,sentenceDate,
  sentenceFinished,intendedSentence,inputWords,inputIds,parseScore,parseString,generatorScore,generatorString,comments,userScore,
  isPreRec,sPreRecText,sPreRecDate,sPreRecImg1,sPreRecImg2,sPreRecImg3,sPreRecPath)
  VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
  $this->db->query($sql,array(
  $ID_User,
  $folderkey[$pos],
  $ss->posInFolder[$i],
  $ss->sentenceType[$i],
  $ss->isNegative[$i],
  $ss->sentenceTense[$i],
  $ss->sentenceDate[$i],
  $ss->sentenceFinished[$i],
  $ss->intendedSentence[$i],
  $ss->inputWords[$i],
  $ss->inputIds[$i],
  $ss->parseScore[$i],
  $ss->parseString[$i],
  $ss->generatorScore[$i],
  $ss->generatorString[$i],
  $ss->comments[$i],
  $ss->userScore[$i],
  $ss->isPreRec[$i],
  $ss->sPreRecText[$i],
  $ss->sPreRecDate[$i],
  $ss->sPreRecImg1[$i],
  $ss->sPreRecImg2[$i],
  $ss->sPreRecImg3[$i],
  $ss->sPreRecPath[$i]
));
}
return $pos;
}
//sobreescribe en la base de datos los registros correspondientes a SuperUser
private function UpdateSuperUser($Folder){
 $ID_SU=$this->session->idsu;
 $ID_User=$this->session->idusu;
 $file = file_get_contents($Folder."/SuperUser.json");
 $su=json_decode($file);
  $sql="UPDATE SuperUser SET realname=?, surnames=?, email=?, cfgDefUser=?, cfgIsFem=?, cfgUsageMouseOneCTwoC=?,
   cfgTimeClick=?, cfgExpansionOnOff=?, cfgAutoEraseSentenceBar=?, cfgPredOnOff=?,
   cfgPredBarVertHor=?, cfgPredBarNumPred=?, cfgScanningOnOff=?, cfgScanningCustomRowCol=?,
   cfgScanningAutoOnOff=?, cfgCancelScanOnOff=?, cfgTimeScanning=?, cfgScanStartClick=?,
   cfgScanOrderPred=?, cfgScanOrderMenu=?, cfgScanOrderPanel=?, cfgScanColor=?,
   cfgMenuReadActive=?, cfgMenuHomeActive=?, cfgMenuDeleteLastActive=?,
   cfgMenuDeleteAllActive=?, cfgSentenceBarUpDown=?, cfgBgColorPanel=?, cfgBgColorPred=?,
   cfgTextInCell=?, cfgUserExpansionFeedback=?, cfgHistOnOff=?, cfgBlackOnWhiteVSWhiteOnBlack=?,
   cfgTimeLapseSelectOnOff=?, cfgTimeLapseSelect=?, cfgTimeNoRepeatedClickOnOff=?,
   cfgTimeNoRepeatedClick=?, UserValidated=?,insertDate=? WHERE ID_SU=?";
  $this->db->query($sql,
  array(
  $su->realname,
  $su->surnames,
  $su->email,
  $ID_User,
  $su->cfgIsFem,
  $su->cfgUsageMouseOneCTwoC,
  $su->cfgTimeClick,
  $su->cfgExpansionOnOff,
  $su->cfgAutoEraseSentenceBar,
  $su->cfgPredOnOff,
  $su->cfgPredBarVertHor,
  $su->cfgPredBarNumPred,
  $su->cfgScanningOnOff,
  $su->cfgScanningCustomRowCol,
  $su->cfgScanningAutoOnOff,
  $su->cfgCancelScanOnOff,
  $su->cfgTimeScanning,
  $su->cfgScanStartClick,
  $su->cfgScanOrderPred,
  $su->cfgScanOrderMenu,
  $su->cfgScanOrderPanel,
  $su->cfgScanColor,
  $su->cfgMenuReadActive,
  $su->cfgMenuHomeActive,
  $su->cfgMenuDeleteLastActive,
  $su->cfgMenuDeleteAllActive,
  $su->cfgSentenceBarUpDown,
  $su->cfgBgColorPanel,
  $su->cfgBgColorPred,
  $su->cfgTextInCell,
  $su->cfgUserExpansionFeedback,
  $su->cfgHistOnOff,
  $su->cfgBlackOnWhiteVSWhiteOnBlack,
  $su->cfgTimeLapseSelectOnOff,
  $su->cfgTimeLapseSelect,
  $su->cfgTimeNoRepeatedClickOnOff,
  $su->cfgTimeNoRepeatedClick,
  $su->UserValidated,
  $su->insertdate,
  $ID_SU
)
);
}
//sobreescribe en la base de datos los registros correspondientes a User
private function UpdateUser($Folder){
 $ID_User=$this->session->idusu;
 $ID_SU=$this->session->idsu;
 $file = file_get_contents($Folder."/User.json");
 $us=json_decode($file);
 $count=count($us->ID_User);
 for($i=0;$i<$count;$i++){
  $sql="UPDATE User SET ID_USU=?,ID_ULanguage=?, ID_UOrg=?,
  cfgExpansionVoiceOnline=?, cfgExpansionVoiceOnlineType=?, cfgExpansionVoiceOffline=?,
  cfgInterfaceVoiceOnOff=?, cfgInterfaceVoiceMascFem=?, cfgInterfaceVoiceOnline=?,
  cfgInterfaceVoiceOffline=?, cfgVoiceOfflineRate=?, cfgExpansionLanguage=?,
  errorTemp=? WHERE ID_User=?";
  $this->db->query($sql,array(
    $ID_SU,
    $us->ID_ULanguage[$i],
    $us->ID_UOrg[$i],
    $us->cfgExpansionVoiceOnline[$i],
    $us->cfgExpansionVoiceOnlineType[$i],
    $us->cfgExpansionVoiceOffline[$i],
    $us->cfgInterfaceVoiceOnOff[$i],
    $us->cfgInterfaceVoiceMascFem[$i],
    $us->cfgInterfaceVoiceOnline[$i],
    $us->cfgInterfaceVoiceOffline[$i],
    $us->cfgVoiceOfflineRate[$i],
    $us->cfgExpansionLanguage[$i],
    $us->errorTemp[$i],
    $ID_User
  ));
}
}
//coge las claves de la tabla boards insertadas anteriormente
private function getBoardkey(){
  $keys=array();
  $ID_User=$this->session->idusu;
  $sql="SELECT ID_Board FROM Boards,GroupBoards WHERE ID_GBUser=? AND ID_GB=ID_GBBoard";
  $query=$this->db->query($sql,$ID_User);
  foreach ($query->result() as $row) {
    array_push($keys,$row->ID_Board);
  }
  return $keys;
}
//coge las claves de la tabla Pictograms insertadas anteriormente
private function getPictokeys(){
  $keys=array();
  $ID_User=$this->session->idusu;
  $sql="SELECT pictoid FROM Pictograms WHERE ID_PUser=?";
  $query=$this->db->query($sql,$ID_User);
  foreach ($query->result() as $row) {
    array_push($keys,$row->pictoid);
  }
  return $keys;
}
private function getPictokeysType($type){
  $keys=array();
  $ID_User=$this->session->idusu;
  $sql="SELECT pictoid FROM Pictograms WHERE ID_PUser=? AND pictoType=?";
  $query=$this->db->query($sql,array($ID_User,$type));
  foreach ($query->result() as $row) {
    array_push($keys,$row->pictoid);
  }
  return $keys;
}
//coge las claves de la tabla Groupboards insertadas anteriormente
private function getGBkeys(){
  $keys=array();
  $ID_User=$this->session->idusu;
  $sql="SELECT ID_GB FROM GroupBoards WHERE ID_GBUser=?";
  $query=$this->db->query($sql,$ID_User);
  foreach ($query->result() as $row) {
    array_push($keys,$row->ID_GB);
  }
  return $keys;
}
//coge las claves de la tabla S_Folder insertadas anteriormente
private function getfolderkey(){
  $keys=array();
  $ID_User=$this->session->idusu;
  $sql="SELECT ID_Folder FROM S_Folder WHERE ID_SFUser=?";
  $query=$this->db->query($sql,$ID_User);
  foreach ($query->result() as $row) {
    array_push($keys,$row->ID_Folder);
  }
  return $keys;
}
//coge las claves de la tabla S_Sentence insertadas anteriormente
private function getSentencekey(){
  $keys=array();
  $ID_User=$this->session->idusu;
  $sql="SELECT ID_SSentence FROM S_Sentence WHERE ID_SSUser=?";
  $query=$this->db->query($sql,$ID_User);
  foreach ($query->result() as $row) {
    array_push($keys,$row->ID_SSentence);
  }
  return $keys;
}
private function getSSentencekey(){
  $keys=array();
  $ID_User=$this->session->idusu;
  $sql="SELECT ID_SSentence FROM S_Sentence WHERE ID_SSUser=? AND isPreRec='0'";
  $query=$this->db->query($sql,$ID_User);
  foreach ($query->result() as $row) {
    array_push($keys,$row->ID_SSentence);
  }
  return $keys;
}
private function getHistorickey(){
  $keys=array();
  $ID_User=$this->session->idusu;
  $sql="SELECT ID_SHistoric FROM S_Historic WHERE ID_SHUser=?";
  $query=$this->db->query($sql,$ID_User);
  foreach ($query->result() as $row) {
    array_push($keys,$row->ID_SHistoric);
  }
  return $keys;
}
//mueve las imagenes del backup al servidor para que la aplicacion pueda usarlas
private function moveImages($imgPath,$imgName){
  if(strlen($imgName)==36&&(substr($imgName,34)=='png'||substr($imgName,34)=='jpg')){
    copy('./Temp/'.$Fname.'/'.'images/'.$imgName , $imgPath);
  }
}
}
?>
