$().ready(
    function getFacets(){
        $.ajax({
            url: 'searchHandlers/getClusterInfo.php',
            success: function(data) {
                alert('Load was performed.'+data);
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