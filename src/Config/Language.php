<?php
namespace App\Config;

enum Language: string {
    case Fr = "fr";
    case En = "en";
    case Es = "es";
    case Cn = "cn";

    public function title(): string
    {
        return match ($this) {
            self::Fr => 'app.language.fr', 
            self::En => 'app.language.en', 
            self::Es => 'app.language.es', 
            self::Cn => 'app.language.cn'
        };
    }
}