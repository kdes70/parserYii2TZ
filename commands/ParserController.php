<?php

namespace app\commands;

use app\components\parser\DataProviderInterface;
use app\components\parser\sources\ExcelFileProvider;
use app\components\parser\sources\GoogleSheetsDataProvider;
use app\components\parser\sources\CsvFileDataProvider;
use yii\console\Controller;
use yii\console\ExitCode;
use app\components\parser\BudgetParser;

/**
 * Контроллер для запуска парсера бюджета.
 */
class ParserController extends Controller
{
    /**
     * @param null $filePath
     * @return int
     */
    public function actionIndex($filePath = null): int
    {
        $dataProvider = $this->getDataProvider($filePath);
        $parser = new BudgetParser($dataProvider);
        $result = $parser->parseAndSave();

        if (!empty($result)) {
            $this->stdout("Парсинг завершен успешно!\n");
            $this->stdout("Всего строк просмотрено: {$result['reads']}\n");
            $this->stdout("Новых записей: {$result['inserted']}\n");
            $this->stdout("Обновлено записей: {$result['updated']}\n");
            return ExitCode::OK;
        }

        return ExitCode::UNSPECIFIED_ERROR;
    }

    /**
     * Если указан путь до файла, то парсем его, иначе парсем Google Sheets API
     */
    private function getDataProvider(?string $filePath = null): DataProviderInterface
    {
        if (!empty($filePath)) {
            return str_ends_with($filePath, '.xlsx')
                ? new ExcelFileProvider($filePath)
                : new CsvFileDataProvider($filePath);
        }

        // TODO: вынести в env spreadsheetId и range
        // По умолчанию используем Google Sheets
        return new GoogleSheetsDataProvider('10En6qNTpYNeY_YFTWJ_3txXzvmOA7UxSCrKfKCFfaRw', 'Totals!A:M');
    }
}
