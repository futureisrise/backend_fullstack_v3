Привет. Ты хочешь узнать как у нас работает код? Добро пожаловать.

### Требования

PHP 7.1+ . Идеально PHP 7.3

- [Style guide](https://codeigniter.com/userguide3/general/styleguide.html) - обязательно к прочтению и установке шаблона CI в PHPSTORM.
- [Выбор типов данных в Mysql](https://highload.today/vybor-tipov-dannykh-v-mysql/) - полезная статья по типам данных MySQL

### GitHubFlow и процесс разработки

В данный момент мы работаем с гитом так:
Master автоматически деплоится после пуша в него на продакшен. Есть dev ветка, которую мы постоянно squash делаем в мастер. В dev мы льем кучу коммитов, они автоматически
подгружаются в dev версии которая поднята для всех и может проходится тестерами.

Хотфиксы мы выливаем в мастер ( создаем ветку от мастер, добавляем коммиты, и выливаем в мастер, или если нужно промежуточно потестить - в дев, а потом в master)

После релиза на production происходит полное пересоздание dev ветки. Чтобы не "сломать" локальную ветку и не утратить собственные изменения необходимо после релизов (
сообщение о готовности отдельно пишется в канал проекта) выполнить скрипт reload_branch, который находится в корне проекта!

**Обязательно всегда перекачивайте ветки! Так как после squash and merge, ветка дев будет уже другой, будет конфликт!**

```shell
reload_branch.bat|sh
```

### Continuous Deployment

Обязательно надо делать Endpoint который будет запрашиваться по API извне ( Nginx , а не CLI ), и будет делать `opcache_reset()`, иначе утечка памяти!

### Task management

Мы работаем через Clickup. Ранее были Github Issues. Пользуемся Labels для описания задачи. Оставляйте лейблы на задачах !

### Локальная разработка на win

Чтобы выполнять миграции и хорошо писать продукт, надо ставить композер и зависимости. В данном случае ставим PHP7.3 ( stable )
Чтобы работало все мы в `php.ini` добавим нужные ext. Часто используется phpredis скачать можно тут и
почитать https://github.com/phpredis/phpredis/blob/develop/INSTALL.markdown . Его лучше устаналивать на линуксе через pecl !

```ini
extension = php_gd2.dll
extension = php_curl.dll
extension = php_mbstring.dll
extension = php_openssl.dll
extension = php_pdo_mysql.dll
extension = php_pdo_sqlite.dll
extension = php_sockets.dll
extension = php_redis.dll ; https://pecl.php.net/package/redis

;extension=bz2
;extension=curl
extension = fileinfo
```

### ShadowIgniter as Codeigniter 3

Мы используем Codeigniter 3 как фреймворк. В нашем случае, мы доработали его, теперь он у нас есть в приватном доступе на репозитории компании :)

Чтобы обновить ядро , обратитесь к главной странице репо, там расписана информация как обновится и тд.

**Аттеншен! Она скачает последнюю ревизию ядра! Может поломать проект! Будьте готовы проверять обновления и изменения!**

Используем его удобства Rout'a, MVC подхода, шаблонизации, конфиги.

### Autoload

Для корректной работы обязательно нужны:

```php
$autoload['libraries'] = ['session', 'sparrow_starter'];
```

### Config

В оригинальной версии логгер - худшее что могли придумать люди. Взяли из 4го, адаптировали. Переименовали строчные константы уровни, вместо ужастный цифр.

```php

/*
|--------------------------------------------------------------------------
| Error Logging Threshold
|--------------------------------------------------------------------------
|
| You can enable error logging by setting a threshold over zero. The
| threshold determines what gets logged. Threshold options are:
|
|  'emergency'  Urgent alert.
|  'alert' Action must be taken immediately Example: Entire website down, database unavailable,  This should trigger the SMS alerts and wake you up.
|  'critical' Critical conditions Example: Application component unavailable, unexpected exception.
|  'error' Runtime errors
|  'warning' Exceptional occurrences that are not errors  Examples: Use of deprecated APIs, poor use of an API
|  'notice' Uncommon events
|  'info' Interesting events Examples: User logs in, SQL logs.
|  'debug' Detailed debug information
|
| You can also pass an array with threshold levels to show individual error types
|
| 	['debug'] = Debug Messages, without Error Messages
|
| For a live site you'll usually only enable Errors (1) to be logged otherwise
| your log files will fill up very fast.
|
*/
$config['log_threshold'] = 'debug';

```

### Igniter

Ознакомиться можно по [Igniter - cli guide](docs/Igniter.md)

### DB

Для работы с базой данных используем Sparrow https://github.com/mikecao/sparrow . Он чуть поправлен, но в целом работает по гайду. Чтобы подключиться к базе достаточно в
autoload.php добавить в liblraries 'sparrow_starter' билблиотеку.

### App::get_ci() Singletone

Для обращений к ядру в наших моделях происходит через синглотон App::get_ci() , вместо привычного $this. Сделано чтобы в (Emerald_Model) моделях можно было легко
обращаться к ядру.

### Emerald_Model

Для построения ООП мы используем наши модели Emerald_model, которые могут быть унаследованы через core/My_Model Каждая строка в таблице - обьект класса table_name_Model .
В них есть метод дампа обьекта `->object_beautify()` , также можно просто дампить обьект: `var_dump($some_emerald_obj);`
Конструктор, с параметром `id`, для получения данных из базы. Метод для обновления обьекта, получения данных из базы `->reload()`
Метод для проверки инициализации обьекта `->is_loaded()`

В функции стараться лучше передавать всегда User $user, вместо int $user_id и подобные манипуляции всегда удобнее.

```php
class Application_items extends \System\Emerald\Emerald_model {
    const CLASS_TABLE = APPLICATION_ITEMS;

    protected $user_id;
    protected $user_id_old;
    protected $bundle_position;
    protected $state;
    protected $type;
    protected $price;
    protected $position;
    protected $assign_id;
    protected $time_finished;

    protected $item;
    protected $user;
    protected $queue;
    protected $delayed_queue;
    protected $relative;

```

Это связи - ORM :

```php
    protected $item;
    protected $user;
    protected $queue;
    protected $delayed_queue;
    protected $relative;
```

Для них пишется отдельный геттер и сеттер

```php
    /**
     * @return User
     */
    public function get_user(): User
    {
        if (empty($this->user))
        {
            $this->user = new User($this->get_user_id());
        }
        return $this->user;
    }
```

и метод `->reload()` обязательно! перегружается:

```php
    public function reload()
    {
        parent::reload();
        $this->item = NULL;
        $this->user = NULL;
        $this->queue = NULL;
        $this->delayed_queue = NULL;
        $this->relative = NULL;
        return $this;
    }
```

Таким образом можем получить все что нам нужно без лишних проблем :)

