<?php

declare(strict_types=1);

namespace PeskyCMF\Utils;

abstract class TimeZonesHelper
{
    private static ?array $timeZonesList = null;
    private static ?array $timeZonesOptionsAssoc = null;
    private static ?array $timeZonesOptions = null;

    /**
     * Plain list of time zones.
     */
    public static function getList(): array
    {
        if (self::$timeZonesList === null) {
            self::$timeZonesList = \DateTimeZone::listIdentifiers();
        }
        return self::$timeZonesList;
    }

    /**
     * Associative list of time zones for <select> options.
     * @param bool $asValueLabelPair true: ['value' => 'label', ...]; false: ['label' => string, 'value' => string]
     * @return array
     */
    public static function getListAsOptions(bool $asValueLabelPair = false): array
    {
        if ($asValueLabelPair) {
            if (self::$timeZonesOptionsAssoc === null) {
                self::$timeZonesOptionsAssoc = [];
                foreach (self::getList() as $tzName) {
                    self::$timeZonesOptionsAssoc[$tzName] = $tzName;
                }
            }
            return self::$timeZonesOptionsAssoc;
        }

        if (self::$timeZonesOptions === null) {
            self::$timeZonesOptions = [];
            foreach (self::getList() as $tzName) {
                self::$timeZonesOptions[] = [
                    'value' => $tzName,
                    'label' => $tzName,
                ];
            }
        }
        return self::$timeZonesOptions;
    }
}
