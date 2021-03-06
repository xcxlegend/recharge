$(document).ready(function() {
      
      $(".menu-toggle").click(function() {
            if($(this).hasClass("active")) {
                  $(".menu-toggle").removeClass("active");
                  $("body").removeClass("nav-extended");
            }
            else {
                  $(".menu-toggle").addClass("active");
                  $("body").addClass("nav-extended");
            };
      });
            
      $("#search-toggle").click(function() {
            if($(this).hasClass("active")) {
                  $(this).removeClass("active");
                  $("#header-search-box").fadeOut(100);
            }
            else {
                  $(this).addClass("active");
                  $("#header-search-box").fadeIn(100);
                  $("#keywords").focus();
                  $("#toggle").removeClass("active");
                  $("ul#navigation_menu").fadeOut(100);
                  $("#login-toggle").removeClass("active");
                  $("ul#mobile-login").fadeOut(100);
            };
      });

   //show/hide footer nav
   
      $("#links-col-1 h4#mobile-heading").click(function() {
            if($("#links-col-1").hasClass("active")) {
                  $("#links-col-1").removeClass("active");
                  $("#links-col-1 ul").hide();
            }
            else {
                  $("#links-col-1").addClass("active");
                  $("#links-col-1 ul").fadeIn();
            };
            $("#links-col-2,#links-col-3,#links-col-4,#links-col-5,#links-col-6").removeClass("active");
            $("#links-col-2 ul,#links-col-3 ul,#links-col-4 ul,#links-col-5 ul,#links-col-6 ul").hide();
      });
   
      $("#links-col-2 h4#mobile-heading").click(function() {
            if($("#links-col-2").hasClass("active")) {
                  $("#links-col-2").removeClass("active");
                  $("#links-col-2 ul").hide();
            }
            else {
                  $("#links-col-2").addClass("active");
                  $("#links-col-2 ul").fadeIn();
            };
            $("#links-col-1,#links-col-3,#links-col-4,#links-col-5,#links-col-6").removeClass("active");
            $("#links-col-1 ul,#links-col-3 ul,#links-col-4 ul,#links-col-5 ul,#links-col-6 ul").hide();
      });

      $("#links-col-3 h4#mobile-heading").click(function() {
            if($("#links-col-3").hasClass("active")) {
                  $("#links-col-3").removeClass("active");
                  $("#links-col-3 ul").hide();
            }
            else {
                  $("#links-col-3").addClass("active");
                  $("#links-col-3 ul").fadeIn();
            };
            $("#links-col-1,#links-col-2,#links-col-4,#links-col-5,#links-col-6").removeClass("active");
            $("#links-col-1 ul,#links-col-2 ul,#links-col-4 ul,#links-col-5 ul,#links-col-6 ul").hide();
      });

      $("#links-col-4 h4#mobile-heading").click(function() {
            if($("#links-col-4").hasClass("active")) {
                  $("#links-col-4").removeClass("active");
                  $("#links-col-4 ul").hide();
            }
            else {
                  $("#links-col-4").addClass("active");
                  $("#links-col-4 ul").fadeIn();
            };
            $("#links-col-1,#links-col-2,#links-col-3,#links-col-5,#links-col-6").removeClass("active");
            $("#links-col-1 ul,#links-col-2 ul,#links-col-3 ul,#links-col-5 ul,#links-col-6 ul").hide();
      });
      
      $("#links-col-5 h4#mobile-heading").click(function() {
            if($("#links-col-5").hasClass("active")) {
                  $("#links-col-5").removeClass("active");
                  $("#links-col-5 ul").hide();
            }
            else {
                  $("#links-col-5").addClass("active");
                  $("#links-col-5 ul").fadeIn();
            };
            $("#links-col-1,#links-col-2,#links-col-3,#links-col-4,#links-col-6").removeClass("active");
            $("#links-col-1 ul,#links-col-2 ul,#links-col-3 ul,#links-col-4 ul,#links-col-6 ul").hide();
      });
      
      $("#links-col-6 h4#mobile-heading").click(function() {
            if($("#links-col-6").hasClass("active")) {
                  $("#links-col-6").removeClass("active");
                  $("#links-col-6 ul").hide();
            }
            else {
                  $("#links-col-6").addClass("active");
                  $("#links-col-6 ul").fadeIn();
            };
            $("#links-col-1,#links-col-2,#links-col-3,#links-col-4,#links-col-5").removeClass("active");
            $("#links-col-1 ul,#links-col-2 ul,#links-col-3 ul,#links-col-4 ul,#links-col-5 ul").hide();
      });
      
   //show/hide international-options nav
   
      $("a#link-languages").click(function() {
            if($(this).hasClass("active")) {
                  $(this).removeClass("active");
                  $("div#list-languages").hide();
            }
            else {
                  $(this).addClass("active");
                  $("div#list-languages").fadeIn();
            };
            $("a#link-payments,a#link-currencies").removeClass("active");
            $("div#list-payments,div#list-currencies").hide();
      });

      $("a#link-payments").click(function() {
            if($(this).hasClass("active")) {
                  $(this).removeClass("active");
                  $("div#list-payments").hide();
            }
            else {
                  $(this).addClass("active");
                  $("div#list-payments").fadeIn();
            };
            $("a#link-currencies,a#link-languages").removeClass("active");
            $("div#list-currencies,div#list-languages").hide();
      });
   
      $("a#link-currencies").click(function() {
            if($(this).hasClass("active")) {
                  $(this).removeClass("active");
                  $("div#list-currencies").hide();
            }
            else {
                  $(this).addClass("active");
                  $("div#list-currencies").fadeIn();
            };
            $("a#link-payments,a#link-languages").removeClass("active");
            $("div#list-payments,div#list-languages").hide();
      });
      
   //show/hide countries nav
   
      $("a#link-unsupported-countries").click(function() {
            if($(this).hasClass("active")) {
                  $(this).removeClass("active");
                  $("div#list-unsupported-countries").hide();
            }
            else {
                  $(this).addClass("active");
                  $("div#list-unsupported-countries").fadeIn();
            };
            $("a#link-payout-methods,a#link-payout-currencies").removeClass("active");
            $("div#list-payout-methods,div#list-payout-currencies").hide();
      });

      $("a#link-payout-methods").click(function() {
            if($(this).hasClass("active")) {
                  $(this).removeClass("active");
                  $("div#list-payout-methods").hide();
            }
            else {
                  $(this).addClass("active");
                  $("div#list-payout-methods").fadeIn();
            };
            $("a#link-payout-currencies,a#link-unsupported-countries").removeClass("active");
            $("div#list-payout-currencies,div#list-unsupported-countries").hide();
      });
   
      $("a#link-payout-currencies").click(function() {
            if($(this).hasClass("active")) {
                  $(this).removeClass("active");
                  $("div#list-payout-currencies").hide();
            }
            else {
                  $(this).addClass("active");
                  $("div#list-payout-currencies").fadeIn();
            };
            $("a#link-payout-methods,a#link-unsupported-countries").removeClass("active");
            $("div#list-payout-methods,div#list-unsupported-countries").hide();
      });
      
      /* jQuery for back to top floating div */

      $(window).scroll(function(){
        
            if($(window).scrollTop() > 400){$('a#backtotop').fadeIn(500);} else {$('a#backtotop').fadeOut(500);}
      });

     /* jQuery for shopping cart filter */

      $('#shopping-cart-filter').on('change', function() {
          if ($('#standard_filter').prop("checked")) {
              var addClass = ".standard";
          } else if ($('#inline_filter').prop("checked")) {
              var addClass = ".inline";
          } else if ($('#api_filter').prop("checked")) {
              var addClass = ".api";
          } else {
              var addClass = "";
          }

          if ($(this).val() == 'all') {
              $('.cart' + addClass).show();
          } else {
              $('.cart').hide();
              $('.' + $(this).val() + addClass).show();
          }

          if ($('div.cart:visible').length == 0) {$('.error').show();} else {$('.error').hide();}
      });

      $('#standard_filter, #inline_filter, #api_filter').on('change', function() {
          if ($('#shopping-cart-filter').val() != 'all') {
              var addClass = "." + $('#shopping-cart-filter').val();
          } else {
              var addClass = "";
          }

          if (!$('#standard_filter').prop("checked") || !$('#inline_filter').prop("checked") || !$('#api_filter').prop("checked")) {
              $('.cart').hide();
              if ($('#standard_filter').prop("checked")) {
                  $('.standard' + addClass).show();
              }
              if ($('#inline_filter').prop("checked")) {
                  $('.inline' + addClass).show();
              }
              if ($('#api_filter').prop("checked")) {
                  $('.api' + addClass).show();
              }
          } else {
              $('.cart' + addClass).show();
          }

          if ($('div.cart:visible').length == 0) {$('.error').show();} else {$('.error').hide();}
      });
      
      $('#search-submit').click(function(e) {$('#search').submit();});
           
      $('.server-code').hide();
      $('#api-language').change(function () {
          $('.server-code').hide();
          $('#'+$(this).val()).fadeIn();
      }).trigger('change');
      
     // glossary index for mobile menu
      $("#glossary-term-list h5").click(function() {
            if($(this).hasClass("active")) {
                  $(this).removeClass("active");
                  $("#glossary-term-list ul").fadeOut(100);
            }
            else {
                  $(this).addClass("active");
                  $("#glossary-term-list ul").fadeIn(100);
            };
      });
      
     // jQuery for Country List Expand/Collapse
      
      $('#country-list #expand-list').click(function() {
            $(this).add('#country-list #list').toggleClass('expanded');
            if($(this).hasClass('expanded')) {
                  $(this).find('span').delay(800).text('Collapse');
                  $('#expand-list .fa').delay(800).addClass('fa-toggle-up').removeClass('fa-toggle-down');
            }
            else {
                  $(this).find('span').delay(800).text('Expand for list view of 2checkout supported markets');
                  $('#expand-list .fa').delay(800).addClass('fa-toggle-down').removeClass('fa-toggle-up');
            };
      });
    
});

$(function(){

   $('#header-search-box').on('click', function(e) {
      console.log(e.target);
      if(e.target.id != 'keywords' && e.target.id != 'header-search-button') {
         $(this).fadeOut(100);
         $("#search-toggle").removeClass("active");
      }
   });
   
   $('#content').on('click', function(e) {
      console.log(e.target);
      if(e.target.id != 'keywords' && e.target.class != 'menu-toggle') {
         $(".menu-toggle").removeClass("active");
         $("body").removeClass("nav-extended");
      }
   });

});

$(window).scroll(function() {

      if($(this).scrollTop()>1){$('#header,#secondary-header').removeClass('default').addClass('scrolling');} else {$('#header,#secondary-header').removeClass('scrolling').addClass('default');}

});

function toggle_visibility(id) {
    var e = document.getElementById(id);
    if(e.style.display == 'block')
        e.style.display = 'none';
    else
        e.style.display = 'block';
}