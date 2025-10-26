#!/bin/bash

echo "=== Testing AJAX Endpoints ==="
echo ""

# Получаем nonce
NONCE=$(wp --allow-root eval 'echo wp_create_nonce("aar_ajax_nonce");' 2>/dev/null)
echo "Nonce: $NONCE"
echo ""

# Тест 1: Получение статей
echo "Test 1: Fetching articles..."
curl -X POST "https://bizfin-pro.ru/wp-admin/admin-ajax.php" \
  -d "action=aar_get_articles" \
  -d "nonce=$NONCE" \
  --silent | jq '.' > /tmp/articles_response.json

if [ $? -eq 0 ]; then
    echo "✓ Articles fetched successfully"
    cat /tmp/articles_response.json | head -20
else
    echo "✗ Failed to fetch articles"
fi
echo ""

# Тест 2: Получение ID первой статьи
FIRST_ARTICLE_ID=$(cat /tmp/articles_response.json | jq '.data[0].id' 2>/dev/null)
echo "First article ID: $FIRST_ARTICLE_ID"
echo ""

# Тест 3: Анализ статьи
if [ ! -z "$FIRST_ARTICLE_ID" ] && [ "$FIRST_ARTICLE_ID" != "null" ]; then
    echo "Test 3: Analyzing article $FIRST_ARTICLE_ID..."
    curl -X POST "https://bizfin-pro.ru/wp-admin/admin-ajax.php" \
      -d "action=aar_analyze_article" \
      -d "nonce=$NONCE" \
      -d "post_id=$FIRST_ARTICLE_ID" \
      --silent | jq '.' > /tmp/analysis_response.json
    
    if [ $? -eq 0 ]; then
        echo "✓ Article analyzed successfully"
        cat /tmp/analysis_response.json | head -30
    else
        echo "✗ Failed to analyze article"
    fi
fi
echo ""

# Тест 4: Проверка логов
echo "Test 4: Checking logs..."
if [ -f "wp-content/logs/aar_errors.log" ]; then
    echo "Recent log entries:"
    tail -n 10 wp-content/logs/aar_errors.log
else
    echo "No log file found"
fi
echo ""

echo "=== Testing Complete ==="
