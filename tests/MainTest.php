<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use w3lifer\PhpHelper\PhpHelper;

final class MainTest extends TestCase
{
    public function testAddPrefixToArrayKeys()
    {
        $this->assertEquals([], PhpHelper::addPrefixToArrayKeys([], ''));
        $this->assertEquals(['a' => 1, 'b' => 2], PhpHelper::addPrefixToArrayKeys(['a' => 1, 'b' => 2], ''));
        $this->assertEquals(['_a' => 1, '_b' => 2], PhpHelper::addPrefixToArrayKeys(['a' => 1, 'b' => 2], '_'));
        $this->assertEquals(
            [
                '_a' => 1,
                '_b' => 2,
                '_c' => ['_a' => 11, '_b' => 22]
            ],
            PhpHelper::addPrefixToArrayKeys([
                'a' => 1,
                'b' => 2,
                'c' => ['a' => 11, 'b' => 22]
            ], '_')
        );
        $this->assertEquals(
            [
                '_a' => 1,
                '_b' => 2,
                '_c' => ['a' => 11, 'b' => 22]
            ],
            PhpHelper::addPrefixToArrayKeys([
                'a' => 1,
                'b' => 2,
                'c' => ['a' => 11, 'b' => 22]
            ], '_', false)
        );
    }

    public function testAddPostfixToArrayKeys()
    {
        $this->assertEquals([], PhpHelper::addPostfixToArrayKeys([], ''));
        $this->assertEquals(['a' => 1, 'b' => 2], PhpHelper::addPostfixToArrayKeys(['a' => 1, 'b' => 2], ''));
        $this->assertEquals(['a_' => 1, 'b_' => 2], PhpHelper::addPostfixToArrayKeys(['a' => 1, 'b' => 2], '_'));
        $this->assertEquals(
            [
                'a_' => 1,
                'b_' => 2,
                'c_' => ['a_' => 11, 'b_' => 22]
            ],
            PhpHelper::addPostfixToArrayKeys([
                'a' => 1,
                'b' => 2,
                'c' => ['a' => 11, 'b' => 22]
            ], '_')
        );
        $this->assertEquals(
            [
                'a_' => 1,
                'b_' => 2,
                'c_' => ['a' => 11, 'b' => 22]
            ],
            PhpHelper::addPostfixToArrayKeys([
                'a' => 1,
                'b' => 2,
                'c' => ['a' => 11, 'b' => 22]
            ], '_', false)
        );
    }

    public function testAddZeroPrefix()
    {
        $this->assertEquals('00', PhpHelper::addZeroPrefix('0'));
        $this->assertEquals('01', PhpHelper::addZeroPrefix('1'));
        $this->assertEquals('10', PhpHelper::addZeroPrefix('10'));
        $this->assertEquals('11', PhpHelper::addZeroPrefix('11'));

        // ---------------------------------------------------------------------

        $this->assertEquals('000', PhpHelper::addZeroPrefix('0', 2));
        $this->assertEquals('001', PhpHelper::addZeroPrefix('1', 2));
        $this->assertEquals('010', PhpHelper::addZeroPrefix('10', 2));
        $this->assertEquals('011', PhpHelper::addZeroPrefix('11', 2));

        $this->assertEquals('100', PhpHelper::addZeroPrefix('100', 2));
        $this->assertEquals('111', PhpHelper::addZeroPrefix('111', 2));

        // ---------------------------------------------------------------------

        $this->assertEquals('0000', PhpHelper::addZeroPrefix('0', 3));
        $this->assertEquals('0001', PhpHelper::addZeroPrefix('1', 3));
        $this->assertEquals('0010', PhpHelper::addZeroPrefix('10', 3));
        $this->assertEquals('0011', PhpHelper::addZeroPrefix('11', 3));

        $this->assertEquals('0100', PhpHelper::addZeroPrefix('100', 3));
        $this->assertEquals('0111', PhpHelper::addZeroPrefix('111', 3));

        $this->assertEquals('1000', PhpHelper::addZeroPrefix('1000', 3));
        $this->assertEquals('1111', PhpHelper::addZeroPrefix('1111', 3));
    }

