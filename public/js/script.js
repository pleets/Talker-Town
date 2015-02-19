/* this variable represents the current script element */
var me = {};

function dirname(dir)
{
   if (dir.lastIndexOf("/") == dir.length - 1)
      dir = dir.substring(0, dir.lastIndexOf("/"));
   
   if (dir.lastIndexOf("/") == -1)
      return "";
   
   return dir.substring(0, dir.lastIndexOf("/"));
}

/* relative path to the element whose script is currently being processed.*/
if (typeof document.currentScript != "undefined" && document.currentScript != null)
{
   var str = document.currentScript.src;
   me.path = (str.lastIndexOf("/") == -1) ? "." : str.substring(0, str.lastIndexOf("/"));
}
else {
   /* alternative method to get the currentScript (older browsers) */
       // ...
   /* else get the URL path */
   me.path = '.';
}


/* Get index path based on current script url and requested url */

var indexPath = '';

var size = me.path.length - 1;

for (var i = 0; i <= size; i++) 
{
	if (me.path[i] == document.URL[i])
		indexPath += me.path[i];
   else
      break;
}


/* Get dirname of index path based on public folder */

var rootPath = dirname(indexPath) + '/';

var jsonpRequest = true;      /* Cross domain */
var historyLoaded = false;    /* For cross domain only, Fix repeat history ... */

var urlRequest = (jsonpRequest) ? "http://talkertown01.mywebcommunity.org/backend.php" : indexPath + "application/index/backend";
var cacheFolder = (jsonpRequest) ? "http://talkertown01.mywebcommunity.org/data/cache/" : rootPath + 'data/cache/';


var comet;

var jsonpClient = function(data) 
{
   console.info('jQuery callback...');
}

