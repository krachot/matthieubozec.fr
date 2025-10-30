<?php

namespace App\Contact\Enum;

enum PrestationTypeEnum: string
{
    case APPLICATION_WEB = 'application_web';
    case SITE = 'site';
    case INTEGRATION = 'integration';
    case MAINTENANCE = 'maintenance';
    case AUTRE = 'autre';

    public function label(): string
    {
        return match ($this) {
            self::APPLICATION_WEB => 'Développement d’applications métiers',
            self::SITE => 'Création de site vitrine ou e-commerce',
            self::INTEGRATION => 'Intégration web & accessibilité',
            self::MAINTENANCE => 'Maintenance / Reprise de projet',
            self::AUTRE => 'Autre',
        };
    }
}
