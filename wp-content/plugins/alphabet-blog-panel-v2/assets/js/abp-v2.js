(function() {
  'use strict';
  
  // Проверяем наличие необходимых элементов
  const root = document.getElementById('abp-v2-root');
  const alphabetOnly = document.querySelector('.abp-v2-alphabet-only');
  
  if (!root && !alphabetOnly) return;

  // Получаем элементы
  const letters = root ? root.querySelectorAll('.abp-v2-letter') : document.querySelectorAll('.abp-v2-alphabet-only .abp-v2-letter');
  const listEl = document.getElementById('abp-v2-list');
  const loader = root ? root.querySelector('.abp-v2-loader') : null;
  const pager = root ? root.querySelector('.abp-v2-pagination') : null;

  let currentLetter = null;
  let currentPage = 1;

  // Показываем/скрываем лоадер
  function setLoading(show) {
    if (loader) {
      if (show) {
        loader.style.display = 'flex';
        if (listEl) listEl.style.opacity = '0.5';
      } else {
        loader.style.display = 'none';
        if (listEl) listEl.style.opacity = '1';
      }
    }
  }

  // Обновляем активную букву
  function updateActiveLetter(letter) {
    letters.forEach(l => l.classList.remove('is-active'));
    const activeLetter = (root || alphabetOnly).querySelector(`[data-letter="${CSS.escape(letter)}"]`);
    if (activeLetter) activeLetter.classList.add('is-active');
    currentLetter = letter;
  }

  // Создаем пагинацию
  function renderPagination(pagination) {
    if (!pager) return;
    
    if (!pagination || pagination.total <= 1) {
      pager.style.display = 'none';
      return;
    }

    pager.style.display = 'flex';
    pager.innerHTML = '';

    // Предыдущая страница
    const prevBtn = document.createElement('button');
    prevBtn.className = 'abp-v2-page';
    prevBtn.textContent = '‹';
    prevBtn.disabled = !pagination.has_prev;
    if (pagination.has_prev) {
      prevBtn.addEventListener('click', () => loadPosts(currentLetter, pagination.current - 1));
    }
    pager.appendChild(prevBtn);

    // Информация о странице
    const info = document.createElement('span');
    info.className = 'abp-v2-page-info';
    info.textContent = `Стр. ${pagination.current} из ${pagination.total}`;
    pager.appendChild(info);

    // Следующая страница
    const nextBtn = document.createElement('button');
    nextBtn.className = 'abp-v2-page';
    nextBtn.textContent = '›';
    nextBtn.disabled = !pagination.has_next;
    if (pagination.has_next) {
      nextBtn.addEventListener('click', () => loadPosts(currentLetter, pagination.current + 1));
    }
    pager.appendChild(nextBtn);
  }

  // Загружаем посты
  async function loadPosts(letter, page = 1, pushState = true) {
    if (!letter) return;
    
    // Если мы на странице статьи - не загружаем посты
    if (document.body.classList.contains('single-post')) {
      return;
    }

    currentLetter = letter;
    currentPage = page;
    setLoading(true);

    const formData = new FormData();
    formData.append('action', 'abp_v2_fetch_posts');
    formData.append('nonce', window.ABP_V2.nonce);
    formData.append('letter', letter);
    formData.append('page', page);

    try {
      const response = await fetch(window.ABP_V2.ajax, {
        method: 'POST',
        body: formData
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const data = await response.json();
      
      if (!data.success) {
        throw new Error(data.data?.message || 'Ошибка ответа сервера');
      }

      // Обновляем контент
      if (listEl) {
        listEl.innerHTML = data.data.html;
      }
      renderPagination(data.data.pagination);

      // Обновляем URL
      if (pushState) {
        const url = `/${window.ABP_V2.slug}2/${encodeURIComponent(letter)}/`;
        window.history.pushState({ letter, page }, '', url);
        document.title = `Блог — буква «${letter}» | BizFin Pro`;
      }

    } catch (error) {
      console.error('ABP v2 Error:', error);
      if (listEl) {
        listEl.innerHTML = '<div class="abp-v2-empty">Ошибка загрузки. Попробуйте обновить страницу.</div>';
      }
      if (pager) {
        pager.style.display = 'none';
      }
    } finally {
      setLoading(false);
    }
  }

  // Обработчики событий для букв
  letters.forEach(letter => {
    letter.addEventListener('click', (e) => {
      if (letter.dataset.disabled === 'true') return;
      
      const letterChar = letter.getAttribute('data-letter');
      
      // Если мы на странице статьи - переходим в блог
      if (document.body.classList.contains('single-post')) {
        window.location.href = `/${window.ABP_V2.slug}2/${encodeURIComponent(letterChar)}/`;
        return;
      }
      
      // Если мы в блоге - загружаем статьи
      updateActiveLetter(letterChar);
      loadPosts(letterChar, 1, true);
    });
  });

  // Поддержка навигации браузера
  window.addEventListener('popstate', (e) => {
    if (e.state && e.state.letter) {
      const { letter, page } = e.state;
      updateActiveLetter(letter);
      loadPosts(letter, page || 1, false);
    }
  });

  // Инициализация
  function initialize() {
    // Если мы на странице статьи - только обновляем активную букву
    if (document.body.classList.contains('single-post')) {
      // Определяем букву из URL статьи
      const pathMatch = window.location.pathname.match(new RegExp(`/${window.ABP_V2.slug}2/([^/]+)/?`));
      const urlLetter = pathMatch ? decodeURIComponent(pathMatch[1]) : null;
      
      if (urlLetter) {
        updateActiveLetter(urlLetter);
      }
      return;
    }

    // Проверяем URL для определения начального состояния
    const pathMatch = window.location.pathname.match(new RegExp(`/${window.ABP_V2.slug}2/([^/]+)/?`));
    const urlLetter = pathMatch ? decodeURIComponent(pathMatch[1]) : null;

    let startLetter = urlLetter;
    
    if (!startLetter) {
      // Выбираем первую доступную букву
      const firstAvailable = (root || alphabetOnly).querySelector('.abp-v2-letter:not([data-disabled="1"])');
      startLetter = firstAvailable ? firstAvailable.getAttribute('data-letter') : null;
    }

    if (startLetter) {
      updateActiveLetter(startLetter);
      loadPosts(startLetter, 1, !!urlLetter);
    } else {
      if (listEl) {
        listEl.innerHTML = '<div class="abp-v2-empty">В блоге пока нет опубликованных статей.</div>';
      }
    }
  }

  // Запускаем инициализацию
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize);
  } else {
    initialize();
  }

  // Создаем объект ABP_V2 для совместимости
  window.ABP_V2 = {
    ajax: window.ABP_V2 ? window.ABP_V2.ajax : '/wp-admin/admin-ajax.php',
    nonce: window.ABP_V2 ? window.ABP_V2.nonce : '',
    slug: 'blog'
  };

  // Функция инициализации поиска и алфавита
  function initializeSearch() {
    const searchInput = document.getElementById('abp-v2-search-input');
    const searchBtn = document.getElementById('abp-v2-search-btn');
    
    if (!searchInput || !searchBtn) return;
    
    // Инициализируем алфавитную навигацию на главной странице
    initializeMainPageAlphabet();
    
    // Обработчик клика по кнопке поиска
    searchBtn.addEventListener('click', function() {
      const query = searchInput.value.trim();
      if (query) {
        performSearch(query);
      }
    });
    
    // Обработчик нажатия Enter в поле поиска
    searchInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        const query = searchInput.value.trim();
        if (query) {
          performSearch(query);
        }
      }
    });
  }
  
  // Функция выполнения поиска
  async function performSearch(query) {
    console.log('Searching for:', query);
    
    // Показываем индикатор загрузки
    const searchBtn = document.getElementById('abp-v2-search-btn');
    if (searchBtn) {
      searchBtn.textContent = '⏳';
      searchBtn.disabled = true;
    }
    
    try {
      // Выполняем поиск через AJAX
      const form = new FormData();
      form.append('action', 'abp_v2_search');
      form.append('nonce', window.ABP_V2.nonce);
      form.append('query', query);
      
      const response = await fetch(window.ABP_V2.ajax, {
        method: 'POST',
        body: form
      });
      
      const json = await response.json();
      
      if (json.success) {
        // Обновляем контент результатами поиска
        updateSearchResults(json.data);
      } else {
        console.error('Search error:', json.data);
        alert('Ошибка поиска: ' + (json.data.message || 'Неизвестная ошибка'));
      }
    } catch (error) {
      console.error('Search request failed:', error);
      alert('Ошибка при выполнении поиска');
    } finally {
      // Восстанавливаем кнопку поиска
      if (searchBtn) {
        searchBtn.textContent = '🔍';
        searchBtn.disabled = false;
      }
    }
  }
  
  // Функция обновления результатов поиска
  function updateSearchResults(data) {
    const postsGrid = document.querySelector('.abp-v2-main-posts-grid');
    const postsHeader = document.querySelector('.abp-v2-main-posts-header h2');
    const postsCount = document.querySelector('.abp-v2-main-posts-count');
    
    if (!postsGrid) return;
    
    // Обновляем заголовок
    if (postsHeader) {
      postsHeader.textContent = `Результаты поиска по запросу «${data.query}»`;
    }
    
    // Обновляем счетчик
    if (postsCount) {
      postsCount.textContent = `Найдено: ${data.count} статей`;
    }
    
    // Обновляем контент
    if (data.posts && data.posts.length > 0) {
      postsGrid.innerHTML = data.posts.map(post => {
        const thumbnailHtml = post.thumbnail ? 
          `<div class="abp-v2-main-post-thumbnail">
            <a href="${post.permalink}">
              <img src="${post.thumbnail}" alt="${post.title}" loading="lazy">
            </a>
          </div>` : '';
        
        return `
          <article class="abp-v2-main-post-card">
            ${thumbnailHtml}
            <h3 class="abp-v2-main-post-title">
              <a href="${post.permalink}">${post.title}</a>
            </h3>
            <div class="abp-v2-main-post-excerpt">
              ${post.excerpt}
            </div>
            <div class="abp-v2-main-post-meta">
              <time datetime="${post.date}">${post.date_formatted}</time>
            </div>
          </article>
        `;
      }).join('');
    } else {
      postsGrid.innerHTML = `
        <div class="abp-v2-main-no-posts">
          <p>По запросу «${data.query}» ничего не найдено.</p>
        </div>
      `;
    }
  }

  // Экспортируем функции для внешнего использования
  window.ABP_V2_API = {
    loadPosts,
    updateActiveLetter,
    initializeSearch,
    performSearch,
  };

  // Функция инициализации алфавитной навигации на главной странице
  function initializeMainPageAlphabet() {
    const alphabetButtons = document.querySelectorAll('.abp-v2-main-alphabet .abp-v2-letter');
    
    alphabetButtons.forEach(button => {
      button.addEventListener('click', function(e) {
        e.preventDefault();
        
        const letter = this.getAttribute('data-letter');
        if (!letter) return;
        
        // Убираем активный класс со всех кнопок
        alphabetButtons.forEach(btn => btn.classList.remove('is-active'));
        
        // Добавляем активный класс к нажатой кнопке
        this.classList.add('is-active');
        
        // Загружаем статьи для выбранной буквы
        loadMainPagePosts(letter);
      });
    });
  }
  
  // Функция загрузки постов для главной страницы
  async function loadMainPagePosts(letter) {
    console.log('Loading posts for letter:', letter);
    
    try {
      const form = new FormData();
      form.append('action', 'abp_v2_fetch_posts');
      form.append('nonce', window.ABP_V2.nonce);
      form.append('letter', letter);
      form.append('page', '1');
      
      const response = await fetch(window.ABP_V2.ajax, {
        method: 'POST',
        body: form
      });
      
      const json = await response.json();
      console.log('AJAX response:', json);
      
      if (json.success) {
        // Обновляем заголовок
        const postsHeader = document.querySelector('.abp-v2-main-posts-header h2');
        if (postsHeader) {
          postsHeader.textContent = `Статьи на букву «${letter}»`;
        }
        
        // Обновляем счетчик
        const postsCount = document.querySelector('.abp-v2-main-posts-count');
        if (postsCount) {
          // Считаем количество статей из HTML
          const tempDiv = document.createElement('div');
          tempDiv.innerHTML = json.data.html;
          const items = tempDiv.querySelectorAll('.abp-item');
          const count = items.length;
          
          postsCount.textContent = `Найдено: ${count} статей`;
          console.log('Found articles count:', count);
        }
        
        // Обновляем контент
        const postsGrid = document.querySelector('.abp-v2-main-posts-grid');
        if (postsGrid) {
          // Преобразуем HTML в формат карточек
          const tempDiv = document.createElement('div');
          tempDiv.innerHTML = json.data.html;
          
          const items = tempDiv.querySelectorAll('.abp-item');
          const cardsHtml = Array.from(items).map(item => {
            const link = item.querySelector('.abp-link');
            if (!link) return '';
            
            const title = link.textContent;
            const href = link.href;
            const excerpt = item.querySelector('.abp-excerpt') ? 
              item.querySelector('.abp-excerpt').textContent : '';
            const date = item.querySelector('.abp-date') ? 
              item.querySelector('.abp-date').textContent : '';
            
            // Извлекаем миниатюру
            const thumbnail = item.querySelector('.abp-thumbnail img');
            const thumbnailHtml = thumbnail ? 
              `<div class="abp-v2-main-post-thumbnail">
                <a href="${href}">
                  <img src="${thumbnail.src}" alt="${thumbnail.alt || title}" loading="lazy">
                </a>
              </div>` : '';
            
            return `
              <article class="abp-v2-main-post-card">
                ${thumbnailHtml}
                <h3 class="abp-v2-main-post-title">
                  <a href="${href}">${title}</a>
                </h3>
                <div class="abp-v2-main-post-excerpt">
                  ${excerpt}
                </div>
                <div class="abp-v2-main-post-meta">
                  <time datetime="${date}">${date}</time>
                </div>
              </article>
            `;
          }).join('');
          
          if (cardsHtml) {
            postsGrid.innerHTML = cardsHtml;
            console.log('Updated posts grid with', items.length, 'articles');
          } else {
            postsGrid.innerHTML = `
              <div class="abp-v2-main-no-posts">
                <p>На букву «${letter}» пока нет статей.</p>
              </div>
            `;
            console.log('No articles found for letter:', letter);
          }
        }
        
        // Обновляем URL без перезагрузки
        const newUrl = `/${window.ABP_V2.slug}2/${encodeURIComponent(letter)}/`;
        window.history.pushState({letter}, '', newUrl);
        
      } else {
        console.error('Error loading posts:', json.data);
      }
    } catch (error) {
      console.error('Error loading posts:', error);
    }
  }

  // Инициализируем поиск после загрузки DOM
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeSearch);
  } else {
    initializeSearch();
  }

})();
