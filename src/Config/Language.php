<?php
namespace App\Config;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum Language: string implements TranslatableInterface {
    case Fr = "fr";
    case En = "en";
    case Es = "es";
    case Cn = "cn";

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        // Translate enum using custom labels
        return match ($this) {
            self::Fr => $translator->trans('app.language.fr', locale: $locale), 
            self::En => $translator->trans('app.language.en', locale: $locale), 
            self::Es => $translator->trans('app.language.es', locale: $locale), 
            self::Cn => $translator->trans('app.language.cn', locale: $locale),
        };
    }
}