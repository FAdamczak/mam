$(function(){

  $('#divRestore').hide();

  $('#bt_desinstalle').click(function(){
    $('#operation').val("DESINSTALLER");
    $("#param").val($('#applications').val());
    $('#theForm').submit();
  });



  $('#bt_recupApk').click(function(){
    $('#operation').val("RECUPAPK");
    $("#param").val($('#applications').val());
    $('#theForm').submit();
  });

  $('#bt_sauvegarge').click(function(){
    $('#operation').val("SAUVEGARDE");
    $("#param").val("");
    $('#theForm').submit();
  });

  $('#bt_restore').click(function(){
    if ($('#divRestore').is(':hidden')) {
      $('#divRestore').show();
    } else {
      $('#divRestore').hide();
    }
  });

  $('#bt_valideRestore').click(function(){
    $('#operation').val("RESTORE");
    //C:\fakepath\
    nomFichier = $('#fichierRestore').val().substr(12);
    $("#param").val(nomFichier);
    $('#theForm').submit();
  });

  $('#bt_eteindre').click(function(){
    $('#operation').val("ETEINDRE");
    $("#param").val("");
    $('#theForm').submit();
  });

  $('#bt_reboot').click(function(){
    $('#operation').val("REBOOT");
    $("#param").val("");
    $('#theForm').submit();
  });

  $('#bt_reinit').click(function(){
    $('#operation').val("REINITIALISER");
    $("#param").val("");
    $('#theForm').submit();
  });

  $('#bt_ring').click(function(){
    $('#operation').val("RING");
    $("#param").val();
    $('#theForm').submit();
  });



  $('#fichierRestore').on('change',function(){
    fichier = $('#fichierRestore').val().substr(12);
    $(this).next('.custom-file-label').html(fichier);
  });

})