    public function testArrayToXml()
    {
        $this->assertEquals(
            '<?xml version="1.0"?>
<data value=""><item0><a>1</a><b>1</b><c>1</c></item0><item1><a>2</a><b>2</b><c>2</c></item1><item2><a>3</a><b>3</b><c>3</c></item2></data>
',
            PhpHelper::arrayToXml([
                [
                    'a' => 1,
                    'b' => 1,
                    'c' => 1,
                ],
                [
                    'a' => 2,
                    'b' => 2,
                    'c' => 2,
                ],
                [
                    'a' => 3,
                    'b' => 3,
                    'c' => 3,
                ],
            ])
        );
    }

    /**
     * @runInSeparateProcess To prevent the following error: Cannot modify header information - headers already sent...
     */
    public function testAuth()
    {
        $this->assertFalse(PhpHelper::auth([]));

        $_SERVER['PHP_AUTH_USER'] = 'hello';
        $_SERVER['PHP_AUTH_PW']   = 'world';
        $this->assertFalse(PhpHelper::auth([]));
        $this->assertFalse(PhpHelper::auth(['root' => 'toor']));

        $_SERVER['PHP_AUTH_USER'] = 'root';
        $_SERVER['PHP_AUTH_PW']   = 'toor';
        $this->assertFalse(PhpHelper::auth([]));
        $this->assertTrue(PhpHelper::auth(['root' => 'toor']));
    }

    /**
     * @runInSeparateProcess To prevent the following error: Cannot modify header information - headers already sent...
     */
    public function testClearAllCookies()
    {
        $this->assertFalse(PhpHelper::clearAllCookies());
        $_SERVER['HTTP_COOKIE'] = 'first=one';
        $this->assertTrue(PhpHelper::clearAllCookies());
        $_SERVER['HTTP_COOKIE'] = 'first=one; second=two';
        $this->assertTrue(PhpHelper::clearAllCookies());
        setcookie('111', '222');
        $this->assertTrue(PhpHelper::clearAllCookies());
        setcookie('111', '222');
        setcookie('333', '444');
        $this->assertTrue(PhpHelper::clearAllCookies());
    }

    public function testCreateRss()
    {
$this->assertEquals(

'<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
'<rss version="2.0"><channel>' .
    '<title>StackHub</title>' .
    '<link>https://stackhub.net</link>' .
    '<description>▷ Concise yet comprehensive technical manuals and online tools — useful and fluff-free</description>' .
'</channel></rss>' . "\n",

PhpHelper::createRss([
    'title' => 'StackHub',
    'link' => 'https://stackhub.net',
    'description' => '▷ Concise yet comprehensive technical manuals and online tools — useful and fluff-free',
], [])

);

$this->assertEquals(

'<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
'<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"><channel>' .
    '<atom:link href="https://stackhub.net/rss.xml" rel="self" type="application/rss+xml"/>' .
    '<title>StackHub</title>' .
    '<link>https://stackhub.net</link>' .
    '<description>▷ Concise yet comprehensive technical manuals and online tools — useful and fluff-free</description>' .
'</channel></rss>' . "\n",

PhpHelper::createRss([
    'title' => 'StackHub',
    'link' => 'https://stackhub.net',
    'description' => '▷ Concise yet comprehensive technical manuals and online tools — useful and fluff-free',
    'atomLink' => 'https://stackhub.net/rss.xml',
], [])

);

        // -------------------------------------------------------------------------------------------------------------

$this->assertEquals(

'<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
'<rss version="2.0"><channel>' .
    '<title>StackHub</title>' .
    '<link>https://stackhub.net</link>' .
    '<description>▷ Concise yet comprehensive technical manuals and online tools — useful and fluff-free</description>' .

    '<item><title>Manuals</title><link>https://stackhub.net/manuals</link>' .
    '<description>▷ Concise yet comprehensive technical manuals — useful and fluff-free</description>' .
    '<guid>https://stackhub.net/manuals</guid></item>' .

    '<item><title>Tools</title><link>https://stackhub.net/tools</link>' .
    '<description>▷ Online tools — useful and fluff-free</description>' .
    '<guid>https://stackhub.net/tools</guid></item>' .
'</channel></rss>' . "\n",

PhpHelper::createRss([
    'title' => 'StackHub',
    'link' => 'https://stackhub.net',
    'description' => '▷ Concise yet comprehensive technical manuals and online tools — useful and fluff-free',
], [
    [
        'title' => 'Manuals',
        'link' => 'https://stackhub.net/manuals',
        'description' => '▷ Concise yet comprehensive technical manuals — useful and fluff-free',
        'guid' => 'https://stackhub.net/manuals',
    ],
    [
        'title' => 'Tools',
        'link' => 'https://stackhub.net/tools',
        'description' => '▷ Online tools — useful and fluff-free',
        'guid' => 'https://stackhub.net/tools',
    ],
])

);
    }