Для указания таблицы в модели используем такую структуру, для получения таблицы используем метод в основном `::get_table_static()` или `::CLASS_TABLE`.

Порядок функций в файле:
1 - Константы и конструктор 2 - Геттеры сеттеры, генерейтеды и в целом связи. 3 - методы работы с объектом, без статики !
4 - create, delete 5 - статик методы (выборки, типа get_by_assign_id, get_active_by_user_id и остальные статик методы), 6 - препарейшены.

```php
class Multi_model extends \System\Emerald\Emerald_model {
    const CLASS_TABLE = 'multi';
```

#### Static transform методы для получения наборов данных

К примеру, нам нужно получить достижение по юзеру и названию, метод выглядеть вот так должен:

```php
    /**
     * @param User $user
     * @param string $name
     * @return User_achievement
     */
    public static function get_by_user_and_name(User $user, string $name): User_achievement
    {
        return static::transform_one(App::get_s()->from(self::CLASS_TABLE)
            ->where(['user_id' => $user->get_id(), 'name' => $name])
            ->select()
            ->one());
    }
```

Получение активных достижений юзером, с условием - те что в профиле показываются или те что нет.

```php
    /**
     * @param User $user
     * @param bool $only_top_profile
     * @return User_achievement[]
     */
    public static function get_active_by_user(User $user, bool $only_top_profile = FALSE): array
    {

        $where = ['user_id' => $user->get_id(), 'state' => My_Core::DB_STATE_ACTIVE];
        if ($only_top_profile)
        {
            $where['is_top_profile'] = 1;
        }
        return static::transform_many(App::get_s()->from(self::get_table_static())
            ->where($where)
            ->order('id', 'DESC')
            ->select()
            ->many());
    }
```

