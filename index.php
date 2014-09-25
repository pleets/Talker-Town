<?php

/*
 * Talker Town - Simple chat using Ajax push (Comet) web application model
 * http://www.pleets.org
 * Copyright 2014, Pleets Apps
 * Free to use under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 *
 * Date: 2014-09-02
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

   <title>Talker Town</title>

   <!-- jQuery -->
   <script type="text/javascript" src="public/libs/jquery/jquery-2.1.1/jquery-2.1.1.min.js"></script>
   <script type="text/javascript" src="public/libs/jquery/plugins/carhartl-jquery-cookie-92b7715/jquery.cookie.js"></script>

   <!-- Semantic UI -->
   <link rel="stylesheet" type="text/css" href="public/libs/semantic-ui-0.18.0/packaged/css/semantic.min.css" media="all" />
   <script type="text/javascript" src="public/libs/semantic-ui-0.18.0/packaged/javascript/semantic.min.js"></script>

   <!-- Commet class -->
   <script type="text/javascript" src="public/js/Comet.js"></script>

   <!-- JScript-Render -->
   <script type="text/javascript" src="public/libs/JScript-Render/JScriptRender.js"></script>
   <link rel="stylesheet" type="text/css" href="public/libs/JScript-Render/css/JScriptRender.css" media="all" />

   <!-- App -->
   <script type="text/javascript" src="public/js/script.js"></script>
   <link rel="stylesheet" type="text/css" href="public/css/style.css" media="all" />
</head>
<body>

<?php
# Store username
if (isset($_POST["username"])) 
{
   setcookie("username", $_POST["username"]);

   $user_info = array(
      "username" => $_POST["username"],
      "avatar" => isset($_POST["gender"]) ? (int) $_POST["gender"] : 11
   );
   file_put_contents('cache/' . $_POST["username"] . '.json', json_encode($user_info));

   setcookie("avatar", $p);
   header("location: .");
}
?>

   <?php if (!isset($_COOKIE["username"])): ?>
   <div class="ui segment" style="background: rgb(247, 247, 247); color: #808080">
      <h1 class="ui basic header center aligned">Talker Town</h1>
      <form action="" method="post" class="ui small form">
         <div class="field">
            <label for="username">Enter your pseudonym</label>
            <div class="ui left labeled icon input">
               <input type="text" id="username" name="username" autofocus="autofocus" placeholder="username">
               <i class="user icon"></i>
               <div class="ui corner label">
                  <i class="icon asterisk"></i>
               </div>
            </div>
         </div>
         <input type="hidden" name="gender" id="gender" value="11">
         <div class="tk-gallery selection" data-input="gender">
            <div class="item" data-value="11" style="background-position: calc(-0*77.5px) calc(-0*74px);"></div>
            <div class="item" data-value="12" style="background-position: calc(-1*77.5px) calc(-0*74px);"></div>
            <div class="item" data-value="13" style="background-position: calc(-2*77.5px) calc(-0*74px);"></div>
            <div class="item" data-value="14" style="background-position: calc(-3*77.5px) calc(-0*74px);"></div>
            <div class="item" data-value="15" style="background-position: calc(-4*77.5px) calc(-0*74px);"></div>
            <div class="item" data-value="16" style="background-position: calc(-5*77.5px) calc(-0*74px);"></div>
            <div class="item" data-value="17" style="background-position: calc(-6*77.5px) calc(-0*74px);"></div>
            <div class="item" data-value="18" style="background-position: calc(-7*77.5px) calc(-0*74px);"></div>
            <div class="item" data-value="21" style="background-position: calc(-0*77.5px) calc(-1*74px);"></div>
            <div class="item" data-value="22" style="background-position: calc(-1*77.5px) calc(-1*74px);"></div>
            <div class="item" data-value="23" style="background-position: calc(-2*77.5px) calc(-1*74px);"></div>
            <div class="item" data-value="24" style="background-position: calc(-3*77.5px) calc(-1*74px);"></div>
            <div class="item" data-value="25" style="background-position: calc(-4*77.5px) calc(-1*74px);"></div>
            <div class="item" data-value="26" style="background-position: calc(-5*77.5px) calc(-1*74px);"></div>
            <div class="item" data-value="27" style="background-position: calc(-6*77.5px) calc(-1*74px);"></div>
            <div class="item" data-value="28" style="background-position: calc(-7*77.5px) calc(-1*74px);"></div>
            <div class="item" data-value="31" style="background-position: calc(-0*77.5px) calc(-2*74px);"></div>
            <div class="item" data-value="32" style="background-position: calc(-1*77.5px) calc(-2*74px);"></div>
            <div class="item" data-value="33" style="background-position: calc(-2*77.5px) calc(-2*74px);"></div>
            <div class="item" data-value="34" style="background-position: calc(-3*77.5px) calc(-2*74px);"></div>
            <div class="item" data-value="35" style="background-position: calc(-4*77.5px) calc(-2*74px);"></div>
            <div class="item" data-value="36" style="background-position: calc(-5*77.5px) calc(-2*74px);"></div>
            <div class="item" data-value="37" style="background-position: calc(-6*77.5px) calc(-2*74px);"></div>
            <div class="item" data-value="37" style="background-position: calc(-7*77.5px) calc(-2*74px);"></div>
            <div class="item" data-value="41" style="background-position: calc(-0*77.5px) calc(-3*74px);"></div>
            <div class="item" data-value="42" style="background-position: calc(-1*77.5px) calc(-3*74px);"></div>
            <div class="item" data-value="43" style="background-position: calc(-2*77.5px) calc(-3*74px);"></div>
            <div class="item" data-value="44" style="background-position: calc(-3*77.5px) calc(-3*74px);"></div>
            <div class="item" data-value="45" style="background-position: calc(-4*77.5px) calc(-3*74px);"></div>
            <div class="item" data-value="46" style="background-position: calc(-5*77.5px) calc(-3*74px);"></div>
            <div class="item" data-value="47" style="background-position: calc(-6*77.5px) calc(-3*74px);"></div>             
            <div class="item" data-value="47" style="background-position: calc(-7*77.5px) calc(-3*74px);"></div>             
         </div>
         <input type="submit" value="Login" class="ui submit button" />      
      </form>
      <div class="ui basic center aligned segment">
         <p>The best city on the Internet</p>
      </div>
   </div>
   <?php else: ?>
   <div class="ui blue inverted">
      <div class="ui tiered menu">
        <div class="menu">
          <a class="item" id="show-users">
            <i class="reorder icon"></i>
            Users
          </a>
          <div class="right menu">
            <div class="ui dropdown item">
              Talker Town <i class="icon dropdown"></i>
              <div class="menu">
               <a href="logout.php" class="ui item"> Sing out</a>
              </div>
            </div>
          </div>
        </div>
      </div>
   </div>
   <div class="ui thin sidebar segment">
      <div class="ui animated selection list" id="online_users"></div>
   </div>
   <div class="ui segment">
      <div id="content" class="ui segment"></div>
      <form action="" method="get" id="chat" class="ui form segment">
         <div class="field">
            <div class="ui small icon input">
               <input type="text" name="word" id="word" autofocus="autofocus" placeholder="message" />
            </div>
         </div>
         <div class="field">
            <input type="submit" name="submit" value="Send" class="ui small submit button" />
            <span id="state">Offline</span>
         </div>
      </form>

      <input type="file" id="file-reader-onchange" value="" multiple="multiple" />
      <div id="file-reader-ondrop" class="drop" style="overflow: hidden; min-height: 50px">
         <span style="color: #C0C0C0; font-size: 25px;">Drop files here</span>
      </div>
      <div id="file_reader_ondrop_response"></div>
      <div id="file_reader_response"></div>

      <!-- Notification audio -->
      <audio id='notification-audio' src='public/audio/notification.wav' style='display: none'>
         <source src='public/audio/notification.wav' type='audio/wav'>
      </audio>
   <?php endif; ?>
   </div>

</body>
</html>