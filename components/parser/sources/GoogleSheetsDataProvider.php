<?php

namespace app\components\parser\sources;

use Google\Client;
use Google\Exception;
use Google\Service\Sheets;
use app\components\parser\DataProviderInterface;
use RuntimeException;

/**
 * Провайдер данных из Google Sheets API.
 */
class GoogleSheetsDataProvider implements DataProviderInterface
{
    private string $spreadsheetId;
    private string $range;

    public function __construct(string $spreadsheetId, string $range)
    {
        $this->spreadsheetId = $spreadsheetId;
        $this->range = $range;

        $this->credentials = null; // __DIR__ . 'credentials.json';
    }

    /**
     * Получает данные из Google Sheets и возвращает их построчно.
     *
     * @return iterable
     * @throws Exception
     */
    public function getData(): iterable
    {
        $client = new Client();
        $client->setApplicationName('Yii2 Budget Parser');
        $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
        // Укажите путь к файлу с учетными данными
        $client->setAuthConfig($this->credentials);

        $service = new Sheets($client);
        $response = $service->spreadsheets_values->get($this->spreadsheetId, $this->range);
        $rows = $response->getValues();
        if (empty($rows)) {
            throw new RuntimeException('Нет данных в Google Sheets.');
        }
        foreach ($rows as $row) {
            yield $row;
        }
    }
}
