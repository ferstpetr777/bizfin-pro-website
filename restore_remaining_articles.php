<?php
require_once('wp-config.php');

// Массив статей с их правильными ревизиями
$articles_to_restore = [
    2527 => 4863, 2526 => 4864, 2525 => 4865, 2524 => 4866, 2523 => 4867, 2522 => 4868, 2520 => 4870, 2519 => 4871, 2518 => 4872, 2517 => 4873, 2516 => 4874, 2515 => 4875, 2513 => 4877, 2512 => 4878, 2510 => 4880, 2508 => 4882, 2506 => 4884, 2505 => 4885, 2504 => 4886, 2503 => 4887, 2502 => 4888, 2501 => 4889, 2499 => 4891, 2498 => 4892, 2497 => 4893, 2496 => 4894, 2495 => 4895, 2494 => 4896, 2493 => 4897, 2492 => 4898, 2490 => 4900, 2489 => 4901, 2487 => 4903, 2486 => 4904, 2485 => 4905, 2484 => 4906, 2482 => 4908, 2481 => 4909, 2478 => 4912, 2477 => 4913, 2476 => 4914, 2474 => 4916, 2473 => 4917, 2470 => 4920, 2468 => 4922, 2466 => 4924, 2465 => 4925, 2463 => 4927, 2461 => 4929, 2460 => 4930, 2456 => 4934, 2455 => 4935, 2454 => 4936, 2452 => 4938, 2450 => 4940, 2449 => 4941, 2448 => 4942, 2445 => 4945, 2444 => 4946, 2442 => 4948, 2441 => 4949, 2440 => 4950, 2439 => 4951, 2437 => 4953, 2436 => 4954, 2435 => 4955, 2434 => 4956, 2433 => 4957, 2432 => 4958, 2430 => 4960, 2429 => 4961, 2428 => 4962, 2426 => 4964, 2425 => 4965, 2423 => 4966, 2422 => 4967, 2421 => 4968, 2420 => 4969, 2046 => 4971
];

echo "=== ВОССТАНОВЛЕНИЕ ОСТАВШИХСЯ СТАТЕЙ ===\n";
echo "Всего статей для восстановления: " . count($articles_to_restore) . "\n\n";

$success_count = 0;
$error_count = 0;
$results = [];

foreach ($articles_to_restore as $post_id => $revision_id) {
    echo "Восстановление статьи ID: $post_id (ревизия: $revision_id)\n";
    
    try {
        // Получаем текущую статью
        $current_post = get_post($post_id);
        if (!$current_post) {
            echo "  ❌ Статья не найдена\n";
            $error_count++;
            $results[] = "ID $post_id: Статья не найдена";
            continue;
        }
        
        // Получаем ревизию
        $revision = get_post($revision_id);
        if (!$revision) {
            echo "  ❌ Ревизия не найдена\n";
            $error_count++;
            $results[] = "ID $post_id: Ревизия $revision_id не найдена";
            continue;
        }
        
        echo "  📄 Заголовок: " . substr($current_post->post_title, 0, 50) . "...\n";
        echo "  📊 Текущая длина: " . strlen($current_post->post_content) . " символов\n";
        echo "  📊 Длина ревизии: " . strlen($revision->post_content) . " символов\n";
        
        // Анализируем структуру текущей статьи
        $current_content = $current_post->post_content;
        $intro_section_end = strpos($current_content, '</section>');
        
        $new_content = '';
        if ($intro_section_end !== false) {
            // Сохраняем структуру до конца intro-section
            $structure_part = substr($current_content, 0, $intro_section_end);
            $new_content = $structure_part . "\n\n" . $revision->post_content;
        } else {
            // Если структура не найдена, заменяем весь контент
            $new_content = $revision->post_content;
        }
        
        // Обновляем статью
        $update_result = wp_update_post(array(
            'ID' => $post_id,
            'post_content' => $new_content
        ));
        
        if ($update_result && !is_wp_error($update_result)) {
            echo "  ✅ Успешно восстановлена! Новая длина: " . strlen($new_content) . " символов\n";
            $success_count++;
            $results[] = "ID $post_id: Успешно восстановлена";
        } else {
            echo "  ❌ Ошибка при обновлении\n";
            if (is_wp_error($update_result)) {
                echo "  🔍 Ошибка: " . $update_result->get_error_message() . "\n";
            }
            $error_count++;
            $results[] = "ID $post_id: Ошибка при обновлении";
        }
        
    } catch (Exception $e) {
        echo "  ❌ Исключение: " . $e->getMessage() . "\n";
        $error_count++;
        $results[] = "ID $post_id: Исключение - " . $e->getMessage();
    }
    
    echo "\n";
    
    // Пауза каждые 10 статей
    if (($success_count + $error_count) % 10 == 0) {
        echo "⏸️ Пауза 2 секунды...\n";
        sleep(2);
    }
}

// Очищаем кеш
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo "🧹 Кеш очищен\n";
}

echo "\n=== ИТОГОВАЯ СТАТИСТИКА ===\n";
echo "✅ Успешно восстановлено: $success_count\n";
echo "❌ Ошибок: $error_count\n";
echo "📊 Всего обработано: " . count($articles_to_restore) . "\n\n";

echo "=== ДЕТАЛЬНЫЕ РЕЗУЛЬТАТЫ ===\n";
foreach ($results as $result) {
    echo $result . "\n";
}

echo "\n=== ЗАВЕРШЕНО ===\n";
?>

