<?php
// Предотвращаем прямой доступ
if (!defined('ABSPATH')) {
    exit;
}

// Получаем статистику
$args = array(
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'post_status' => 'inherit',
    'posts_per_page' => -1,
    'orderby' => 'ID',
    'order' => 'ASC'
);

$all_images = get_posts($args);
$compressed_images = array();
$uncompressed_images = array();
$total_original_size = 0;
$total_compressed_size = 0;

foreach ($all_images as $image) {
    $file_path = get_attached_file($image->ID);
    if ($file_path && file_exists($file_path)) {
        $file_size = filesize($file_path);
        $total_original_size += $file_size;
        
        $compressed = get_post_meta($image->ID, '_sic_compressed', true);
        if ($compressed) {
            $compressed_images[] = $image;
            $total_compressed_size += $file_size;
        } else {
            $uncompressed_images[] = $image;
        }
    }
}

$total_images = count($all_images);
$compressed_count = count($compressed_images);
$uncompressed_count = count($uncompressed_images);
$savings = $total_original_size - $total_compressed_size;
$savings_percentage = $total_original_size > 0 ? round(($savings / $total_original_size) * 100, 1) : 0;

// Форматируем размеры
function format_size($bytes) {
    $units = array('B', 'KB', 'MB', 'GB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}
?>

<div class="wrap">
    <h1>Статистика Smart Image Compressor</h1>
    
    <div class="sic-statistics-dashboard">
        <!-- Общая статистика -->
        <div class="sic-stats-grid">
            <div class="sic-stat-card">
                <div class="sic-stat-icon">📊</div>
                <div class="sic-stat-content">
                    <h3>Всего изображений</h3>
                    <div class="sic-stat-number"><?php echo $total_images; ?></div>
                </div>
            </div>
            
            <div class="sic-stat-card sic-success">
                <div class="sic-stat-icon">✅</div>
                <div class="sic-stat-content">
                    <h3>Сжато</h3>
                    <div class="sic-stat-number"><?php echo $compressed_count; ?></div>
                    <div class="sic-stat-percentage"><?php echo $total_images > 0 ? round(($compressed_count / $total_images) * 100, 1) : 0; ?>%</div>
                </div>
            </div>
            
            <div class="sic-stat-card sic-warning">
                <div class="sic-stat-icon">⚠️</div>
                <div class="sic-stat-content">
                    <h3>Не сжато</h3>
                    <div class="sic-stat-number"><?php echo $uncompressed_count; ?></div>
                    <div class="sic-stat-percentage"><?php echo $total_images > 0 ? round(($uncompressed_count / $total_images) * 100, 1) : 0; ?>%</div>
                </div>
            </div>
            
            <div class="sic-stat-card sic-info">
                <div class="sic-stat-icon">💾</div>
                <div class="sic-stat-content">
                    <h3>Экономия места</h3>
                    <div class="sic-stat-number"><?php echo format_size($savings); ?></div>
                    <div class="sic-stat-percentage"><?php echo $savings_percentage; ?>%</div>
                </div>
            </div>
        </div>
        
        <!-- Детальная статистика -->
        <div class="sic-detailed-stats">
            <div class="sic-stat-section">
                <h2>Размеры файлов</h2>
                <div class="sic-size-stats">
                    <div class="sic-size-item">
                        <span class="sic-size-label">Исходный размер:</span>
                        <span class="sic-size-value"><?php echo format_size($total_original_size); ?></span>
                    </div>
                    <div class="sic-size-item">
                        <span class="sic-size-label">Текущий размер:</span>
                        <span class="sic-size-value"><?php echo format_size($total_compressed_size); ?></span>
                    </div>
                    <div class="sic-size-item sic-savings">
                        <span class="sic-size-label">Экономия:</span>
                        <span class="sic-size-value"><?php echo format_size($savings); ?> (<?php echo $savings_percentage; ?>%)</span>
                    </div>
                </div>
            </div>
            
            <div class="sic-stat-section">
                <h2>Прогресс сжатия</h2>
                <div class="sic-progress-container">
                    <div class="sic-progress-bar">
                        <div class="sic-progress-fill" style="width: <?php echo $total_images > 0 ? ($compressed_count / $total_images) * 100 : 0; ?>%"></div>
                    </div>
                    <div class="sic-progress-text">
                        <?php echo $compressed_count; ?> из <?php echo $total_images; ?> изображений сжато
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Топ изображений по размеру -->
        <div class="sic-stat-section">
            <h2>Самые большие изображения</h2>
            <div class="sic-large-images">
                <?php
                $large_images = array();
                foreach ($all_images as $image) {
                    $file_path = get_attached_file($image->ID);
                    if ($file_path && file_exists($file_path)) {
                        $file_size = filesize($file_path);
                        $large_images[] = array(
                            'id' => $image->ID,
                            'title' => $image->post_title,
                            'size' => $file_size,
                            'compressed' => get_post_meta($image->ID, '_sic_compressed', true)
                        );
                    }
                }
                
                // Сортируем по размеру
                usort($large_images, function($a, $b) {
                    return $b['size'] - $a['size'];
                });
                
                $top_images = array_slice($large_images, 0, 10);
                
                foreach ($top_images as $img): ?>
                    <div class="sic-large-image-item <?php echo $img['compressed'] ? 'compressed' : 'uncompressed'; ?>">
                        <div class="sic-image-info">
                            <h4><?php echo esc_html($img['title']); ?></h4>
                            <p>ID: <?php echo $img['id']; ?> | Размер: <?php echo format_size($img['size']); ?></p>
                        </div>
                        <div class="sic-image-status">
                            <?php if ($img['compressed']): ?>
                                <span class="sic-status compressed">Сжато</span>
                            <?php else: ?>
                                <span class="sic-status uncompressed">Не сжато</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Рекомендации -->
        <div class="sic-stat-section">
            <h2>Рекомендации</h2>
            <div class="sic-recommendations">
                <?php if ($uncompressed_count > 0): ?>
                    <div class="sic-recommendation sic-warning">
                        <h4>⚠️ Есть не сжатые изображения</h4>
                        <p>У вас есть <?php echo $uncompressed_count; ?> изображений, которые можно сжать для экономии места.</p>
                        <a href="<?php echo admin_url('admin.php?page=smart-image-compressor'); ?>" class="button button-primary">
                            Сжать изображения
                        </a>
                    </div>
                <?php endif; ?>
                
                <?php if ($savings_percentage > 0): ?>
                    <div class="sic-recommendation sic-success">
                        <h4>✅ Отличная работа!</h4>
                        <p>Вы уже сэкономили <?php echo format_size($savings); ?> (<?php echo $savings_percentage; ?>%) дискового пространства.</p>
                    </div>
                <?php endif; ?>
                
                <div class="sic-recommendation sic-info">
                    <h4>💡 Совет</h4>
                    <p>Регулярно проверяйте новые изображения и сжимайте их для поддержания оптимальной производительности сайта.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.sic-statistics-dashboard {
    margin-top: 20px;
}

.sic-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.sic-stat-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.sic-stat-card.sic-success {
    border-left: 4px solid #46b450;
}

.sic-stat-card.sic-warning {
    border-left: 4px solid #ffb900;
}

.sic-stat-card.sic-info {
    border-left: 4px solid #0073aa;
}

.sic-stat-icon {
    font-size: 24px;
}

.sic-stat-content h3 {
    margin: 0 0 5px 0;
    color: #23282d;
    font-size: 14px;
    font-weight: 600;
}

.sic-stat-number {
    font-size: 28px;
    font-weight: bold;
    color: #23282d;
}

.sic-stat-percentage {
    font-size: 12px;
    color: #666;
    margin-top: 2px;
}

.sic-detailed-stats {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.sic-stat-section {
    margin-bottom: 30px;
}

.sic-stat-section:last-child {
    margin-bottom: 0;
}

.sic-stat-section h2 {
    margin-top: 0;
    color: #23282d;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.sic-size-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.sic-size-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: #f9f9f9;
    border-radius: 4px;
}

.sic-size-item.sic-savings {
    background: #d4edda;
    color: #155724;
}

.sic-size-label {
    font-weight: 600;
}

.sic-size-value {
    font-weight: bold;
}

.sic-progress-container {
    margin-top: 15px;
}

.sic-progress-bar {
    width: 100%;
    height: 20px;
    background: #f0f0f0;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 10px;
}

.sic-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #0073aa, #005177);
    transition: width 0.3s ease;
}

.sic-progress-text {
    text-align: center;
    font-weight: bold;
    color: #23282d;
}

.sic-large-images {
    max-height: 400px;
    overflow-y: auto;
}

.sic-large-image-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 10px;
    background: #fff;
}

.sic-large-image-item.compressed {
    border-left: 4px solid #46b450;
}

.sic-large-image-item.uncompressed {
    border-left: 4px solid #ffb900;
}

.sic-image-info h4 {
    margin: 0 0 5px 0;
    color: #23282d;
}

.sic-image-info p {
    margin: 0;
    font-size: 12px;
    color: #666;
}

.sic-recommendations {
    display: grid;
    gap: 15px;
}

.sic-recommendation {
    padding: 15px;
    border-radius: 4px;
    border-left: 4px solid;
}

.sic-recommendation.sic-warning {
    background: #fff3cd;
    border-color: #ffb900;
    color: #856404;
}

.sic-recommendation.sic-success {
    background: #d4edda;
    border-color: #46b450;
    color: #155724;
}

.sic-recommendation.sic-info {
    background: #d1ecf1;
    border-color: #0073aa;
    color: #0c5460;
}

.sic-recommendation h4 {
    margin: 0 0 10px 0;
}

.sic-recommendation p {
    margin: 0 0 10px 0;
}

@media (max-width: 768px) {
    .sic-stats-grid {
        grid-template-columns: 1fr;
    }
    
    .sic-size-stats {
        grid-template-columns: 1fr;
    }
}
</style>

