<?php

file_put_contents('start.txt', 'bot started!');

//991997554:AAGqDLM7crzMzsrCpbrDKnyRJZy2skcMLHk
//https:// api. telegram. org/bot991997554:AAGqDLM7crzMzsrCpbrDKnyRJZy2skcMLHk/setWebhook?url=https://denissaha.ru/enggptbot/telega.php
error_reporting(E_ALL);

$fh = fopen('errFile.txt', 'w+');

// функция обработки ошибок
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    global $fh;
    // if (!(error_reporting() & $errno)) {
    //     // Этот код ошибки не включён в error_reporting,
    //     // так что пусть обрабатываются стандартным обработчиком ошибок PHP
    //     return false;
    // }

    // может потребоваться экранирование $errstr:
    $errstr = htmlspecialchars($errstr);

    switch ($errno) {
        case E_USER_ERROR:
            echo "<b>Пользовательская ОШИБКА</b> [$errno] $errstr<br />\n";
            echo "  Фатальная ошибка в строке $errline файла $errfile";
            echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
            echo "Завершение работы...<br />\n";
            exit(1);

        case E_USER_WARNING:
            echo "<b>Пользовательское ПРЕДУПРЕЖДЕНИЕ</b> [$errno] $errstr<br />\n";
            break;

        case E_USER_NOTICE:
            echo "<b>Пользовательское УВЕДОМЛЕНИЕ</b> [$errno] $errstr<br />\n";
            break;

        default:
            //echo "Неизвестная ошибка: [$errno] $errstr<br />\n";
            $text = "Notice: [$errno] $errstr in $errfile on line $errline<br>\n";
            fwrite($fh, $text);
            break;
    }

    /* Не запускаем внутренний обработчик ошибок PHP */
    return true;
}

$old_error_handler = set_error_handler("myErrorHandler");

include('vendor/autoload.php'); //Подключаем библиотеку
use Telegram\Bot\Api;

$telegram = new Api('991997554:AAGqDLM7crzMzsrCpbrDKnyRJZy2skcMLHk'); //Устанавливаем токен, полученный у BotFather

file_put_contents('telegram.txt', print_r($telegram, true));

$result = $telegram->getWebhookUpdates(); //Передаем в переменную $result полную информацию о сообщении пользователя
file_put_contents('result.txt', print_r($result, true));

$text = $result["message"]["text"]; //Текст сообщения
$chat_id = $result["message"]["chat"]["id"]; //Уникальный идентификатор пользователя
$name = $result["message"]["from"]["username"]; //Юзернейм пользователя
$keyboard = [["Последние статьи"], ["Картинка"], ["Гифка"]]; //Клавиатура

if ($text) {
    if ($text == "/start") {
        $reply = "Добро пожаловать в бота!";
        $reply_markup = $telegram->replyKeyboardMarkup(['keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => false]);
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply, 'reply_markup' => $reply_markup]);
    } elseif ($text == "/help") {
        $reply = "Информация с помощью.";
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply]);
    } elseif ($text == "Картинка") {
        $url = "https://68.media.tumblr.com/6d830b4f2c455f9cb6cd4ebe5011d2b8/tumblr_oj49kevkUz1v4bb1no1_500.jpg";
        $telegram->sendPhoto(['chat_id' => $chat_id, 'photo' => $url, 'caption' => "Описание."]);
    } elseif ($text == "Гифка") {
        $url = "https://68.media.tumblr.com/bd08f2aa85a6eb8b7a9f4b07c0807d71/tumblr_ofrc94sG1e1sjmm5ao1_400.gif";
        $telegram->sendDocument(['chat_id' => $chat_id, 'document' => $url, 'caption' => "Описание."]);
    } elseif ($text == "Последние статьи") {
        $html = simplexml_load_file('http://netology.ru/blog/rss.xml');
        foreach ($html->channel->item as $item) {
            $reply .= "\xE2\x9E\xA1 " . $item->title . " (<a href='" . $item->link . "'>читать</a>)\n";
        }
        $telegram->sendMessage(['chat_id' => $chat_id, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => $reply]);
    } else {
        $reply = "По запросу \"<b>" . $text . "</b>\" ничего не найдено.";
        $telegram->sendMessage(['chat_id' => $chat_id, 'parse_mode' => 'HTML', 'text' => $reply]);
    }
} else {
    $telegram->sendMessage(['chat_id' => $chat_id, 'text' => "Отправьте текстовое сообщение."]);
}