    public function testCreateSitemap()
    {
        $this->assertEquals(
            PhpHelper::XML_DECLARATION . PHP_EOL .
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><url>' .
                '<loc>https://shiftcalendar.online</loc>' .
                '<lastmod>1970-01-01</lastmod>' .
                '<changefreq>always</changefreq>' .
                '<priority>0.0</priority>' .
            '</url></urlset>',
            PhpHelper::createSitemap([
                [
                    'loc' => 'https://shiftcalendar.online',
                    'lastmod' => '1970-01-01',
                    'changefreq' => 'always',
                    'priority' => '0.0',
                ],
            ])
        );
        $this->assertEquals(
            '<url>' .
                '<loc>https://shiftcalendar.online</loc>' .
                '<lastmod>1970-01-01</lastmod>' .
                '<changefreq>always</changefreq>' .
                '<priority>0.0</priority>' .
            '</url>',
            PhpHelper::createSitemap([
                [
                    'loc' => 'https://shiftcalendar.online',
                    'lastmod' => '1970-01-01',
                    'changefreq' => 'always',
                    'priority' => '0.0',
                ],
            ], false)
        );
    }

    public function testCreateSitemapIndex()
    {
        $this->assertEquals(
            PhpHelper::XML_DECLARATION . PHP_EOL .
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><sitemap>' .
                '<loc>https://shiftcalendar.online/sitemap.xml</loc>' .
                '<lastmod>1970-01-01</lastmod>' .
            '</sitemap></sitemapindex>',
            PhpHelper::createSitemapIndex([
                [
                    'loc' => 'https://shiftcalendar.online/sitemap.xml',
                    'lastmod' => '1970-01-01',
                    'changefreq' => 'always',
                    'priority' => '0.0',
                ],
            ])
        );
        $this->assertEquals(
            '<sitemap>' .
                '<loc>https://shiftcalendar.online/sitemap.xml</loc>' .
                '<lastmod>1970-01-01</lastmod>' .
            '</sitemap>',
            PhpHelper::createSitemapIndex([
                [
                    'loc' => 'https://shiftcalendar.online/sitemap.xml',
                    'lastmod' => '1970-01-01',
                    'changefreq' => 'always',
                    'priority' => '0.0',
                ],
            ], false)
        );
    }

    public function testCreateSqlValuesString()
    {
        $this->assertEquals(
            '(1, "one", "\"")',
            PhpHelper::createSqlValuesString([1, 'one', '"'])
        );
        $this->assertEquals(
            "(1, 'one', '\'')",
            PhpHelper::createSqlValuesString([1, 'one', '\''], "'")
        );
    }

