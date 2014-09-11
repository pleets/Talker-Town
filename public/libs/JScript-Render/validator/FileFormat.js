/*
 * JScript Render - FileFormat class
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

/* FileFormat class */
JScriptRender.validator.FileFormat = function(format)
{
   JScriptRender.validator.FileFormat.prototype.format = format;
}

JScriptRender.validator.FileFormat.prototype =
{
   Messages: {},

   isValid: function(file) 
   {
      this.Messages = {};

      if (!this.format.test(file.type))
      {
         this.Messages.InvalidFileFormat = "The file format is invalid!, the current file format is " + this.format;
         return false;
      }

      return true;
   },
   getMessages: function()
   {
      return this.Messages;
   }
}
