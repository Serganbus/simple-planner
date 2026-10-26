# Personal Planner

Персональный планировщик дня — CLI-инструмент на PHP (Symfony Console). Позволяет вести план на каждый день: расписание с привязкой ко времени, гибкие задачи с оценкой в минутах и приоритетом, главную цель дня, теги и статистику выполнения.

Данные хранятся в JSON-файлах — по одному файлу на день (`<дата>.json`).

## Требования

- PHP >= 8.4
- Composer

## Установка

```bash
composer install
```

Запуск:

```bash
php bin/planner <команда>
```

По умолчанию данные сохраняются в каталог `data/`. Путь можно переопределить переменной окружения `PLANNER_DATA_PATH`:

```bash
# Windows (PowerShell)
$env:PLANNER_DATA_PATH = "C:\path\to\data"

# Linux/macOS
export PLANNER_DATA_PATH=/path/to/data
```

## Основные понятия

- **Day (день)** — план на конкретную дату (`YYYY-MM-DD`) с необязательной главной целью (`main goal`).
- **Schedule item (элемент расписания)** — дело с фиксированным временем начала и окончания (`HH:MM`).
- **Flexible task (гибкая задача)** — задача без привязки ко времени, с оценкой в минутах и приоритетом (`high`, `medium`, `low`).
- У элементов расписания и задач есть **статус** (`pending` / `done`) и произвольные **теги**.

## Команды

Если дата не указана (аргумент `date` или опция `--date`), используется сегодняшняя.

### Работа с днём

```bash
# Создать план на день
php bin/planner day:create 2026-10-27 --goal "Разобрать бэклог"

# Показать план на день
php bin/planner day:show 2026-10-27
php bin/planner day:show --schedule            # только расписание
php bin/planner day:show --tasks               # только гибкие задачи
php bin/planner day:show --tag work            # фильтр по тегу
php bin/planner day:show --pending             # только невыполненные
php bin/planner day:show --format json         # вывод в JSON

# Статистика за день
php bin/planner day:stats 2026-10-27
```

### Расписание

```bash
# Добавить элемент расписания
php bin/planner schedule:add --date 2026-10-27 --start 09:00 --end 10:00 \
    --title "Планёрка" --tags "work,meeting"

# Изменить элемент (id выводится командами day:show и schedule:add)
php bin/planner schedule:edit <id> --date 2026-10-27 --start 10:00 --title "Новое название"

# Удалить элемент
php bin/planner schedule:remove <id> --date 2026-10-27

# Отметить выполненным / вернуть в ожидание
php bin/planner schedule:done <id> --date 2026-10-27
php bin/planner schedule:undone <id> --date 2026-10-27
```

### Гибкие задачи

```bash
# Добавить задачу (обязательны --title и --minutes)
php bin/planner task:add --date 2026-10-27 --title "Написать отчёт" \
    --minutes 60 --priority high --tags "work,writing"

# Изменить задачу
php bin/planner task:edit <id> --date 2026-10-27 --minutes 90 --priority medium

# Удалить задачу
php bin/planner task:remove <id> --date 2026-10-27

# Отметить выполненной / вернуть в ожидание
php bin/planner task:done <id> --date 2026-10-27
php bin/planner task:undone <id> --date 2026-10-27
```

### Прочее

```bash
# Список всех уникальных тегов по всем дням
php bin/planner util:tags
```

Полную справку по любой команде можно получить через `php bin/planner help <команда>`.

## Структура проекта

```
bin/planner                     Точка входа CLI-приложения
data/                           Данные (JSON-файлы по дням)
src/
  Domain/
    Model/                      Day, ScheduleItem, FlexibleTask
    Repository/                 DayRepositoryInterface
    Service/                    PlannerService (бизнес-логика)
  Infrastructure/
    Repository/                 JsonDayRepository (JSON-хранилище)
    Console/Command/            Команды: Day, Schedule, Task, Util
tests/                          Unit-тесты (PHPUnit)
```

## Тестирование

Тесты — PHPUnit 13, запуск:

```bash
composer test
```

или напрямую:

```bash
vendor/bin/phpunit        # Linux/macOS
vendor\bin\phpunit        # Windows
```

Отдельный набор тестов:

```bash
composer test -- --filter PlannerServiceTest
```
