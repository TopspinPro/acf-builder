<?php

namespace Tsp\AcfBuilder\Runtime;

interface AdminAssets
{
    public function enqueue(): void;

    public function localize(string $objectName, array $data): void;
}
