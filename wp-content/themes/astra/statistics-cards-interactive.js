// Интерактивные эффекты для статистических карточек
document.addEventListener('DOMContentLoaded', function() {
    // Находим все карточки статистики
    const cards = document.querySelectorAll('.bfkpis__card');
    
    if (cards.length === 0) return;
    
    // Добавляем интерактивность к каждой карточке
    cards.forEach(function(card, index) {
        // Добавляем табуляцию для доступности
        card.setAttribute('tabindex', '0');
        card.setAttribute('role', 'button');
        card.setAttribute('aria-label', 'Статистическая карточка');
        
        // Добавляем обработчики событий
        card.addEventListener('click', function(e) {
            handleCardClick(card, index);
        });
        
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                handleCardClick(card, index);
            }
        });
        
        // Добавляем эффект ripple при клике
        card.addEventListener('mousedown', function(e) {
            createRippleEffect(card, e);
        });
        
        // Добавляем эффект свечения при фокусе
        card.addEventListener('mouseenter', function() {
            addGlowEffect(card);
        });
        
        card.addEventListener('mouseleave', function() {
            removeGlowEffect(card);
        });
        
        // Анимация появления с задержкой
        setTimeout(function() {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            
            requestAnimationFrame(function() {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            });
        }, index * 100);
    });
    
    // Функция обработки клика по карточке
    function handleCardClick(card, index) {
        // Добавляем эффект нажатия
        card.style.transform = 'translateY(-2px) scale(0.98)';
        
        setTimeout(function() {
            card.style.transform = '';
        }, 150);
        
        // Добавляем подсветку
        card.classList.add('highlight');
        
        setTimeout(function() {
            card.classList.remove('highlight');
        }, 2000);
        
        // Логика для разных карточек
        const header = card.querySelector('header');
        if (header) {
            const headerText = header.textContent.trim();
            
            // Можно добавить различную логику для разных карточек
            switch(true) {
                case headerText.includes('Выдано гарантий'):
                    showDetailedStats('volume', card);
                    break;
                case headerText.includes('Клиенты'):
                    showDetailedStats('clients', card);
                    break;
                case headerText.includes('Одобрение'):
                    showDetailedStats('approval', card);
                    break;
                case headerText.includes('Средний срок'):
                    showDetailedStats('timing', card);
                    break;
                case headerText.includes('Повторные'):
                    showDetailedStats('repeat', card);
                    break;
                case headerText.includes('Безштрафное'):
                    showDetailedStats('clean', card);
                    break;
                default:
                    showGeneralInfo(card);
            }
        }
    }
    
    // Создание эффекта ripple
    function createRippleEffect(card, event) {
        const ripple = document.createElement('div');
        const rect = card.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;
        
        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            left: ${x}px;
            top: ${y}px;
            background: rgba(255, 107, 0, 0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
            z-index: 1;
        `;
        
        card.appendChild(ripple);
        
        setTimeout(function() {
            ripple.remove();
        }, 600);
    }
    
    // Добавление эффекта свечения
    function addGlowEffect(card) {
        card.style.boxShadow = '0 0 20px rgba(255, 107, 0, 0.15), 0 10px 15px -3px rgba(0, 0, 0, 0.1)';
    }
    
    function removeGlowEffect(card) {
        card.style.boxShadow = '';
    }
    
    // Показать детальную статистику
    function showDetailedStats(type, card) {
        // Создаем модальное окно или всплывающую подсказку
        const tooltip = createTooltip(card, getStatsInfo(type));
        document.body.appendChild(tooltip);
        
        setTimeout(function() {
            tooltip.remove();
        }, 3000);
    }
    
    // Показать общую информацию
    function showGeneralInfo(card) {
        const tooltip = createTooltip(card, 'Нажмите для получения подробной информации');
        document.body.appendChild(tooltip);
        
        setTimeout(function() {
            tooltip.remove();
        }, 2000);
    }
    
    // Создание всплывающей подсказки
    function createTooltip(card, text) {
        const tooltip = document.createElement('div');
        const rect = card.getBoundingClientRect();
        
        tooltip.style.cssText = `
            position: fixed;
            top: ${rect.top - 50}px;
            left: ${rect.left + rect.width / 2}px;
            transform: translateX(-50%);
            background: #1a1a1a;
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
            max-width: 300px;
            text-align: center;
        `;
        
        tooltip.textContent = text;
        
        // Добавляем стрелку
        const arrow = document.createElement('div');
        arrow.style.cssText = `
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-top: 6px solid #1a1a1a;
        `;
        
        tooltip.appendChild(arrow);
        
        requestAnimationFrame(function() {
            tooltip.style.opacity = '1';
        });
        
        return tooltip;
    }
    
    // Получение информации о статистике
    function getStatsInfo(type) {
        const stats = {
            volume: 'Общая сумма выданных гарантий за последние 12 месяцев. Обновляется ежедневно.',
            clients: 'Количество уникальных клиентов: юридических лиц и индивидуальных предпринимателей.',
            approval: 'Процент одобренных заявок на банковские гарантии за скользящий период 12 месяцев.',
            timing: 'Среднее время от подачи заявки до получения готовой банковской гарантии.',
            repeat: 'Процент клиентов, которые обратились за второй и более гарантией в течение года.',
            clean: 'Процент гарантий, исполненных без штрафных санкций за последние 24 месяца.'
        };
        
        return stats[type] || 'Подробная информация о данном показателе.';
    }
    
    // Добавляем CSS для анимации ripple
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        .bfkpis__card {
            position: relative;
            overflow: hidden;
        }
    `;
    document.head.appendChild(style);
    
    // Добавляем обработчик для скрытия тултипов при клике вне карточек
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.bfkpis__card')) {
            const tooltips = document.querySelectorAll('[style*="position: fixed"]');
            tooltips.forEach(tooltip => tooltip.remove());
        }
    });
});
