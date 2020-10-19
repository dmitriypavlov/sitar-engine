# Sitar Web Engine

## Релизы версий

[![](https://img.shields.io/github/v/release/dmitriypavlov/sitar-engine?color=green&label=Sitar%20Web%20Engine)](https://github.com/dmitriypavlov/sitar-engine/releases) [![](https://img.shields.io/badge/Sitar%20Web%20Engine-GIT-orange)](https://github.com/dmitriypavlov/sitar-engine/archive/master.zip)

Текущая [версия GIT](https://github.com/dmitriypavlov/sitar-engine/archive/master.zip) может содержать изменения еще не вошедшие в [актуальный релиз](https://github.com/dmitriypavlov/sitar-engine/releases).

Запуск локального сервера PHP для разработки проекта:

```bash
cd sitar-engine
php -S localhost:8080 ./router.php
```

## Оглавление

- [Введение](#Введение)
	- [Принцип работы](#Принцип-работы)
	- [Нормальный режим](#Нормальный-режим)
	- [Режим редактирования](#Режим-редактирования)
	- [Принудительный нормальный режим](#Принудительный-нормальный-режим)
- [Дополнительные возможности](#Дополнительные-возможности)
	- [Шаблоны представления](#Шаблоны-представления)
	- [Независимые шаблоны](#Независимые-шаблоны)
	- [Работа с сессией](#Работа-с-сессией)
	- [Предварительный обработчик](#Предварительный-обработчик)
	- [Загрузка библиотек зависимостей](#Загрузка-библиотек-зависимостей)
	- [Отладка](#Отладка)
- [Структура файлов](#Структура-файлов)
- [HOW-TO](#HOW-TO)
	- [Переключение локализации](#Переключение-локализации)
	- [Авторизация администратора](#Авторизация-администратора)
	- [Файловый менеджер](#Файловый-менеджер)

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

В нормальном режиме любой вызов метода `show` объекта `$data` с параметром `key` в файле шаблона `/template.html`

```php
<?=$data->show("key")?>
```

будет заменен на значение `data` параметра `key` из файла `/storage/editable.$lang.json`

```json
{
	"key": "data"
}
```

Кроме того, класс объекта `$data` поддерживает многоязычность, реализованную через обращение к разным независимым файлам `/storage/editable.$lang.json`.

Если код языка не установлен до вызова `<?=$data->show("key")?>`, то будет использован файл по умолчанию: `editable.ru.json`.

Чтобы использовать файл контента с другой локализацией, необходимо до вызова метода `show` объекта `$data` один раз установить переменную PHP-сессии `lang` через встроенную в Sitar функцию

```php
<?php session("lang", "ua")?>
```

Таким образом, метод `show` объекта `$data` будет использовать файл с именем `/storage/editable.$lang.json`, где `$lang` — это значение ключа PHP-сессии `lang`.

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

будет заменен на значение `data` параметра `key` из файла `/storage/editable.$lang.json`

```json
{
	"key": "data"
}
```

Отличие режима редактирования от нормального режима заключается в том, что значение параметра `key` из файла `/storage/editable.$lang.json` помещается в html элемент `<data>` со свойством `contenteditable = false`, для того, чтобы включить нативный способ редактирования содержимого в веб-браузере. Таким образом, Sitar выдает веб-браузеру следующий html-код:

```html
<data id="key" contenteditable="false" title="key">data</data>
```

Чтобы включить режим редактирования, необходимо установить значение переменной PHP-сессии `admin` в `true`.

```php
session("admin", "true");
```

Класс объекта `$data` при каждом вызове проверяет данную переменную сессии и переключается в соответствующий режим.

Если веб-страница сформирована в режиме редактирования, пользователь имеет возможность начать редактировать содержимое каждого редактируемого блока (вызова метода `show` объекта `$data` с параметром `key`).

В данном режиме все редактируемые блоки при наведении курсора выделяются красной пунктирной обводкой, кроме того, есть возможность увидеть html `title` каждого блока, соответствующий ключу данных в файле `/storage/editable.$lang.json`.

Для того, чтобы начать редактировать соответствующий блок, необходимо его выбрать, удерживая клавишу `Alt (Option)`. Блок, открытый в режиме редактирования, выделяется красной сплошной обводкой и представляет собой html-код. Редактируемые блоки могут содержать как текст, так и html, javascript или PHP-код.

В режиме редактирования свойство блока `contenteditable` устанавливается в `true`, что включает соответствующее поведение веб-браузера.

Для того, чтобы сохранить измененное содержимое блока, необходимо его выбрать, удерживая клавишу `Alt (Option)`. При сохранении содержимого блока браузер делает асинхронный http-вызов к серверу Sitar, передавая html `id` блока, соответствующее параметру `key` в файле `/storage/editable.$lang.json` и его содержимое. После чего информация сохраняется в файле `/storage/editable.$lang.json` на сервере, а Sitar сообщает браузеру результат операции сохранения.

Если сохранение произошло успешно, редактируемый блок подсвечивается сплошной зеленой обводкой и переключается в нормальный режим. В случае ошибки сохранения, веб-браузер выведет сообщение об ошибке.

Если пользователь попытается закрыть страницу, на которой есть хотя бы один блок, открытый в режиме редактирования (не был сохранен) — веб-браузер выведет соответствующее сообщение, напоминающее о необходимости сохранить измененный блок перед закрытием страницы.

Для успешного сохранения веб-сервер должен иметь соответствующие права на запись файлов в директории `storage`.

[↑ Оглавление](#Оглавление)

### Принудительный нормальный режим

Так как в режиме редактирования данные помещаются в дополнительный блочный html элемент `<data>`, может возникнуть необходимость подавить это поведение. Например, проводится локализация какого-либо свойства элемента html и его редактирование нежелательно или невозможно (нельзя вставить блочный элемент в структуру) — в таком случае предусмотрен принудительный нормальный режим для отдельных блоков в общем режиме редактирования:

```html
<input type="text" placeholder="<?=$data->show('placeholder-text', false)?>">
```

В данном примере методу `show` объекта `$data` передается второй параметр `false`, переключающий блок в принудительный нормальный режим и отключающий режим редактирования.
Такие данные не будут подсвечиваться при наведении и не будут нарушать структуру html-кода дополнительными блочными элементами `<data>`.

Редактирование таких данных следует производить непосредственно на сервере в файле `/storage/editable.$lang.json` через файловый менеджер.

[↑ Оглавление](#Оглавление)

## Дополнительные возможности

Кроме функционала вывода данных в представление и их редактирования, Sitar реализует ряд дополнительных возможностей для удобства быстрой разработки проекта.

[↑ Оглавление](#Оглавление)

### Шаблоны представления

Sitar разделяет данные от их представления, для этой цели представление (шаблон) реализуется в отдельном файле `/template.html`. Шаблон представляет собой html-документ, с подключенными к нему дополнительными ресурсами (javascript, css и т.п.). Для вывода содержимого, в нужных местах файла шаблона делаются вызовы объекта `$data`.

Sitar перехватывает (маршрутизирует) все http-запросы к сайту на файл `router.php`, таким образом, для URI

```
https://sitar.com
https://sitar.com/article
https://sitar.com/pages/text?id=1

и т.д.
```

будет использован единственный шаблон `/template.html` из корня веб-сайта.

Исходя из этого, логика навигации по URI должна быть реализована внутри шаблона с использованием глобальной переменной `$request`, которая содержит часть строки запроса пользователя без имени домена и GET-параметров

```
"/"
"/article"
"/pages/text"

и т.д.
```

Кроме того, если запрос содержит любые символы, кроме `/`, `alphanumeric`, `-`, `.`, он будет переадресован в корень веб-сайта и отброшен.

[↑ Оглавление](#Оглавление)

### Независимые шаблоны

Sitar поддерживает множественные шаблоны `template.html`, кроме обязательного, размещаемого в корне сайта. В случае отсутствия в проекте основного шаблона, Sitar выведет соответствующее исключение `TEMPLATE_READ_ERROR`.

Для того, чтобы система использовала другой шаблон, при запросе к `https://sitar.com/subfolder` необходимо в корне сайта создать соответствующий файл шаблона по пути `/subfolder/template.html`.

Поиск файла шаблона в дереве каталогов происходит сверху вниз, соответственно, для представления данных вызываемых по URL с множественной вложенностью, будет использован последний шаблон, найденный в дереве каталогов, путь к которому в файловой системе совпадает с запросом URI (шаблон более низкого уровня будет иметь высший приоритет, в случае размещения нескольких шаблонов в дереве). Таким образом, есть возможность создавать страницы с разным представлением данных, в зависимости от URI.

Например, при иерархии шаблонов:

```
/temlate.html (A)
/subfolder/template.html (B)
/subfolder/articles/template.html (C)
```

для следующих запросов будут использованы шаблоны:

```
https://sitar.com --> (A)
https://sitar.com/article --> (A)
https://sitar.com/subfolder --> (B)
https://sitar.com/subfolder/folder --> (B)
https://sitar.com/subfolder/articles --> (C)
https://sitar.com/subfolder/articles/long/path --> (C)
```

Ресурсы всех шаблонов (CSS, javascript, изображения и т.п.) рекомендуется размещать в директории `/template`. Кроме того, в данной директории можно сохранять постоянные части шаблона представления, такие как `header.html`, `footer.html` и т.д, впоследствии подключая их в любой из шаблонов в файловой системе `template.html`

```php
<?php require_once("template/header.html")?>
```

[↑ Оглавление](#Оглавление)

### Работа с сессией

Sitar поддерживает работу с сессиями PHP (хранением данных на сервере в формате `ключ`,`значение` с идентификацией сессии пользователя по cookie в браузере).

Процедуры работы с сессиями реализованы в файле `/session.php`, в начале которого можно настроить идентификатор сессии (рекомендуется использовать новый для каждого проекта) и длительность "жизни" сессии, по истечению которой сессия будет уничтожена сборщиком мусора.

```php
$name = "sitar"; 	// session name
$time = 86400; 		// lifetime seconds
```

Сессия начинается автоматически при выполнении любого обращения к Sitar. По истечении времени "жизни" сессии — она уничтожается (уничтожаются все сохраненные данные), после чего открывается снова при новом обращении.

Для сохранения данных в сессию пользователя (таких, как идентификатора локализации `lang = ua`, либо признака возможности редактирования содержимого `admin = true`) необходимо выполнить функцию `session()` с двумя аргументами:

```php
<?php session("key", "value")?>
```

Для чтения данных определенного ключа сессии необходимо выполнить функцию `session()` с одним аргументом:

```php
<?php $value = session("key")?>
```

Для удаления определенного ключа сессии необходимо выполнить функцию `session()` с двумя аргументами, второй из которых `-1`:

```php
<?php session("key", -1)?>
```

Удаление всех ключей сессии производится аналогично предыдущему методу, но в качестве имени ключа указывается `"*"`:

```php
<?php session("*", -1)?>
```

В сессиях следует сохранять данные в разрезе определенного пользователя, с пониманием того, что в любой момент эти данные могут быть уничтожены. Такими данными могут быть: признак локализации, признак авторизации, временные токены, выбранная пользователем цветовая тема сайта и т.п.

[↑ Оглавление](#Оглавление)

### Предварительный обработчик

Sitar предоставляет возможность создавать пользовательские javascript функции, вызываемые непосредственно перед редактированием и после сохранения данных блока в браузере.

```javascript
function preedit(node) {
	console.log("Сейчас будет отредактирован блок");
	console.log(node);
}

function postedit(node) {
	console.log("Только что был отредактирован блок");
	console.log(node);
}
```

Оба метода могут быть полезны, если перед тем, как открыть данные редактируемого блока, необходимо провести с ними какую либо манипуляцию (например, удалить определенные свойства html и т.п.) или после сохранения блока нужно запустить какую-либо функцию (например, перезапустить слайдер и т.п.).

Sitar вызовет эти функции в соответствующей ситуации, если они были определены, и передаст в них единственный аргумент `node` — объект html, с которым проводится манипуляция на стороне клиента (браузера).

[↑ Оглавление](#Оглавление)

### Загрузка библиотек зависимостей

Клиентские библиотеки и зависимости могут быть загружены системным образом, с соблюдением требований порядка загрузки в файле `/runtime.js` методом `load()` внутри функции `init()`.

В случае, если необходимо загрузить библиотеку `template.js`, нужно использовать код:

```javascript
function init() {

	// ...

	load("/template/template.js");

	// ...
}
```

В случае, если необходимо загрузить библиотеку `template.js`, которая зависит от предварительной загрузки другой (например, `jquery.min.js`):

```javascript
function init() {

	// ...

	load("/component/jquery.min.js", () => {

		// ...

		load("/template/template.js");

		// ...
	});

	// ...
}
```

Аналогичным способом загружаются дополнительные файлы CSS (функция `load()` автоматически определяет тип ресурса, исходя расширения файла):

```javascript
function init() {

	// ...

	load("/template/mobile.css");

	// ...
}
```

[↑ Оглавление](#Оглавление)

### Отладка

В качестве отладчика Sitar использует [Tracy](https://github.com/nette/tracy).
Для активации отладчика в режимах PRODUCTION или DEVELOPMENT (см. [документацию](https://tracy.nette.org/en/guide)) необходимо раскомментировать соответствующую строку в файле `router.php`

```php
// Debugger::enable(Debugger::PRODUCTION, __DIR__ . "/storage/log");
// Debugger::enable(Debugger::DEVELOPMENT);
```

[↑ Оглавление](#Оглавление)

## Структура файлов

- `/.htaccess`

Реализует настройки веб-сервера. Отвечает за перенаправление запросов к `router.php`, защиту системных файлов, настройки кеширования ресурсов и подобные, типичные для этого файла, задачи.

- `/editable.css`

Реализует обязательные клиентские представления CSS, используемые в механизме `contenteditable`.

- `/editable.js`

Реализует клиентскую логику локального редактирования блоков `contenteditable`, а также методы асинхронного сохранения измененных данных на сервер Sitar.

- `/editable.php`

Реализует основной класс серверного объекта `$data` для работы с данными `/storage/editable.$lang.json` (загрузка, сохранение, выполнение сохраненного кода PHP и т.д.)

- `/router.php`

Реализует маршрутизатор запросов, загрузку модулей Sitar, поиск и загрузку файлов шаблонов `template.html`.

- `/runtime.js`

Реализует клиентскую загрузку javascript кода и библиотек зависимостей после события `DOMContentLoaded`.

- `/session.php`

Реализует процедуры работы с пользовательскими сессиями PHP.

- `/template.html`
- `/subdirectory/template.html`

Реализует шаблон представления данных. Может быть дополнительно расположен в нескольких субдиректориях, включая множественную вложенность, для создания разных видов представлений для соответствующих этой вложенности запросов URI.

- `/storage`

Содержит файлы данных `editable.$lang.json`. Может содержать любые изменяемые или загружаемые пользователем данные, например директорию `upload` для изображений, либо другие файлы структурированных данных.

- `/component`

Может содержать сторонние библиотеки, зависимости и модули, подключаемые к проекту.

[↑ Оглавление](#Оглавление)

## HOW-TO

### Переключение локализации

1. Создать пользовательский элемент выбора языка, например:

```html
<a href="/ru">Русский</a>
<a href="/ua">Українська</a>
<a href="/en">English</a>
```

2. Присвоить переменной сессии `lang` код языка `ru`, `ua`, `en`, в зависимости от URI, например:

```php
<?php if ($request == "/ru" || $request == "/ua" || $request == "/en") {

	$code = str_replace("/", null, $request);
	session("lang", $code);
	exit();
} ?>
```

3. Создать разные файлы содержимого с соответствующими локализациями:

```
/storage/editable.ru.json
/storage/editable.ua.json
/storage/editable.en.json
```

[↑ Оглавление](#Оглавление)

### Авторизация администратора

Пример реализации см. в файле `/admin/template.html`

[↑ Оглавление](#Оглавление)

### Файловый менеджер

Пример реализации см. в файлах `/admin/template.html`, `/component/tinyfilemanager.php` (используется [Tiny File Manager](https://tinyfilemanager.github.io))

[↑ Оглавление](#Оглавление)