<?php

declare(strict_types=1);

namespace w3lifer\PhpHelper;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Generator;
use JetBrains\PhpStorm\ArrayShape;
use LogicException;
use SimpleXMLElement;
use ZipArchive;

class PhpHelper
{
    public const XML_DECLARATION = '<?xml version="1.0" encoding="UTF-8"?>';
    public const OPEN_URLSET_TAG = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    public const OPEN_SITEMAPINDEX_TAG = '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    public static function addChildrenToSimpleXMLElement(SimpleXMLElement $simpleXMLElement, array $children): void
    {
        foreach ($children as $name => $value) {
            if (is_array($value)) {
                self::addChildrenToSimpleXMLElement($simpleXMLElement, $value);
            } else {
                $simpleXMLElement->addChild($name, $value);
            }
        }
    }

    /**
     * Returns received array where keys are prefixed with specified prefix
     * @see https://stackoverflow.com/a/2608166/4223982
     */
    public static function addPrefixToArrayKeys(array $array, string $prefix, bool $recursively = true): array
    {
        $newArray = [];
        foreach ($array as $key => $value) {
            if ($recursively && is_array($value)) {
                $newArray[$prefix . $key] = self::addPrefixToArrayKeys($value, $prefix);
                continue;
            }
            $newArray[$prefix . $key] = $value;
        }
        return $newArray;
    }

    /**
     * Returns received array where keys are postfixed with specified postfix
     * @see https://stackoverflow.com/a/2608166/4223982
     */
    public static function addPostfixToArrayKeys(array $array, string $postfix, bool $recursively = true): array
    {
        $newArray = [];
        foreach ($array as $key => $value) {
            if ($recursively && is_array($value)) {
                $newArray[$key . $postfix] = self::addPostfixToArrayKeys($value, $postfix);
                continue;
            }
            $newArray[$key . $postfix] = $value;
        }
        return $newArray;
    }

    /**
     * Returns the passed value with a zero prefix, if the value is less than 1e&lt;order&gt;
     */
    public static function addZeroPrefix(string $value, int $order = 1): string
    {
        $times = 0;
        $orderValue = pow(10, $order);
        if ($value < $orderValue) {
            $times = strlen((string) $orderValue) - strlen($value);
        }
        return str_repeat('0', $times) . $value;
    }

