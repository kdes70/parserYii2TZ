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
    public function actionIndex($filePath = null): int
    {
        $dataProvider = $this->getDataProvider($filePath);
        $parser = new BudgetParser($dataProvider);
        $parser->parseAndSave();

        $this->stdout("Парсинг завершен успешно!\n");
        return ExitCode::OK;
    }

    private function getDataProvider(?string $filePath = null): DataProviderInterface
    {
        if (!empty($filePath)) {
            return str_ends_with($filePath, '.xlsx')
                ? new ExcelFileProvider($filePath)
                : new CsvFileDataProvider($filePath);
        }

        // TODO: вынести в конфигурацию spreadsheetId и range
        return new GoogleSheetsDataProvider('10En6qNTpYNeY_YFTWJ_3txXzvmOA7UxSCrKfKCFfaRw', 'Totals!A:M');
    }
}
