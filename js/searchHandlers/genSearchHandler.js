$().ready(
    function getFacets(){
        $.ajax({
            url: 'searchHandlers/getClusterInfo.php',
            success: function(data) {
                $('#facets').append(data);
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