<?php
declare(strict_types=1);

namespace App\Config;

enum Gender: string {
    case M = 'male';
    case F = 'female';

    public function title(): string
    {
        return match ($this) {
            self::M => 'app.gender.male', 
            self::F => 'app.gender.female', 
        };
    }
}