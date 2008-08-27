<?php

/*
Copyright (c) 2008, Benjamin Schaffer (a.k.a. Yitzchak Schaffer)
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice,
    this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright
    notice, this list of conditions and the following disclaimer in the
    documentation and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF
THE POSSIBILITY OF SUCH DAMAGE.
*/

require_once 'XML/Serializer.php';
require_once 'LCHebrewDetrans.class.php';

class Record
{
  private $id = '';
  private $fields = array();

  public function addField($code, $content)
  {
    try {
      $f = new Field($code, $content);
      $this->fields[] = $f;
    } catch (Exception $e) {
      // ignore
    }
  }
  
  public function setId($id) {
    if (!preg_match('/b[0-9x]{8}/', $id)) {
      throw new Exception('Invalid record ID');
    }
    
    $this->id = $id;
  }
  
  public function getArray()
  {
    $fieldArray = array();
    foreach ($this->fields as $f) {
      $fieldArray[] = $f->getArray();
    }
    
    return array(
      'id'      => $this->id,
      'fields'  => $fieldArray
    );
  }
}

class Field
{
  private $code = '00000';
  private $content = '';
  private $hebrewCode = null;
  private $hebrewContentOPAC = null;
  private $hebrewContentUTF = null;
  
  public function __construct($code, $text)
  {
    if (!preg_match('/[0-9]{3}[0-9b]{2}/', $code)) {
      throw new Exception("Invalid field code: $code");
    }
    
    $this->code = $code;
    $this->content = $text;
    $this->addHebrew();
  }
  
  public function getArray()
  {
    return array(
      'code'              => $this->code,
      'content'           => $this->content,
      'hebrewCode'        => $this->hebrewCode,
      'hebrewContentOPAC' => $this->hebrewContentOPAC,
      'hebrewContentUTF'  => $this->hebrewContentUTF
    );
  }
  
  private function addHebrew()
  {
    // get raw Hebrew
    $detransEngine = new LCHebrewDetrans( $this->content );
    $result = $detransEngine->convert();
    $opac = $result[0];
    $utf = $result[1];

    // get Hebrew tag & indicators
    $hebrewCode = $this->code;
    if (substr($hebrewCode, 0, 3) == '245' && substr($opac, 0, 5) == '{292}') {
      // begins with direct article - change number of nonfiling chars
      $hebrewCode = substr($hebrewCode, 0, 4) . '1';
    }
    
    $this->content           = trim( $this->content );
    $this->hebrewCode        = trim( $hebrewCode );
    $this->hebrewContentOPAC = trim( $opac );
    $this->hebrewContentUTF  = trim( $utf );
  }
}

class XMLrecords
{
  private $records = array();
  
  public function add($a)
  {
    $this->records[] = $a;
  }
  
  public function save($file, $overwrite = false)
  {
    if (file_exists($file) === true && $overwrite !== true) {
      throw new Exception('File already exists');
    }
    
    $fp = fopen($file, 'wb');
    
    fwrite($fp, '<?xml version="1.0" encoding="UTF-8" ?>' . "\n");
    fwrite($fp, '<Records>' . "\n");
    
    foreach ($this->records as $r) {
      fwrite($fp, '<record>' . "\n");
      
      fwrite($fp, '</record>' . "\n");
    }

    fwrite($fp, '</Records>' . "\n");
  }
}

/****************** End of class definitions *********/