    /**
     * @see https://stackoverflow.com/a/5965940/4223982
     */
    public static function arrayToXml(array $data, ?SimpleXMLElement &$xmlData = null): string
    {
        if ($xmlData === null) {
            $xmlData = new SimpleXMLElement('<?xml version="1.0"?><data value=""></data>');
        }
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item' . $key; // Dealing with <0/>..<n/> issues
                }
                $subNode = $xmlData->addChild($key);
                self::arrayToXml($value, $subNode);
            } else {
                $xmlData->addChild($key, htmlspecialchars((string) $value));
            }
        }
        return $xmlData->asXML();
    }

    /**
     * Basic access authentication
     * @param array $credentials An array whose keys are logins and values are passwords
     */
    public static function auth(array $credentials): bool
    {
        $validated =
            isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) &&
            array_key_exists($_SERVER['PHP_AUTH_USER'], $credentials) &&
            $credentials[$_SERVER['PHP_AUTH_USER']] === $_SERVER['PHP_AUTH_PW'];
        if (!$validated) {
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Basic');
            return false;
        }
        return true;
    }

    /**
     * Clears all cookies
     * @see https://stackoverflow.com/a/2310591/4223982
     */
    public static function clearAllCookies(): bool
    {
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach ($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name = trim($parts[0]);
                setcookie($name, '', time() - 1000);
                setcookie($name, '', time() - 1000, '/');
            }
            return true;
        }
        return false;
    }

    /**
     * Usage
```
PhpHelper::createRss([
    'title' => 'StackHub',
    'link' => 'https://stackhub.net',
    'description' => '▷ Concise yet comprehensive technical manuals and online tools',
    'atomLink' => 'https://stackhub.net/rss.xml',
], [
    [
        'title' => 'Manuals',
        'link' => 'https://stackhub.net/manuals',
        'description' => '▷ Concise yet comprehensive technical manuals',
        'guid' => 'https://stackhub.net/manuals',
    ],
    [
        'title' => 'Tools',
        'link' => 'https://stackhub.net/tools',
        'description' => '▷ Online tools',
        'guid' => 'https://stackhub.net/tools',
    ],
])
```
     * @see https://rssboard.org/rss-specification RSS Specification
     * @param array $channelInfo
     * @see https://rssboard.org/rss-specification#requiredChannelElements Required channel elements
     * @see https://rssboard.org/rss-specification#optionalChannelElements Optional channel elements
     * @param array $items
     * @see https://rssboard.org/rss-specification#hrelementsOfLtitemgt Elements of &lt;item&gt;
     * @return string
     * @throws Exception
     */
    public static function createRss(array $channelInfo, array $items): string
    {
        $rss = new SimpleXMLElement(self::XML_DECLARATION . PHP_EOL . '<rss version="2.0"/>');
        $channel = $rss->addChild('channel');
        if (isset($channelInfo['atomLink'])) {
            $rss->addAttribute('xmlns:xmlns:atom', 'http://www.w3.org/2005/Atom');
            $channel->addChild(
                'atom:atom:link href="' . $channelInfo['atomLink'] . '" rel="self" type="application/rss+xml"'
            );
            unset($channelInfo['atomLink']);
        }
        self::addChildrenToSimpleXMLElement($channel, $channelInfo);
        foreach ($items as $item) {
            $itemElement = $channel->addChild('item');
            self::addChildrenToSimpleXMLElement($itemElement, $item);
        }
        return $rss->asXML();
    }

    /**
     * https://sitemaps.org/protocol.html
     */
    public static function createSitemap(array $items, bool $addUrlsetTag = true): string
    {
        $sitemap = '';
        foreach ($items as $item) {
            $sitemap .=
                '<url>' .
                    '<loc>' . $item['loc'] . '</loc>' .
                    (!empty($item['lastmod']) ? '<lastmod>' . $item['lastmod'] . '</lastmod>' : '') .
                    (!empty($item['changefreq']) ? '<changefreq>' . $item['changefreq'] . '</changefreq>' : '') .
                    (!empty($item['priority']) ? '<priority>' . $item['priority'] . '</priority>' : '') .
                '</url>'
            ;
        }
        if (!$addUrlsetTag) {
            return $sitemap;
        }
        return self::XML_DECLARATION . PHP_EOL . self::OPEN_URLSET_TAG . $sitemap . '</urlset>';
    }

    /**
     * https://sitemaps.org/protocol.html#index
     */
    public static function createSitemapIndex(array $items, bool $addSitemapindexTag = true): string
    {
        $sitemapIndex = '';
        foreach ($items as $item) {
            $sitemapIndex .=
                '<sitemap>' .
                    '<loc>' . $item['loc'] . '</loc>' .
                    (!empty($item['lastmod']) ? '<lastmod>' . $item['lastmod'] . '</lastmod>' : '') .
                '</sitemap>'
            ;
        }
        if (!$addSitemapindexTag) {
            return $sitemapIndex;
        }
        return self::XML_DECLARATION . PHP_EOL . self::OPEN_SITEMAPINDEX_TAG . $sitemapIndex . '</sitemapindex>';
    }

    public static function createSqlValuesString(array $values, string $valueWrapper = '"'): string
    {
        $sqlValues = '(';
        foreach ($values as $value) {
            $sqlValues .= $valueWrapper . $value . $valueWrapper . ', ';
        }
        $sqlValues = rtrim($sqlValues, ', ');
        $sqlValues .= ')';
        return $sqlValues;
    }

    /**
     * Converts CSV string to array
     * Example of CSV string:
     * ``` csv
     * First name,Last name
     * John,Doe
     * "Richard", "Roe"
     * ```
     * Result with `$removeFirstLine` as `false`:
     * ``` php
     * [
     *   0 => [
     *     0 => 'First name',
     *     1 => 'Last name',
     *   ],
     *   1 => [
     *     0 => 'John',
     *     1 => 'Doe',
     *   ],
     *   2 => [
     *     0 => 'Richard',
     *     1 => 'Roe',
     *   ],
     * ]
     * ```
     * Result with `$removeFirstLine` as `true`:
     * ``` php
     * [
     *   0 => [
     *     0 => 'John',
     *     1 => 'Doe',
     *   ],
     *   1 => [
     *     0 => 'Richard',
     *     1 => 'Roe',
     *   ],
     * ]
     * ```
     */
    public static function csvStringToArray(string $csvString, bool $removeFirstLine = false): array
    {
        $trimmedCsvString = trim($csvString);
        $explodedCsvString = explode(PHP_EOL, $trimmedCsvString);
        if ($removeFirstLine) {
            unset($explodedCsvString[0]);
        }
        $result = [];
        foreach ($explodedCsvString as $csvLine) {
            $csvLine = trim($csvLine);
            // Regarding the "escape" argument, see the warning here: https://php.net/str-getcsv
            $result[] = str_getcsv($csvLine, escape: '');
        }
        return $result;
    }

    /**
     * Filter input array by passed params
     * Example of input array:
     * ``` php
     * [
     *     ['firstname' => 'John', 'lastname' => 'Doe'],
     *     ['firstname' => 'Вася', 'lastname' => 'Пупкин'],
     * ]
     * ```
     * If passed params are `['firstname' => 'Jo']`, then result will be the following:
     * ``` php
     * [
     *     ['firstname' => 'John', 'lastname' => 'Doe'],
     * ]
     * ```
     * If passed params are `['firstname' => 'jo']`, then result will be an empty array (case-sensitive search)
     */
    public static function filterListOfArraysByKeyValuePairs(array $inputArray, array $searchParams): array
    {
        foreach ($searchParams as $searchParamName => $searchParamValue) {
            if (!isset($inputArray[0][$searchParamName]) || $searchParamValue === '') {
                continue;
            }
            foreach ($inputArray as $rowIndex => $row) {
                $match = mb_strpos($row[$searchParamName], $searchParamValue);
                if ($match === false) {
                    unset($inputArray[$rowIndex]);
                }
            }
        }
        return $inputArray;
    }

    /**
     * 1234567890
     * 123456789
     *
     * ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * ABCDEFGH JKLMN PQRSTUVWXYZ
     *
     * abcdefghijklmnopqrstuvwxyz
     * abcdefghijk mnopqrstuvwxyz
     *
     * @noinspection SpellCheckingInspection
     */
    public static function generateBase58Id(int $length = 8): string
    {
        $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

        $id = '';
        for ($i = 0; $i < $length; $i++) {
            $id .= $alphabet[random_int(0, 57)];
        }

        return $id;
    }

    public static function getBase64Image(string $absolutePathToImage): string
    {
        $mimeType = mime_content_type($absolutePathToImage);
        $base64Image = base64_encode(file_get_contents($absolutePathToImage));
        return 'data:' . $mimeType . ';base64,' . $base64Image;
    }

    /**
     * @see https://stackoverflow.com/q/19901850/4223982
     * @see https://stackoverflow.com/q/19901850/4223982#comment97429662_27457689
     */
    public static function getClassNameFromObject(object $object): string
    {
        return substr(strrchr('\\' . get_class($object), '\\'), 1);
    }

    /**
     * @see https://stackoverflow.com/q/19901850/4223982
     * @see https://stackoverflow.com/q/19901850/4223982#comment97429662_27457689
     */
    public static function getClassNameFromString(string $className): string
    {
        return substr(strrchr('\\' . $className, '\\'), 1);
    }

    /**
     * Returns an array of dates between two dates
     * For example, if input data will be '1969-12-31' and '1970-01-02' (or '12/31/1969' and '01/02/1970'), then result
     * will be the following:
     * ``` php
     * [
     *   0 => '1969-12-31',
     *   1 => '1970-01-01',
     *   2 => '1970-01-02',
     * ]
     * ```
     */
    public static function getDatesBetweenDates(string $startDate, string $endDate, string $format = 'Y-m-d'): array
    {
        $dates[] = date($format, strtotime($startDate));
        $dateDiff = (new DateTime($startDate))->diff(new DateTime($endDate));
        for ($i = 1; $i <= $dateDiff->days; $i++) {
            $dates[] = date($format, strtotime($dates[$i - 1] . '+1 day'));
        }
        return $dates;
    }

    /**
     * Returns an array of file names in the specified directory
     * Note that directories will not be listed in the returned array
     * @see https://stackoverflow.com/a/24784144/4223982
     */
    public static function getFilesInDirectory(
        string $pathToDirectory,
        bool $recursively = false,
        array $fileExtensions = [],
        &$result = []
    ): array {
        $fileNames = scandir($pathToDirectory);
        foreach ($fileNames as $fileName) {
            $path = realpath($pathToDirectory . DIRECTORY_SEPARATOR . $fileName);
            if (!is_dir($path)) {
                if ($fileExtensions) {
                    foreach ($fileExtensions as $fileExtension) {
                        if (preg_match('=\.' . $fileExtension .'$=i', $path)) {
                            $result[] = $path;
                            continue 2;
                        }
                    }
                } else {
                    $result[] = $path;
                }
            } else if ($recursively && $fileName !== '.' && $fileName !== '..') {
                self::getFilesInDirectory($path, $recursively, $fileExtensions, $result);
            }
        }
        return $result;
    }

    /** @noinspection PhpUnused */
    public static function getFullUrl(): string
    {
        return
            $_SERVER['REQUEST_SCHEME'] . '://' .
            $_SERVER['HTTP_HOST'] . ':' .
            $_SERVER['SERVER_PORT'] .
            $_SERVER['REQUEST_URI'];
    }

    /**
     * @return int 0-6 (Monday-Sunday)
     */
    public static function getNormalizedDayOfWeek(int $dayOfWeek): int
    {
        return $dayOfWeek === 7 ? 0 : $dayOfWeek;
    }

    /**
     * Utility function for getting random values with weighting
     * Pass in an associative array, such as `['a' => 5, 'b' => 10, 'c' => 15]`
     * An array like this means that "a" has a 5% chance of being selected, "b" 45%, and "c" 50%
     * The return value is the array key, "a", "b", or "c" in this case
     * Note that the values assigned do not have to be percentages
     * The values are simply relative to each other
     * If one value weight was 2, and the other weight of 1, the value with the weight of 2 has about a 66% chance of
     * being selected
     * Also note that weights should be integers
     * @see https://stackoverflow.com/a/11872928/4223982
     * @noinspection PhpUnused
     */
    public static function getRandomWeightedElement(array $weightedValues): bool|int
    {
        $rand = mt_rand(1, (int) array_sum($weightedValues));
        foreach ($weightedValues as $key => $value) {
            $rand -= $value;
            if ($rand <= 0) {
                return $key;
            }
        }
        return false;
    }

    /**
     * @see https://stackoverflow.com/a/12434004/4223982
     */
    public static function getResponseHeader(string $header, array $response): string
    {
        foreach ($response as $headerLine) {
            if (stripos($headerLine, $header . ':') === 0) {
                return trim(explode(':', $headerLine, 2)[1]);
            }
        }
        return '';
    }

    /**
     * Returns timezone offset from the current time zone
     * @return int Timezone offset in seconds
     * @throws Exception
     */
    public static function getTimezoneOffset(string $timeZone): int
    {
        return (new DateTime('now', new DateTimeZone($timeZone)))->getOffset();
    }

    /**
     * @see https://stackoverflow.com/a/21336407/4223982
     */
    public static function insertAfterKey(array $array, string $afterKey, string $key, string $new): array
    {
        $pos = (int) array_search($afterKey, array_keys($array)) + 1;
        return array_merge(array_slice($array, 0, $pos), [$key => $new], array_slice($array, $pos));
    }

    /**
     * Checks if request is AJAX
     */
    public static function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * Makes a string's first character uppercase
     * @see https://php.net/ucfirst#57298
     */
    public static function mbUcfirst(string $string): string
    {
        $firstChar = mb_strtoupper(mb_substr($string, 0, 1));
        return $firstChar . mb_substr($string, 1);
    }

    /**
     * Returns an array of cookies sorted alphabetically by name: ['name' => 'value', ...]
     */
    public static function parseCookies(string $cookiesString): array
    {
        $cookies = [];
        $cookiesStringExploded = explode(';', $cookiesString);
        foreach ($cookiesStringExploded as $cookieString) {
            $cookieStringExploded = explode('=', $cookieString);
            $cookies[$cookieStringExploded[0]] = $cookieStringExploded[1];
        }
        ksort($cookies);
        return $cookies;
    }

    /**
     * Returns a parsable string representation of a received array
     * Example of the incoming array:
     * ```
     * [
     *   'a' => 1,
     *   'b' => [
     *     'c' => 3,
     *   ],
     * ]
     * ```
     * Example of the returned string:
     * ```
     * array (
     *   'a' => 1,
     *   'b' => array (
     *     'c' => 3,
     *   ),
     * )
     * ```
     */
    public static function prettyVarExportSoft(array $array): string
    {
        $arrayAsString = var_export($array, true);
        return preg_replace('= \=> \R {2,}array \(=', ' => array (', $arrayAsString);
    }

    /**
     * Returns a parsable string representation of a received array
     * Example of the incoming array:
     * ```
     * [
     *   'a' => 1,
     *   'b' => [
     *     'c' => 3,
     *   ],
     * ]
     * ```
     * Example of the returned string:
     * ```
     * [
     *   'a' => 1,
     *   'b' => [
     *     'c' => 3,
     *   ],
     * ]
     * ```
     */
    public static function prettyVarExportHard(array $array): string
    {
        $arrayAsString = var_export($array, true);
        $arrayAsString = preg_replace('=^array \(=', '[', $arrayAsString);
        $arrayAsString = preg_replace('=\)$=', ']', $arrayAsString);
        $arrayAsString = preg_replace('= \=> \R {2,}array \(=', ' => [', $arrayAsString);
        return preg_replace('=(\R {2,})\),=', '$1],', $arrayAsString);
    }

    public static function putArrayToCsvFile(string $filename, array $array): bool
    {
        $filePointer = fopen($filename, 'w');
        foreach ($array as $row) {
            // Regarding the "escape" argument, see the warning here: https://php.net/fputcsv
            fputcsv($filePointer, $row, escape: '');
        }
        return fclose($filePointer);
    }

    public static function quickSort(array $array): array
    {
        $length = count($array);
        if ($length <= 1) {
            return $array;
        } else {
            $pivot = $array[0];
            $left = $right = [];
            for ($i = 1; $i < $length; $i++) {
                if ($array[$i] < $pivot) {
                    $left[] = $array[$i];
                } else {
                    $right[] = $array[$i];
                }
            }
            return array_merge(self::quickSort($left), [$pivot], self::quickSort($right));
        }
    }

    /**
     * @see https://php.net/language.generators.overview#example-317
     * @noinspection PhpUnused
     */
    public static function rangeGenerator(int $start, int $limit, int $step = 1): Generator
    {
        if ($start <= $limit) {
            if ($step <= 0) {
                throw new LogicException('Step must be positive');
            }
            for ($i = $start; $i <= $limit; $i += $step) {
                yield $i;
            }
        } else {
            if ($step >= 0) {
                throw new LogicException('Step must be negative');
            }
            for ($i = $start; $i >= $limit; $i += $step) {
                yield $i;
            }
        }
    }

    /**
     * @see https://stackoverflow.com/a/19972329/4223982
     * @noinspection PhpUnused
     */
    public static function removeCookie(string $name): bool
    {
        if (isset($_COOKIE[$name])) {
            unset($_COOKIE[$name]);
            setcookie($name, '', -1, '/');
            return true;
        }
        return false;
    }

    /**
     * Removes directory recursively
     * The `glob()` function is not used because `GLOB_BRACE` is not supported everywhere
     * @see https://stackoverflow.com/a/58287388/4223982
     */
    public static function removeDirectoryRecursively(string $pathToDirectory): bool
    {
        if (!file_exists($pathToDirectory)) {
            return false;
        }
        $pathToDirectory = rtrim($pathToDirectory, '/');
        $pathToDirectory .= '/';
        $allFiles = array_filter(scandir($pathToDirectory), fn ($item) => !in_array($item, ['.', '..']));
        foreach ($allFiles as $filename) {
            $path = $pathToDirectory . $filename;
            // https://stackoverflow.com/questions/11267086/11267139#comment101937776_11267139
            if (is_link($path) || is_file($path)) {
                if ($path === __FILE__) { // https://stackoverflow.com/a/29007136/4223982
                    throw new Exception('You are trying to remove me itself');
                }
                unlink($path);
            } elseif (is_dir($path)) {
                self::removeDirectoryRecursively($path);
            }
        }
        return rmdir($pathToDirectory);
    }

    /**
     * Removes duplicates from a multidimensional array
     * Example of the incoming array:
     * ```
     * [
     *   ['a' => 1, 'b' => 2],
     *   ['a' => 3, 'b' => 4],
     *   ['a' => 1, 'b' => 2],
     * ]
     * ```
     * Example of the returned array:
     * ```
     * [
     *   ['a' => 1, 'b' => 2],
     *   ['a' => 3, 'b' => 4],
     * ]
     * ```
     * @see https://stackoverflow.com/a/946300/4223982
     */
    public static function removeDuplicatesFromMultiDimensionalArray(array $array): array
    {
        return array_map('unserialize', array_unique(array_map('serialize', $array)));
    }

    /**
     * Example of the returned array:
     * ```
     * [
     *   'days' => 1,
     *   'hours' => 1,
     *   'minutes' => 1,
     *   'seconds' => 1,
     * ]
     * ```
     * @see https://stackoverflow.com/a/19680778/4223982
     */
    #[ArrayShape(['days' => "int", 'hours' => "int", 'minutes' => "int", 'seconds' => "int"])]
    public static function secondsToTime(int $seconds): array
    {
        $dateTimeFrom = new DateTimeImmutable('@0');
        $dateTimeTo = new DateTimeImmutable('@' . $seconds);
        $diff = $dateTimeFrom->diff($dateTimeTo);
        return [
            'days' => (int) $diff->format('%a'),
            'hours' => (int) $diff->format('%h'),
            'minutes' => (int) $diff->format('%i'),
            'seconds' => (int) $diff->format('%s'),
        ];
    }

    /**
     * Sorts array by date
     * For example, if the received array will be as the following:
     * ```
     * [
     *   [
     *     'title' => 'Title 2',
     *     'date'  => '1970-01-01',
     *   ],
     *   [
     *     'title' => 'Title 1',
     *     'date'  => '1969-12-31',
     *   ],
     *   [
     *     'title' => 'Title 3',
     *     'date'  => '1970-01-02',
     *   ],
     * ]
     * ```
     * then the result will be:
     * ```
     * [
     *   [
     *     'title' => 'Title 1',
     *     'date'  => '1969-12-31',
     *   ],
     *   [
     *     'title' => 'Title 2',
     *     'date'  => '1970-01-01',
     *   ],
     *   [
     *     'title' => 'Title 3',
     *     'date'  => '1970-01-02',
     *   ],
     * ]
     * ```
     * @see https://stackoverflow.com/a/6401744/4223982
     */
    public static function sortByDate(array $array, string $key, bool $asc = true): array
    {
        usort($array, function ($a, $b) use ($key) {
            return strtotime($a[$key]) - strtotime($b[$key]);
        });
        if (!$asc) {
            $array = array_reverse($array);
        }
        return $array;
    }

    /**
     * @see https://php.net/str-repeat#88830
     */
    public static function strRepeatWithSeparator(string $input, int $multiplier, string $separator = ''): string
    {
        return $multiplier === 0 ? '' : str_repeat($input . $separator, $multiplier - 1) . $input;
    }

    /**
     * @see https://stackoverflow.com/a/51175400/4223982
     */
    public static function tabDelimitedStringToArray(string $string): array
    {
        $result = [];
        $lines = explode(PHP_EOL, $string);
        $l = 0;
        foreach($lines as $line) {
            $result[$l] = explode("\t", $line);
            $l++;
        }
        return $result;
    }

    /**
     * Extracts ZIP archive to the specified path
     * @return bool|int TRUE on success, FALSE or error number on failure
     * @see https://stackoverflow.com/a/8889126/4223982
     */
    public static function unzip(string $pathToArchive, string $extractTo): bool|int
    {
        $zipArchive = new ZipArchive();
        $result = $zipArchive->open($pathToArchive);
        if ($result === true) {
            $extracted = $zipArchive->extractTo($extractTo);
            $closed = $zipArchive->close();
            return $extracted && $closed;
        }
        return $result;
    }
}
