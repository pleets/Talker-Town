<?php

namespace Parser\Controller;

class MessageController
{
    private $message;

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function parseURLs()
    {
        return $this->message();
    }

    public function parseEmoticons()
    {
        $message = $this->message;

        # plain text  =>  HTML class
        $emoticons = array
        (
            ">:("   =>  "emoticon emoticon_grumpy",
            "3:)"   =>  "emoticon emoticon_devil",
            "O:)"   =>  "emoticon emoticon_angel",
            ">:o"   =>  "emoticon emoticon_upset",
            ":)"    =>  "emoticon emoticon_smile",
            ":("    =>  "emoticon emoticon_frown",
            ":P"    =>  "emoticon emoticon_tongue",
            "=D"    =>  "emoticon emoticon_grin",
            ":o"    =>  "emoticon emoticon_gasp",
            ";)"    =>  "emoticon emoticon_wink",
            ":v"    =>  "emoticon emoticon_pacman",
            ":/"    =>  "emoticon emoticon_unsure",
            ":'("   =>  "emoticon emoticon_cry",
            "^_^"   =>  "emoticon emoticon_kiki",
            "8-)"   =>  "emoticon emoticon_glasses",
            "<3"    =>  "emoticon emoticon_heart",
            "-_-"   =>  "emoticon emoticon_squint",
            "o.O"   =>  "emoticon emoticon_confused",
            ":3"    =>  "emoticon emoticon_colonthree",
            "(y)"   =>  "emoticon emoticon_like",
        );

        foreach ($emoticons as $needle => $class)
        {
            while (strpos($message, $needle) !== false)
            {
                $message = str_replace($needle, "<a class='$class'></a>", $message);
            }
        }

        $this->message = $message;

        return $this->message;
    }
}
