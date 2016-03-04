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
        $message = $this->message;

        $parts = explode(" ", $message);

        foreach ($parts as $unity)
        {
            $trimed_unity = trim($unity);

            $unity_protocolized = preg_replace(
                "/^([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \?=.-]*)*\/?$/",
                "http://" . $trimed_unity, $trimed_unity
            );

            $parsed_unity = preg_replace(
                "/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \?=.-]*)*\/?$/",
                "<a href='$unity_protocolized' target='_blank'>$trimed_unity</a>", $unity_protocolized
            );

            $message = str_replace($trimed_unity, $parsed_unity, $message);
        }

        $this->message = $message;

        return $this->message;
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

        $parts = explode(" ", $message);

        foreach ($parts as $unity)
        {
            foreach ($emoticons as $needle => $class)
            {
                if (trim($unity) == $needle)
                    $message = str_replace(trim($unity), "<span class='$class'></span>", $message);
            }
        }

        $this->message = $message;

        return $this->message;
    }
}
