<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SearchResult
 *
 * @author djs22
 */
class SearchResult {


    	public function __construct(&$snippets,$author="n/a",$body="n/a",$title="n/a",$url="n/a",$desc="n/a",$id=0){
            $na="N/A";
            if($author=="")
                $author=$na;
            if($body=="")
                $body=$na;
            if($title=="")
                $title=$id;
            $this->author=$author;
            $this->body=$body;
            $this->title=$title;
            $this->url=$url;
            $this->id=$id;
            $this->snippets=$snippets;
            $this->desc=$desc;

        }

        public function format($class=''){
          $firstHalf= "
              <div id='searchResult' class='".$class."'>
                <a href='".$this->url."'>
                   <span class='searchHeader'>".$this->title." by ".$this->author."</span>
                </a>
              <div id='snippets'>";

           $secondHalf='';
           if(sizeof($this->snippets)==0){
              $secondHalf.="<span class=\"snippet\" id=\"".$this->id."\">".trim($this->desc)."</span>";
           }
           foreach($this->snippets as $section=>$snip){
              foreach($snip as $num=>$text){
                  //echo sizeof(this>snippets)
                  $secondHalf.="<span class=\"snippet\" id=\"".$this->id."\">".trim($text, "\[^A-Za-z0-9:]\*")."</span><span class='snipSep'> ... </span>";
              }
           }
               
           $secondHalf.="</div></div>";

           return $firstHalf.$secondHalf;
        }
}
?>
