<?php
/*Aqui se produce la recuperacion del backup, hay varias funciones que comprueban si los backups existen,
tambien varias funciones auxiliares al final del fichero las cuales cogen las claves recien insertadas para
evitar colisiones entre claves, en la funcion LaunchTotalRecover es muy importante el orden en el que se ejecutan las
funciones*/
class RecoverBackup extends CI_Model {
    public function __construct(){
        parent::__construct();
        $this->load->library('session');
        $gbcont=count($this->getGBkeys());
        $bcont=count($this->getBoardkey());
        $scont=count($this->getSentencekey());
        $fcont=count($this->getfolderkey());
        $hcont=count($this->getHistorickey());
        $pcont=count($this->getPictokeys());
        $this->load->database();
    }

    function checklang(){
      $Fname=$this->getLastGlobalBackup();
      $ID_Language=$this->session->uinterfacelangauge;
      $file = file_get_contents($Folder."/User.json");
      $us=json_decode($file);
      if($ID_Language==$us->ID_ULanguage)
       return true;
       else return false;
    }
    //llama a la recuperacion parcial de imagenes
    function LaunchParcialRecover_images($Fname){
      $Fname="Temp/".$Fname;
      $this->InsertImages($Fname);
      return $Fname;
    }
    function LaunchParcialRecover_Pictograms($Fname){
      $Fname="Temp/".$Fname;
      $this->InsertPictograms($Fname);
      $this->InsertPictogramsLanguage($Fname);
      return $Fname;
    }
      //llama a la recuperacion parcial de imagenes
    function LaunchParcialRecover_vocabulary($Fname){
      if(!$this->checkifPictogramsexists()){
      $this->LaunchParcialRecover_Pictograms($Fname);
      }
      $Fname="Temp/".$Fname;
      $this->InsertAdjectives($Fname);
      $this->InsertNames($Fname);
      return $Fname;
    }
      //llama a la recuperacion parcial de la carpetas tematicas
    function LaunchParcialRecover_Folder($Fname){
      $Fname="Temp/$Fname";
      $cfold=count($this->getfolderkey());
      $bla=$this->InsertSFolder($Fname);
      $this->InsertSSentence($Fname,$cfold);
      $this->InsertSHistoric($Fname);
      $this->InsertRSSentencePictograms($Fname,$scont);
      $this->InsertRSHistoricPictograms($Fname,$hcont);
      return $bla;
    }
      //llama a la recuperacion parcial de configuracion
    function LaunchParcialRecover_cfg($ow,$Fname){
      $Fname="Temp/".$Fname;
      $this->UpdateSuperUser($Fname,$ow);
      $this->UpdateUser($Fname);
      return $Fname;
    }
    function checkifPictogramsexists(){
     $exists=true;
       if(count($this->getPictokeys())===0) $exists=false;
     return $exists;
   }

