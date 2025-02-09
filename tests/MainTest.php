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

    public function testCreateSqlValuesString()
    {
        $this->assertEquals('("one", "two", "three")', PhpHelper::createSqlValuesString(['one', 'two', 'three',]));
        $this->assertEquals("('first', 'second', 'third')", PhpHelper::createSqlValuesString(['first', 'second', 'third'], "'"));
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

        file_put_contents($pathToTmpDir . '/tmp.txt', 'Lorem ipsum ...');
        file_put_contents($pathToTmpDir . '/.tmp.txt', 'Lorem ipsum ...');
        file_put_contents($pathToTmpDir . '/..tmp.txt', 'Lorem ipsum ...');
        file_put_contents($pathToTmpDir . '/...tmp.txt', 'Lorem ipsum ...');

        file_put_contents($pathToTmpDir . '/tmp.txt.', 'Lorem ipsum ...');
        file_put_contents($pathToTmpDir . '/tmp.txt..', 'Lorem ipsum ...');
        file_put_contents($pathToTmpDir . '/tmp.txt...', 'Lorem ipsum ...');

        file_put_contents($pathToTmpDir . '/.tmp.txt.', 'Lorem ipsum ...');
        file_put_contents($pathToTmpDir . '/.tmp.txt..', 'Lorem ipsum ...');
        file_put_contents($pathToTmpDir . '/.tmp.txt...', 'Lorem ipsum ...');

        file_put_contents($pathToTmpDir . '/..tmp.txt.', 'Lorem ipsum ...');
        file_put_contents($pathToTmpDir . '/..tmp.txt..', 'Lorem ipsum ...');
        file_put_contents($pathToTmpDir . '/..tmp.txt...', 'Lorem ipsum ...');

        file_put_contents($pathToTmpDir . '/...tmp.txt', 'Lorem ipsum ...');
        file_put_contents($pathToTmpDir . '/...tmp.txt.', 'Lorem ipsum ...');
        file_put_contents($pathToTmpDir . '/...tmp.txt..', 'Lorem ipsum ...');
        file_put_contents($pathToTmpDir . '/...tmp.txt...', 'Lorem ipsum ...');

        mkdir($pathToTmpDir . '/tmp');
        file_put_contents($pathToTmpDir . '/tmp/tmp.txt', 'Lorem ipsum ...');

        $this->assertTrue(PhpHelper::removeDirectoryRecursively($pathToTmpDir));
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
        $pathToArchive = __DIR__ . '/_data/tmp.zip';
        $extractTo = __DIR__ . '/_output/tmp';
        $this->assertFalse(PhpHelper::unzip($pathToArchive, ''));
        $this->assertTrue(PhpHelper::unzip($pathToArchive, $extractTo));
        $this->assertTrue(PhpHelper::removeDirectoryRecursively($extractTo));
    }
}
