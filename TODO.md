## Техническое задание: База данных и админ-панель (Laravel + Filament + PostgreSQL)

### 0. Контекст и цели

Нужно реализовать:

1. Полную структуру БД для контентных сущностей сайта.
2. Полностью работающую админ-панель на Filament для управления всем контентом.
3. Единые требования для всех сущностей: авторство, даты, soft delete, публикация/черновики, SEO, slug, медиа с обязательными `alt` и `title`.
4. Черновики с предпросмотром только для админов, и исключение индексации предпросмотра.
5. Контакты/О нас — редактируются через Settings.
6. Главная — блоки через Fabricator.
7. Формы — собственный модуль: конструктор форм + входящие заявки + интеграция антиспама (Turnstile + honeypot + rate limit).

---

## 1) Общие технологические требования

### 1.1. Стек

* Laravel (текущая версия проекта).
* PostgreSQL.
* Filament v3 (установлен).
* Fabricator (главная/блоки).
* oddvalue/laravel-drafts (черновики).
* spatie/laravel-medialibrary (+ Filament plugin).
* spatie/laravel-sluggable.
* wildside/userstamps.
* spatie/laravel-settings.
* spatie/laravel-permission.
* spatie/laravel-sitemap (позже, но таблиц не требует).
* spatie/laravel-backup (позже, таблиц не требует).
* spatie/laravel-honeypot.
* Cloudflare Turnstile (пакет-обёртка).
* (Опционально) spatie/laravel-activitylog.

### 1.2. Единые поля и поведение для контентных моделей

Для всех сущностей “контента” (см. список ниже) обязателен следующий набор:

**Поля:**

* `id` (bigint).
* `title` (string, required).
* `slug` (string, required, уникальность по модели; уточнить правила уникальности ниже).
* `seo_title` (string, nullable).
* `seo_description` (text, nullable).
* `is_published` (boolean, default false).
* `published_at` (timestamp, nullable).
* `created_at`, `updated_at`.
* `deleted_at` (soft deletes).
* `created_by`, `updated_by`, `deleted_by` (nullable FK на users).

**Требования:**

* `title` и `slug` обязательны.
* `slug` генерируется из `title` при создании, может редактироваться вручную; уникальность обеспечивается.
* Публикация: публично отображаются только записи `is_published = true` и `published_at <= now()` (если `published_at` используется).
* Soft delete: удаление в админке по умолчанию “мягкое”, возможность восстановления; физическое удаление — только супер-админу (если вообще нужно).
* Авторство: `*_by` выставляются автоматически (userstamps), включая `deleted_by` при soft delete.

### 1.3. Черновики

Требования:

* Редактирование должно происходить в черновике, не затрагивая опубликованную версию.
* Должны быть действия: `Create draft`, `Edit draft`, `Discard draft`, `Publish draft`, `Preview draft`.
* Связи many-to-many (портфолио ↔ товары/услуги) обязаны корректно работать в черновиках: изменения связей не видны на сайте до публикации.

### 1.4. Предпросмотр черновиков (только админы, не индексировать)

* Предпросмотр доступен только авторизованным админам (Filament guard).
* Роуты предпросмотра всегда отдают заголовки:

  * `X-Robots-Tag: noindex, nofollow, noarchive`
* `robots.txt` должен запрещать `/preview` (и любые пути предпросмотра).
* На страницах предпросмотра добавлять `<meta name="robots" content="noindex,nofollow,noarchive">` (дублирующе, на всякий случай).

---

## 2) Сущности сайта и связи

### 2.1. Сущности (контент)

1. **Услуги**

* `services`

2. **Товары**

* `products`

3. **Категории товаров**

* `product_categories` (дерева не требуется, если не указано — одноуровневые категории)

4. **Новости**

* `news`

5. **Портфолио (работы)**

* `portfolio_projects`

6. **Производственные возможности (оборудование/машины/роботы)**

* `capabilities` (или `production_capabilities`)

7. **Главная страница (блоки)**

* управляется Fabricator (его таблицы/структуры по пакету).
* дополнительно: должен быть единый идентификатор “home” (slug) в страницах, чтобы фронт понимал, какую страницу рендерить.

### 2.2. Настройки (Settings)

