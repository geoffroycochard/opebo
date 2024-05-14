<?php
declare(strict_types=1);

namespace App\Config;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\Translation\TranslatableInterface;

enum Objective: string implements TranslatableInterface {
    case Acc = 'accomodation';
    case Adm = 'admin-support';
    case Exc = 'exchange-friendly';
    case Hfi = 'help-intership';
    case Hfj = 'help-job';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        // Translate enum using custom labels
        return match ($this) {
            self::Acc => $translator->trans('app.objective.acc', locale: $locale), 
            self::Adm => $translator->trans('app.objective.adm', locale: $locale), 
            self::Exc => $translator->trans('app.objective.exc', locale: $locale), 
            self::Hfi => $translator->trans('app.objective.hfi', locale: $locale),
            self::Hfj => $translator->trans('app.objective.hfj', locale: $locale)
        };
    }

}