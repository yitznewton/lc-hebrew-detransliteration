<?php

/*
Copyright (c) 2008, Benjamin Schaffer (a.k.a. Yitzchak Schaffer)
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
    * The name of Benjamin Schaffer (a.k.a. Yitzchak Schaffer) may be used to endorse or promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

class LCHebrewDetrans
{
  private $text = '';
  private $opacText = '';
  private $utfText = '';
  
  private $opacCodes = array(
    'alef'        => '{288}',
    'bet'         => '{289}',
    'gimel'       => '{290}',
    'dalet'       => '{291}',
    'he'          => '{292}',
    'vav'         => '{293}',
    'zayin'       => '{294}',
    'het'         => '{295}',
    'tet'         => '{296}',
    'yud'         => '{297}',
    'kaf'         => '{299}',
    'kaf_sofit'   => '{298}',
    'lamed'       => '{300}',
    'mem'         => '{302}',
    'mem_sofit'   => '{301}',
    'nun'         => '{304}',
    'nun_sofit'   => '{303}',
    'samekh'      => '{305}',
    'ayin'        => '{306}',
    'pe'          => '{308}',
    'pe_sofit'    => '{307}',
    'tsadi'       => '{310}',
    'tsadi_sofit' => '{309}',
    'kuf'         => '{311}',
    'resh'        => '{312}',
    'shin'        => '{313}',
    'sin'         => '{313}',
    'tav'         => '{314}'
  );
  
  private $utfCodes = array(
    'alef'        => '&#x5D0;',
    'bet'         => '&#x5D1;',
    'gimel'       => '&#x5D2;',
    'dalet'       => '&#x5D3;',
    'he'          => '&#x5D4;',
    'vav'         => '&#x5D5;',
    'zayin'       => '&#x5D6;',
    'het'         => '&#x5D7;',
    'tet'         => '&#x5D8;',
    'yud'         => '&#x5D9;',
    'kaf'         => '&#x5DB;',
    'kaf_sofit'   => '&#x5DA;',
    'lamed'       => '&#x5DC;',
    'mem'         => '&#x5DE;',
    'mem_sofit'   => '&#x5DD;',
    'nun'         => '&#x5E0;',
    'nun_sofit'   => '&#x5DF;',
    'samekh'      => '&#x5E1;',
    'ayin'        => '&#x5E2;',
    'pe'          => '&#x5E4;',
    'pe_sofit'    => '&#x5E3;',
    'tsadi'       => '&#x5E6;',
    'tsadi_sofit' => '&#x5E5;',
    'kuf'         => '&#x5E7;',
    'resh'        => '&#x5E8;',
    'shin'        => '&#x5E9;',
    'sin'         => '&#x5E9;',
    'tav'         => '&#x5EA;'
  );
  
  private $manualWords = array(
    'hu' =>
      array('he', 'vav', 'alef'),
    'bet' =>
      array('bet', 'yud', 'tav'),
    'bayit' =>
      array('bet', 'yud', 'tav'),
    'da\{DOTB\}vid' =>
      array('dalet', 'vav', 'dalet'),
    'yerushalayim' =>
      array('yud', 'resh', 'vav', 'shin', 'lamed', 'yud', 'mem_sofit'),
    'yerushala\[y\]im' =>
      array('yud', 'resh', 'vav', 'shin', 'lamed', 'mem_sofit'),
    'mosheh' =>
      array('mem', 'shin', 'he'),
    '\{MLLHRING\}inyan' =>
      array('ayin', 'nun', 'yud', 'nun_sofit'),
    '\{MLLHRING\}inyene' =>
      array('ayin', 'nun', 'yud', 'nun', 'yud'),
    'tsiyon' =>
      array('tsadi', 'yud', 'vav', 'nun_sofit'),
    'oti' =>
      array('alef', 'vav', 'tav', 'yud'),
    'perush' =>
      array('pe', 'yud', 'resh', 'vav', 'shin'),
    'or' =>
      array('alef', 'vav', 'resh'),
    'pir\{242\}ke' =>
      array('pe', 'resh', 'kuf', 'yud'),
    'midrash' =>
      array('mem', 'dalet', 'resh', 'shin'),
    'ohel' =>
      array('alef', 'he', 'lamed'),
    '\{MLLHRING\}im' =>
      array('ayin', 'mem_sofit'),
    'otsar' =>
      array('alef', 'vav', 'tsadi', 'resh'),
    'sifrut' =>
      array('samekh', 'pe', 'resh', 'vav', 'tav'),
    'mavo' =>
      array('mem', 'bet', 'vav', 'alef'),
    '\{DOTB\}hamishah' =>
      array('het', 'mem', 'shin', 'he'),
    'ora\{DOTB\}h' =>
      array('alef', 'vav', 'resh', 'het'),
    'rishonim' =>
      array('resh', 'alef', 'shin', 'vav', 'nun', 'yud', 'mem_sofit'),
    'rishon' =>
      array('resh', 'alef', 'shin', 'vav', 'nun_sofit'),
    'rosh' =>
      array('resh', 'alef', 'shin'),
    'reshit' =>
      array('resh', 'alef', 'shin', 'yud', 'tav'),
    'bereshit' =>
      array('bet', 'resh', 'alef', 'shin', 'yud', 'tav'),
    '\{MLLHRING\}iyun' =>
      array('ayin', 'yud', 'vav', 'nun_sofit'),
    '\{MLLHRING\}iyunim' =>
      array('ayin', 'yud', 'vav', 'nun', 'yud', 'mem_sofit'),
    '\{MLLHRING\}ivrit' =>
      array('ayin', 'bet', 'resh', 'yud', 'tav'),
    'shul\{DOTB\}han' =>
      array('shin', 'lamed', 'het', 'nun_sofit'),
    'aharon' =>
      array('alef', 'he', 'resh', 'nun_sofit'),
    'ish' =>
      array('alef', 'yud', 'shin'),
    'ishe' =>
      array('alef', 'yud', 'shin', 'yud'),
    'ishim' =>
      array('alef', 'yud', 'shin', 'yud', 'mem_sofit'),
    '\{DOTB\}humshe' =>
      array('het', 'mem', 'shin', 'yud'),
    'kh?ol' =>
      array('kaf', 'lamed'),
    'be\{MLRHRING\}ur' =>
      array('bet', 'yud', 'alef', 'vav', 'resh')
  );
  
  private $wordPatterns = array();

  
  public function __construct($text = null)
    {
      if ($text !== null) {
      $this->setText($text);
    }
    
    // initialize the array of manual-override word replacements
    $this->generateWordPatterns();
  }
  
  public function setText($text)
  {
    if (
      stripos($text, ' of ')    !== false ||
      stripos($text, ' in ')    !== false ||
      stripos($text, 'the ')    !== false ||
      stripos($text, 'c')       !== false ||
      stripos($text, 'j')       !== false ||
      stripos($text, 'q')       !== false ||
      stripos($text, 'x')       !== false ||
      stripos($text, 'w')       !== false ||
      stripos($text, 'oo')      !== false ||
      stripos($text, 'ou')      !== false ||
      stripos($text, '{uml}')   !== false ||
      stripos($text, '{grave}') !== false
    ) {
      throw new Exception('Probably not Hebrew');
    }
    
    $this->text = $text;
  }
  
  public function setManualWords($words)
  {
    if (is_array($words) === false) {
      $words = array($words);
    }
    
    $this->manualWords = $words;
    $this->generateWordPatterns();
  }
  
  private function getOpacCode($letter)
  {
    if (isset($letter, $this->opacCodes)) {
      return $this->opacCodes[$letter];
    } else {
      throw new Exception ("Letter '$letter' not in OPAC comparison table.");
    }
  }

  private function getUtfCode($letter)
  {
    if (isset($letter, $this->utfCodes)) {
      return $this->utfCodes[$letter];
    } else {
      throw new Exception ("Letter '$letter' not in UTF comparison table.");
    }
  }
  
  private function replaceLetters($pattern, $replacement)
  {
    $replaceStringOpac = '';
    $replaceStringUtf = '';
    
    if (is_array($replacement) === false) {
      $replacement = array($replacement);
    }

    foreach ($replacement as $r) {
      $replaceStringOpac .= $this->getOpacCode($r);
      $replaceStringUtf .= $this->getUtfCode($r);
    }
  
    $this->opacText = preg_replace($pattern, $replaceStringOpac, $this->opacText);
    $this->utfText = preg_replace($pattern, $replaceStringUtf, $this->utfText);
  }
  
  private function generateWordPatterns()
  {
    $result = array();
    
    foreach ($this->manualWords as $word => $letters) {
      // find words in field matching override words, delimited
      // by space|hyphen|subfield delimiter|^|$
      $pattern = '(?<=^|[ -]|\|[a-zA-Z]|$.)' . $word . '(?=[^a-z&\{]|$)';
        
      $opac_letters = '';
      $utf_letters = '';
      foreach ($letters as $l) {
        $opac_letters .= $this->getOpacCode($l);
        $utf_letters .= $this->getUtfCode($l);
      }
      
      $result[] = array($pattern, $opac_letters, $utf_letters);
    }
    
    $this->wordPatterns = $result;
  }

  public function convert()
  {
    $text = $this->text;
    $text = strtolower($text);
    
    // make the subfield delimiters and diacritics
    // uppercase to escape Hebrification
    $text = preg_replace('/ ?\$([a-z]) ?/e', "'|' . strtoupper('$1')", $text);
    $text = preg_replace('/\{([a-z]+)\}/e', "'{' . strtoupper('$1') . '}'", $text);
    
    // truncate 245$c
    // CHANGED - CHECK TO MAKE SURE IT DOESN'T BREAK!
    $text = preg_replace('/ \/.*$/', '', $text);
    
    $this->opacText = $text;
    $this->utfText = $text;

    // XML entities - uppercase for now to sneak by the converter
    $this->utfText = str_replace('&', '&AMP;', $this->utfText);
    $this->utfText = str_replace('"', '&QUOT;', $this->utfText);
    
    foreach ($this->wordPatterns as $word) {
      // replace manual override words
      $this->opacText = preg_replace("/$word[0]/", $word[1], $this->opacText);
      $this->utfText  = preg_replace("/$word[0]/", $word[2], $this->utfText);
    }

    // wipe 'i' if it's the first vowel after initial consonant
    $this->opacText = preg_replace('/((^|[ -]|\|.)(.{1,2}|\{[^\}]+\}.{1,2}))i(?! |$)/', '$1', $this->opacText);
    $this->utfText = preg_replace('/((^|[ -]|\|.)(.{1,2}|\{[^\}]+\}.{1,2}))i(?! |$)/', '$1', $this->utfText);
  
    // single letters that are two in transliterated form
    $this->replaceLetters('/(?<=[^\};])kh[a]?(?=[^a-z&\{-]|$)/', 'kaf_sofit');
    $this->replaceLetters('/(?<=[^\};])kh/', 'kaf');
    $this->replaceLetters('/sh/', 'shin');
    $this->replaceLetters('/(?<=[^\};])ts(?=[^a-z&\{-]|$)/', 'tsadi_sofit');
    $this->replaceLetters('/(?<=[^\};])ts/', 'tsadi');

    // alef
    $this->replaceLetters('/(?<=[^h])a(?=[^a-z&\{-]|$)/', 'alef');
    $this->replaceLetters('/(?<=^|[ -])[ae][iy]/', array('alef', 'yud'));
    $this->replaceLetters('/(?<=^|[ -])oi/', array('alef', 'vav', 'yud'));
    $this->replaceLetters('/(?<=^|[ -])[aei]/', 'alef');
    $this->replaceLetters('/(?<=^|[ -])[ou]/', 'vav');
    $this->replaceLetters('/\{MLRHRING\}[ae]/', 'alef');
    $this->replaceLetters('/\{MLRHRING\}i/', array('alef', 'yud'));
    $this->replaceLetters('/\{MLRHRING\}[ou]/', array('alef', 'vav'));

    // remaining vowels
    $this->replaceLetters('/(?<=^| )yi/', 'yud');
    $this->replaceLetters('/e(?=[^-a-z&\{])/', 'yud');
    $this->replaceLetters('/ei/', array('yud', 'yud'));
    $this->replaceLetters('/a[iy]/', 'yud');
    $this->replaceLetters('/oi/', array('vav', 'yud'));
    $this->replaceLetters('/[ae]/', array() );
    $this->replaceLetters('/i/', 'yud');
    $this->replaceLetters('/[ou]/', 'vav');
    
    // diacriticked consonants
    $this->replaceLetters('/\{DOTB\}v/', 'vav');
    $this->replaceLetters('/\{DOTB\}h/', 'het');
    $this->replaceLetters('/\{DOTB\}t/', 'tet');
    $this->replaceLetters('/\{DOTB\}k/', 'kuf');
    $this->replaceLetters('/\{ACUTE\}s/', 'sin');
    
    // plain consonants
    $this->replaceLetters('/[bv]/', 'bet');
    $this->replaceLetters('/\{MLLHRING\}/', 'ayin');
    $this->replaceLetters('/g/', 'gimel');
    $this->replaceLetters('/d/', 'dalet');
    $this->replaceLetters('/h/', 'he');
    $this->replaceLetters('/z/', 'zayin');
    $this->replaceLetters('/y/', 'yud');
    $this->replaceLetters('/k[a]?(?=[^a-z&\{-]|$)/', 'kaf_sofit');
    $this->replaceLetters('/k/', 'kaf');
    $this->replaceLetters('/l/', 'lamed');
    $this->replaceLetters('/m(?=[^a-z&\{-]|$)/', 'mem_sofit');
    $this->replaceLetters('/m/', 'mem');
    $this->replaceLetters('/n(?=[^a-z&\{-]|$)/', 'nun_sofit');
    $this->replaceLetters('/n/', 'nun');
    $this->replaceLetters('/s/', 'samekh');
    $this->replaceLetters('/[pf](?=[^a-z&\{-]|$)/', 'pe_sofit');
    $this->replaceLetters('/[pf]/', 'pe');
    $this->replaceLetters('/r/', 'resh');
    $this->replaceLetters('/t/', 'tav');
    
    // now, clean up:
    
    // remove any remaining diacritics from results (?!)
    $this->opacText = preg_replace('/\{[A-Z]+\}/', '', $this->opacText);
    $this->utfText = preg_replace('/\{[A-Z]+\}/', '', $this->utfText);
    
    // wipe prefix hyphens (e.g. ha-Torah)
    $this->opacText = preg_replace('/(?<=^| )(.{0,10})-/', '$1', $this->opacText);
    $this->utfText = preg_replace('/(?<=^| )(.{0,14})-/', '$1', $this->utfText);

    // back to lowercase for the subfield delimiters and XML entities which remain
    $this->opacText = strtolower($this->opacText);
    $this->utfText = strtolower($this->utfText);
  
    return array( $this->opacText, $this->utfText );
  }
}
