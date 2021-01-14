$(function(){

  $(".custom-file-input").on("change", function() {
    var fileName = $(this).val().split("\\").pop();
    $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
  });

  $("#btSupprimer").click(function(){
    $('#message').show();
  });

  $('#confirmDelete').click(function(){
    $('#message').hide();
    $('#leForm').submit();
  })

})
