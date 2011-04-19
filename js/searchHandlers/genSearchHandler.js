$().ready(
    function getFacets(){
        alert("gettingFacets");
        $.ajax({
            url: 'searchHandlers/getClusterInfo.php',
            success: function(data) {
                alert('Load was performed.'+data);
            }
        })
    }
);

function validateForm(){
    if($('#searchBox').val()==""){
        alert("No empty queries");
        return false;
    }
    return true;
}