<?php

?>

<script>
function validateForm(){
    if($('#searchBox').val()==""){
        alert("No empty queries");
        return false
    }
    return true
}
</script>

<form method="get" action="genSearch" id="homeSearch" onsubmit="return validateForm()">
    <h2>Search the Online Discourse Database</h2>
    <input type="text" name="searchTerm" id="searchBox"/>
    <!--    <select name="filter">
            <option value="">All</option>
            <option value="title:">Title</option>
            <option value="body:">Body</option>
            <option value="author:">Author</option>
        </select>-->
   <!-- Author: <input type="checkbox" name="author" value="true" checked/>
    Title: <input type="checkbox" name="title" value="true" checked/>
    Body: <input type="checkbox" name="body" value="true" checked/>-->
    <input type="submit" name="Search" value="Search" id="searchButton"/>
</form>