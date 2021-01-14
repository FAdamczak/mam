$(function(){
  //affichePrincipal();

  $('#nbSel').text("0");

  $('#param').on('click',function(){
    affichePrincipal();
  })

  $('.tablette').click(function(){

    var nb = parseInt($('#nbSel').text());
    var total = $('.tablette').length;

    if ($(this).hasClass("selected")) {
      //console.log("possède la classe selected");
      $(this).removeClass("selected");
      --nb;
    } else {
      //console.log("ne possède pas la classe selected");
      $(this).addClass("selected");
      ++nb;
    }
    $('#nbSel').text(nb);

  });

  function affichePrincipal() {
    if ($('#menu').css("visibility")=="hidden") {
      $('#menu').css("visibility","visible");
      $('#menu').show(500);
      $('#principal').removeClass('col-md-12').addClass('col-md-8');
    } else {
      $('#menu').hide(250);
      $('#menu').css("visibility","hidden");
      $('#principal').removeClass('col-md-8').addClass('col-md-12');
    }
  }

  $('#btValide').click(function(){
    $('#leParc').val("");

    lesTablettes = "";

    $('.selected').each(function(){
      var laDiv = $(this).children().eq(0)
      var id_Tablette = laDiv.children().eq(0).text();
      lesTablettes+=id_Tablette + "***";
    });
    $('#leParc').val(lesTablettes);

    $('form').submit();
  })

  $(".custom-file-input").on("change", function() {
    var fileName = $(this).val().split("\\").pop();
    $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
  });

})
