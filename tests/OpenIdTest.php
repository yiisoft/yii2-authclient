<?php

namespace yiiunit\extensions\authclient;

use yii\authclient\OpenId;

class OpenIdTest extends TestCase
{
    protected function setUp()
    {
        $config = [
            'components' => [
                'request' => [
                    'hostInfo' => 'http://testdomain.com',
                    'scriptUrl' => '/index.php',
                ],
            ]
        ];
        $this->mockApplication($config, '\yii\web\Application');
    }

    // Tests :

    public function testSetGet()
    {
        $client = new OpenId();

        $trustRoot = 'http://trust.root';
        $client->setTrustRoot($trustRoot);
        $this->assertEquals($trustRoot, $client->getTrustRoot(), 'Unable to setup trust root!');

        $returnUrl = 'http://return.url';
        $client->setReturnUrl($returnUrl);
        $this->assertEquals($returnUrl, $client->getReturnUrl(), 'Unable to setup return URL!');
    }

    /**
     * @depends testSetGet
     */
    public function testGetDefaults()
    {
        $client = new OpenId();

        $this->assertNotEmpty($client->getTrustRoot(), 'Unable to get default trust root!');
        $this->assertNotEmpty($client->getReturnUrl(), 'Unable to get default return URL!');
    }

    public function testDiscover()
    {
        $this->markTestSkipped('OpenID is almost dead. There are no famous public servers that support it.');

        $url = 'http://openid.yandex.ru';
        $client = new OpenId();
        $info = $client->discover($url);
        $this->assertNotEmpty($info);
        $this->assertNotEmpty($info['url']);
        $this->assertNotEmpty($info['identity']);
        $this->assertEquals(2, $info['version']);
        $this->assertArrayHasKey('identifier_select', $info);
        $this->assertArrayHasKey('ax', $info);
        $this->assertArrayHasKey('sreg', $info);
    }

    /**
     * Data provider for [[testCompareUrl()]]
     * @return array test data
     */
    public function dataProviderCompareUrl()
    {
        return [
            [
                'http://domain.com/index.php?r=site%2Fauth&authclient=myclient',
                'http://domain.com/index.php?r=site%2Fauth&authclient=myclient',
                true
            ],
            [
                'http://domain.com/index.php?r=site%2Fauth&authclient=myclient',
                'http://domain.com/index.php?r=site/auth&authclient=myclient',
                true
            ],
            [
                'http://domain.com/index.php?r=site%2Fauth&authclient=myclient',
                'http://domain.com/index.php?r=site/auth&authclient=myclient2',
                false
            ],
            [
                'http://domain.com/index.php?r=site%2Fauth&authclient=myclient&custom=value',
                'http://domain.com/index.php?r=site%2Fauth&custom=value&authclient=myclient',
                true
            ],
            [
                'https://domain.com/index.php?r=site%2Fauth&authclient=myclient',
                'http://domain.com/index.php?r=site%2Fauth&authclient=myclient',
                false
            ],
        ];
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/3633
     *
     * @dataProvider dataProviderCompareUrl
     *
     * @param string $url1
     * @param string $url2
     * @param bool $expectedResult
     */
    public function testCompareUrl($url1, $url2, $expectedResult)
    {
        $client = new OpenId();
        $comparisonResult = $this->invoke($client, 'compareUrl', [$url1, $url2]);
        $this->assertEquals($expectedResult, $comparisonResult);
    }
}