1. **Контакты**

* фиксированные поля (телефоны, email, адрес, карта/координаты, график работы, соцсети и т.д.)

2. **О нас**

* одно поле “текст сверху страницы” (WYSIWYG или textarea + editor компонент).

3. **Глобальные**

* логотип (медиа), favicon (медиа), название компании, базовые SEO (дефолт title/description), при необходимости — скрипты аналитики и т.п.

### 2.3. Меню (верхнее/нижнее)

Делать собственный модуль.

Сущности:

* `menus` (например: header, footer)
* `menu_items` (древовидные пункты меню)

Типы ссылок:

* внешняя ссылка `url`
* ссылка на маршрут `route_name + params_json`
* ссылка на контентную сущность `linkable_type/linkable_id` (polymorphic)

Поддержка:

* вложенность (1+ уровней)
* сортировка (order)
* видимость (опубликовано/не опубликовано, либо отдельный флаг `is_visible`)
* открывать в новой вкладке

---

## 3) Детальная спецификация БД (PostgreSQL)

### 3.1. Таблица users (админы)

* Использовать стандартную таблицу Laravel `users`.
* В проекте **только админы**.
* Поля минимум:

  * `name`
  * `email` (unique)
  * `password`
  * `is_active` (bool, default true) — блокировка доступа
  * timestamps, remember token
* Аутентификация — через Filament.

### 3.2. Таблицы контентных сущностей

#### 3.2.1. services

* Общие поля (см. 1.2)
* Доп. поля:

  * `short_description` (text, nullable) — для списков
  * `content` (text/longtext) — описание услуги (WYSIWYG)
  * `sort_order` (int, default 0)

Индексы:

* unique(`slug`)
* index(`is_published`, `published_at`)
* index(`deleted_at`)

#### 3.2.2. product_categories

* Общие поля
* Доп. поля:

  * `description` (text, nullable)
  * `sort_order` (int, default 0)
  * (опционально) `parent_id` если вдруг понадобится дерево; по умолчанию **не делать**, пока не требуется.

Индексы:

* unique(`slug`)
* index(`is_published`, `published_at`)

#### 3.2.3. products

* Общие поля
* Доп. поля:

  * `product_category_id` (FK, required)
  * `short_description` (text, nullable)
  * `content` (text/longtext)
  * `sku` (string, nullable, unique если используется)
  * `sort_order` (int, default 0)
  * `filters` (jsonb, nullable) — если фильтрация по атрибутам будет data-driven (см. 3.4)

Индексы:

* unique(`slug`)
* index(`product_category_id`)
* index(`is_published`, `published_at`)

#### 3.2.4. news

* Общие поля
* Доп. поля:

  * `excerpt` (text, nullable)
  * `content` (text/longtext)
  * `published_at` используется активно (для сортировки/ленты)
  * `sort_order` (int, default 0)

Индексы:

* unique(`slug`)
* index(`published_at`)
* index(`is_published`, `published_at`)

#### 3.2.5. portfolio_projects

* Общие поля
* Доп. поля:

  * `excerpt` (text, nullable)
  * `content` (text/longtext)
  * `client_name` (string, nullable)
  * `project_date` (date, nullable)
  * `sort_order` (int, default 0)

Индексы:

* unique(`slug`)
* index(`is_published`, `published_at`)

#### 3.2.6. capabilities (производственные возможности)

* Общие поля
* Доп. поля:

  * `description` (text/longtext)
  * `sort_order` (int, default 0)

Индексы:

* unique(`slug`)
* index(`is_published`, `published_at`)

### 3.3. Pivot-таблицы (портфолио ↔ товары/услуги)

Нужно 2 таблицы (проще, чем polymorphic pivot):

#### 3.3.1. portfolio_project_product

* `portfolio_project_id` (FK)
* `product_id` (FK)
* `sort_order` (int, default 0) — порядок показа примеров
* PK составной (`portfolio_project_id`, `product_id`)
* Индексы по обоим FK

#### 3.3.2. portfolio_project_service

* `portfolio_project_id` (FK)
* `service_id` (FK)
* `sort_order` (int, default 0)
* PK составной (`portfolio_project_id`, `service_id`)