мы используем `static::transform_many()` и `static::transform_one()` для создания быстрого Emerald моделей. **Обязательно `static` !**

### Emerald_Enum

Волшебные классы с наборами констант. Чаще всего для работы с данными ENUM\SET базы данных. Хранятся на уровне с моделью, в папке enum

```
application/models/achievement/Achievement_system.php
application/models/achievement/enum/Achievement_type.php
```

```php
namespace Model\Achievement\Enum;

use System\Emerald\Emerald_enum;

class Achievement_type extends Emerald_enum {
    const COMPLEX = 'complex';
    const NUMBER = 'number';
    const SINGLE = 'single';
}
```

Появляется возможность использовать методы `get_list` и `has_value` что крайне упрощает жизнь.

### Application_ модели

Для работы с таким типом моделей, которые относяться к поддоменам - используем название типа application_cases_model, где может быть таблица super_some_model,
mega_some_model, low_some_model и так далее.

Для получения таблицы:

```php
public function get_table(): ?string
```

### View <> To Frontend

Для API возврата данных на фронтенд есть методы в контроллере: `$this->response()`, `$this->response_success()`, `$this->response_error()`.

К примеру `response_error($message)` только принимает GENERIC параметры из \System\Libraries\Core - Core модель. Она может наследоваться через свою Library и там
расширять ответы ядра.

```php
    const RESPONSE_GENERIC_INTERNAL_ERROR = 'internal_error'; // Если у нас exception выбил что-то . в info пишем инфу по ошибке.
    const RESPONSE_GENERIC_NEED_AUTH = 'need_auth'; // Если надо авторизоваться
    const RESPONSE_GENERIC_ALLREADY_LOGGED = 'allready_logged'; // Если уже авторизован
    const RESPONSE_GENERIC_NEED_SUM = 'need_sum'; // недостаточно баланса на счету
```

пример:

```php
        if ( ! User::is_logged())
        {
            return $this->response_error(\System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }
```

Методы также внутри себя могут отдавать подобный массив, для проверки используем статусы в классе проекта

```php
    const RESPONSE_STATUS_SUCCESS = 'success';
    const RESPONSE_STATUS_INFO = 'info';
    const RESPONSE_STATUS_ERROR = 'error';
```

Для отправки данных на фронт используем объекты и методы `preparation`. В каждом классе который возвращает данные на фронт имеем метод похожий на:

```php
    /**
     * @param Application_items $data
     * @param string $preparation
     * @return stdClass
     * @throws Exception
     */
    public static function preparation(Application_items $data, string $preparation = 'default'):stdClass
    {
        switch ($preparation)
        {
            case 'default':
                return static::_preparation_default($data);
            case 'item_page':
                return static::_preparation_item_page($data);
            case 'seo_list':
                return static::_preparation_seo_list($data);
            default:
                throw new Exception('undefined preparation type');
        }
    }
```

и сам препарейшен. Всегда должен возвращать stdClass , а не массив и тд. Таким образом код становится еще более четкий.

```php
    /**
     * @param Application_items $data
     * @return stdClass
     */
    protected static function _preparation_default(Application_items $data):stdClass
    {
        $i = new stdClass();

        $i->id = $data->get_info_v3()->get_id();
        $i->item_id = $data->get_id();
        $i->item_name = $data->get_info_v3()->get_item_name();
        $i->sort = $data->get_info_v3()->get_sort();

        return $i;
    }
```

Если вы хотим пачку обьектов препарировать - используем `::preparation_many()` передавая первым параметром массив Emerald, а не экземпляр класса Emerald.

Для удобства навигации в PHPSTORM к примеру, можете сделать пустой метод, который наследуется в классе.

```php
    /**
     * @param array $data
     * @param string $preparation
     * @param string|null $key
     * @return array
     */
    public static function preparation_many(array $data, string $preparation = 'default', string $key = NULL): array
    {
        return parent::preparation_many($data, $preparation, $key);
    }
```

### Transactions

