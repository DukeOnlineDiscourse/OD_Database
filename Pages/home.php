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
        <select name="filter">
            <option value="">All</option>
            <option value="sup_title:">Title</option>
            <option value="body:">Body</option>
            <option value="author:">Author</option>
        </select>
        <input type="hidden" name="startResp" value="0"/>
        <input type="hidden" name="numRows" value="5"/>
    <input type="submit" name="Search" value="Search" id="searchButton"/>
</form>