Требование:

* В админке должно быть удобное управление связями с обеих сторон:

  * в товаре — “Примеры работ из портфолио”
  * в услуге — то же
  * в работе — выбор товаров и услуг.

### 3.4. Фильтрация каталога (категории с фильтрами)

Нужно заложить структуру под фильтры так, чтобы админ мог управлять без программиста.

Рекомендованный вариант (data-driven фильтры):

* `product_attributes`

  * `title`
  * `slug`
  * `type` enum: `select`, `multiselect`, `number_range`, `boolean`
  * `sort_order`
  * общие поля (по необходимости; публикация не обязательна)
* `product_attribute_values`

  * `product_attribute_id`
  * `value` (string)
  * `sort_order`
* `product_category_attribute` (какие фильтры применимы к категории)

  * `product_category_id`
  * `product_attribute_id`
  * `sort_order`
* `product_attribute_value_product` (значения атрибутов у товара)

  * `product_id`
  * `product_attribute_id`
  * `product_attribute_value_id` (nullable для диапазонов/boolean, тогда хранить в jsonb/columns)
  * (для number_range) `number_value` (numeric, nullable)
  * (для boolean) `bool_value` (bool, nullable)

Минимально допустимый упрощённый вариант (если фильтры простые):

* хранить `products.filters` в jsonb и делать UI в админке без нормализации — **не рекомендуется**, если клиент должен “менять всё” и фильтры будут расширяться.

### 3.5. Меню

#### 3.5.1. menus

* `id`
* `key` (string, unique) — `header`, `footer`
* `title` (string)
* timestamps

#### 3.5.2. menu_items

* `id`
* `menu_id` (FK)
* `parent_id` (FK nullable) — вложенность
* `title` (string)
* `type` enum: `url`, `route`, `model`
* `url` (string nullable)
* `route_name` (string nullable)
* `route_params` (jsonb nullable)
* `linkable_type` (string nullable)
* `linkable_id` (bigint nullable)
* `target_blank` (bool default false)
* `is_visible` (bool default true)
* `sort_order` (int default 0)
* timestamps, soft delete (по желанию)

Индексы:

* index(`menu_id`, `parent_id`)
* index(`linkable_type`, `linkable_id`)

### 3.6. Формы (собственный модуль)

#### 3.6.1. forms

* `id`
* `title` (string)
* `slug` (unique)
* `is_active` (bool default true)
* `success_message` (text nullable)
* `recipients` (jsonb) — список email получателей
* `settings` (jsonb) — произвольные настройки (например: тема письма, reply-to стратегия, webhook URL, включение файлов, включение turnstile и т.п.)
* `created_at`, `updated_at`
* soft delete + userstamps (как у контента)

#### 3.6.2. form_fields

* `id`
* `form_id` (FK)
* `type` enum: `text`, `textarea`, `email`, `phone`, `select`, `multiselect`, `checkbox`, `radio`, `file`, `date`, `number`, `url`
* `name` (string) — системное имя поля (unique в рамках form)
* `label` (string)
* `placeholder` (string nullable)
* `help_text` (text nullable)
* `is_required` (bool default false)
* `options` (jsonb nullable) — варианты для select/radio
* `validation_rules` (text nullable) — строка правил Laravel validation (например `required|email|max:255`)
* `sort_order` (int default 0)
* `is_active` (bool default true)

Индексы:

* unique(`form_id`, `name`)
* index(`form_id`)

#### 3.6.3. form_submissions

* `id`
* `form_id` (FK)
* `payload` (jsonb) — ответы (ключ=field.name)
* `meta` (jsonb) — ip, user_agent, referer, page_url, utm, locale
* `status` enum: `new`, `in_progress`, `done`, `spam`
* `created_at`
* soft delete (не обязательно, но желательно для “удалили в админке” без потери)

Индексы:

* index(`form_id`, `created_at`)
* index(`status`)

#### 3.6.4. form_submission_files (если разрешены файлы)

* `id`
* `form_submission_id` (FK)
* Использовать MediaLibrary для хранения файлов (рекомендуется) либо отдельные колонки `path`, `disk`, `original_name`, `mime`, `size`.
* В админке файлы должны быть доступны к просмотру/скачиванию.

