$(document).ready(function(){
  //DROPDOWN NAVIGATION
  $(".nav-dropdown-btn").click(function() {
    $(".nav-dropdown-content").animate({
          height: "toggle",
          opacity: "toggle"
      }, "slow");
  });

  //IMAGE SLIDER
  $('.hero-image-slider').slick({
    dots: true,
    autoplay: true,
    autoplaySpeed: 3000,
    arrows: false,
    speed: 1000,
    pauseOnHover: false,
    pauseOnFocus: false
  });

  //TEXT SLIDER
  $('.hero-text-slider').slick({
    dots: false,
    autoplay: true,
    autoplaySpeed: 10000,
    arrows: false,
    speed: 1000,
    pauseOnHover: false,
    pauseOnFocus: false,
    fade: true,
  });

  //FORM LOGIC
  $("#question-select").change(function() {
    $.post(
      'update-answer-submission.php', 
      {questionIndex : $(this).val()}, 
      function(output) {
        if(output['name'].includes('.m4a')) {
          $('#question-preview').empty();
          $('#question-preview').append('<video controls><source src="' + output['image'] + '" type="video/mp4"><source src="' + output['image'] + '" type="video/webm"><source src="' + output['image'] + '" type="video/ogg">Your browser does not support the audio tag.</video>');
          $('#question-2').show();
          $('#space').attr('value', 'Space 2');
        } else {
          $('#question-preview').empty();
          $('#question-preview').append('<img src="' + output['image'] + "'>");
          $('#question-2').show();
          $('#space').attr('value', 'Space 2');
        }
      },
      'json');
    if ($(this).val() == "question-1") {
      $('#question-1').show();
      $('#question-2').hide();
      $('#space').attr('value', 'Space 1');
    } else if ($(this).val() == "question-2") {
      $('#question-2').show();
      $('#question-1').hide();
      $('#space').attr('value', 'Space 2');
    } else {
      $('#question-1').hide();
      $('#question-2').hide();
      $('#space').attr('value', '');
    }
  });
  $("#question-select").trigger("change");

  //ADD PADDING FOR FIXED HEADER IN MOBILE
  function calculateHeaderHeight(){
    var headerHeight = $('header').outerHeight();
    console.log(headerHeight);

    if ($('.hero').length) {
      $('.hero').css('padding-top', headerHeight);
    } else if ($('.icon-profile').length) {
      $('.icon-profile').css('padding-top', headerHeight);
    } else if ($('.member-profile').length) {
      $('.member-profile').css('padding-top', headerHeight);
    } else {
      $('.main-heading').css('padding-top', headerHeight);
    }

  }

  //REMOVE PADDING WHEN RESIZING WINDOW WIDER THAN MOBILE
  function undoHeaderHeight(){

    if ($('.hero').length) {
      $('.hero').css('padding-top', 0);
    } else if ($('.icon-profile').length) {
      $('.icon-profile').css('padding-top', 0);
    } else if ($('.member-profile').length) {
      $('.member-profile').css('padding-top', 0);
    } else {
      $('.main-heading').css('padding-top', 0);
    }

  }

  if(699 >= $(window).width()) {
    calculateHeaderHeight();
  }

  $(window).resize(function() {
    if(699 >= $(window).width()) {
      calculateHeaderHeight();
    }
  });

  $(window).resize(function() {
    if( $(window).width() >= 699) {
      undoHeaderHeight();
    }
  });

});
