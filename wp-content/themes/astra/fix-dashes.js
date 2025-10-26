// Скрипт для замены длинных тире на обычные дефисы
document.addEventListener('DOMContentLoaded', function() {
    // Проверяем, что мы на странице статьи
    if (document.body.classList.contains('single-post')) {
        // Находим контент статьи
        const content = document.querySelector('.entry-content') || 
                       document.querySelector('.post-content') || 
                       document.querySelector('article') || 
                       document.querySelector('main');
        
        if (content) {
            // Заменяем длинные тире на обычные дефисы
            content.innerHTML = content.innerHTML.replace(/—/g, '-');
            content.innerHTML = content.innerHTML.replace(/&#8212;/g, '-');
        }
    }
});