ОЧЕНЬ ВАЖНЫЙ МОМЕНТ КАК РАБОТАТЬ С ТРАНЗАКЦИЯМИ

Уровни изоляции поддерживаются.

НАЧАЛО:

```php
App::get_s()->set_transaction_repeatable_read()->execute();
App::get_s()->start_trans()->execute();
```

Конец:

```php
if (!$someaction_result){
    App::get_s()->rollback()->execute();
} else {
    App::get_s()->commit()->execute();
}
````

ОЧЕНЬ ВАЖНО! Всегда проверять на affected, применились ли изменения. Если нет - роллбек! Иначе будет всем нам беда большая.

```php
   $affected_int =  App::get_s()->get_affected_rows();

   $affected_bool =  App::get_s()->is_affected();
````

### Migrations

Для созданий миграций используем Phinx. Также есть алиасы в Igniter [Igniter - cli guide](docs/Igniter.md)

Документация по Phinx: [Phinx docs](https://phinx.readthedocs.io/en/latest/migrations.html).

### Code nesting

Мы не используем вложенности, если они не нужны. В данном примере видно что если сеттингов нет - можно дальше `return` сделать, или еще что то. Таким образом, мы
избавляемся от лапшы, код не растет в ширину, и в целом все лучше видно :)

![](https://i.gyazo.com/36eb53b466e93244602d5594bedbaa25.png)

### Logging, Exceptions and Try catch...

Наш код должен работать идеально, как швейцарские часы. Код должен быть целостный, везде должны быть в моделях проверки пустоты обьектов ( ->is_loaded(TRUE); )
Никаких кетчей по приколу мы не делаем! Все должно обрабатыватся правильно, последовательно и точно. Если чтото идет не так - выкидываем Exception, далее его ловим в
Newrelic или логах Nginx, иначе никогда не найдем ошибку!

Try catch - в основном используется в контроллерах, где проверяем существует ли данные в базе по данным которые приходят.

Logs.. Логи мы можем использовать, для логирования заведомо возможных ошибок ( Откат транзакции ( Rollback ), хендлинг Racemode ), или же неочевидных ответов от внешних
сервисов ( Не правильный Hash в платежной системе пришел, запрос из не авторизированных айпи, 4xx, 5xx, 1xxx ошибки сервера, или же стркутура ответа не подходит
обработчику.

Обязательно понимать уровни логов. Если мы подозреваем что может прийти не ладное или оставили дебаг информацию для dev или local среды - уровень DEBUG. Если же ошибка
критическая - ERROR . Если это информационные ( выдали кому-то что-то ) - INFO WARN предупреждает нас о чем то всегда, что запрос выполнялся долго,к примеру.

### phpstorm.php

Все новые библиотеки, модели и т.д. нужно добавлять в phpdoc файла phpstorm.php по примеру с теми, что там уже есть для использования поиска вхождений класса и
автокомплита. В основном пытаемся уйти от данной практики.

Файлы Controller.php и Model.php в /system/core правой кнопкой мыши и  `Mark as Plain Text.`

### Getters and Setters for PHPSTORM for Emerald_model

Вообще лучше использовать генератор моделей, который описан в Igniter , но для удобства можно добавить генераторы, когда понадобиться добавлять поля.

```php
/**
* @return ${TYPE_HINT}
*/
public ${STATIC} function ${GET_OR_IS}_${FIELD_NAME}()#if(${RETURN_TYPE}): ${RETURN_TYPE}#else#end
{
#if (${STATIC} == "static")
    return self::$${FIELD_NAME};
#else
    return $this->${FIELD_NAME};
#end
}

```

```php
/**
* @param ${TYPE_HINT} $${PARAM_NAME}
*
* @return bool
*/
public ${STATIC} function set_${FIELD_NAME}(#if (${SCALAR_TYPE_HINT})${SCALAR_TYPE_HINT} #else#end$${PARAM_NAME})
{
#if (${STATIC} == "static")
    self::$${FIELD_NAME} = $${PARAM_NAME};
#else
    $this->${FIELD_NAME} = $${PARAM_NAME};
#end
    return $this->save('${FIELD_NAME}', $${PARAM_NAME});
}
```