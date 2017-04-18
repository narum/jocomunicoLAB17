<?php

/* Provide a Word Document
 * @rjlopezdev
 */
class WordDocument_Model extends CI_Model {

  //Data folder
  var $folder_name;

  //WordDocument
  var $phpWord;
  var $sentences;
  //WordDocument Styles
  var $imgStyle;
  var $headerStyle;
  var $sentenceStyle;
  var $tableStyle;

  function __construct(){
      parent::__construct();
      $this->load->database();
      $this->phpWord = new \PhpOffice\PhpWord\PhpWord();
  }

  public function getWordDocument($folder_data){

    $this->sentences = $folder_data;
    $this->setStyles();

    // Adding a Section to the document
    $section = $this->phpWord->addSection();

    $section->addText($this->sentences['folderTitle'], $this->headerStyle);
    $section->addTextBreak();

    foreach ($this->sentences['preRecSentences'] as $i) {

      $table = $section->addTable($this->tableStyle);

      $table->addRow();
      $table->addCell(150)->addImage(
        $i['image1'],
        $this->imgStyle
      );
      $table->addCell(150)->addImage(
        $i['image2'],
        $this->imgStyle
      );
      $table->addCell(150)->addImage(
        $i['image3'],
        $this->imgStyle
      );

      $section->addText($i['imgText'], $this->sentenceStyle);
      $section->addTextBreak();
    }

    foreach ($this->sentences['NOTpreRecSentences'] as $i) {
      $count = 0;

      $table = $section->addTable($this->tableStyle);
      $first = $i[0];

      foreach ($i as $e) {
        if($e === $first){
          $section->addText($e, $this->sentenceStyle);
          $section->addTextBreak();
        }else {
          if($count++ % 5 == 0) $table->addRow();
          switch ($e) {
            case 'y.png':
              $table->addCell(150)->addImage(
                'img/pictosespeciales/' . $e,
                $this->imgStyle
              );
              break;
            case 'plural.png':
              $table->addCell(150)->addImage(
                'img/pictosespeciales/' . $e,
                $this->imgStyle
              );
              break;
            case 'femenino.png':
              $table->addCell(150)->addImage(
                'img/pictosespeciales/' . $e,
                $this->imgStyle
              );
              break;
            default:
              $table->addCell(150)->addImage(
                'img/pictos/' . $e,
                $this->imgStyle
              );
              break;
          }
        }
      }
    }

    //Save WordDocument on tempory folder
    $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($this->phpWord, 'Word2007');
    $file_name = './Temp/' . time() . '.docx';
    $objWriter->save($file_name);

    return $file_name;
  }

  /* Preset WordDocument styles
   * @rjlopezdev
   */
  private function setStyles(){
    //Image style
    $this->imgStyle = array(
        'width'         => 100,
        'height'        => 100,
        'marginTop'     => -1,
        'marginLeft'    => -1,
        'wrappingStyle' => 'behind'
    );
    //Title style
    $this->headerStyle = array(
      'bold'            => true,
      'size'            => 32,
      'color'           => '#000000'
    );
    //Table style
    $this->tableStyle = array(
      'borderColor'     => '#000000',
      'borderSize'      => 10,
      'cellMargin'      => 50
    );
    //Sentence style
    $this->sentenceStyle = array(
      'bold'            => true,
      'size'            => 16,
      'color'           => '#000000',
      'valign'          => 'center'
    );

  }

}

?>
