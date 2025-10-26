// Исправление рамки изображения директора в Safari с сохранением стильных теней
document.addEventListener('DOMContentLoaded', function() {
    // Находим все изображения директора
    const directorImages = document.querySelectorAll('img[alt*="Анастасия"], img[alt*="Борисова"], img[src*="borisova"], img[src*="Борисова"], img[src*="директор"], img[src*="Директор"]');
    
    directorImages.forEach(function(img) {
        // Принудительно устанавливаем белую рамку
        img.style.border = '1px solid #ffffff';
        img.style.outline = 'none';
        img.style.webkitAppearance = 'none';
        img.style.mozAppearance = 'none';
        img.style.appearance = 'none';
        
        // Применяем стильную тень в стиле Apple
        img.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.08), 0 8px 40px rgba(0, 0, 0, 0.06), 0 16px 80px rgba(0, 0, 0, 0.04)';
        img.style.webkitBoxShadow = '0 4px 20px rgba(0, 0, 0, 0.08), 0 8px 40px rgba(0, 0, 0, 0.06), 0 16px 80px rgba(0, 0, 0, 0.04)';
        
        // Скругленные углы
        img.style.borderRadius = '12px';
        
        // Плавный переход
        img.style.transition = 'all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
        
        // Добавляем hover эффект
        img.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 8px 30px rgba(0, 0, 0, 0.12), 0 16px 60px rgba(0, 0, 0, 0.08), 0 32px 120px rgba(0, 0, 0, 0.06)';
            this.style.webkitBoxShadow = '0 8px 30px rgba(0, 0, 0, 0.12), 0 16px 60px rgba(0, 0, 0, 0.08), 0 32px 120px rgba(0, 0, 0, 0.06)';
        });
        
        img.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.08), 0 8px 40px rgba(0, 0, 0, 0.06), 0 16px 80px rgba(0, 0, 0, 0.04)';
            this.style.webkitBoxShadow = '0 4px 20px rgba(0, 0, 0, 0.08), 0 8px 40px rgba(0, 0, 0, 0.06), 0 16px 80px rgba(0, 0, 0, 0.04)';
        });
        
        // Добавляем класс для дополнительной идентификации
        img.classList.add('director-image-styled');
    });
    
    // Дополнительная проверка для динамически загруженного контента
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    const newImages = node.querySelectorAll ? node.querySelectorAll('img[alt*="Анастасия"], img[alt*="Борисова"], img[src*="borisova"], img[src*="Борисова"], img[src*="директор"], img[src*="Директор"]') : [];
                    newImages.forEach(function(img) {
                        if (!img.classList.contains('director-image-styled')) {
                            // Применяем те же стили
                            img.style.border = '1px solid #ffffff';
                            img.style.outline = 'none';
                            img.style.webkitAppearance = 'none';
                            img.style.mozAppearance = 'none';
                            img.style.appearance = 'none';
                            img.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.08), 0 8px 40px rgba(0, 0, 0, 0.06), 0 16px 80px rgba(0, 0, 0, 0.04)';
                            img.style.webkitBoxShadow = '0 4px 20px rgba(0, 0, 0, 0.08), 0 8px 40px rgba(0, 0, 0, 0.06), 0 16px 80px rgba(0, 0, 0, 0.04)';
                            img.style.borderRadius = '12px';
                            img.style.transition = 'all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
                            img.classList.add('director-image-styled');
                            
                            // Добавляем hover эффект
                            img.addEventListener('mouseenter', function() {
                                this.style.transform = 'translateY(-2px)';
                                this.style.boxShadow = '0 8px 30px rgba(0, 0, 0, 0.12), 0 16px 60px rgba(0, 0, 0, 0.08), 0 32px 120px rgba(0, 0, 0, 0.06)';
                                this.style.webkitBoxShadow = '0 8px 30px rgba(0, 0, 0, 0.12), 0 16px 60px rgba(0, 0, 0, 0.08), 0 32px 120px rgba(0, 0, 0, 0.06)';
                            });
                            
                            img.addEventListener('mouseleave', function() {
                                this.style.transform = 'translateY(0)';
                                this.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.08), 0 8px 40px rgba(0, 0, 0, 0.06), 0 16px 80px rgba(0, 0, 0, 0.04)';
                                this.style.webkitBoxShadow = '0 4px 20px rgba(0, 0, 0, 0.08), 0 8px 40px rgba(0, 0, 0, 0.06), 0 16px 80px rgba(0, 0, 0, 0.04)';
                            });
                        }
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
