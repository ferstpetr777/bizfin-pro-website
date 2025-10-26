jQuery(document).ready(function($) {
    'use strict';
    
    const $form = $('#crc-rating-form');
    const $result = $('#crc-result');
    const $submitBtn = $('#crc-submit-btn');
    const $buttonText = $('.crc-button-text');
    const $spinner = $('.crc-spinner');
    const $innInput = $('#crc-inn');
    
    // Форматирование ИНН при вводе
    $innInput.on('input', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        if (value.length > 12) {
            value = value.substring(0, 12);
        }
        $(this).val(value);
    });
    
    // Обработка отправки формы
    $form.on('submit', function(e) {
        e.preventDefault();
        
        const inn = $innInput.val().trim();
        
        if (!inn) {
            showError('Пожалуйста, введите ИНН');
            return;
        }
        
        if (inn.length !== 10 && inn.length !== 12) {
            showError('ИНН должен содержать 10 или 12 цифр');
            return;
        }
        
        getCompanyRating(inn);
    });
    
    function getCompanyRating(inn) {
        setLoading(true);
        hideResult();
        
        var ajaxData = {
            action: 'crc_get_company_rating',
            inn: inn
        };
        
        // Добавляем nonce только для авторизованных пользователей
        if (crc_ajax.is_logged_in && crc_ajax.nonce) {
            ajaxData.nonce = crc_ajax.nonce;
        }
        
        $.ajax({
            url: crc_ajax.ajax_url,
            type: 'POST',
            data: ajaxData,
            timeout: 120000, // Увеличиваем таймаут до 2 минут
            success: function(response) {
                if (response.success) {
                    showSuccess(response.data);
                } else {
                    showError(response.data || 'Произошла ошибка при получении данных');
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'Произошла ошибка при запросе к серверу';
                
                if (status === 'timeout') {
                    errorMessage = 'Превышено время ожидания ответа от сервера. Проверка рейтинга может занять до 2 минут из-за большого количества источников данных. Попробуйте еще раз или обратитесь к администратору.';
                } else if (xhr.status === 0) {
                    errorMessage = 'Ошибка сети. Проверьте подключение к интернету';
                } else if (xhr.status >= 500) {
                    errorMessage = 'Ошибка сервера. Попробуйте позже';
                } else if (xhr.status === 403) {
                    errorMessage = 'Доступ запрещен. Проверьте настройки API';
                } else if (xhr.status === 408) {
                    errorMessage = 'Превышено время ожидания. Сервер обрабатывает запрос слишком долго. Попробуйте еще раз.';
                }
                
                showError(errorMessage);
            },
            complete: function() {
                setLoading(false);
            }
        });
    }
    
    function setLoading(loading) {
        if (loading) {
            $submitBtn.prop('disabled', true);
            $buttonText.hide();
            $spinner.show();
            
            // Показываем сообщение о том, что процесс может занять время
            setTimeout(function() {
                if ($submitBtn.prop('disabled')) {
                    showProgressMessage('Получение данных из различных источников... Это может занять до 2 минут.');
                }
            }, 10000); // Показываем через 10 секунд
        } else {
            $submitBtn.prop('disabled', false);
            $buttonText.show();
            $spinner.hide();
            hideProgressMessage();
        }
    }
    
    function showProgressMessage(message) {
        let progressHtml = '<div id="crc-progress" style="text-align: center; padding: 15px; background: #f0f8ff; border: 1px solid #b0d4f1; border-radius: 5px; margin: 10px 0; color: #0066cc;">';
        progressHtml += '<div style="font-size: 14px; font-weight: 600; margin-bottom: 5px;">⏳ Обработка запроса</div>';
        progressHtml += '<div style="font-size: 12px;">' + escapeHtml(message) + '</div>';
        progressHtml += '</div>';
        
        $result.html(progressHtml).removeClass('error').show();
    }
    
    function hideProgressMessage() {
        $('#crc-progress').remove();
    }
    
    function showSuccess(data) {
        const company = data.company;
        const rating = data.rating;
        
        let html = '<div class="crc-company-info">';
        html += '<div class="crc-company-name">' + escapeHtml(company.name.full_with_opf || company.name.full) + '</div>';
        html += '<div class="crc-company-details">';
        html += '<strong>ИНН:</strong> ' + escapeHtml(company.inn) + '<br>';
        html += '<strong>ОГРН:</strong> ' + escapeHtml(company.ogrn) + '<br>';
        html += '<strong>Статус:</strong> ' + getStatusText(company.state.status) + '<br>';
        html += '<strong>Адрес:</strong> ' + escapeHtml(company.address.value) + '<br>';
        html += '<strong>ОКВЭД:</strong> ' + escapeHtml(company.okved) + '<br>';
        html += '<strong>Дата регистрации:</strong> ' + formatDate(company.state.registration_date);
        
        // Добавляем информацию о МСП если есть
        if (company.msp && company.msp.category) {
            html += '<br><strong>Статус МСП:</strong> ' + escapeHtml(company.msp.category);
        }
        
        // Добавляем информацию из ЕГРЮЛ если есть
        if (company.egrul && company.egrul.g) {
            html += '<br><strong>Руководитель:</strong> ' + escapeHtml(company.egrul.g);
        }
        
        // Добавляем информацию об арбитражных рисках если есть
        if (company.arbitration && company.arbitration.risk_level) {
            var riskColor = company.arbitration.risk_level === 'low' ? 'green' : 
                           company.arbitration.risk_level === 'medium' ? 'orange' : 'red';
            html += '<br><strong>Арбитражные риски:</strong> <span style="color: ' + riskColor + ';">' + 
                    escapeHtml(company.arbitration.recommendation) + '</span>';
        }
        
        // Добавляем информацию о государственных закупках если есть - ОТКЛЮЧЕНО
        // if (company.zakupki && company.zakupki.summary) {
        //     var zakupkiLevel = company.zakupki.summary.reputation_level;
        //     var zakupkiColor = zakupkiLevel === 'excellent' ? 'green' : 
        //                       zakupkiLevel === 'good' ? 'lightgreen' :
        //                       zakupkiLevel === 'average' ? 'orange' : 'red';
        //     var contracts = company.zakupki.total_contracts || 0;
        //     var amount = company.zakupki.total_amount || 0;
        //     html += '<br><strong>Госзакупки:</strong> <span style="color: ' + zakupkiColor + ';">' + 
        //             escapeHtml(company.zakupki.summary.recommendation) + 
        //             (contracts > 0 ? ' (' + contracts + ' контрактов, ' + 
        //              amount.toLocaleString('ru-RU') + ' руб.)' : '') + '</span>';
        // }
        
        // Добавляем информацию о ФНС данных если есть - ОТКЛЮЧЕНО
        // if (company.fns && company.fns.revenue) {
        //     var revenue = company.fns.revenue || 0;
        //     var profitability = company.fns.profitability || 0;
        //     var bankruptcyRisk = company.fns.bankruptcy_risk || 'unknown';
        //     var riskColor = bankruptcyRisk === 'low' ? 'green' : 
        //                    bankruptcyRisk === 'medium' ? 'orange' : 'red';
        //     html += '<br><strong>ФНС данные:</strong> Выручка: ' + revenue.toLocaleString('ru-RU') + ' руб., ' +
        //             'Рентабельность: ' + profitability.toFixed(1) + '%, ' +
        //             '<span style="color: ' + riskColor + ';">Риск банкротства: ' + bankruptcyRisk + '</span>';
        // }
        
        // Добавляем информацию о Росстат данных если есть
        if (company.rosstat && company.rosstat.region) {
            var regionName = company.rosstat.region.region_name || 'Не указан';
            var sectorName = company.rosstat.sector ? company.rosstat.sector.sector_name : 'Не указана';
            html += '<br><strong>Росстат данные:</strong> Регион: ' + escapeHtml(regionName) + 
                    ', Отрасль: ' + escapeHtml(sectorName);
        }
        
        // Добавляем информацию о ЕФРСБ данных если есть
        if (company.efrsb && company.efrsb.bankruptcy_status) {
            var bankruptcyStatus = company.efrsb.bankruptcy_status;
            var riskLevel = company.efrsb.bankruptcy_risk_level || 'unknown';
            var riskColor = riskLevel === 'low' ? 'green' : 
                           riskLevel === 'medium' ? 'orange' : 'red';
            var casesCount = company.efrsb.bankruptcy_cases ? company.efrsb.bankruptcy_cases.length : 0;
            html += '<br><strong>ЕФРСБ данные:</strong> <span style="color: ' + riskColor + ';">' + 
                    escapeHtml(bankruptcyStatus) + '</span>, ' +
                    'Дел: ' + casesCount;
        }
        
        // Добавляем информацию о РНП данных если есть
        if (company.rnp && company.rnp.is_dishonest_supplier !== undefined) {
            var isDishonest = company.rnp.is_dishonest_supplier;
            var violationCount = company.rnp.violation_count || 0;
            var reputation = company.rnp.reputation_impact || 'unknown';
            var reputationColor = reputation === 'positive' ? 'green' : 
                                 reputation === 'negative' ? 'red' : 'orange';
            html += '<br><strong>РНП данные:</strong> <span style="color: ' + reputationColor + ';">' + 
                    (isDishonest ? 'В реестре недобросовестных поставщиков' : 'Не в реестре') + 
                    '</span>, Нарушений: ' + violationCount;
        }
        
        // Добавляем информацию о ФССП данных если есть - ОТКЛЮЧЕНО
        // if (company.fssp && company.fssp.has_enforcement_proceedings !== undefined) {
        //     var hasProceedings = company.fssp.has_enforcement_proceedings;
        //     var proceedingsCount = company.fssp.proceedings_count || 0;
        //     var totalDebt = company.fssp.total_debt_amount || 0;
        //     var riskLevel = company.fssp.financial_risk_level || 'unknown';
        //     var riskColor = riskLevel === 'low' ? 'green' : 
        //                    riskLevel === 'medium' ? 'orange' : 'red';
        //     html += '<br><strong>ФССП данные:</strong> <span style="color: ' + riskColor + ';">' + 
        //             (hasProceedings ? 'Есть исполнительные производства' : 'Нет исполнительных производств') + 
        //             '</span>, Производств: ' + proceedingsCount + 
        //             (totalDebt > 0 ? ', Задолженность: ' + totalDebt.toLocaleString('ru-RU') + ' руб.' : '');
        // }
        
        html += '</div>';
        html += '</div>';
        
        html += '<div class="crc-rating-display" style="background: ' + rating.rating.color + '; color: white;">';
        html += '<div class="crc-rating-score">' + rating.total_score + '/' + rating.max_score + '</div>';
        html += '<div class="crc-rating-level">' + rating.rating.level + '</div>';
        html += '<div class="crc-rating-name">' + rating.rating.name + '</div>';
        html += '</div>';
        
        html += '<div class="crc-factors">';
        html += '<h4 class="crc-factors-title">Детализация рейтинга</h4>';
        
        Object.keys(rating.factors).forEach(function(key) {
            const factor = rating.factors[key];
            const percentage = Math.round((factor.score / factor.max_score) * 100);
            
            html += '<div class="crc-factor">';
            html += '<div class="crc-factor-info">';
            html += '<div class="crc-factor-name">' + escapeHtml(factor.name) + '</div>';
            html += '<div class="crc-factor-description">' + escapeHtml(factor.description) + '</div>';
            html += '<div class="crc-progress-bar">';
            html += '<div class="crc-progress-fill" style="width: ' + percentage + '%"></div>';
            html += '</div>';
            html += '</div>';
            html += '<div class="crc-factor-score">';
            html += '<div class="crc-factor-score-value">' + factor.score + '</div>';
            html += '<div class="crc-factor-score-max">из ' + factor.max_score + '</div>';
            html += '</div>';
            html += '</div>';
        });
        
        html += '</div>';
        
        // Добавляем кнопки свернуть (верхний правый и нижний правый углы)
        html += '<button type="button" class="crc-collapse-btn crc-collapse-top" aria-label="Свернуть">Свернуть</button>';
        html += '<button type="button" class="crc-collapse-btn crc-collapse-bottom" aria-label="Свернуть">Свернуть</button>';

        $result.html(html).removeClass('error').show();
        
        // Плавная прокрутка к результату
        $('html, body').animate({
            scrollTop: $result.offset().top - 100
        }, 500);

        // Навешиваем обработчики на кнопки свернуть
        $result.find('.crc-collapse-btn').off('click').on('click', function(){
            $result.slideUp(200);
        });
    }
    
    function showError(message) {
        let html = '<div style="text-align: center; padding: 20px;">';
        html += '<div style="font-size: 48px; margin-bottom: 15px;">⚠️</div>';
        html += '<div style="font-size: 16px; font-weight: 600; margin-bottom: 10px;">Ошибка</div>';
        html += '<div style="font-size: 14px; color: #666;">' + escapeHtml(message) + '</div>';
        html += '</div>';
        
        $result.html(html).addClass('error').show();
        
        // Плавная прокрутка к результату
        $('html, body').animate({
            scrollTop: $result.offset().top - 100
        }, 500);
    }
    
    function hideResult() {
        $result.hide();
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    function getStatusText(status) {
        const statusMap = {
            'ACTIVE': 'Действует',
            'LIQUIDATING': 'В процессе ликвидации',
            'LIQUIDATED': 'Ликвидирована',
            'BANKRUPT': 'Банкротство',
            'REORGANIZING': 'В процессе реорганизации'
        };
        return statusMap[status] || status || 'Неизвестно';
    }
    
    function formatDate(timestamp) {
        if (!timestamp) return 'Не указана';
        
        const date = new Date(timestamp);
        if (isNaN(date.getTime())) return 'Неверная дата';
        
        return date.toLocaleDateString('ru-RU', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }
    
    // Автофокус на поле ввода
    $innInput.focus();
    
    // Обработка Enter в поле ввода
    $innInput.on('keypress', function(e) {
        if (e.which === 13) {
            $form.submit();
        }
    });
});