function getRecords($filename)
{
  $file = fopen($filename, 'rb') or exit('Could not open file.');
  $records = array();
  
  while ($line = fgets($file)) {
    if (substr($line, 0, 4) == '=LDR') {
      if (isset($r)) $records[] = $r;  // push previous record
      $r = new Record();
    }

    if (substr($line, 0, 12) == '=035  \\\\$a.b') {
      $r->setId(substr($line, 11, 9));
    }
    
    $goodFields = array(
      '245',
      '246',
      '440',
      '490',
      '740'
    );
    
    if (in_array(substr($line, 1, 3), $goodFields)) {
      $tag = substr($line, 1, 3);
      $inds = substr($line, 6, 2);
      $inds = str_replace("\\", 'b', $inds);
      $content = substr($line, 10);
      
    	if ($tag == '700') {
        // only keep title
        if (strpos($content, '$t') !== false) {
          // if there's a subfield t, discard everything before it and change to 74002$a
          $tag = '740';
          $inds = '02';
          $content = substr($content, strpos($content, '$t') + 2);
        } else {
          // ignore the line
          continue;
        }
      }
      
      if ($tag == '245') {
        // only subfields 'a' and 'b'
        $startSubB = strpos($content, '$b');
        if ($startSubB !== false) {
          // there is a subfield b
          $startNextSub = strpos($content, '$', $startSubB + 1);
          if ($startNextSub !== false) {
            // if there are subfields after b, truncate
            $content = substr($content, 0, strpos($content, '$', $startSubB + 1)) . "\n";
          }
        } else {
          // any subfields after 'a' are not 'b', so truncate if exist
          if (strpos($content, '$') !== false) {
            $content = substr($content, 0, strpos($content, '$'));
          }
        }
      }
      
      if ($tag == '246' && strpos($content, '$a') !== false) {
        // knock off subfield i if present
        $content = trim(substr($content, strpos($content, '$a') + 2));
      }

      $r->addField($tag.$inds, $content);
    }
  }
  $records[] = $r;  // push final record

  return $records;
}

function writeXMLproof($filename, $records)
{
  if (file_exists($filename) === true) {
    throw new Exception('XML output file already exists');
  }

  $options = array(
    XML_SERIALIZER_OPTION_INDENT        => '  ',
    XML_SERIALIZER_OPTION_RETURN_RESULT => true,
    XML_SERIALIZER_OPTION_ENTITIES      => XML_SERIALIZER_ENTITIES_NONE,
    "defaultTagName"  => "field",
    "rootName"        => "record"
  );
  $xmlSer = new XML_Serializer($options);

  $fp = fopen($filename, 'wb');
  fwrite($fp, '<?xml version="1.0" encoding="UTF-8" ?>' . "\n");
  fwrite($fp, '<?xml-stylesheet type="text/css" href="style.css"?>' . "\n");
  fwrite($fp, '<Records>' . "\n");

  foreach ($records as $r) {
    $ra = $r->getArray();
    if (count($ra['fields']) === 0) {
      continue;     // skip records with no converted fields
    }
    
    $xmlSer->serialize($ra);
    $data = $xmlSer->getSerializedData();
    $data = str_replace('& ', '&amp; ', $data);  // ampersands in OPAC fields
    fwrite($fp, $xmlSer->getSerializedData() . "\n");
  }

  fwrite($fp, '</Records>');
  fclose($fp);
}


function writeOPACfields($filename, $records)
{
  if (file_exists($filename) === true) {
    throw new Exception('OPAC output file already exists');
  }

  $fp = fopen($filename, 'wb');

  foreach ($records as $r) {
    $ra = $r->getArray();
    if (count($ra['fields']) === 0) {
      continue;     // skip records with no converted fields
    }
    
    foreach ($ra['fields'] as $f) {
      // convert MARC characters to OPAC encoding
      $find = array(
        '{dotb}',
        '{mllhring}',
        '{mlrhring}',
        '{acute}',
        '$'
      );
      $repl = array(
        '{242}',
        '{176}',
        '{174}',
        '{226}',
        '|'
      );
      $f['content'] = str_replace($find, $repl, $f['content']);
      
      // output TSV
      $romanTag = substr($f['code'], 0, 3);
      $romanInds = substr($f['code'], 3, 2);
      $hebrewTag = substr($f['hebrewCode'], 0, 3);
      $hebrewInds = substr($f['hebrewCode'], 3, 2);
      
      fwrite($fp, "{$ra['id']}\t$romanTag\t$romanInds\t{$f['content']}\t$hebrewTag\t$hebrewInds\t{$f['hebrewContentOPAC']}\n");
    }
  }

  fclose($fp);
}

$records = getRecords('./hebrew.mrk');
// writeXMLproof('./results.xml', $records);
writeOPACfields('./hebrew_opac.txt', $records);