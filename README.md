# Sitar Web Engine

## Оглавление

- [Sitar Web Engine](#sitar-web-engine)
	- [Введение](#Введение)
		- [Принцип работы](#Принцип-работы)
		- [Нормальный режим](#Нормальный-режим)
		- [Режим редактирования](#Режим-редактирования)
		- [Принудительный нормальный режим](#Принудительный-нормальный-режим)
	- [Дополнительные возможности](#Дополнительные-возможности)
	- [Шаблоны представления](#Шаблоны-представления)
	- [Функциональные модули](#Функциональные-модули)

## Введение

Sitar [sɪˈtɑːr] — это многофункциональный веб-фреймворк, предназначенный для разделения данных (контента) от их представления (шаблонов) и редактирования данного контента непосредственно в веб-браузере.

[↑ Оглавление](#Оглавление)

## Принцип работы

Построение веб-страницы (и отдельных блоков) происходит на веб-сервере в одном из режимов:

- нормальный режим
- режим редактирования
- принудительный нормальный режим

[↑ Оглавление](#Оглавление)

### Нормальный режим

В нормальном режиме любой вызов метода `show` объекта `$data` с параметром `key` в файле шаблона `template.html`

```php
<?=$data->show("key")?>
```

будет заменен на значение `data` параметра `key` из файла `storage/editable.$lang.json`

```json
{
	"key": "data"
}
```

Кроме того, класс объекта `$data` поддерживает многоязычность, реализованную через обращение к разным независимым файлам `editable.$lang.json` в директории `storage`.

Если код языка не установлен до вызова `<?=$data->show("key")?>`, то будет использован файл по умолчанию: `editable.ru.json`.

Чтобы использовать файл контента с другой локализацией, необходимо до вызова метода `show` объекта `$data` один раз установить переменную PHP-сессии `lang` через встроенную в Sitar функцию

```php
<?php session("lang", "ua")?>
```

Таким образом, метод `show` объекта `$data` будет использовать файл с именем `storage/editable.$lang.json`, где `$lang` — это значение ключа PHP-сессии `lang`.

Кроме обычных данных (текст, html), Sitar поддерживает хранение в файле данных кода PHP и выполнение его при вызове метода `show` объекта `$data`.

```json
{
	"key": "<?='hello'?>"
}
```

Выполнение кода PHP происходит только в нормальном режиме, в режиме редактирования код не выполняется. Данная функциональность является опасной и ее следует использовать с осторожностью.

[↑ Оглавление](#Оглавление)

### Режим редактирования

В режиме редактирования любой вызов метода `show` объекта `$data` с параметром `key` в файле шаблона `template.html`

```php
<?=$data->show("key")?>
```

будет заменен на значение `data` параметра `key` из файла `storage/editable.$lang.json`

```json
{
	"key": "data"
}
```

Отличие режима редактирования от нормального режима заключается в том, что значение параметра `key` из файла `storage/editable.$lang.json` помещается в html элемент `<data>` со свойством `contenteditable = false`, для того, чтобы включить нативный способ редактирования содержимого в веб-браузере. Таким образом, Sitar выдает веб-браузеру следующий html-код:

```html
<data id="key" contenteditable="false" title="key">data</data>
```

Чтобы включить режим редактирования, необходимо установить значение переменной PHP-сессии `admin` в `true`.

```php
session("admin", "true");
```

Класс объекта `$data` при каждом вызове проверяет данную переменную сессии и переключается в соответствующий режим.

Если веб-страница сформирована в режиме редактирования, пользователь имеет возможность начать редактировать содержимое каждого редактируемого блока (вызова метода `show` объекта `$data` с параметром `key`).

В данном режиме все редактируемые блоки при наведении курсора выделяются красной пунктирной обводкой, кроме того, есть возможность увидеть html `title` каждого блока, соответствующий ключу данных в файле `storage/editable.$lang.json`.

Для того, чтобы начать редактировать соответствующий блок, необходимо его выбрать, удерживая клавишу `Alt (Option)`. Блок, открытый в режиме редактирования, выделяется красной сплошной обводкой и представляет собой html-код. Редактируемые блоки могут содержать как текст, так и html, javascript или PHP-код.

В режиме редактирования свойство блока `contenteditable` устанавливается в `true`, что включает соответствующее поведение веб-браузера.

Для того, чтобы сохранить измененное содержимое блока, необходимо его выбрать, удерживая клавишу `Alt (Option)`. При сохранении содержимого блока браузер делает асинхронный http-вызов к серверу Sitar, передавая html `id` блока, соответствующее параметру `key` в файле `storage/editable.$lang.json` и его содержимое. После чего информация сохраняется в файле `storage/editable.$lang.json` на сервере, а Sitar сообщает браузеру результат операции сохранения.

Если сохранение произошло успешно, редактируемый блок подсвечивается сплошной зеленой обводкой и переключается в нормальный режим. В случае ошибки сохранения, веб-браузер выведет сообщение об ошибке.

Если пользователь попытается закрыть страницу, на которой есть хотя бы один блок, открытый в режиме редактирования (не был сохранен) — веб-браузер выведет соответствующее сообщение, напоминающее о необходимости сохранить измененный блок перед закрытием страницы.

Для успешного сохранения веб-сервер должен иметь соответствующие права на запись файлов в директории `storage`.

[↑ Оглавление](#Оглавление)

### Принудительный нормальный режим

Так как в режиме редактирования данные помещаются в дополнительный блочный элемент `data`, может возникнуть необходимость подавить это поведение. Например, делается локализация какого-либо свойства элемента html и его редактирование нежелательно или невозможно (нельзя вставить блочный элемент в структуру) — в таком случае предусмотрен принудительный нормальный режим отдельных блоков в общем режиме редактирования:

```html
<input type="text" placeholder="<?=$data->show('placeholder-text', false)?>">
```

В данном примере методу `show` объекта `$data` передается второй параметр `false`, переключающий блок в принудительный нормальный режим и отключающий режим редактирования.
Такие данные не будут подсвечиваться при наведении и не будут нарушать структуру html-кода дополнительными блочными элементами `<data>`.

Редактирование таких данных следует производить непосредственно на сервере в файле `storage/editable.$lang.json`

[↑ Оглавление](#Оглавление)

## Дополнительные возможности

Кроме функционала вывода данных в представление и их редактирования, Sitar реализует ряд дополнительных возможностей для удобства быстрой разработки проекта.

[↑ Оглавление](#Оглавление)

### Шаблоны представления

Sitar разделяет данные от их представления, для этой цели представление (шаблон) реализуется в отдельном файле `template.html`. Шаблон представляет собой html-документ, с подключенными к нему дополнительными ресурсами (javascript, css и т.п.). Для вывода содержимого, в нужных местах файла шаблона делаются вызовы объекта `$data`.

Sitar перехватывает (маршрутизирует) все http-запросы к сайту на файл `router.php`, таким образом, для URI

- https://sitar.com
- https://sitar.com/article
- https://sitar.com/pages/text?id=1 и т.д.

будет использован единственный шаблон `template.html` из корня веб-сайта.

Исходя из этого, логика навигации по URI должна быть реализована внутри шаблона с использованием глобальной переменной `$request`, которая содержит часть строки запроса пользователя без имени домена и GET-параметров

- "/"
- "/article"
- "/pages/text"

Кроме того, если запрос содержит любые символы, кроме `/`, `alphanumeric`, `-`, `.`, он будет переадресован в корень веб-сайта и отброшен.

Sitar поддерживает множественные шаблоны `template.html`, кроме обязательного, размещаемого в корне сайта.

Для того, чтобы система использовала другой шаблон, при запросе к `https://sitar.com/subfolder` необходимо в корне сайта создать соответствующий файл шаблона по пути `/subfolder/template.html`.

Поиск файла шаблона в дереве каталогов происходит сверху вниз, соответственно, для представления данных вызываемых по URL с множественной вложенностью, будет использован последний шаблон, найденный в дереве каталогов, путь к которому в файловой системе совпадает с запросом URI (шаблон более низкого уровня будет иметь высший приоритет, в случае размещения нескольких шаблонов в дереве). Таким образом, есть возможность создавать страницы с разным представлением данных, в зависимости от URI.

Пример:

при иерархии шаблонов
```
/temlate.html (A)
/subfolder/template.html (B)
/subfolder/articles/template.html (C)
```

для следующих запросов будут использованы шаблоны

```
- https://sitar.com --> (A)
- https://sitar.com/article --> (A)
- https://sitar.com/subfolder --> (B)
- https://sitar.com/subfolder/folder --> (B)
- https://sitar.com/subfolder/articles --> (C)
- https://sitar.com/subfolder/articles/long/path --> (C)
```

Ресурсы всех шаблонов (файлы CSS, изображения и т.п.) рекомендуется размещать в директории `/template`. Кроме того, в данной директории можно сохранять постоянные части шаблона представления, такие как `header.html`, `footer.html` и т.д, впоследствии подключая их в любой из шаблонов в файловой системе `template.html`

```php
<?php require_once("template/header.html")?>
```

[↑ Оглавление](#Оглавление)

### Функциональные модули

[↑ Оглавление](#Оглавление)