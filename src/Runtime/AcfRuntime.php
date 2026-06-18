<?php

namespace Tsp\AcfBuilder\Runtime;

class AcfRuntime
{
    private static bool $hasRegistered = false;

    public static function register(AdminAssets $assets, array $options = []): void
    {
        DependentChoiceFields::register($assets);
        DatePickerFields::register($assets);
        FieldGroupSwitcher::register($assets, (array) ($options['field_group_switcher'] ?? []));
        TabPreferences::register($assets);

        self::$hasRegistered = true;
    }

    public static function hasRegistered(): bool
    {
        return self::$hasRegistered;
    }
}
