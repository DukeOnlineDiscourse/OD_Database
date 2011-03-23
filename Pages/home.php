<?php

?>

        <form method="post" action="genSearch">
            <h1>Enter a search term</h1>
            <input type="text" name="searchTerm"/>
            <span id="searchOpts">
          <!--  <span id="subSearch">Search By:</span>-->

                <select name="filter">
                    <option value="">All</option>
                    <option value="title:">Title</option>
                    <option value="body:">Body</option>
                    <option value="author:">Author</option>
                </select>
           <!-- Author: <input type="checkbox" name="author" value="true" checked/>
            Title: <input type="checkbox" name="title" value="true" checked/>
            Body: <input type="checkbox" name="body" value="true" checked/>-->

            </div>
        </form>