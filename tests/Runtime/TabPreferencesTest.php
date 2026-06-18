<?php

namespace Tsp\AcfBuilder\Tests\Runtime;

use Tsp\AcfBuilder\Runtime\TabPreferences;

class TabPreferencesTest extends \PHPUnit_Framework_TestCase
{
    public function testFieldGroupMatchesPostTypeScreen()
    {
        $config = [
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'academy_series',
                    ],
                ],
            ],
        ];

        $this->assertTrue(TabPreferences::fieldGroupMatchesScreen($config, (object) [
            'post_type' => 'academy_series',
        ]));
        $this->assertFalse(TabPreferences::fieldGroupMatchesScreen($config, (object) [
            'post_type' => 'page',
        ]));
    }

    public function testFieldGroupMatchesOptionsPage()
    {
        $config = [
            'location' => [
                [
                    [
                        'param' => 'options_page',
                        'operator' => '==',
                        'value' => 'blog-promos',
                    ],
                ],
            ],
        ];

        $this->assertTrue(TabPreferences::fieldGroupMatchesOptionsPage($config, null, 'blog-promos'));
        $this->assertFalse(TabPreferences::fieldGroupMatchesOptionsPage($config, null, 'shop-settings'));
    }
}
