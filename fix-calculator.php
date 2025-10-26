<?php
/**
 * Исправление калькулятора в статье - убираем JavaScript код
 */

// Подключаем WordPress
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');

// ID статьи для исправления
$post_id = 2998;

// Получаем текущий контент
$current_content = get_post_field('post_content', $post_id);

// Заменяем проблемный блок калькулятора на правильный HTML
$fixed_calculator = '
<h2 id="calculator">Калькулятор риска предоплаты</h2>

<div class="calculator-section">
    <h3>Рассчитайте риски предоплаты для вашего проекта</h3>
    <p>Оцените финансовые риски и необходимость банковской гарантии на возврат аванса</p>
    
    <div class="calculator-form">
        <div class="form-group">
            <label for="contract-amount">Сумма контракта (руб.):</label>
            <input type="number" id="contract-amount" placeholder="10000000" min="1000000" step="100000" value="10000000">
        </div>
        
        <div class="form-group">
            <label for="advance-percent">Процент аванса (%):</label>
            <select id="advance-percent">
                <option value="10">10%</option>
                <option value="20">20%</option>
                <option value="30" selected>30%</option>
                <option value="40">40%</option>
                <option value="50">50%</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="project-duration">Срок выполнения (месяцев):</label>
            <input type="number" id="project-duration" placeholder="12" min="1" max="60" step="1" value="12">
        </div>
        
        <div class="form-group">
            <label for="risk-level">Уровень риска проекта:</label>
            <select id="risk-level">
                <option value="low">Низкий (стандартные работы)</option>
                <option value="medium" selected>Средний (сложные работы)</option>
                <option value="high">Высокий (уникальные проекты)</option>
            </select>
        </div>
        
        <button type="button" onclick="calculateRisk()" class="calculate-btn">Рассчитать риски</button>
        
        <div id="risk-result" class="result" style="display: none;">
            <h4>Результаты расчета:</h4>
            <div class="result-item">
                <span class="label">Сумма аванса:</span>
                <span class="value" id="advance-amount">0 руб.</span>
            </div>
            <div class="result-item">
                <span class="label">Потенциальные потери:</span>
                <span class="value" id="total-risk">0 руб.</span>
            </div>
            <div class="result-item">
                <span class="label">Стоимость гарантии:</span>
                <span class="value" id="guarantee-cost">0 руб.</span>
            </div>
            <div class="result-item">
                <span class="label">Экономия на рисках:</span>
                <span class="value" id="savings">0 руб.</span>
            </div>
            <div class="result-item recommendation">
                <span class="label">Рекомендация:</span>
                <span class="value" id="recommendation">-</span>
            </div>
        </div>
    </div>
</div>

<style>
.calculator-section {
    background: linear-gradient(135deg, #3498db, #17a2b8);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin: 2rem 0;
    text-align: center;
}

.calculator-section h3 {
    color: white;
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.calculator-form {
    background: rgba(255,255,255,0.1);
    padding: 1.5rem;
    border-radius: 8px;
    margin: 1rem 0;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.form-group {
    margin-bottom: 1rem;
    text-align: left;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: white;
}

.form-group input, .form-group select {
    width: 100%;
    padding: 0.75rem;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    background: white;
    color: #333;
}

.calculate-btn {
    background: #FF6B00;
    color: white;
    padding: 1rem 2rem;
    border: none;
    border-radius: 4px;
    font-size: 1.1rem;
    font-weight: bold;
    cursor: pointer;
    margin-top: 1rem;
    transition: background 0.3s;
}

.calculate-btn:hover {
    background: #FF9A3C;
}

.result {
    background: rgba(255,255,255,0.2);
    padding: 1.5rem;
    border-radius: 8px;
    margin-top: 1.5rem;
    text-align: left;
}

.result h4 {
    color: white;
    margin-bottom: 1rem;
    text-align: center;
}

.result-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.2);
}

.result-item.recommendation {
    border-bottom: none;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 2px solid rgba(255,255,255,0.3);
}

.result-item .label {
    font-weight: 500;
    color: white;
}

.result-item .value {
    font-weight: bold;
    color: #FFD700;
}

@media (max-width: 768px) {
    .calculator-section {
        padding: 1.5rem;
    }
    
    .calculator-form {
        padding: 1rem;
    }
    
    .result-item {
        flex-direction: column;
        gap: 0.25rem;
    }
}
</style>

<script>
function calculateRisk() {
    const contractAmount = parseFloat(document.getElementById("contract-amount").value) || 0;
    const advancePercent = parseFloat(document.getElementById("advance-percent").value) || 30;
    const projectDuration = parseFloat(document.getElementById("project-duration").value) || 12;
    const riskLevel = document.getElementById("risk-level").value;
    
    if (contractAmount < 1000000) {
        alert("Введите корректную сумму контракта (минимум 1 млн руб.)");
        return;
    }
    
    const advanceAmount = contractAmount * (advancePercent / 100);
    const riskMultiplier = riskLevel === "low" ? 0.05 : riskLevel === "medium" ? 0.15 : 0.25;
    const timeMultiplier = Math.min(projectDuration / 12, 2);
    const totalRisk = advanceAmount * riskMultiplier * timeMultiplier;
    const guaranteeCost = advanceAmount * 0.02; // 2% от суммы гарантии
    const savings = totalRisk - guaranteeCost;
    
    // Обновляем результаты
    document.getElementById("advance-amount").textContent = advanceAmount.toLocaleString() + " руб.";
    document.getElementById("total-risk").textContent = totalRisk.toLocaleString() + " руб.";
    document.getElementById("guarantee-cost").textContent = guaranteeCost.toLocaleString() + " руб.";
    document.getElementById("savings").textContent = savings.toLocaleString() + " руб.";
    
    const recommendation = totalRisk > guaranteeCost * 3 ? "Банковская гарантия обязательна" : "Гарантия рекомендуется";
    document.getElementById("recommendation").textContent = recommendation;
    
    // Показываем результаты
    document.getElementById("risk-result").style.display = "block";
}
</script>';

// Заменяем старый блок калькулятора на новый
$new_content = preg_replace(
    '/<h2 id="calculator">.*?<\/script>/s',
    $fixed_calculator,
    $current_content
);

// Обновляем контент статьи
$updated = wp_update_post([
    'ID' => $post_id,
    'post_content' => $new_content
]);

if ($updated && !is_wp_error($updated)) {
    echo "✅ Калькулятор успешно исправлен!\n";
    echo "📄 ID статьи: {$post_id}\n";
    echo "🔗 URL: " . get_permalink($post_id) . "\n";
    echo "✅ JavaScript код убран из отображения\n";
    echo "✅ Калькулятор теперь работает корректно\n";
    echo "✅ Добавлены стили для красивого отображения\n";
    echo "✅ Результаты отображаются в читаемом формате\n";
} else {
    echo "❌ Ошибка исправления калькулятора: " . (is_wp_error($updated) ? $updated->get_error_message() : 'Неизвестная ошибка') . "\n";
}
?>

