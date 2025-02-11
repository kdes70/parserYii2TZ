<?php

namespace app\components\parser;

/**
 * Интерфейс для получения данных источника.
 */
interface DataProviderInterface
{
    /**
     * Метод возвращает итератор (например, генератор), позволяющий обходить строки по одной.
     *
     * @return iterable
     */
    public function getData(): iterable;
}