---

## 4) Медиа и требования к alt/title

### 4.1. Spatie MediaLibrary

* Все сущности, которым нужны изображения/файлы, подключают MediaLibrary.
* Для каждого медиаобъекта **обязательны**:

  * `custom_properties.alt` (string)
  * `custom_properties.title` (string)

### 4.2. Реализация в админке (обязательно)

* В Filament формах загрузки медиа:

  * Нельзя сохранить запись/медиа без заполнения alt и title.
  * Должны быть UI-поля для alt/title для каждого изображения (если множественная загрузка — для каждого элемента).
* Для главного изображения/логотипа через Settings — аналогично (alt/title обязательны).

---

## 5) Админ-панель (Filament): структура и функциональные требования

### 5.1. Доступ и безопасность

* Вход только для админов.
* Поддержка ролей/разрешений через spatie/laravel-permission:

  * Роль `super_admin` (полный доступ).
  * Роль `admin` (по умолчанию).
* Возможность “деактивировать” пользователя (`is_active=false`) — запрет входа.

### 5.2. Общие требования ко всем ресурсам (CRUD)

Для ресурсов: товары, услуги, категории, новости, портфолио, capabilities, формы.

* Список (table):

  * Колонки: `title`, `slug`, `is_published`, `published_at`, `updated_at`
  * Доп. колонки: `created_by`, `updated_by`
  * Фильтры:

    * Published / Unpublished
    * Trashed (soft deleted)
    * Has Draft / Draft Changed (если возможно)
  * Быстрые действия:

    * Publish / Unpublish (single и bulk)
    * Restore (для trashed)
    * Delete (soft)
* Форма (create/edit):

  * `title` (required)
  * `slug` (required, auto-generate + ручное редактирование)
  * SEO блок: `seo_title`, `seo_description`
  * Контентные поля (описания, excerpt, etc.)
  * Медиа (с обязательными alt/title)
  * Блок статуса:

    * `is_published`, `published_at`
  * Readonly блок метаданных:

    * `created_at`, `updated_at`, `deleted_at`
    * `created_by`, `updated_by`, `deleted_by`

### 5.3. Черновики в админке (для всех контентных сущностей)

Для каждой сущности:

* Если запись опубликована:

  * Редактирование по умолчанию создаёт/открывает черновик.
  * Кнопки:

    * `Preview Draft` (открыть предпросмотр на фронте; только админы)
    * `Publish Draft`
    * `Discard Draft`
* Если запись не опубликована:

  * Можно редактировать как черновик.
  * `Preview Draft` доступен.
  * `Publish` публикует текущую версию.

### 5.4. Предпросмотр черновиков: реализация в Filament

* В каждом ресурсе добавить действие (Action) `Preview`.
* Action открывает URL предпросмотра фронта.
* Предпросмотр доступен только авторизованным админам (middleware auth).

### 5.5. Ресурс “Главная” через Fabricator

* В админке должен быть раздел “Страницы” (если Fabricator так организован).
* Должна быть страница с ключом/slug `home`.
* Блоки главной:

  * Текстовые блоки, заголовки
  * Списки/карточки (например “услуги”)
  * Галереи/клиенты (логотипы)
  * CTA
  * Любые будущие блоки должны добавляться расширением конфигурации.
* Требование: возможность черновика и предпросмотра “home” аналогично остальным (если Fabricator не покрывает — доработать интеграцию).

### 5.6. Контакты и О нас через Settings (Filament Settings Page)

* Отдельная страница в админке “Настройки сайта”

  * Контактные поля (фиксировано)
  * О нас: WYSIWYG/textarea один блок текста
  * Логотип (медиа) с обязательными alt/title
* Эти настройки не имеют черновиков (если не требуется), но должны быть валидируемыми.

### 5.7. Меню (header/footer)

* Ресурс `Menu` и `MenuItem`:

  * CRUD для меню (обычно 2 записи: header/footer, но допускается больше).
  * Управление пунктами меню:

    * nested (parent/child)
    * drag & drop сортировка (желательно)
    * выбор типа ссылки (url/route/model)
    * для model: выбор сущности из выпадающего списка (товар/категория/услуга/новость/портфолио/страница)
    * is_visible, target_blank
