<?php

/*
 * if you try to search in ./folderWithCountFiles/
 * just remove children dirs from DIRS_FOR_SCANNING
 * because it will be summed twice.
 */

const DIRS_FOR_SCANNING = [
    './folderWithCountFiles/withCountFilesDirectory/parent1',
    './folderWithCountFiles/withCountFilesDirectory/parent2',
    'goinrijbr',
    '4364334',
    '/',
//    'folderWithCountFiles/',
];

const DELIMITER = ' ';

interface DirScannerInterface {
    public function scan(array $directories): array;
}

class DirScanner implements DirScannerInterface
{
    /**
     * @var array
     */
    private array $scannedDirs = [];

    /**
     * @var string
     */
    private string $resultCode = '';

    /**
     * @param array $directories
     * @return array
     */
    public function scan(array $directories): array
    {
        $filtered = $this->filter($directories);

        foreach ($filtered as $dir)
        {
            exec("find $dir -name count", $this->scannedDirs, $this->resultCode);
        }

        if ($this->resultCode !== '0') {
            exit(Printer::format('The scanning operation was failed. Terminated.'));
        }

        Printer::print('files found in this directories:');
        print_r($this->scannedDirs);
        return $this->scannedDirs;
    }

    /**
     * @param array $dirs
     * @return array
     */
    private function filter(array $dirs): array
    {
        $dirFiltered = [];

        foreach ($dirs as $item) {
            $realPath = realpath($item);

            if (!is_dir($realPath) || $item === '/' || $item === './' || $item === '../') {
                continue;
            }

            $dirFiltered[] = $realPath;
        }

        return array_unique($dirFiltered);
    }
}

class DataParser
{
    /**
     * @param array $paths
     * @return int
     */
    public static function extractDataFromFiles(array $paths): int
    {
        $acc = '';
        foreach ($paths as $path) {
            foreach (self::readFromFile($path) as $item){
                $acc .= $item . DELIMITER;
            }
        }

        $arr =  self::prepareTheCalculationData($acc);

        return Calculator::sum($arr);
    }

    /**
     * @param string $data
     * @return array
     */
    public static function prepareTheCalculationData(string $data): array
    {
        $arrStrings = explode(DELIMITER, $data);
        return array_map(function ($item){
            return (int) $item;
        }, $arrStrings) ?? [0];
    }

    /**
     * @param string $path
     * @return iterable
     */
    private static function readFromFile(string $path): iterable
    {
        if (is_file($path) && is_readable($path)) {
            $resource = fopen($path, 'r');
            if (!is_resource($resource)) {
                echo Printer::format("Could not open file $path");
            }
            while (!feof($resource)) {
                yield trim(fgets($resource));
            }
            fclose($resource);
        }
    }
}

class Calculator
{
    /**
     * @param array $ints
     * @return int
     */
    public static function sum(array $ints): int
    {
        $sum = 0;
        foreach ($ints as $int) {
            $sum += $int;
        }

        return $sum;
    }
}

class Printer {
    /**
     * @param string $message
     * @return void
     */
    public static function print(string $message): void
    {
        echo self::format($message);
    }

    /**
     * @param string $rawString
     * @return string
     */
    public static function format(string $rawString): string
    {
        return sprintf("%s \n", $rawString);
    }
}

class Main {

    /**
     * @var DirScanner
     */
    private DirScannerInterface $dirScanner;

    public function __construct()
    {
        $this->dirScanner = new DirScanner();
    }

    /**
     * @return void
     */
    public function doWork()
    {
        $result = DataParser::extractDataFromFiles($this->dirScanner->scan(DIRS_FOR_SCANNING));

        Printer::print(sprintf('the result sum from all files with name "count" is: %d', $result));
    }
}

$main = new Main();
$main->doWork();

exit(0);