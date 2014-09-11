/*
 * JScript Render - Date Class
 * http://www.pleets.org
 *
 * Copyright 2014, Pleets Apps
 * Free to use under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 */

/* JScriptRender alias */
if (!window.hasOwnProperty('JScriptRender'))
   JScriptRender = {};

/* Namespace */
if (!JScriptRender.hasOwnProperty('validator'))
   JScriptRender.validator = new Object();

/* Date class */
JScriptRender.validator.Date = new Function();    

JScriptRender.validator.Date.prototype = 
{
   Messages: {},
 
   isValid: function(string)
   {
      // Remove whitespaces
      string = string.trim();

      this.Messages = {};

      /* Pattern: Y-m-d */
      var RegExp2 = /^(\d{4}-\d{2}-\d{2})*$/;

      if (string.match(RegExp2)) 
      {
         var date = string.split('-');
   
         if (string.length)
         {
            var newDate = new Date(date[0], date[1] - 1, date[2]);

            if (!(!newDate || newDate.getFullYear() == date[0] && newDate.getMonth() == date[1] -1 && newDate.getDate() == date[2]))
            {
               this.Messages.dateInvalidDate = "The input does not appear to be a valid date";                              
               return false;
            }
         }
      }
      else {
         this.Messages.dateFalseFormat = "The input does not fit the date format 'Y-m-d'";
         return false;
      }

      return true;
   },
   getMessages: function()
   {
      return this.Messages;
   }    
}
