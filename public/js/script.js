/* this variable represents the current script element */
var me = {};

String.prototype.replaceAll = function (find, replace) {
   var str = this;
   return str.replace(new RegExp(find.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&'), 'g'), replace);
};

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

var jsonpRequest = false;      /* Cross domain */
var historyLoaded = false;    /* For cross domain only, Fix repeat history ... */

var urlRequest = (jsonpRequest) ? "http://talkertown02.mywebcommunity.org/backend.php" : indexPath + "app/index/backend";
var cacheFolder = (jsonpRequest) ? "http://talkertown02.mywebcommunity.org/data/cache/" : rootPath + 'data/cache/';

/* Streaming */
window.URL = window.URL || window.webkitURL;
navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia || false;

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

   if (jsonpRequest)
      comet.timestamp = 1;

   var settings =
   {
      data: { logged_user: $('#current-session').val(), user_color: $('#user-color').val() },
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

               if ($('#content').length)
               {
                  if (data["firstTimestamp"] == 0 && !historyLoaded)
                  {
                     var msg = data["msg"];      // Get history messages
                     //console.info(btoa(unescape(encodeURIComponent( msg ))));
                     $('#content').append( decodeURIComponent(escape(window.atob( msg ))) );
                     $('#content')[0].scrollTop = 9999999;

                     setTimeout(function()
                     {
                        $('#content')[0].scrollTop = 9999999;
                     }, 100);

                     historyLoaded = true;
                  }
                  else
                  {
                     if (data["latest_messages"].length)
                     {
                        for (var m in data["latest_messages"])
                        {
                           var html = decodeURIComponent(escape(window.atob( data["latest_messages"][m] )));
                           var user = ($($.parseHTML(html)).attr('data-user'));
                           var receiver = ($($.parseHTML(html)).attr('data-receiver'));

                           // if (data["user"] !== $.cookie("username"))         $.cookie not works
                           if (user !== $("#current-session").val())
                           {
                              if (receiver.trim() == "")
                              {
                                 $('#content').append( html );
                                 $('#content')[0].scrollTop = 9999999;

                                 $("#notification-audio")[0].load();
                                 $("#notification-audio")[0].play();
                              }
                              if (receiver == $("#current-session").val())
                              {
                                 if (!($("#private-messages").find(".private-message-box[data-user='" + user + "']").length))
                                 {
                                    $("#private-messages").prepend(" \
                                        <div class='private-message-box' data-user='" + user + "'> \
                                            <div class='ui vertical menu'> \
                                              <div class='header item'> \
                                                <i class='close icon'></i> \
                                                <i class='user icon'></i> \
                                                " + user + " \
                                              </div> \
                                              <div class='content item'></div> \
                                                   <form class='ui form' autocomplete='off'> \
                                                    <div class='ui grid'> \
                                                        <div class='twelve wide mobile only twelve wide tablet only twelve wide computer only twelve wide large monitor only twelve wide widescreen only column' style='padding-right: 1px'> \
                                                            <div class='ui small icon input'> \
                                                                <input type='text' name='word' placeholder='message' /> \
                                                            </div> \
                                                        </div> \
                                                        <div class='four wide column' style='padding-left: 1px'> \
                                                            <button type='sumbit' class='ui small icon submit inverted orange button'><i class='chevron right icon'></i></button> \
                                                        </div> \
                                                    </div> \
                                                </form> \
                                            </div> \
                                        </div> \
                                    ");
                                 }
                                 else {
                                    if ($("#private-messages").find(".private-message-box[data-user='" + user + "']").hasClass('hidden'))
                                       $("#private-messages").find(".private-message-box[data-user='" + user + "']").find('.vertical.menu').addClass('teal inverted');
                                 }

                                 $("#private-messages").find(".private-message-box[data-user='" + user + "']").find('.content').append( html );
                                 $("#private-messages").find(".private-message-box[data-user='" + user + "']").find('.content')[0].scrollTop = 9999999;

                                 $("#notification-audio")[0].load();
                                 $("#notification-audio")[0].play();
                              }
                              else {
                                 // this message is private (other user)
                              }
                           }
                        }
                     }

                     if (data["user"].trim() !== '' && data["msg"].trim() !== '' && data["firstTimestamp"] != 0)
                     {
                        var decode_message = decodeURIComponent(escape(window.atob( data["msg"] )));
                        console.info(decode_message);
                     }
                  }
               }


               $("#online_users").empty();


               if (!data["online_users"].length || (data["online_users"].length == 1 && data["online_users"][0] == $("#current-session").val() ) ) {
                  $("#online_users").append(" \
                     <div class='ui warning message'> \
                       <div class='header'> \
                         Where are the users ? \
                       </div> \
                       In this moment there are not users! \
                     </div> \
                     ");
               }
               else
               {
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

                           bg_x = ( -32 * x ) - 1;
                           bg_y = ( -(336/11) * y ) + 0;

                           if ($("#current-session").val() != data["username"])
                           {
                              var nitem = "<div class='item' data-user='" + data["username"] +  "'>" +
                                          "<img class='ui avatar image' style='background-position: " + bg_x + "px " + bg_y + "px' />" +
                                          "<div class='content'>" +
                                          "<div class='header'>" + data["username"] +  "</div>" +
                                          "<div class='description'><i class='mobile icon'></i><small>3min</small></div>" +
                                          "</div>" +
                                          "<div>";

                              $("#online_users").append(nitem);
                           }

                        },
                        error: function(a, b ,c){
                           console.info(b + ": " + c);
                        }
                     });
                  };
               }
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
         },
         disconnect: function()
         {
            $('#reconnect-message').modal('show');
         }
      }
   }

   // Get identity information and connect if identityInformatin is not null
   $.ajax({
   	url: indexPath +  'app/index/getIdentityInformation',
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
         var original_message = $('#word').val();
         $('#word').val('');

         data = {};
         data.user = $('#current-session').val();
         data.user_color = $('#user-color').val() || '#2A9426';
         data.timestamp = comet.timestamp;

         var decode_message = original_message.replace(/(<([^>]+)>)/ig,"");
         var message = original_message;

         // Parse message
         if (message.substring(0,7) == 'http://' || message.substring(0,8) == 'https://')
            var msg = "<p id='" + data["timestamp"] + "' data-user='" + data["user"] + "' data-receiver=''><strong style='color: " + data.user_color + "'>" + data["user"] + ":</strong> <a target='_blank' href='" + message + "'>" + message + "</a></p>";
         else {

            // Fb emoticons
            var str = decode_message.replaceAll(">:(", "<a class='emoticon emoticon_grumpy'></a>");
            message = str.replaceAll("3:)", "<a class='emoticon emoticon_devil'></a>");
            message = message.replaceAll("O:)", "<a class='emoticon emoticon_angel'></a>");
            message = message.replaceAll(">:o", "<a class='emoticon emoticon_upset'></a>");

            message = message.replaceAll(":)", "<a class='emoticon emoticon_smile'></a>");
            message = message.replaceAll(":(", "<a class='emoticon emoticon_frown'></a>");
            message = message.replaceAll(":P", "<a class='emoticon emoticon_tongue'></a>");
            message = message.replaceAll("=D", "<a class='emoticon emoticon_grin'></a>");
            message = message.replaceAll(":o", "<a class='emoticon emoticon_gasp'></a>");
            message = message.replaceAll(";)", "<a class='emoticon emoticon_wink'></a>");
            message = message.replaceAll(":v", "<a class='emoticon emoticon_pacman'></a>");
            message = message.replaceAll(":/", "<a class='emoticon emoticon_unsure'></a>");
            message = message.replaceAll(":'(", "<a class='emoticon emoticon_cry'></a>");
            message = message.replaceAll("^_^", "<a class='emoticon emoticon_kiki'></a>");
            message = message.replaceAll("8-)", "<a class='emoticon emoticon_glasses'></a>");
            message = message.replaceAll("<3", "<a class='emoticon emoticon_heart'></a>");
            message = message.replaceAll("-_-", "<a class='emoticon emoticon_squint'></a>");
            message = message.replaceAll("o.O", "<a class='emoticon emoticon_confused'></a>");
            message = message.replaceAll(":3", "<a class='emoticon emoticon_colonthree'></a>");
            message = message.replaceAll("(y)", "<a class='emoticon emoticon_like'></a>");

            var msg = "<p id='" + data["timestamp"] + "' data-user='" + data["user"] + "' data-receiver=''><strong style='color: " + data.user_color + "'>" + data["user"] + "</strong>: " + message + "</p>";
         }

         $('#content').append(msg);


         $('#content')[0].scrollTop = 9999999;

         settings =
         {
            data: {
               msg: window.btoa(unescape(encodeURIComponent( original_message ))), logged_user: $('#current-session').val(), user_color: $('#user-color').val()
            },
            callback: {
               success: function(data)
               {
                  /*if (typeof data != "object")
                     data = $.parseJSON(data);

                  $('#content').append( decodeURIComponent(escape(window.atob( data["msg"] ))) );*/
               },
               error: function(jqXHR, textStatus, errorThrown) {
                  $("#"+data.timestamp).addClass('ui small compact red message');
                  alert('The red messages was not sent');
               },
               complete: function()
               {
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
                        msg: window.btoa(unescape(encodeURIComponent( rootPath + "data/cache/files/" + uploadedFiles[i] )))
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

                           setTimeout(function(){
                              $('#content')[0].scrollTop = 9999999;
                           }, 500);

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
      fields: {
         username: {
            identifier : 'username',
            optional: false,
            rules: [
               {
                  type   : 'minLength[3]',
                  prompt : 'Your username must be at least 3 characters'
               },
               {
                  type   : 'maxLength[25]',
                  prompt : 'Your username is more than 25 characters to long'
               }
            ]
         }
      },
      inline: true,
      on: 'blur',
      onSuccess: function(event)
      {
         event.preventDefault();

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
                  var html = "<div class='ui error message'><div class='header'> Exception! </div><div class='content'>";
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


   /* Private messages */
   $("body").delegate("#online_users .item", "click", function(event) {

      var user = $(this).attr('data-user');

      var exists = false;
      $.each($("#private-messages").children("div.private-message-box"), function(){
         if ($(this).attr('data-user') == user)
            exists = true;
      });

      if (exists)
      {
         var tab = $(".private-message-box[data-user='" + user + "'] .item.header");

         if (tab.parent().parent().hasClass('hidden'))
         {
            tab.parent().parent().removeClass('hidden');

            if (tab.parent().hasClass('teal inverted'))
               tab.parent().removeClass('teal inverted');
         }
      }
      else {
         $("#private-messages").prepend(" \
             <div class='private-message-box' data-user='" + user + "'> \
                 <div class='ui vertical menu'> \
                   <div class='header item'> \
                     <i class='close icon'></i> \
                     <i class='user icon'></i> \
                     " + user + " \
                   </div> \
                   <div class='content item'></div> \
                     <form class='ui form' autocomplete='off'> \
                         <div class='ui grid'> \
                             <div class='twelve wide mobile only twelve wide tablet only twelve wide computer only twelve wide large monitor only twelve wide widescreen only column' style='padding-right: 1px'> \
                                 <div class='ui small icon input'> \
                                     <input type='text' name='word' placeholder='message' /> \
                                 </div> \
                             </div> \
                             <div class='four wide column' style='padding-left: 1px'> \
                                 <button type='sumbit' class='ui small icon submit inverted orange button'><i class='chevron right icon'></i></button> \
                             </div> \
                         </div> \
                     </form> \
                 </div> \
             </div> \
         ");

      }

      $('.left.sidebar').sidebar('toggle');
      $("#private-messages").find(".private-message-box[data-user='" + user + "']").find('input').focus();

   });

   $("body").delegate(".private-message-box form", "submit", function(event) {

      event.preventDefault();

      var input = $(this).find("[name='word']");

      if (input.val().trim() != "")
      {
         var box = $(this).parent().parent().find('.content');
         var _to = $(this).parent().parent().attr('data-user');

         var original_message = input.val();
         input.val('');

         data = {};
         data.user = $('#current-session').val();
         data.user_color = $('#user-color').val() || '#2A9426';
         data.timestamp = comet.timestamp;

         var decode_message = original_message;

         // Fb emoticons
         var str = decode_message.replaceAll(">:(", "<a class='emoticon emoticon_grumpy'></a>");
         message = str.replaceAll("3:)", "<a class='emoticon emoticon_devil'></a>");
         message = message.replaceAll("O:)", "<a class='emoticon emoticon_angel'></a>");
         message = message.replaceAll(">:o", "<a class='emoticon emoticon_upset'></a>");

         message = message.replaceAll(":)", "<a class='emoticon emoticon_smile'></a>");
         message = message.replaceAll(":(", "<a class='emoticon emoticon_frown'></a>");
         message = message.replaceAll(":P", "<a class='emoticon emoticon_tongue'></a>");
         message = message.replaceAll("=D", "<a class='emoticon emoticon_grin'></a>");
         message = message.replaceAll(":o", "<a class='emoticon emoticon_gasp'></a>");
         message = message.replaceAll(";)", "<a class='emoticon emoticon_wink'></a>");
         message = message.replaceAll(":v", "<a class='emoticon emoticon_pacman'></a>");
         message = message.replaceAll(":/", "<a class='emoticon emoticon_unsure'></a>");
         message = message.replaceAll(":'(", "<a class='emoticon emoticon_cry'></a>");
         message = message.replaceAll("^_^", "<a class='emoticon emoticon_kiki'></a>");
         message = message.replaceAll("8-)", "<a class='emoticon emoticon_glasses'></a>");
         message = message.replaceAll("<3", "<a class='emoticon emoticon_heart'></a>");
         message = message.replaceAll("-_-", "<a class='emoticon emoticon_squint'></a>");
         message = message.replaceAll("o.O", "<a class='emoticon emoticon_confused'></a>");
         message = message.replaceAll(":3", "<a class='emoticon emoticon_colonthree'></a>");
         message = message.replaceAll("(y)", "<a class='emoticon emoticon_like'></a>");

         // Parse message
         if (message.substring(0,7) == 'http://' || message.substring(0,8) == 'https://')
            var msg = "<p id='" + data["timestamp"] + "' data-user='" + data["user"] + "' data-receiver='" + _to + "'><strong style='color: " + data.user_color + "'>" + data["user"] + ":</strong> <a target='_blank' href='" + message + "'>" + message + "</a></p>";
         else
            var msg = "<p id='" + data["timestamp"] + "' data-user='" + data["user"] + "' data-receiver='" + _to + "'><strong style='color: " + data.user_color + "'>" + data["user"] + "</strong>: " + message + "</p>";


         box.append(msg);
         box[0].scrollTop = 9999999;

         settings =
         {
            data: {
               msg: window.btoa(unescape(encodeURIComponent( original_message ))), logged_user: $('#current-session').val(), user_color: $('#user-color').val(), receiver: _to
            },
            callback: {
               success: function(data)
               {
                  /*if (typeof data != "object")
                     data = $.parseJSON(data);

                  $('#content').append( decodeURIComponent(escape(window.atob( data["msg"] ))) );*/
               },
               error: function(jqXHR, textStatus, errorThrown) {
                  $("#"+data.timestamp).addClass('ui small compact red message');
                  alert('The red messages was not sent');
               },
               complete: function()
               {
                  box[0].scrollTop = 9999999;
                  input.focus();
               }
            }
         }

         comet.doRequest(settings);
      }

   });

   $("body").delegate(".private-message-box .item.header", "click", function(event) {
      if ($(this).parent().parent().hasClass('hidden'))
      {
         $(this).parent().parent().removeClass('hidden');

         if ($(this).parent().hasClass('teal inverted'))
            $(this).parent().removeClass('teal inverted');
      }
      else
         $(this).parent().parent().addClass('hidden');
   });

   $("body").delegate(".private-message-box .item.header .icon.close", "click", function(event) {
      $(this).parent().parent().parent().remove();
   });

   // Show emoticons
   $("#btn-emoticons").click(function(){
      $('#emoticons-box').modal('show');
   });

   $("#btn-streaming").click(function(event)
   {
      event.preventDefault();

      $('#streaming').modal('show');
      $("#btn-start").trigger("click");
   });

   $("#btn-upload-files").click(function(event)
   {
      event.preventDefault();

      $('#mdl-upload-files').modal('show');
   });

   $("#btn-logout-1").click(function(event)
   {
      event.preventDefault();

      $('#mdl-logout').modal('show');
   });

   $("#btn-logout-2").click(function(event)
   {
      event.preventDefault();

      $('#mdl-logout').modal('show');
   });

   $("#btn-start").click(function(event)
   {
      event.preventDefault();

      $("#photo-stm").show();
      $("#video-stm").hide();
      $("#canvas-stm").hide();

      if (navigator.getUserMedia === false)
      {
         console.info('Your browser not supports navigator.getUserMedia()');
      }
      else {

         window.videoData =
         {
            'StreamVideo': null,
            'url': null
         }

         /* Run camera */
         navigator.getUserMedia({
            'audio': false,
            'video': true
         }, function(streamVideo) {

            videoData.StreamVideo = streamVideo;
            videoData.url = window.URL.createObjectURL(streamVideo);

            $("#video-stm").attr('src', videoData.url);
            $("#video-stm").show();
            $("#photo-stm").hide();

         }, function() {
            $("#photo-stm").show();
            alert('Error starting camera!');
         });
      }

   });

   $("#stop-stm").click(function(event) {

      if (videoData.StreamVideo) {
         videoData.StreamVideo.stop();
         window.URL.revokeObjectURL(videoData.url);
      }
   });

   $("#capture-stm").click(function(event) {

      var camera, foto, context, w, h;

      camera = $("#video-stm");
      foto = $("#canvas-stm");

      w = camera.width();
      h = camera.height();

      foto.attr({
         'width': w,
         'height': h
      });

      context = foto[0].getContext('2d');
      context.drawImage(camera[0], 0, 0, w, h);

      $('#video-stm').hide();
      $('#canvas-stm').show();
   });

   $("#send-stm").click(function(event){

      event.preventDefault();

      var url = $(this).attr('href');

      var canvas = $("#canvas-stm")[0];
      var dataURL = canvas.toDataURL();

      $.ajax({
         type: 'post',
         url: url,
         dataType: 'json',
         data: {
            imgBase64: dataURL
         }
      }).done(function(data) {

         if (data.state == 0)
         {

         }
         else if (data.state == 1)
         {
            comet.doRequest({
               data: {
                  msg: window.btoa(unescape(encodeURIComponent( rootPath + data.file )))
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

                     setTimeout(function(){
                        $('#content')[0].scrollTop = 9999999;
                     }, 500);

                     $('#word').focus();
                     $("#streaming").modal('hide');
                  }
               }
            });
         }

      });

   });

});