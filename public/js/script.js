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
               var nitem = "<div class='item'>" +
                           "<img class='ui avatar image' src='public/img/avatar.png'>" +
                           "<div class='content'>" +
                           "<div class='header'>" + data["online_users"][i] +  "</div>" +
                           "</div>" +
                           "<div>";

               $("#online_users").append(nitem);
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
});