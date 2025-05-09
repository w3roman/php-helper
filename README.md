# [php-helper](https://packagist.org/packages/w3lifer/php-helper)

- [Installation](#installation)
- [Usage](#usage)
  - [Methods](#methods)
  - [Constants](#constants)
- [Tests](#tests)

## Installation

``` sh
composer req w3lifer/php-helper
```

## Usage

### Methods

``` php
addChildrenToSimpleXMLElement(SimpleXMLElement $simpleXMLElement, array $children): void;

addPrefixToArrayKeys(array $array, string $prefix, bool $recursively = true): array;

addPostfixToArrayKeys(array $array, string $postfix, bool $recursively = true): array;

addZeroPrefix(string $value, int $order = 1): string;

arrayToXml(array $data, SimpleXMLElement &$xmlData = null): string;

auth(array $credentials): bool;

clearAllCookies(): bool;

createRss(array $channelInfo, array $items): string;

createSitemap(array $items, bool $addUrlsetTag = true): string;

createSitemapIndex(array $items, bool $addSitemapindexTag = true): string;

createSqlValuesString(array $values, string $valueWrapper = '"'): string;

csvStringToArray(string $csvString, bool $removeFirstLine = false): array;

filterListOfArraysByKeyValuePairs(array $inputArray, array $searchParams): array;

getBase64Image(string $absolutePathToImage): string;

getClassNameFromObject(object $object): string;

getClassNameFromString(string $className): string;

getDatesBetweenDates(string $startDate, string $endDate, string $format = 'Y-m-d'): array;

getFilesInDirectory(string $pathToDirectory, bool $recursively = false, array $fileExtensions = [], &$result = []): array;

getFullUrl(): string;

getNormalizedDayOfWeek(int $dayOfWeek): int;

getRandomWeightedElement(array $weightedValues);

getResponseHeader(string $header, array $response): string;

getTimezoneOffset(string $timeZone): int;

insertAfterKey(array $array, string $afterKey, string $key, string $new);

isAjax(): bool;

mbUcfirst(string $string): string;

parseCookies(string $cookiesString): array;

prettyVarExportSoft(array $array): string;

prettyVarExportHard(array $array): string;

putArrayToCsvFile(string $filename, array $array): bool;

quickSort(array $array): array;

rangeGenerator(int $start, int $limit, int $step = 1): Generator;

removeCookie(string $name): bool;

removeDirectoryRecursively(string $pathToDirectory): bool;

removeDuplicatesFromMultiDimensionalArray(array $array): array;

secondsToTime(int $seconds): array;

sortByDate(array $array, string $key, bool $asc = true): array;

strRepeatWithSeparator(string $input, int $multiplier, string $separator = ''): string;

tabDelimitedStringToArray(string $string): array;

unzip(string $pathToArchive, string $extractTo);
```

### Constants

- `XML_DECLARATION`
- `OPEN_URLSET_TAG`
- `OPEN_SITEMAPINDEX_TAG`

## Tests

``` sh
make tests # make t
```