$(function(){

   comet = new jRender.ajax.Comet({
      url: urlRequest,
      jsonp: jsonpRequest,
   });

   var settings =
   {
      data: { logged_user: $('#current-session').val() },
      callback: {
         success: function(data) 
         {
            // Connection established
            if (typeof data != "object")
               data = $.parseJSON(data);

            console.info("firstTimestamp->" + data.firstTimestamp  + " -----" + "timestamp->" + data.timestamp);

            if (jsonpRequest)
               comet.timestamp = data.timestamp;

            if (data.errors.length)
            {
               for (var i = data.errors.length - 1; i >= 0; i--) {
                  var code = data.errors[i].code;
                  
                  /* Lost session */
                  if (parseInt(code) == 101)
                  {
                     // show lost session message
                     $('#lost-session-message').modal('show');

                     $("#word").attr('disabled', 'disabled');

                     setTimeout(function(){
                        location.reload();
                     }, 5000);

                     comet.disconnect();
                  } 
                  else {
                     //
                  }
               };
            } 
            else {

               $("#state").text("Online");

               if (!($("#" + data["timestamp"]).length))
               {
                  if ($('#content').length)
                  {
                     if (data["firstTimestamp"] == 0 && !historyLoaded)
                     {
                        var msg = data["msg"];      // Get history messages
                        //console.info(btoa(unescape(encodeURIComponent( msg ))));
                        $('#content').append( decodeURIComponent(escape(window.atob( msg ))) );
                        $('#content')[0].scrollTop = 9999999;

                        historyLoaded = true;
                     }
                     else if (data["user"] !== $("#current-session").val())
                     {
                        if (data["user"].trim() !== '' && data["msg"].trim() !== '') 
                        {
                           var decode_message = decodeURIComponent(escape(window.atob( data["msg"] )));

                           // Fb emoticons
                           var str = decode_message.replace(">:(", "<a class='emoticon emoticon_grumpy'></a>");
                           message = str.replace("3:)", "<a class='emoticon emoticon_devil'></a>");
                           message = message.replace("O:)", "<a class='emoticon emoticon_angel'></a>");
                           message = message.replace(">:o", "<a class='emoticon emoticon_upset'></a>");

                           message = message.replace(":)", "<a class='emoticon emoticon_smile'></a>");
                           message = message.replace(":(", "<a class='emoticon emoticon_frown'></a>");
                           message = message.replace(":P", "<a class='emoticon emoticon_tongue'></a>");
                           message = message.replace("=D", "<a class='emoticon emoticon_grin'></a>");
                           message = message.replace(":o", "<a class='emoticon emoticon_gasp'></a>");
                           message = message.replace(";)", "<a class='emoticon emoticon_wink'></a>");
                           message = message.replace(":v", "<a class='emoticon emoticon_pacman'></a>");
                           message = message.replace(":/", "<a class='emoticon emoticon_unsure'></a>");
                           message = message.replace(":'(", "<a class='emoticon emoticon_cry'></a>");
                           message = message.replace("^_^", "<a class='emoticon emoticon_kiki'></a>");
                           message = message.replace("8-)", "<a class='emoticon emoticon_glasses'></a>");
                           message = message.replace("<3", "<a class='emoticon emoticon_heart'></a>");
                           message = message.replace("-_-", "<a class='emoticon emoticon_squint'></a>");
                           message = message.replace("o.O", "<a class='emoticon emoticon_confused'></a>");
                           message = message.replace(":3", "<a class='emoticon emoticon_colonthree'></a>");
                           message = message.replace("(y)", "<a class='emoticon emoticon_like'></a>");

                           // Parse message
                           if (message.substring(0,7) == 'http://' || message.substring(0,8) == 'https://')
                              var msg = "<p id='" + data["timestamp"] + "'>" + data["user"] + " ~ <a target='_blank' href='" + message + "'>" + message + "</a></p>";
                           else
                              var msg = "<p id='" + data["timestamp"] + "'>" + data["user"] + " ~ " + message + "</p>";

                           $('#content').append(msg);
                           $('#content')[0].scrollTop = 9999999;
                        }
                     }

                     // if (data["user"] !== $.cookie("username"))         $.cookie not works
                     if (data["user"] !== $("#current-session").val())
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
                  $.ajax({
                     url: cacheFolder + user + '.json',
                     type: 'get',
                     dataType: (jsonpRequest) ? 'jsonp' : 'json',
                     jsonpCallback: 'jsonpClient',
                     success: function(data) {

                        var i = parseInt(data["avatar"].toString().charAt(0)) - 1;
                        var j = parseInt(data["avatar"].toString().charAt(1)) - 1;

                        var x = j;
                        var y = i;

                        bg_x = ( -32 * x ) + 3;
                        bg_y = ( -(336/11) * y ) + 2.3;

                        var nitem = "<div class='item'>" +
                                    "<img class='ui avatar image' style='background-position: " + bg_x + "px " + bg_y + "px' />" +
                                    "<div class='content'>" +
                                    "<div class='header'>" + data["username"] +  "</div>" +
                                    "<div class='description'><i class='mobile icon'></i><small>3min</small></div>" +
                                    "</div>" +
                                    "<div>";

                        $("#online_users").append(nitem);
                     },
                     error: function(a, b ,c){
                        console.info(b + ": " + c);
                     }
                  });
               };
            }
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

   // Get identity information and connect if identityInformatin is not null
   $.ajax({
   	url: indexPath +  'application/index/getIdentityInformation',
   	dataType: 'json',
   	success: function(data) {
            
         if (typeof data != "object")
         	data = $.parseJSON(data);

   		if (data.username != null)
   			comet.connect(settings);

   	}
   })

   $("#chat").submit(function(event){

      event.preventDefault();

      if ($('#word').val().trim() != "")
      {
         var message = $('#word').val();
         $('#word').val('').attr('disabled', 'disabled');

         $('#content').append("<p id='loading-message-status'>" + "wait ..." + " <i class='spinner icon'></i><p>");
         $('#content')[0].scrollTop = 9999999;

         settings = 
         {
            data: {
               msg: message, logged_user: $('#current-session').val()
            },
            callback: {
               success: function(data) 
               {
                  if (typeof data != "object")
                     data = $.parseJSON(data);     

                  $('#content').append( decodeURIComponent(escape(window.atob( data["msg"] ))) );
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
   $('.ui.modal').modal();
   $('.ui.sidebar').sidebar();
   $('.ui.dropdown').dropdown();

   $("#show-users").click(function(){
      $('.ui.sidebar').sidebar('toggle');
   });

   $('.message .close').on('click', function() {
      $(this).closest('.message').fadeOut();
   });


   $jS.ready(function(){

      if ($("#file_reader_response").length)
      {
         var Reader = $jS.reader;

         var _files = new Reader.File({
            fileBox: document.querySelector("#file-reader-onchange"),      // input[type='file']
            dropBox: document.querySelector("#file-reader-ondrop"),        // dropbox
            preview: document.querySelector("#file-reader-ondrop"),        // preview
            url: 'index/fileUpload',
            size: 104857600,
         });

         _files.addDropEvent(function(files){
            _files.upload(files);
         });
         _files.addChangeEvent(function(files){
            _files.upload(files, function(uploadedFiles) {
               uploadedFiles = $.parseJSON(uploadedFiles);

               for (var i = uploadedFiles.length - 1; i >= 0; i--) {

                  comet.doRequest({
                     data: {
                        msg: rootPath + "data/cache/files/" + uploadedFiles[i]
                     },
                     callback: {
                        success: function(data) 
                        {
                           $('#content').append( decodeURIComponent(escape(window.atob( data["msg"] ))) );
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                           $('#loading-message-status i').attr('class', 'remove icon');
                           $('#loading-message-status').removeAttr('id');
                           $('#content').append("<div class='ui small compact red message'><strong>Error!</strong> The file was not sent.</div>");
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
                  });                  
               };
            });
         });
      }
   });

   /* Avatar gallery */

   $("body").delegate(".tk-gallery.selectable .item", "click", function(event){
      event.preventDefault();
      
      var input = $(this).parent().parent().attr('data-input');

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

   /* Log in form */
   $('#frmUsers').form({
      username: {
         identifier : 'username',
         rules: [
            {
               type   : 'empty',
               prompt : 'Please enter a username'
            },
            {
               type   : 'length[3]',
               prompt : 'Your username must be at least 3 characters'
            },
            {
               type   : 'maxLength[25]',
               prompt : 'Your username is more than 25 characters to long'
            }
         ]
      },
   }, {
      inline    :  true,
      on        :  'blur',
      onSuccess :  function()
      {
         var frm = $(this);

         $.ajax({
            url: frm.attr('action'),
            type: 'post',
            dataType: 'json',
            data: frm.serializeArray(),
            beforeSend: function() 
            {
               frm.addClass('loading');
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
               var html = "<div class='ui error message'><div class='header'> " + textStatus + " </div>";
               html += errorThrown + '</ul></div>';


               // insert html into a modal

               var modal = '<div id="frmLogInSystemError" class="ui modal"><i class="close icon"></i><div class="header">System Error</div><div class="content">';
               modal += html + '</div><div class="actions"><div class="ui positive right labeled icon button">Ok <i class="checkmark icon"></i></div></div></div>';

               if ($('#frmLogInSystemError').length)
                  $('#frmLogInSystemError').remove();
               
               $("body").append(modal);

               $('#frmLogInSystemError').modal('show');
               frm.removeClass('loading');
            },
            success: function(data) 
            {
               // Form Validation with Zend Form
               if (typeof data.formErrors !== 'undefined')
               {
                  var html = "<div class='ui error message'><div class='header'> There was some errors with your submission </div><div class='content'>";
                  html += '<ul class="list">';

                  for (var input in data.formErrors)
                  {
                     html += '<li><strong>' + input + '</strong></li>';

                     for (var inputError in data.formErrors[input])
                     {
                        html += '<li>' + data.formErrors[input][inputError] + '</li>';
                     }
                  }

                  html += '</ul></div></div>';


                  // insert html into a modal

                  var modal = '<div id="frmLogInErrors" class="ui modal"><i class="close icon"></i><div class="header">Form validation</div><div class="content">';
                  modal += html + '</div><div class="actions"><div class="ui positive right labeled icon button">Ok <i class="checkmark icon"></i></div></div></div>';

                  if ($('#frmLogInErrors').length)
                     $('#frmLogInErrors').remove();

                  $("body").append(modal);

                  $('#frmLogInErrors').modal('show');
               }

               // Other Exceptions
               if (typeof data.Exception !== 'undefined')
               {
                  var html = "<div class='ui error message'><div class='header'> Validation exception! </div><div class='content'>";
                  html += data.Exception + '</div></div>';


                  // insert html into a modal

                  var modal = '<div id="frmLogInException" class="ui modal"><i class="close icon"></i><div class="header">Form validation</div><div class="content">';
                  modal += html + '</div><div class="actions"><div class="ui positive right labeled icon button">Ok <i class="checkmark icon"></i></div></div></div>';

                  if ($('#frmLogInException').length)
                     $('#frmLogInException').remove();

                  $("body").append(modal);

                  $('#frmLogInException').modal('show');
               }

               // Successfully log in
               if (typeof data.user !== 'undefined')
                  location.reload();
               else
                  frm.removeClass('loading');
            }
         });
      }
   });

   /* Emoticons */
   $(".emoticonsPanel .panelCell").click(function(event){
      event.preventDefault();

      var text = $(this).children("a").attr('data-text');
      $("#word").val($("#word").val() + text);
      $("#word").focus();
   });

});