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


    	public function __construct($author="n/a",$body="n/a",$title="n/a",$name="n/a",$id=0){
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
            $this->name=$name;
            $this->id=$id;

            $this->con = mysql_connect('localhost:8888', 'root', 'root');
            if (!$this->con)
              {
              die('Could not connect: ' . mysql_error());
              }
            mysql_select_db("ODDemo", $this->con);
        }

        public function format(){
            $query = "Select * FROM Files WHERE id='".$this->name."'";
          //  echo $query;
			$result = mysql_fetch_array(mysql_query($query));
            $url=$result['url'];
          //  echo $url;
          return "
              <div id='searchResult'>
               <a href='".$url."'.>
                   <h2>".$this->title." by ".$this->author."</h2>
               </a>
               </div>";
        }
}
?>
