var comet;

$(function(){

   comet = new jRender.ajax.Comet({
      url: "./source/backend.php"
      /* For remote requests (Cross domain) */
      //url:  "http://www.example.com/backend.php",
      //jsonp: true
   });

   var settings = 
   {
      data: {},
      callback: {
         success: function(data) 
         {
            // Connection established
            if (typeof data != "object")
               data = $.parseJSON(data);

            $("#state").text("Online");

            if (!($("#" + data["timestamp"]).length))
            {
               if ($('#content').length)
               {
                  $('#content').append(data["msg"]);
                  $('#content')[0].scrollTop = 9999999;
               }
            }

            $("#online_users").empty();

            for (var i = data["online_users"].length - 1; i >= 0; i--)
            {
               var user = data["online_users"][i];
               var bg_x, bg_y; 

               // Get user's configuration
               $.getJSON('cache/' + user + '.json', function(data) {

                  var x = parseInt(data["avatar"].toString().charAt(0)) - 1;
                  var y = parseInt(data["avatar"].toString().charAt(1)) - 1;

                  bg_x = ( -30 * x );
                  bg_y = ( -28.63 * y ) + 1;

                  var nitem = "<div class='item'>" +
                              "<img class='ui avatar image' style='background-position: " + bg_x + "px " + bg_y + "px' />" +
                              "<div class='content'>" +
                              "<div class='header'>" + data["username"] +  "</div>" +
                              "</div>" +
                              "<div>";

                  $("#online_users").append(nitem);
               });
            };
         },
         error: function(jqXHR, textStatus, errorThrown)
         {
            // Connection unestablished
            setTimeout(function(){
               if (!comet.state)
                  $("#state").text("Offline");
            }, 3000);
         },
         complete: function() 
         {
            // For each request
            if (comet.state)
               $("#state").text("Online");
         }
      }
   }

   // To connect only if the cookie exists
   if (typeof $.cookie('username') != 'undefined')
      comet.connect(settings);

   $("#chat").submit(function(event){

      event.preventDefault();

      settings = 
      {
         data: {
            msg: $('#word').val()
         },
         callback: {
            success: function(data) {
               $('#content').append(data["msg"]);
               $('#content')[0].scrollTop = 9999999;
               $('#word').val('');
            },
            error: function(jqXHR, textStatus, errorThrown) {
               $('#content').append("<p><strong>Error! Try again ...</strong></p>")
            }
         }
      }

      comet.doRequest(settings);
      return false;
   });

   /* Semantic ui tools */
   $('.ui.sidebar').sidebar();
   $("#show-users").click(function(){
      $('.ui.sidebar').sidebar('toggle');      
   });
   $('.ui.dropdown').dropdown();
});