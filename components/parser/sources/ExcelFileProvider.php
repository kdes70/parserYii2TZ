<?php
namespace app\components\parser\sources;

use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;
use app\components\parser\DataProviderInterface;

/**
 * Потоковый загрузчик данных из XLSX-файла.
 */
class ExcelFileProvider implements DataProviderInterface
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
     * Потоковое чтение XLSX-файла, возвращающее строки одну за одной.
     *
     * @return iterable
     */
    public function getData(): iterable
    {
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($this->filePath);
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($sheet->getRowIterator() as $row) {
            $rowData = [];
            foreach ($row->getCellIterator() as $cell) {
                $rowData[] = $cell->getValue();
            }
            yield $rowData;
        }
    }
}
