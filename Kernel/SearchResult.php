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


    	public function __construct(&$snippets,$author="n/a",$body="n/a",$title="n/a",$url="n/a",$id=0){
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

            $this->con = mysql_connect('localhost:8888', 'root', 'root');
            if (!$this->con)
              {
              die('Could not connect: ' . mysql_error());
              }
            mysql_select_db("ODDemo", $this->con);
        }

        public function format(){
          $firstHalf= "
              <div id='searchResult'>
                <a href='".$this->url."'.>
                   <span class='searchHeader'>".$this->title." by ".$this->author."</span>
                </a>
              <div id='snippets'>";

           $secondHalf;

           foreach($this->snippets as $section=>$snip){
              foreach($snip as $num=>$text){
                  $secondHalf.="<span class=\"snippet\" id=\"".$this->id."\">".trim($text, "\[^A-Za-z0-9:]\*")."</span><span class='snipSep'> ... </span>";
              }
           }
               
           $secondHalf.="</div></div>";

           return $firstHalf.$secondHalf;
        }
}
?>
