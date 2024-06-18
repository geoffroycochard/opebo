<?php
declare(strict_types=1);

namespace App\Config;
use Symfony\Contracts\Translation\TranslatorInterface;

enum Civility: string {
    case MR = 'mr';
    case MRS = 'mrs';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        // Translate enum using custom labels
        return match ($this) {
            self::MR => $translator->trans('app.civility.mr', locale: $locale), 
            self::MRS => $translator->trans('app.civility.mrs', locale: $locale)
        };
    }
}