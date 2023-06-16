<?php
declare(strict_type=1);

namespace App\Config;

enum Objective: string {
    case Acc = 'accomodation';
    case Adm = 'admin-support';
    case Exc = 'exchange-friendly';
    case Hfi = 'help-intership';
    case Hfj = 'help-job';


    public function title(): string
    {
        return match ($this) {
            self::Acc => 'app.objective.acc', 
            self::Adm => 'app.objective.adm', 
            self::Exc => 'app.objective.exc', 
            self::Hfi => 'app.objective.hfi',
            self::Hfj => 'app.objective.hfj'
        };
    }

}