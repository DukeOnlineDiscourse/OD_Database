$().ready(
    function() {
      $(".bcCrumb").tooltip({ position: "bottom right", opacity: 0.825});
      
      var params=document.URL.split('?')[1];
      $.ajax({
          url: 'searchHandlers/getClusterInfo.php?'+params,
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