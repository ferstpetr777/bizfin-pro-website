# ПОЛНЫЙ СПРАВОЧНИК САЙТОВ И БЭКАПОВ

## 📊 ОБЩАЯ СТАТИСТИКА

**Дата создания:** 21 октября 2025, 06:05 МСК  
**Всего сайтов найдено:** 20  
**Активных WordPress сайтов:** 17  
**Других CMS:** 1 (PrestaShop)  
**Недоступных:** 2  

---

## 🌐 СПИСОК ВСЕХ САЙТОВ

### ✅ АКТИВНЫЕ WORDPRESS САЙТЫ (17)

| № | Сайт | Размер | Путь | Статус бэкапа |
|---|------|--------|------|---------------|
| 1 | ecopackpro.ru | 5.1G | /var/www/fastuser/data/www/ecopackpro.ru | 🔄 В процессе |
| 2 | bizfin-pro.ru | 2.9G | /var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru | ⏳ Ожидает |
| 3 | agregat-nnpro.ru | 1.8G | /var/www/fastuser/data/www/agregat-nnpro.ru | ⏳ Ожидает |
| 4 | letscomebaikal.ru | 1.8G | /var/www/fastuser/data/www/letscomebaikal.ru | ⏳ Ожидает |
| 5 | usvetlany-olkhon.ru | 1.4G | /var/www/fastuser/data/www/usvetlany-olkhon.ru | ⏳ Ожидает |
| 6 | aiuna.ru | 1.5G | /var/www/fastuser/data/www/aiuna.ru | ⏳ Ожидает |
| 7 | lesclubatmosfera.ru | 1.5G | /var/www/fastuser/data/www/lesclubatmosfera.ru | ⏳ Ожидает |
| 8 | geserbaikal.ru | 1.2G | /var/www/geserbaikal__usr/data/www/geserbaikal.ru | ⏳ Ожидает |
| 9 | more-otdyh.ru | 1.1G | /var/www/fastuser/data/www/more-otdyh.ru | ⏳ Ожидает |
| 10 | olkhon-tours.ru | 595M | /var/www/olkhon_tours_usr/data/www/olkhon-tours.ru | ⏳ Ожидает |
| 11 | izumitel.ru | 680M | /var/www/fastuser/data/www/izumitel.ru | ⏳ Ожидает |
| 12 | minihotel-serg13.ru | 472M | /var/www/fastuser/data/www/minihotel-serg13.ru | ⏳ Ожидает |
| 13 | hotel-serg.ru | 442M | /var/www/fastuser/data/www/hotel-serg.ru | ⏳ Ожидает |
| 14 | booking.bochkinhome.ru | 434M | /var/www/fastuser/data/www/booking.bochkinhome.ru | ⏳ Ожидает |
| 15 | ledoplastika.ru | 425M | /var/www/ledoplastika_usr5740/data/www/ledoplastika.ru | ⏳ Ожидает |
| 16 | msaitov.ru | 247M | /var/www/msaitov_ru_usr/data/www/msaitov.ru | ⏳ Ожидает |
| 17 | video-bot.ru | 93M | /var/www/video_bot_ru_usr/data/www/video-bot.ru | ⏳ Ожидает |

### ⚠️ ДРУГИЕ CMS (1)

| № | Сайт | Тип | Размер | Путь | Статус |
|---|------|-----|--------|------|--------|
| 1 | bolshoy-sukhodol.ru | PrestaShop | - | /var/www/bolshoy-sukhodol.ru | ❌ Не WordPress |

### ❌ НЕДОСТУПНЫЕ САЙТЫ (2)

| № | Сайт | Причина | Статус |
|---|------|---------|--------|
| 1 | baikalovostrog.ru | Только бэкапы | ❌ Недоступен |
| 2 | ostrov-olkhone.ru | Пустая директория | ❌ Недоступен |

---

## 🔧 ТЕХНИЧЕСКАЯ ИНФОРМАЦИЯ

### Скрипт последовательного бэкапа
- **Файл:** `/root/backup_all_sites_sequential.sh`
- **Лог:** `/var/log/sequential_backup.log`
- **Принцип работы:** Последовательная обработка каждого сайта с экономией места на сервере

### Алгоритм работы:
1. Создать бэкап сайта
2. Загрузить в облако Mail.ru
3. Проверить целостность в облаке
4. Удалить бэкап с сервера
5. Перейти к следующему сайту

### Облако Mail.ru
- **URL:** https://cloud.mail.ru/
- **Аккаунт:** 1976globus@mail.ru
- **Структура:** `/backups/<DOMAIN>/<YYYYmmdd-HHMM>/`

---

## 📈 ПРОГРЕСС БЭКАПА

**Текущий статус:** 🔄 В процессе  
**Обрабатывается:** ecopackpro.ru (1/17)  
**Время начала:** 21.10.2025 06:05:18  
**Ожидаемое время завершения:** ~2-3 часа  

### Детали процесса:
- ✅ Подключение к облаку Mail.ru установлено
- ✅ Тестовый бэкап прошел успешно
- 🔄 Создание дампа базы данных ecopackpro.ru
- ⏳ Архивирование файлов сайта
- ⏳ Загрузка в облако
- ⏳ Проверка целостности
- ⏳ Удаление локального бэкапа

---

## 📋 СТРУКТУРА БЭКАПА

Каждый бэкап содержит:
- `db.sql` - Дамп базы данных
- `site.tar.gz` - Архив файлов сайта (без cache и uploads)
- `uploads.tar.gz` - Архив загруженных файлов
- `backup_info.md` - Информация о бэкапе
- `checksums.sha256` - Хэши для проверки целостности

---

## 🚀 КОМАНДЫ ДЛЯ МОНИТОРИНГА

```bash
# Проверка прогресса
tail -f /var/log/sequential_backup.log

# Проверка запущенных процессов
ps aux | grep backup_all_sites_sequential

# Проверка места на диске
df -h /tmp

# Проверка бэкапов в облаке
curl -X PROPFIND "https://webdav.cloud.mail.ru/backups/" \
  -H "Authorization: Basic $(echo -n "1976globus@mail.ru:rgJ0PdE421eR8mTCzus8" | base64)"
```

---

## 📞 ПОДДЕРЖКА

При возникновении проблем:
1. Проверьте лог файл: `/var/log/sequential_backup.log`
2. Убедитесь в наличии места на диске: `df -h`
3. Проверьте подключение к интернету
4. Проверьте учетные данные Mail.ru Cloud

---

**📅 Создано:** 21 октября 2025, 06:05 МСК  
**🔄 Последнее обновление:** 21 октября 2025, 06:05 МСК  
**📊 Статус:** Активный мониторинг