* На фронте меню выводится из БД.

---

## 6) Формы: требования к админке и работе модуля

### 6.1. Конструктор форм

* Ресурс `Forms`:

  * `title`, `slug`, `is_active`
  * recipients (список email)
  * success_message
  * settings (переключатели): включить turnstile, включить honeypot, лимиты rate limit, включить файлы, webhook url, email templates.
* Вложенный менеджер `Fields`:

  * добавление/редактирование полей (тип, label, name, required, options, validation rules)
  * сортировка
  * включение/отключение поля

### 6.2. Входящие заявки

* Ресурс `Submissions`:

  * список заявок по форме
  * фильтр по status и форме
  * просмотр payload “красиво” (таблица label/value)
  * meta (ip, user_agent, url, utm)
  * действия:

    * сменить статус
    * пометить как spam
    * экспорт (csv/xlsx опционально)
    * удалить/восстановить (если soft delete включён)

### 6.3. Защита (обязательна)

На каждом submit:

* Turnstile проверка токена (если включена у формы).
* Honeypot поля и time-trap.
* Rate limit:

  * по IP
  * по форме
  * значения задаются в настройках формы с дефолтами.

### 6.4. Отправка уведомлений

* Email-уведомление на recipients.
* Reply-To: если в форме есть поле email — использовать.
* Шаблон письма: базовый дефолт; опционально редактируемый шаблон (в settings формы).
* Логи отправки (минимум в laravel logs; опционально отдельная таблица).

---

## 7) Требования к качеству реализации

### 7.1. Миграции и ограничения

* Все внешние ключи настроить с понятной политикой:

  * для `*_by` — `SET NULL` при удалении пользователя.
  * для pivot — `CASCADE` при удалении родителя (в soft delete случаях фактического каскада нет, но при force delete важно).
* Индексы для всех поисковых/фильтрационных полей.

### 7.2. Политики и ограничения действий

* Публиковать/снимать с публикации — отдельные permissions.
* Force delete — только super_admin (или вообще запретить, если так безопаснее для “без поддержки”).

### 7.3. Единые компоненты Filament

* Общий Trait/AbstractResource для повторяющихся секций:

  * SEO секция
  * Publishing секция
  * Meta секция (who/when)
  * Soft delete filters/actions

### 7.4. Стабильность и предсказуемость

* Предпросмотр не должен попадать в sitemap.
* Предпросмотр не должен быть доступен без авторизации.
* Валидации на стороне сервера обязательны (Filament validation + Request validation для submit форм).

---

## 8) Критерии приёмки (Definition of Done)

### БД

* Созданы миграции для всех таблиц, описанных выше.
* Настроены FK, индексы, уникальности.
* Все контентные таблицы содержат обязательные общие поля (SEO, slug, publish, userstamps, soft deletes).

### Админ-панель

* Все сущности управляются через Filament: CRUD, soft delete, restore, publish/unpublish.
* Везде отображается кто/когда создал/изменил/удалил.
* Черновики работают: изменения не влияют на опубликованный контент до публикации.
* Предпросмотр черновика доступен только админам и всегда “noindex”.
* Медиа загружается с обязательными alt/title.
* Настройки Контакты/О нас/логотип редактируются через Settings-страницу.
* Меню header/footer редактируется в админке, поддерживает вложенность и разные типы ссылок.
* Формы: админ создаёт форму, добавляет поля, получает заявки, видит список/детали/статусы, включены Turnstile + honeypot + rate limit.

---

## 9) Примечания по реализации (важные решения заранее)

1. Для фильтрации каталога рекомендуется нормализованная схема (3.4), иначе “админ без разработчика” быстро упрётся в ограничения.
2. Для портфолио↔товары/услуги — две pivot таблицы (не polymorphic pivot).
3. Для “About us” и “Contacts” — Settings, без черновиков (если отдельно не попросите).
4. Для предпросмотра: в коде должен быть единый middleware `NoIndexMiddleware`, который ставит заголовки и meta robots.
5. Весь создаваемый код обязан соответствовать требованиям SOLID, DDD, DRY, KISS.
6. Код должен быть покрыт тестами полностью.