      //llama a la recuperacion parcial de paneles
    function LaunchParcialRecover_panels($mainGboard,$Fname){

      if(!$this->checkifPictogramsexists()){
      $this->LaunchParcialRecover_Pictograms($Fname);
      }
      $Fname="Temp/$Fname";
      $gbcont=count($this->getGBkeys());
      $bcont=count($this->getBoardkey());
      $pcont=count($this->getPictokeys());
      $this->InsertGroupBoards($Fname,$mainGboard);
      $this->InsertBoards($Fname,$gbcont);
      $this->InsertCells($Fname,$bcont,$scont,$fcont,$pcont);
      return $Fname;
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
private function InsertBoards($Folder,$gbcont){
 $gbkeys=$this->getGBkeys();
 $file = file_get_contents($Folder."/Boards.json");
 $fileGB = file_get_contents($Folder."/GroupBoards.json");
 $boards=json_decode($file);
 $GB=json_decode($fileGB);
 $count=count($boards->ID_Board);
 $gbkeys=array_slice($gbkeys,$gbcont);
 for($i=0;$i<$count;$i++){
   if(!(is_null($boards->ID_GBBoard[$i]))){
       $posc=array_search($boards->ID_GBBoard[$i],$GB->ID_GB);
   }else{
       $posc=null;
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
return count($gbkeys);
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
private function InsertCells($Folder,$bcont,$scont,$fcont,$pcont){
 $ID_Cell=array();
 $a=array();
 $sentencekey=$this->getSentencekey();
 $folderkey=$this->getfolderkey();
 $boardkey=$this->getBoardkey();
 $pictokey=$this->getPictokeys();

 $file = file_get_contents($Folder."/Cell.json");
 $files = file_get_contents($Folder."/Boards.json");
 $filesent = file_get_contents($Folder."/S_sentence.json");
 $filefol= file_get_contents($Folder."/S_Folder.json");
 $picto=file_get_contents($Folder."/Pictograms.json");

 $cells=json_decode($file);
 $boards=json_decode($files);
 $sentences=json_decode($filesent);
 $pic=json_decode($picto);
 $sfolder=json_decode($filefol);

 $boardkey=array_slice($boardkey,$bcont);
 $sentencekey=array_slice($sentencekey,$scont);
 $folderkey=array_slice($folderkey,$fcont);
 $count=count($cells->ID_Cell);
 for($i=0;$i<$count;$i++){
   if(!(is_null($cells->boardLink[$i]))){
       $posc=array_search($cells->boardLink[$i],$boards->ID_Board);
   }else{
       $posc=null;
   }
   if(!(is_null($cells->ID_CSentence[$i]))){
       $poscs=array_search($cells->ID_CSentence[$i],$sentences->ID_SSentence);
    }else{
       $poscs=null;
    }
   if(!(is_null($cells->sentenceFolder[$i]))){
       $posf=array_search($cells->sentenceFolder[$i],$sfolder->ID_Folder);
   }else{
       $posf=null;
   }
   if($cells->ID_CPicto[$i]>2020){
          $posp=array_search($cells->ID_CPicto[$i],$pic->pictoid);
          $picto=$pictokey[$posp];
   }else{
     $picto=$cells->ID_CPicto[$i];
   }
   array_push($a,$poscs);
    $sql="INSERT INTO Cell(isFixedInGroupBoards,imgCell,ID_CPicto,ID_CSentence,sentenceFolder,boardLink,color,
    ID_CFunction,textInCell,textInCellTextOnOff,cellType,activeCell)VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
    $this->db->query($sql,array(
    $cells->isFixedInGroupBoards[$i],
    $cells->imgCell[$i],
    $picto,
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
 $this->InsertRBoardCell($Folder,$ID_Cell,$bcont);
 return $sentencekey;
}
//Inserta en la base de datos los registros correspondientes a groupboards
private function InsertGroupBoards($Folder){
 $ID_User=$this->session->idusu;
 $file = file_get_contents($Folder."/GroupBoards.json");
 $gboards=json_decode($file);
 $count=count($gboards->ID_GBUser);
   for($i=0;$i<$count;$i++){
     if($i==0 && !$this->existsmain()) $mainGboard="1"; else $mainGboard="0";
    $sql="INSERT INTO GroupBoards(ID_GBUser,GBname,primaryGroupBoard,defWidth,defHeight,imgGB)VALUES (?,?,?,?,?,?)";
    $this->db->query($sql,
     array(
      $ID_User,
      $gboards->GBname[$i],
      $mainGboard,
      $gboards->defWidth[$i],
      $gboards->defHeight[$i],
      $gboards->imgGB[$i]
    ));
  }
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
private function InsertRBoardCell($Folder,$ID_Cell,$bcont){
   $boardkey=$this->getBoardkey();
   $file = file_get_contents($Folder."/R_BoardCell.json");
   $fileB=file_get_contents($Folder."/Boards.json");
   $rbcell=json_decode($file);
   $boards=json_decode($fileB);
   $count=count($rbcell->ID_RBoard);
   $boardkey=array_slice($boardkey,$bcont);
   for($i=0;$i<$count;$i++){
     if(!(is_null($rbcell->ID_RBoard[$i]))){
         $posc=array_search($rbcell->ID_RBoard[$i],$boards->ID_Board);
     }else{
         $posc=null;
     }
    $sql="INSERT INTO R_BoardCell(ID_RBoard,ID_RCell,posInBoard,isMenu,posInMenu,customScanBlock1,customScanBlockText1,customScanBlock2,
      customScanBlockText2)VALUES (?,?,?,?,?,?,?,?,?)";
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
}
//Inserta en la base de datos los registros correspondientes a R_S_HistoricPictograms
private function InsertRSHistoricPictograms($Folder,$scont){
  $a=array();
 $histokey=$this->getHistorickey();
 $ID_User=$this->session->idusu;
 $file = file_get_contents($Folder."/R_S_HistoricPictograms.json");
 $his=file_get_contents($Folder."/S_Historic.json");
 $hist=json_decode($his);
 $rshp=json_decode($file);
 $histokey=array_slice($histokey,$scont);
 $count=count($rshp->ID_RSHPSentencePicto);
 for($i=0;$i<$count;$i++){
   if(!(is_null($rshp->ID_RSHPSentence[$i]))){
       $posh=array_search($rshp->ID_RSHPSentence[$i],$hist->ID_SHistoric);
   }else{
       $posh=null;
   }
   array_push($a,$posh);
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
return $rshp;
}
//Inserta en la base de datos los registros correspondientes a R_S_SentencePictograms
private function InsertRSSentencePictograms($Folder,$scont){
 $histokey=$this->getSSentencekey();
 $ID_User=$this->session->idusu;
 $file = file_get_contents($Folder."/R_S_SentencePictograms.json");
 $his = file_get_contents($Folder."/S_Sentence.json");
 $rssp=json_decode($file);
 $hist=json_decode($his);
 $histokey=array_slice($histokey,$scont);
 $count=count($rssp->ID_RSSPSentencePicto);
 for($i=0;$i<$count;$i++){
      if(!(is_null($rssp->ID_RSSPSentence[$i]))){
          $posh=array_search($rssp->ID_RSSPSentence[$i],$hist->ID_SSentence);
      }else{
          $posh=null;
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
return $sf;
}
//Inserta en la base de datos los registros correspondientes a S_Sentence
private function InsertSSentence($Folder,$folds){
 $ID_User=$this->session->idusu;
 $folderkey=$this->getfolderkey();
 $file = file_get_contents($Folder."/S_Sentence.json");
 $filefol= file_get_contents($Folder."/S_Folder.json");
 $ss=json_decode($file);
 $sfolder=json_decode($filefol);
 $count=count($ss->ID_SSentence);
 $folderkey=array_slice($folderkey,$folds);
 for($i=0;$i<$count;$i++){
   if(!(is_null($ss->ID_SFolder[$i]))){
       $posf=array_search($ss->ID_SFolder[$i],$sfolder->ID_Folder);
   }else{
       $posf=null;
   }
  $sql="INSERT INTO S_Sentence(ID_SSUser,ID_SFolder,posInFolder,sentenceType,isNegative,sentenceTense,sentenceDate,
  sentenceFinished,intendedSentence,inputWords,inputIds,parseScore,parseString,generatorScore,generatorString,comments,userScore,
  isPreRec,sPreRecText,sPreRecDate,sPreRecImg1,sPreRecImg2,sPreRecImg3,sPreRecPath)
  VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
  $this->db->query($sql,array(
  $ID_User,
  $folderkey[$posf],
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
}
//sobreescribe en la base de datos los registros correspondientes a SuperUser
private function UpdateSuperUser($Folder,$ow){
 $ID_SU=$this->session->idsu;
 $ID_User=$this->session->idusu;
 $file = file_get_contents($Folder."/SuperUser.json");
 $su=json_decode($file);
 if($ow){
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
  ));
 }else{
   $sql="UPDATE SuperUser SET cfgDefUser=?, cfgIsFem=?, cfgUsageMouseOneCTwoC=?,
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
  ));
 }
}
//sobreescribe en la base de datos los registros correspondientes a User
private function UpdateUser($Folder){
 $ID_User=$this->session->idusu;
 $ID_SU=$this->session->idsu;
 $file = file_get_contents($Folder."/User.json");
 $us=json_decode($file);
 $count=count($us->ID_User);
 for($i=0;$i<$count;$i++){
  $sql="UPDATE User SET ID_USU=?,ID_ULanguage=?, ID_UOrg=?, cfgExpansionLanguage=?,
  errorTemp=? WHERE ID_User=?";
  $this->db->query($sql,array(
    $ID_SU,
    $us->ID_ULanguage[$i],
    $us->ID_UOrg[$i],
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
private function existsmain(){
  $exists=false;
  $ID_User=$this->session->idusu;
  $sql="SELECT primaryGroupBoard FROM GroupBoards WHERE ID_GBUser=? AND primaryGroupBoard='1'";
  $query=$this->db->query($sql,$ID_User);
  if($query->num_rows()>0) $exists=true;
  return $exists;
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
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      copy('/xampp/htdocs/Temp/'.$Fname.'/'.'images/'.$imgName , $imgPath);
    } else {
      copy('./Temp/'.$Fname.'/'.'images/'.$imgName , $imgPath);
    }

  }
}
}
?>
