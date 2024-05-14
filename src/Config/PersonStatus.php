<?php
declare(strict_types=1);

namespace App\Config;

enum PersonStatus: string {
    case Active = 'active';
    case Deactive = 'deactive';
    case Anonymous = 'anonymous';

    public function title(): string
    {
        return match ($this) {
            self::Active => 'app.person_status.active', 
            self::Deactive => 'app.person_status.deactive', 
            self::Anonymous => 'app.person_status.anonymous', 
        };
    }
}