$().ready(
    function getFacets(){
        var st = document.URL.split("searchTerm=")[1].split("&")[0];
        $.ajax({
            url: 'searchHandlers/getClusterInfo.php?searchTerm='+st,
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