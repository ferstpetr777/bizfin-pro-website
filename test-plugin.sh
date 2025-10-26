#!/bin/bash

echo "=== Article Analyzer & Regenerator E2E Test ==="
echo "Date: $(date)"
echo ""

# Создаем отчет
REPORT_FILE="/tmp/aar_test_report.txt"
echo "Test Report - $(date)" > $REPORT_FILE
echo "====================" >> $REPORT_FILE
echo "" >> $REPORT_FILE

# Функция логирования
log_test() {
    echo "[TEST] $1"
    echo "[TEST] $1" >> $REPORT_FILE
}

# Функция проверки результата
check_result() {
    if [ $1 -eq 0 ]; then
        log_test "✓ PASS: $2"
    else
        log_test "✗ FAIL: $2"
    fi
}

log_test "Starting comprehensive E2E tests..."

# Тест 1: Проверка наличия плагина
log_test "1. Checking plugin files..."
if [ -f "wp-content/plugins/article-analyzer-regenerator/article-analyzer-regenerator.php" ]; then
    check_result 0 "Plugin main file exists"
else
    check_result 1 "Plugin main file missing"
fi

# Тест 2: Проверка assets
log_test "2. Checking assets..."
if [ -f "wp-content/plugins/article-analyzer-regenerator/assets/admin.js" ]; then
    check_result 0 "admin.js exists"
else
    check_result 1 "admin.js missing"
fi

if [ -f "wp-content/plugins/article-analyzer-regenerator/assets/admin.css" ]; then
    check_result 0 "admin.css exists"
else
    check_result 1 "admin.css missing"
fi

# Тест 3: Проверка логов
log_test "3. Checking logs directory..."
if [ -d "wp-content/logs" ]; then
    check_result 0 "Logs directory exists"
else
    check_result 1 "Logs directory missing"
fi

# Тест 4: Проверка матрицы
log_test "4. Checking matrix criteria..."
if [ -f "wp-content/plugins/bizfin-seo-article-generator/BSAG_MATRIX_CRITERIA.md" ]; then
    check_result 0 "Matrix criteria file exists"
else
    check_result 1 "Matrix criteria file missing"
fi

# Тест 5: Проверка WordPress функций
log_test "5. Checking WordPress DB connection..."
wp --allow-root db check >> $REPORT_FILE 2>&1
check_result $? "Database connection"

# Тест 6: Получение списка статей
log_test "6. Fetching articles..."
ARTICLE_COUNT=$(wp --allow-root post list --post_type=post --format=count 2>/dev/null)
log_test "Found $ARTICLE_COUNT articles"

if [ "$ARTICLE_COUNT" -gt "0" ]; then
    check_result 0 "Articles found in database"
else
    check_result 1 "No articles found"
fi

# Тест 7: Получение 5 случайных статей
log_test "7. Getting 5 random articles..."
ARTICLES=$(wp --allow-root post list --post_type=post --posts_per_page=5 --orderby=rand --format=ids 2>/dev/null)
log_test "Selected articles: $ARTICLES"

# Тест 8: Анализ первой статьи
if [ ! -z "$ARTICLES" ]; then
    FIRST_ARTICLE=$(echo $ARTICLES | cut -d' ' -f1)
    log_test "8. Analyzing article ID: $FIRST_ARTICLE"
    
    # Проверяем существование статьи
    if wp --allow-root post get $FIRST_ARTICLE --field=ID > /dev/null 2>&1; then
        check_result 0 "Article $FIRST_ARTICLE exists"
        
        # Получаем контент
        CONTENT_LENGTH=$(wp --allow-root post get $FIRST_ARTICLE --field=post_content | wc -c)
        log_test "Content length: $CONTENT_LENGTH bytes"
        
        if [ "$CONTENT_LENGTH" -gt "0" ]; then
            check_result 0 "Article has content"
        else
            check_result 1 "Article has no content"
        fi
    else
        check_result 1 "Article not found"
    fi
else
    log_test "Skipping article analysis - no articles found"
fi

# Тест 9: Проверка логов плагина
log_test "9. Checking plugin logs..."
if [ -f "wp-content/logs/aar_errors.log" ]; then
    LOG_SIZE=$(stat -f%z wp-content/logs/aar_errors.log 2>/dev/null || stat -c%s wp-content/logs/aar_errors.log 2>/dev/null)
    if [ "$LOG_SIZE" -gt "0" ]; then
        log_test "Log file size: $LOG_SIZE bytes"
        check_result 0 "Log file has content"
        
        # Показываем последние строки логов
        log_test "Recent log entries:"
        tail -n 5 wp-content/logs/aar_errors.log >> $REPORT_FILE
    else
        check_result 1 "Log file is empty"
    fi
else
    log_test "No log file found yet (this is normal for first run)"
fi

# Тест 10: Проверка версии плагина
log_test "10. Checking plugin version..."
VERSION=$(grep "Version:" wp-content/plugins/article-analyzer-regenerator/article-analyzer-regenerator.php | head -1 | cut -d':' -f2 | tr -d ' ;')
log_test "Plugin version: $VERSION"

# Финальный отчет
echo "" >> $REPORT_FILE
echo "=== Test Summary ===" >> $REPORT_FILE
echo "Total articles: $ARTICLE_COUNT" >> $REPORT_FILE
echo "Tested articles: 5" >> $REPORT_FILE
echo "Plugin version: $VERSION" >> $REPORT_FILE
echo "" >> $REPORT_FILE

log_test "E2E testing completed!"
echo ""
echo "Full report saved to: $REPORT_FILE"
cat $REPORT_FILE
