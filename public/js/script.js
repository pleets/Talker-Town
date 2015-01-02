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

                  if (data["user"] !== $.cookie("username"))
                  {
                     $("#notification-audio")[0].load();
                     $("#notification-audio")[0].play();
                  }
               }
            }

            $("#online_users").empty();

            for (var i = data["online_users"].length - 1; i >= 0; i--)
            {
               var user = data["online_users"][i];
               var bg_x, bg_y; 

               // Get user's configuration
               $.getJSON('cache/' + user + '.json', function(data) {

                  var i = parseInt(data["avatar"].toString().charAt(0)) - 1;
                  var j = parseInt(data["avatar"].toString().charAt(1)) - 1;

                  var x = j;
                  var y = i;

                  bg_x = ( -30 * x );
                  bg_y = ( -(315/11) * y ) + 1;

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

      if ($('#word').val().trim() != "")
      {
         var message = $('#word').val();
         $('#word').val('').attr('disabled', 'disabled');

         $('#content').append("<p id='loading-message-status'>" + message + " <i class='loading icon'></i><p>");
         $('#content')[0].scrollTop = 9999999;

         settings = 
         {
            data: {
               msg: message
            },
            callback: {
               success: function(data) 
               {
                  $('#content').append(data["msg"]);
               },
               error: function(jqXHR, textStatus, errorThrown) {
                  $('#loading-message-status i').attr('class', 'remove icon');
                  $('#loading-message-status').removeAttr('id');
                  $('#content').append("<div class='ui small compact red message'><strong>Error!</strong> The message was not sent.</div>");
               },
               complete: function()
               {
                  if ($('#loading-message-status i').attr('class') != 'remove icon')
                     $('#loading-message-status').remove();

                  $('#word').removeAttr('disabled');

                  $('#content')[0].scrollTop = 9999999;
                  $('#word').focus();
               }
            }
         }

         comet.doRequest(settings);
      }

      return false;
   });

   /* Semantic ui tools */
   $('.ui.sidebar').sidebar();
   $("#show-users").click(function(){
      $('.ui.sidebar').sidebar('toggle');
   });
   $('.ui.dropdown').dropdown();

   $j.ready(function(){

      if ($("#file_reader_response").length)
      {
         var Reader = $j.reader;

         var _files = new Reader.File({
            fileBox: document.querySelector("#file-reader-onchange"),      // input[type='file']
            dropBox: document.querySelector("#file-reader-ondrop"),        // dropbox
            preview: document.querySelector("#file-reader-ondrop"),        // preview
            url: 'source/file-upload.php',
            size: 104857600,
         });

         _files.addDropEvent(function(files){
            _files.upload(files);
         });
         _files.addChangeEvent(function(files){
            _files.upload(files, function(uploadedFiles) {
               uploadedFiles = $.parseJSON(uploadedFiles);
               var links = "<div>";
               for (var i = uploadedFiles.length - 1; i >= 0; i--) {
                  links += "<a target='_blank' class='ui basic button' href='public/img/user/" + uploadedFiles[i] + "'>" + uploadedFiles[i] + " </a> ";
               };
               links += "</div>";
               $("#file_reader_response").append(links);
            });
         });
      }
   });


   /* Avatar gallery */

   $(".tk-gallery.selectable").find(".item").click(function(event){
      event.preventDefault();
      
      var input = $(this).parent().attr('data-input');

      $(".tk-gallery.selectable").find(".item").addClass("unselected").removeClass("selected");
      $(this).removeClass("unselected").addClass("selected");

      $("#"+input).val($(this).attr('data-value'));
   });

   $(".tk-gallery.selectable").find(".control-right").click(function(event){
      event.preventDefault();

      var me = $(this);

      me.addClass("disabled");

      var items = $(".tk-gallery.selectable").find('.item');

      var item = items.first().clone()
      $(".tk-gallery.selectable").find(".items").append(item);

      items.first().animate({
         width: 0
      }, 200, function(){
         items.first().remove();
         me.removeClass("disabled");
      });
   });

   $(".tk-gallery.selectable").find(".control-left").click(function(event){
      event.preventDefault();

      var me = $(this);

      me.addClass("disabled");

      var items = $(".tk-gallery.selectable").find('.item');

      var item = items.last().clone()

      $(".tk-gallery.selectable").find(".items").prepend(item);
      item.css('width', 0);

      item.animate({
         width: parseInt(items.last().css('width'))
      }, 200, function(){
         items.last().remove();
         me.removeClass("disabled");         
      });


   });
});