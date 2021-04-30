<?php

namespace System\Libraries;
/*
 * Core constant class. Tons of useful cross project constants.
 * Just make My_Core , or My_Project file in Library directory in your application.
 */

class Core {

    const CORE_NAME = 'ShadowIgniter3';

    const RESPONSE_GENERIC_CODE = 'code'; // внутренний код ошибки чтобы поинмать на каком моменте пиздарезка

    const DB_STATE_ACTIVE = 'active';
    const DB_STATE_FINISHED = 'finished';
    const DB_STATE_PENDING = 'pending';
    const DB_STATE_CANCELLED = 'cancelled';
    const DB_STATE_ERROR = 'error';

    const RESPONSE_STATUS_SUCCESS = 'success';
    const RESPONSE_STATUS_INFO = 'info';
    const RESPONSE_STATUS_ERROR = 'error';

    // ACTION | REQUEST ASSIGNED
    const RESPONSE_GENERIC_INTERNAL_ERROR = 'internal_error'; // Если у нас excpetion выбил чтото . в info пишем инфу по ошибке.
    const RESPONSE_GENERIC_NEED_AUTH = 'need_auth'; // Если надо авторизоваться


    const RESPONSE_GENERIC_DISABLED = 'disabled'; // Фунционал отключен или временно не доступен - через сеттинги выключили
    const RESPONSE_GENERIC_NO_ACCESS = 'no_access'; // зарос должен быть только через AJAX
    const RESPONSE_GENERIC_WRONG_PARAMS = 'wrong_params'; // Входящие данные не правлиьные.
    const RESPONSE_GENERIC_UNAVAILABLE = 'unavailable'; // временное или постоянное недоступное действие ( в основном относиться к кейсопену )

    const RESPONSE_GENERIC_TRY_LATER = 'try_later'; // action was done before. 2nd time dont need to ask :)
    const RESPONSE_GENERIC_NO_DATA = 'no_data'; // нет данных по данному запросу . Пусто или че ? тоже самое что и сверху
    const RESPONSE_GENERIC_SHOULD_WAIT = 'should_wait'; // Действие с задержкой - повторный запрос отправить позже

    const WEEK_DAYS = ['su', 'mo', 'tu', 'we', 'th', 'fr', 'sa'];


    const CACHE_KEY_SETTINGS = 'settings';

    const OBJECT_BEAUTIFY_NESTING_LEVEL = 4;
}
