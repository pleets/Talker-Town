<?php

/*
 * Talker Town - Simple chat using Ajax push (Comet) web application model
 * http://www.pleets.org
 * Copyright 2014, Pleets Apps
 * Free to use under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 *
 * Date: 2014-08-01
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

   <!-- App -->
   <script type="text/javascript" src="public/js/script.js"></script>
   <link rel="stylesheet" type="text/css" href="public/css/style.css" media="all" />
</head>
<body>

<?php
# Store username
if (isset($_POST["username"])) {
   setcookie("username", $_POST["username"]);
   header("location: .");
}
?>
<div class="ui grid">
   <?php if (!isset($_COOKIE["username"])): ?>
   <div class="ui sixteen wide column">
      <form action="" method="post" class="ui form segment">
         <div class="field">
            <label for="username">Username</label>
            <div class="ui left labeled icon input">
               <input type="text" id="username" name="username" autofocus="autofocus" placeholder="username">
               <i class="user icon"></i>
               <div class="ui corner label">
                  <i class="icon asterisk"></i>
               </div>
            </div>
         </div>
      </form>
   </div>
   <?php else: ?>
   <div class="ui four wide column">
      <div class="ui selection list" id="online_users">
      </div>
   </div>
   <div class="ui twelve wide column">
      <div id="content" class="ui segment"></div>
      <form action="" method="get" id="chat" class="ui form segment">
         <div class="field">
            <div class="ui small icon input">
               <input type="text" name="word" id="word" autofocus="autofocus" placeholder="message" />
            </div>
         </div>
         <div class="field">
            <input type="submit" name="submit" value="Send" class="ui small blue submit button" />
            <a href="logout.php" class="ui small primary button"> Sing out</a>
            <span id="state">Offline</span>
         </div>
      </form>
   <?php endif; ?>
   </div>
</div>
</body>
</html>