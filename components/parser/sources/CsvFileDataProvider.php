<?php
namespace app\components\parser\sources;

use app\components\parser\DataProviderInterface;
use RuntimeException;

class CsvFileDataProvider implements DataProviderInterface
{
    private string $filePath;

    public function __construct(string $filePath)
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException("Файл {$filePath} не найден.");
        }
        $this->filePath = $filePath;
    }

    /**
     * Читает CSV-файл построчно и возвращает данные.
     *
     * @return iterable
     */
    public function getData(): iterable
    {
        if (($handle = fopen($this->filePath, 'rb')) === false) {
            throw new RuntimeException("Не удалось открыть файл {$this->filePath}");
        }
        while (($row = fgetcsv($handle)) !== false) {
            yield $row;
        }
        fclose($handle);
    }
}
