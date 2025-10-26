// JavaScript для исправления ТОЛЬКО статистических блоков - НЕ ГЛОБАЛЬНО
document.addEventListener('DOMContentLoaded', function() {
    // Находим ТОЛЬКО числовые элементы в статистических карточках
    const statsCards = document.querySelectorAll('.bfkpis__card');
    
    statsCards.forEach(function(card) {
        const numberElements = card.querySelectorAll('.num, .unit');
        
        numberElements.forEach(function(element) {
            // Убираем тени ТОЛЬКО с чисел в статистических блоках
            element.style.textShadow = 'none';
            element.style.webkitTextShadow = 'none';
            element.style.boxShadow = 'none';
            element.style.webkitBoxShadow = 'none';
            element.style.filter = 'none';
        });
        
        // Улучшаем контрастность ТОЛЬКО заголовков в статистических блоках
        const header = card.querySelector('header');
        if (header) {
            header.style.fontWeight = '700';
            header.style.color = '#000000';
            header.style.textShadow = '0 1px 2px rgba(0, 0, 0, 0.1)';
        }
    });
    
    // Мониторим ТОЛЬКО статистические блоки
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    const newStatsCards = node.querySelectorAll ? node.querySelectorAll('.bfkpis__card') : [];
                    newStatsCards.forEach(function(card) {
                        const numberElements = card.querySelectorAll('.num, .unit');
                        numberElements.forEach(function(element) {
                            element.style.textShadow = 'none';
                            element.style.webkitTextShadow = 'none';
                            element.style.boxShadow = 'none';
                            element.style.webkitBoxShadow = 'none';
                            element.style.filter = 'none';
                        });
                    });
                }
            });
        });
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});
