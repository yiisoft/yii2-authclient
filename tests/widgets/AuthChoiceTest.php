<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\extensions\authclient\widgets;

use yii\authclient\Collection;
use yii\authclient\clients\Google;
use yii\authclient\widgets\AuthChoice;
use yii\authclient\widgets\AuthChoiceStyleAsset;
use yii\helpers\Html;
use yiiunit\extensions\authclient\TestCase;

class AuthChoiceTest extends TestCase
{
    /**
     * @dataProvider customLinkDataProvider
     */
    public function testCustomProviderLink(string $text, string $expectedContent): void
    {
        $this->mockWebApplication([
            'components' => [
                'assetManager' => [
                    'bundles' => [AuthChoiceStyleAsset::class => ['sourcePath' => null, 'css' => []]],
                ],
                'authClientCollection' => [
                    'class' => Collection::class,
                    'clients' => ['google' => ['class' => Google::class]],
                ],
                'customAuthClients' => [
                    'class' => Collection::class,
                    'clients' => ['custom' => [
                        'class' => Google::class,
                        'name' => 'custom',
                        'title' => 'Custom <Provider>',
                    ]],
                ],
            ],
        ]);

        ob_start();
        try {
            $choice = AuthChoice::begin([
                'baseAuthUrl' => ['/site/auth'],
                'clientCollection' => 'customAuthClients',
                'popupMode' => false,
                'autoRender' => false,
            ]);
            $clients = $choice->getClients();
            $this->assertSame(['custom'], array_keys($clients));
            $client = $clients['custom'];
            echo $choice->clientLink($client, $text === 'title' ? Html::encode($client->getTitle()) : $text);
            AuthChoice::end();
            $html = ob_get_contents();
        } finally {
            ob_end_clean();
        }

        $this->assertIsString($html);
        $this->assertStringContainsString($expectedContent, $html);
        $this->assertStringContainsString('authclient=custom', $html);
        $this->assertSame(1, substr_count($html, '<a '));
        $this->assertStringNotContainsString('<Provider>', $html);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public function customLinkDataProvider(): array
    {
        return [
            'encoded title' => ['title', '>Custom &lt;Provider&gt;</a>'],
            'custom image' => [
                Html::img('/images/provider.svg', ['alt' => 'Custom <Provider>']),
                '<img src="/images/provider.svg" alt="Custom &lt;Provider&gt;">',
            ],
        ];
    }
}
