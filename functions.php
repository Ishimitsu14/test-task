<?php

/**
 * @param string $url
 * @return string
 * @throws Exception
 */
function parseUrl(string $url)
{
    // Проверяем валидный ли url
    if (isValidUrl($url)) {
        $parseUrl = parse_url($url);
        $path = urldecode($parseUrl['path']);
        $query = [];
        if (isset($parseUrl['query'])) {
            parse_str($parseUrl['query'], $query);
            foreach ($query as $key => $value) {
                if ($value == 3) {
                    unset($query[$key]);
                }
            }
            asort($query);
        }
        $query['url'] = urlencode($path);
        $query = http_build_query($query);
        $parseUrl['query'] = $query;
        unset($parseUrl['path']);
        return buildUrl($parseUrl);
    }

    // Выкидываем сообщение о том, что пришел не валидный url
    throw new Exception('Invalid url');
}

/**
 * @param string $user_ids
 * @return array
 * @throws Exception
 */
function loadUsersData(string $user_ids)
{
    // Определяем переменную $data заранее, если после выборки мы ничего не получим, то вернется пустой массив,
    // а не ошибко о том, что переменная неизвестна
    $data = [];
    // Создаем соединение с базой
    $link = mysqli_connect('localhost', 'root', 'root', 'test-ticket');
    // проверяем удалось ли соединиться с БД
    if (mysqli_connect_errno()) {
        // Выкидываем сообщение о неудачном подключении
        throw new Exception(sprintf("Failed to connect: %s\n", mysqli_connect_error()));
    }
    // Собираем запрос и экранируем данные, чтобы уберечь себя от sql инъекций
    $user_ids = mysqli_real_escape_string($link, $user_ids);
    // В запросе выбираем только id и name, чтобы не тянуть все поля, ибо мы их использовать не будем
    $query = "SELECT `id`, `name` FROM users WHERE id IN ($user_ids)";
    $sql = mysqli_query($link, $query);
    // Проходимся по полученым данным из БД
    while ($obj = $sql->fetch_object()) {
        $data[$obj->id] = $obj->name;
    }
    // Закрываем содинение
    mysqli_close($link);

    return $data;
}

/**
 * @param array $parts
 * @return string
 */
function buildUrl(array $parts)
{
    $scheme   = isset($parts['scheme']) ? ($parts['scheme'] . '://') : '';

    $host     = ($parts['host'] ?? '');
    $port     = isset($parts['port']) ? (':' . $parts['port']) : '';

    $user     = ($parts['user'] ?? '');

    $pass     = isset($parts['pass']) ? (':' . $parts['pass'])  : '';
    $pass     = ($user || $pass) ? "$pass@" : '';

    $path     = ($parts['path'] ?? '');
    $query    = isset($parts['query']) ? ('/?' . $parts['query']) : '';
    $fragment = isset($parts['fragment']) ? ('#' . $parts['fragment']) : '';

    return implode('', [$scheme, $user, $pass, $host, $port, $path, $query, $fragment]);
}


/**
 * @param $url
 * @return false|int
 */
function isValidUrl(string $url) {
    return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
}