function validateForm(){
    if($('#searchBox').val()==""){
        alert("No empty queries");
        return false;
    }
    return true;
}