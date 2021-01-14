$(function(){

  $("#nbSel").text("0");

  $(".appliBiblio").click(function(){
    nb = parseInt($("#nbSel").text());
    if ($(this).hasClass("selected")) {
      //console.log("possède la classe selected");
      //console.log($(this).attr('class'));
      $(this).removeClass("selected");
      --nb;
    } else {
      //console.log("ne possède pas la classe selected");
      //console.log($(this).attr('class'));
      $(this).addClass("selected");
      ++nb;
    }
    $('#nbSel').text(nb);
  });

  $('#btValide').click(function(){
    //  Vérifications avant soumission du formulaire
    mes="";

    //  le champ nom n'est pas vide.
    if($('#nom').val()=="") {
      mes+="Préciser le nom du profil.<br>";
    }

    //  Il y a au moins 2 applications sélectionnées
    if($(".selected").length < 2) {
      mes+="Un profil doit être constitué d'au moins 2 applications<br>";
    }

    if(mes!="") {
      $('#infoMessage').html(mes);
      $('#message').show(250);
    } else {
      lesApp=""
      $(".selected").each(function(){
        id = $(this).attr('id').substring(4);
        lesApp+=id+"***";
      })
      $('#lesApplis').val(lesApp);
      $('#nouveauProfil').submit();
    }
  })

  $('#fermerModal').click(function(){
    $('#message').hide();
  })

  $("#btSupprimer").click(function(){
    $('#message').show();
  });

  $('#confirmDelete').click(function(){
    $('#message').hide();
    $('#leForm').submit();
  });

  /*** VISU PROFIL  ****/
  $('.card-title').mouseenter(function(){
    $(this).css('cursor','pointer');
  });

  $('.card-text').each(function(){
    $(this).hide();
  })

  $('.card-title').click(function(){
    const elt = $(this).parent().children().eq(1);
    ($(elt).is(':visible')) ?  $(elt).hide() : $(elt).show();
  });


})