    public function testCsvStringToArray()
    {
        $this->assertEquals([['']], PhpHelper::csvStringToArray(''));
        $this->assertEquals([[1]], PhpHelper::csvStringToArray('1'));
        $this->assertEquals([[123]], PhpHelper::csvStringToArray('123'));
        $this->assertEquals([['1']], PhpHelper::csvStringToArray('1'));
        $this->assertEquals([['123']], PhpHelper::csvStringToArray('123'));
        $this->assertEquals(
            [
                ['First name', 'Last name'],
                ['John', 'Doe'],
                ['Richard', 'Roe'],
            ],
            PhpHelper::csvStringToArray('
                First name,Last name
                John,Doe
                "Richard", "Roe"
            ')
        );
        $this->assertEquals(
            [
                ['John', 'Doe'],
                ['Richard', 'Roe'],
            ],
            PhpHelper::csvStringToArray('
                First name,Last name
                John,Doe
                "Richard", "Roe"
            ', true)
        );
    }

    public function testFilterListOfArraysByKeyValuePairs()
    {
        $this->assertEquals(
            [
                ['firstname' => 'John', 'lastname' => 'Doe'],
            ],
            PhpHelper::filterListOfArraysByKeyValuePairs(
                [
                    ['firstname' => 'John', 'lastname' => 'Doe'],
                    ['firstname' => 'Вася', 'lastname' => 'Пупкин'],
                ],
                ['firstname' => 'Jo']
            )
        );
        $this->assertEquals(
            [],
            PhpHelper::filterListOfArraysByKeyValuePairs(
                [
                    ['firstname' => 'John', 'lastname' => 'Doe'],
                    ['firstname' => 'Вася', 'lastname' => 'Пупкин'],
                ],
                ['firstname' => 'jo']
            )
        );
    }

    public function testGenerateBase58Id()
    {
        $this->assertEquals(8, strlen(PhpHelper::generateBase58Id()));
        $this->assertEquals(10, strlen(PhpHelper::generateBase58Id(10)));
    }

    public function testGetBase64Image()
    {
        /** @noinspection SpellCheckingInspection */
        $this->assertEquals(
<<<STRING
data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD//gA+Q1JFQVRPUjogZ2QtanBlZyB2MS4wICh1c2luZyBJSkcgSlBFRyB2ODApLCBkZWZhdWx0IHF1YWxpdHkK/9sAQwAIBgYHBgUIBwcHCQkICgwUDQwLCwwZEhMPFB0aHx4dGhwcICQuJyAiLCMcHCg3KSwwMTQ0NB8nOT04MjwuMzQy/9sAQwEJCQkMCwwYDQ0YMiEcITIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy/8AAEQgB9AH0AwEiAAIRAQMRAf/EAB8AAAEFAQEBAQEBAAAAAAAAAAABAgMEBQYHCAkKC//EALUQAAIBAwMCBAMFBQQEAAABfQECAwAEEQUSITFBBhNRYQcicRQygZGhCCNCscEVUtHwJDNicoIJChYXGBkaJSYnKCkqNDU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6g4SFhoeIiYqSk5SVlpeYmZqio6Slpqeoqaqys7S1tre4ubrCw8TFxsfIycrS09TV1tfY2drh4uPk5ebn6Onq8fLz9PX29/j5+v/EAB8BAAMBAQEBAQEBAQEAAAAAAAABAgMEBQYHCAkKC//EALURAAIBAgQEAwQHBQQEAAECdwABAgMRBAUhMQYSQVEHYXETIjKBCBRCkaGxwQkjM1LwFWJy0QoWJDThJfEXGBkaJicoKSo1Njc4OTpDREVGR0hJSlNUVVZXWFlaY2RlZmdoaWpzdHV2d3h5eoKDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uLj5OXm5+jp6vLz9PX29/j5+v/aAAwDAQACEQMRAD8A9/ooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAoqG8vLbT7OW7vJ44LaFS8ksjBVRR3JNfNPxQ+Ndx4h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4c/7B8P/AKCKAOoooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAr4Q13/kYdT/6+5f8A0M19318Ia7/yMOp/9fcv/oZoAz6+1/hv/wAk18Of9g+H/wBBFfFFfa/w3/5Jr4c/7B8P/oIoA6iiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACvhDXf+Rh1P/r7l/8AQzX3fXwhrv8AyMOp/wDX3L/6GaAM+vtf4b/8k18Of9g+H/0EV8UV9r/Df/kmvhz/ALB8P/oIoA6iiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACvhDXf+Rh1P8A6+5f/QzX3fXwhrv/ACMOp/8AX3L/AOhmgDPr7X+G/wDyTXw5/wBg+H/0EV8UV9r/AA3/AOSa+HP+wfD/AOgigDqKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAK+ENd/wCRh1P/AK+5f/QzX3fXwhrv/Iw6n/19y/8AoZoAz6+1/hv/AMk18Of9g+H/ANBFfFFfa/w3/wCSa+HP+wfD/wCgigDqKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAK+ENd/5GHU/+vuX/ANDNfd9fCGu/8jDqf/X3L/6GaAM+vtf4b/8AJNfDn/YPh/8AQRXxRX2v8N/+Sa+HP+wfD/6CKAOoooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAr4Q13/kYdT/6+5f/AEM19318Ia7/AMjDqf8A19y/+hmgDPr7X+G//JNfDn/YPh/9BFfFFfa/w3/5Jr4c/wCwfD/6CKAOoooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAr4Q13/kYdT/AOvuX/0M19318Ia7/wAjDqf/AF9y/wDoZoAz6+1/hv8A8k18Of8AYPh/9BFfFFfa/wAN/wDkmvhz/sHw/wDoIoA6iiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACvhDXf8AkYdT/wCvuX/0M19318Ia7/yMOp/9fcv/AKGaAM+vtf4b/wDJNfDn/YPh/wDQRXxRX2v8N/8Akmvhz/sHw/8AoIoA6iiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACvhDXf+Rh1P/r7l/wDQzX3fXwhrv/Iw6n/19y/+hmgDPr7X+G//ACTXw5/2D4f/AEEV8UV9r/Df/kmvhz/sHw/+gigDqKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAK8Hv/2b/tuo3N3/AMJXs8+VpNv9nZ27iTjPm+9e8UUAfP8A/wAMy/8AU3f+U3/7bXtnhvR/+Ef8Nabo/n/aPsVukHm7Nm/aMZxk4/M1qUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFAH//2Q==
STRING, PhpHelper::getBase64Image(__DIR__ . '/_data/test.jpg')
        );
    }

    public function testGetClassFromObject()
    {
        $this->assertEquals('MainTest', PhpHelper::getClassNameFromObject($this));
        $this->assertEquals('PhpHelper', PhpHelper::getClassNameFromObject(new PhpHelper()));
    }

    public function testGetClassFromString()
    {
        $this->assertEquals('MainTest', PhpHelper::getClassNameFromString('MainTest'));
        $this->assertEquals('PhpHelper', PhpHelper::getClassNameFromString(PhpHelper::class));
    }

    public function testGetDatesBetweenDates()
    {
        $this->assertEquals(
            ['1969-12-31', '1970-01-01', '1970-01-02'],
            PhpHelper::getDatesBetweenDates('1969-12-31', '1970-01-02')
        );
        $this->assertEquals(
            ['12/31/1969', '01/01/1970', '01/02/1970'],
            PhpHelper::getDatesBetweenDates(
                '12/31/1969',
                '01/02/1970',
                'm/d/Y'
            )
        );
    }

    public function testGetFilesInDirectory()
    {
        $paths = scandir(__DIR__);
        $fileNames= [];
        foreach ($paths as $path) {
            $fileName = __DIR__ . '/' . $path;
            if (is_file($fileName)) {
                $fileNames[] = $fileName;
            }
        }
        $this->assertEquals($fileNames, PhpHelper::getFilesInDirectory(__DIR__));
    }

    public function testGetNormalizedDayOfWeek()
    {
        $this->assertEquals(0, PhpHelper::getNormalizedDayOfWeek(7));
        $this->assertEquals(1, PhpHelper::getNormalizedDayOfWeek(1));
        $this->assertEquals(2, PhpHelper::getNormalizedDayOfWeek(2));
        $this->assertEquals(3, PhpHelper::getNormalizedDayOfWeek(3));
        $this->assertEquals(4, PhpHelper::getNormalizedDayOfWeek(4));
        $this->assertEquals(5, PhpHelper::getNormalizedDayOfWeek(5));
        $this->assertEquals(6, PhpHelper::getNormalizedDayOfWeek(6));
    }

    public function testGetResponseHeader()
    {
        $this->assertEquals(
            'text/html; charset=utf-8',
            PhpHelper::getResponseHeader('content-type', ['content-type:text/html; charset=utf-8'])
        );
        $this->assertEquals(
            'text/html; charset=utf-8',
            PhpHelper::getResponseHeader('content-type', ['Content-Type:text/html; charset=utf-8'])
        );
        $this->assertEquals(
            'text/html; charset=utf-8',
            PhpHelper::getResponseHeader('content-type', ['content-type: text/html; charset=utf-8 '])
        );
    }

    public function testGetTimezoneOffset()
    {
        $this->assertEquals(10800, PhpHelper::getTimezoneOffset('Europe/Minsk'));
    }

    public function testInsertAfterKey()
    {
        $this->assertEquals(
            [
                'one' => 'first',
                'two' => 'second',
                'three' => 'third',
            ],
            PhpHelper::insertAfterKey([
                'one' => 'first',
                'three' => 'third',
            ], 'one', 'two', 'second')
        );
    }

    public function testIsAjax()
    {
        $this->assertFalse(PhpHelper::isAjax());

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->assertTrue(PhpHelper::isAjax());
    }

    public function testMbUcfirst()
    {
        $this->assertEquals(123, PhpHelper::mbUcfirst('123'));
        $this->assertEquals('Hello', PhpHelper::mbUcfirst('hello'));
        $this->assertEquals('Hello', PhpHelper::mbUcfirst('Hello'));
        $this->assertEquals('Привет', PhpHelper::mbUcfirst('привет'));
        $this->assertEquals('Привет', PhpHelper::mbUcfirst('Привет'));
    }

    public function testParseCookies()
    {
        $parsedCookies = PhpHelper::parseCookies(' c=3; b=2; a=1 ');
        $i = 0;
        $cookies = [['a', '1'], ['b', '2'], ['c', '3']];
        foreach ($parsedCookies as $key => $value) {
            $this->assertEquals($cookies[$i][0], $key);
            $this->assertEquals($cookies[$i][1], $value);
            $i++;
        }
    }

    public function testPrettyVarExportSoft()
    {
        $this->assertEquals(<<<'NOWDOC'
array (
  'a' => 1,
  'b' => array (
    'c' => 3,
  ),
)
NOWDOC
            , PhpHelper::prettyVarExportSoft([
                'a' => 1,
                'b' => [
                    'c' => 3,
                ],
            ]));
    }

    public function testPrettyVarExportHard()
    {
        $this->assertEquals(<<<'NOWDOC'
[
  'a' => 1,
  'b' => [
    'c' => 3,
  ],
]
NOWDOC
            , PhpHelper::prettyVarExportHard([
                'a' => 1,
                'b' => [
                    'c' => 3,
                ],
            ]));
    }

    public function testPutArrayToCsvFile()
    {
        $this->assertFileDoesNotExist(__DIR__ . '/_output/tmp.csv');
        $this->assertTrue(
            PhpHelper::putArrayToCsvFile(
                __DIR__ . '/_output/tmp.csv',
                [
                    ['John', 'Doe'],
                    ['Richard', 'Roe'],
                ]
            )
        );
        $this->assertFileExists(__DIR__ . '/_output/tmp.csv');
        unlink(__DIR__ . '/_output/tmp.csv');
        $this->assertFileDoesNotExist(__DIR__ . '/_output/tmp.csv');
    }

    public function testQuickSort()
    {
        $this->assertEquals(
            [1, 2, 2, 5, 7, 8, 9, 21, 23, 24, 43, 92, 99, 114],
            PhpHelper::quickSort([43, 21, 2, 1, 9, 24, 2, 99, 23, 8, 7, 114, 92, 5])
        );
    }

    public function testRemoveDirectoryRecursively()
    {
        $pathToTmpDir = __DIR__ . '/tmp';
        mkdir($pathToTmpDir);

        $paths = [
            '...',

            '.tmp.txt',
            '..tmp.txt',
            '...tmp.txt',

            'tmp.txt',
            'tmp..txt',
            'tmp...txt',

            'tmp.txt.',
            'tmp.txt..',
            'tmp.txt...',
        ];

        foreach ($paths as $path) {
            file_put_contents($pathToTmpDir . '/' . $path, '');
        }

        $pathToTmpDirToWhichLinkIsGiven = __DIR__ . '/tmp-dir-to-which-link-is-given';
        mkdir($pathToTmpDirToWhichLinkIsGiven);
        symlink($pathToTmpDirToWhichLinkIsGiven, $pathToTmpDir . '/link');

        $nestedDir = $pathToTmpDir . '/nested-dir';
        mkdir($nestedDir);

        foreach ($paths as $path) {
            file_put_contents($nestedDir . '/' . $path, '');
        }

        $this->assertTrue(PhpHelper::removeDirectoryRecursively($pathToTmpDir));
        $this->assertTrue(file_exists($pathToTmpDirToWhichLinkIsGiven));
        $this->assertTrue(PhpHelper::removeDirectoryRecursively($pathToTmpDirToWhichLinkIsGiven));
    }

    public function testRemoveDuplicatesFromMultiDimensionalArray()
    {
        $this->assertSame(
            [
                ['a' => 1, 'b' => 2],
                ['a' => 3, 'b' => 4],
            ],
            PhpHelper::removeDuplicatesFromMultiDimensionalArray([
                ['a' => 1, 'b' => 2],
                ['a' => 3, 'b' => 4],
                ['a' => 1, 'b' => 2],
            ])
        );
    }

    public function testSecondsToTime()
    {
        $this->assertEquals(['days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 0], PhpHelper::secondsToTime(0));
        $this->assertEquals(['days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 1], PhpHelper::secondsToTime(1));
        $this->assertEquals(['days' => 0, 'hours' => 0, 'minutes' => 1, 'seconds' => 1], PhpHelper::secondsToTime(61));
        $this->assertEquals(['days' => 0, 'hours' => 1, 'minutes' => 1, 'seconds' => 1], PhpHelper::secondsToTime(3661));
        $this->assertEquals(['days' => 1, 'hours' => 1, 'minutes' => 1, 'seconds' => 1], PhpHelper::secondsToTime(90061));
    }

    public function testSortByDate()
    {
        $this->assertEquals(
            [
                [
                    'title' => 'Title 1',
                    'date'  => '1969-12-31',
                ],
                [
                    'title' => 'Title 2',
                    'date'  => '1970-01-01',
                ],
                [
                    'title' => 'Title 3',
                    'date'  => '1970-01-02',
                ],
            ],
            PhpHelper::sortByDate([
                [
                    'title' => 'Title 2',
                    'date'  => '1970-01-01',
                ],
                [
                    'title' => 'Title 1',
                    'date'  => '1969-12-31',
                ],
                [
                    'title' => 'Title 3',
                    'date'  => '1970-01-02',
                ],
            ], 'date')
        );
        $this->assertEquals(
            [
                [
                    'title' => 'Title 3',
                    'date'  => '1970-01-02',
                ],
                [
                    'title' => 'Title 2',
                    'date'  => '1970-01-01',
                ],
                [
                    'title' => 'Title 1',
                    'date'  => '1969-12-31',
                ],
            ],
            PhpHelper::sortByDate([
                [
                    'title' => 'Title 2',
                    'date'  => '1970-01-01',
                ],
                [
                    'title' => 'Title 1',
                    'date'  => '1969-12-31',
                ],
                [
                    'title' => 'Title 3',
                    'date'  => '1970-01-02',
                ],
            ], 'date', false)
        );
    }

    public function testStrRepeatWithSeparator()
    {
        $this->assertTrue(PhpHelper::strRepeatWithSeparator('', 0) === '');
        $this->assertTrue(PhpHelper::strRepeatWithSeparator('', 1) === '');
        $this->assertTrue(PhpHelper::strRepeatWithSeparator('', 2) === '');
        $this->assertTrue(PhpHelper::strRepeatWithSeparator('a', 0) === '');
        $this->assertTrue(PhpHelper::strRepeatWithSeparator('a', 1) === 'a');
        $this->assertTrue(PhpHelper::strRepeatWithSeparator('a', 2) === 'aa');
        $this->assertTrue(PhpHelper::strRepeatWithSeparator('a', 0) === '');
        $this->assertTrue(PhpHelper::strRepeatWithSeparator('a', 1) === 'a');
        $this->assertTrue(PhpHelper::strRepeatWithSeparator('a', 2) === 'aa');
        $this->assertTrue(PhpHelper::strRepeatWithSeparator('a', 0, '|') === '');
        $this->assertTrue(PhpHelper::strRepeatWithSeparator('a', 1, '|') === 'a');
        $this->assertTrue(PhpHelper::strRepeatWithSeparator('a', 2, '|') === 'a|a');
    }

    public function testTabDelimitedStringToArray()
    {
        $this->assertEquals(
            [['a', 'b', 'c'], ['d', 'f', 'g']],
            PhpHelper::tabDelimitedStringToArray(
                'a	b	c
d	f	g'
            )
        );
    }

    public function testUnzip()
    {
        $pathToArchive = __DIR__ . '/_data/test.zip';
        $extractTo = __DIR__ . '/_output/test';
        $this->assertFalse(PhpHelper::unzip($pathToArchive, ''));
        $this->assertTrue(PhpHelper::unzip($pathToArchive, $extractTo));
        $this->assertTrue(file_exists($extractTo . '/test.txt'));
        $this->assertTrue(PhpHelper::removeDirectoryRecursively($extractTo));
    }
}
