<?php

namespace App\Render;

class RutubeMetaBoxRenderer
{
	private $template_html_key = 'rutube_video_template_html';
	private $template_css_key = 'rutube_video_template_css';

	public function get_post_template_meta($post, $key)
	{
		return get_post_meta($post->ID, $key, true);
	}

	public function get_template_html($post)
	{
		$template = $this->get_post_template_meta($post, $this->template_html_key);
		return !empty($template) ? $template : $this->get_default_template_html();
	}

	public function get_template_css($post)
	{
		$template = $this->get_post_template_meta($post, $this->template_css_key);
		return !empty($template) ? $template : $this->get_default_template_css();
	}

	public function render($post)
	{
		$channel_id = get_post_meta($post->ID, 'rutube_channel_id', true);
		$video_limit = get_post_meta($post->ID, 'rutube_video_limit', true);
		$video_limit = !empty($video_limit) ? $video_limit : 6;
		$template_html = $this->get_post_template_meta($post, $this->template_html_key);
		$template_css = $this->get_post_template_meta($post, $this->template_css_key);

?>
		<div class="rutube-tabs">
			<ul class="rutube-tabs-nav">
				<li><a href="#rutube-settings" class="active">Настройки</a></li>
				<li><a href="#rutube-videos">Видео</a></li>
				<li><a href="#rutube-template">Шаблон</a></li>
				<li><a href="#rutube-management">Управление</a></li>
			</ul>
			<div class="rutube-tabs-content">
				<form method="post" action="options.php">
					<?php wp_nonce_field('save_rutube_meta', 'rutube_meta_nonce'); ?>
					<?php
					$this->render_settings_tab($channel_id, $video_limit);
					$this->render_videos_tab($channel_id, $video_limit);
					$this->render_template_tab($template_html, $template_css);
					$this->render_management_tab();
					?>
					<p class="btn btn-submit">
						<input type="submit" name="submit" id="submit" class="button button-primary" value="Сохранить">
					</p>
				</form>
			</div>
		</div>
	<?php
	}

	private function render_settings_tab($channel_id, $video_limit)
	{
	?>
		<div id="rutube-settings" class="tab-content active">
			<div class="block">
				<table class="form-table">
					<tr>
						<th scope="row"><label for="rutube_channel_id">ID канала</label></th>
						<td>
							<input type="text" id="rutube_channel_id" name="rutube_channel_id"
								value="<?php echo esc_attr($channel_id); ?>" class="regular-text">
							<p class="description">Введите ID вашего канала на Rutube. Например: <code>https://rutube.ru/channel/123456/</code></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="rutube_video_limit">Лимит на показ</label></th>
						<td>
							<input type="number" id="rutube_video_limit" name="rutube_video_limit"
								value="<?php echo esc_attr($video_limit); ?>" class="small-text" min="1" max="20">
							<p class="description">Введите число от 1 до 20 — максимальное количество видео для показа за раз</p>
						</td>
					</tr>
				</table>
			</div>
		</div>
	<?php
	}
	private function render_videos_tab($channel_id, $video_limit)
	{
	?>
		<div id="rutube-videos" class="tab-content">
			<?php $this->render_video_contaniner_and_modal($channel_id, $video_limit); ?>
		</div>
	<?php
	}
	public function render_video_contaniner_and_modal($channel_id, $video_limit, $render = [])
	{
	?>
		<div class="block">
			<div id="rutube-video-container" data-channel="<?php echo esc_attr($channel_id); ?>"
				data-limit="<?php echo esc_attr($video_limit); ?>" data-page="1">
				<div class="rutube-video-grid">
					<?php if (!empty($render['render_videos_html'])) {
						echo $render['render_videos_html'];
					}
					?>
				</div>
			</div>

			<div id="rutube-loading-items">
				<?php if (!empty($render['loading_html'])) {
					echo $render['loading_html'];
				} ?>
			</div>
		</div>
		<div id="rutube-modal" class="rutube-modal">
			<div class="modal-content">
				<span class="modal-close">&times;</span>
				<div class="video-container">
					<iframe id="rutube-iframe" width="720" height="405" frameborder="0"
						allow="clipboard-write; autoplay" webkitAllowFullScreen
						mozallowfullscreen allowFullScreen></iframe>
				</div>
			</div>
		</div>
	<?php
	}
	private function render_template_tab($template_html, $template_css)
	{
	?>

		<div id="rutube-template" class="tab-content">
			<div class="block">
				<h3>HTML Шаблон видео</h3>
				<?php
				$buttons = [
					'{url}' => 'Вставить URL',
					'{thumbnail_url}' => 'Вставить Миниатюру',
					'{date}' => 'Вставить Дату',
					'{title}' => 'Вставить Заголовок',
					'{duration}' => 'Вставить Длительность',
					'{pg_rating}' => 'Вставить Возрастной рейтинг',
					'{channel}' => 'Вставить Канал',
					'{description}' => 'Вставить Описание',
				];
				$description = "<p><strong>{date}</strong> — Вставляет дату в формате по умолчанию <code>d F Y H:i:s</code>.</p>
 				<p>Вы можете указать свой формат, например: <code>{date d.m.Y}</code> (28.03.2021) или <code>{date Y-m-d}</code> (2021-03-28).</p>
				<p>Доступные форматы:
				<ul>
					<li><code>d</code> — день (01-31)</li>
					<li><code>m</code> — месяц (01-12)</li>
					<li><code>F</code> — название месяца (Март)</li>
					<li><code>Y</code> — год (2021)</li>
					<li><code>H:i:s</code> — часы:минуты:секунды (21:21:19)</li>
				</ul>
 				</p>";
				$this->render_editor($template_html, $this->template_html_key, 'html', $buttons, $description);
				?>
				</form>
			</div>
			<div class="block">
				<h3>CSS Шаблон видео</h3>
				<?php
				$this->render_editor($template_css, $this->template_css_key, 'css');
				?>
				</form>
			</div>
		</div>
	<?php
	}
	private function render_management_tab()
	{
	?>
		<div id="rutube-management" class="tab-content">
			<div class="block">
				<h3>Управление</h3>
				<div class="clear-block">
					<button id="clear-rutube-cache" class="button button-secondary">Очистить кеш</button>
					<div id="cache-message-box"></div>
				</div>
			</div>
		</div>
	<?php
	}

	private function render_editor($template, $template_key, $editor_mode, $buttons = [], $description = null)
	{
	?>
		<div class="editor">
			<?php if (!empty($buttons)) {
				$this->render_editor_buttons($buttons);
			}
			if (!empty($description)) : ?>
				<div class="editor-description">
					<?php echo $description; ?>
				</div>
			<?php endif; ?>
			<textarea class="editor-template" name="<?php echo esc_attr($template_key); ?>" data-editor-mode="<?php echo $editor_mode ?>" rows="10" style="width:100%;"><?php echo esc_textarea($template); ?></textarea>
			<div class="editor-shortcuts">
				<p><strong>Горячие клавиши:</strong></p>
				<ul>
					<li><kbd>Ctrl + D</kbd> – Дублировать строку</li>
					<li><kbd>Ctrl + I</kbd> – Форматировать код</li>
				</ul>
			</div>
		</div>
	<?php
	}

	private function render_editor_buttons($buttons)
	{
	?>
		<div class="editor-buttons">
			<?php foreach ($buttons as $key => $label) : ?>
				<button data-insert="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></button>
			<?php endforeach; ?>
		</div>
<?php
	}


	private function get_default_template_html()
	{
		return '<div class="rutube-video">
			<div class="video-thumbnail">
				<div class="preloader"></div>
				<img class="lazy-thumbnail" src="{thumbnail_url}" alt="{title}">
				<button class="play-button" data-src="{url}">▶</button>
			</div>
			<div class="video-info">
				<p class="video-title">{title}</p>
				<span class="video-date">{date}</span>
				<span class="video-duration">{duration}</span>
				<span class="video-age">Возраст: {pg_rating}+</span>
				<span class="video-channel">Канал: {channel}</span>
			</div>
		</div>';
	}

	private function get_default_template_css()
	{
		return "
		#rutube-video-container .rutube-video-grid:not(:last-child) {
			margin-bottom: 15px;
		}

		.rutube-video-grid {
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			gap: 15px;
		}

		.rutube-video {
			background: #fff;
			padding: 10px;
			border-radius: 8px;
			text-align: center;
			box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
		}

		#rutube-loading-items {
			text-align: center;
			margin-top: 15px;
		}

		#rutube-loading-items button {
			padding: 8px 15px;
			background: #0073aa;
			color: #fff;
			border: none;
			border-radius: 5px;
			cursor: pointer;
			margin: 5px;
			transition: background 0.3s;
		}

		#rutube-loading-items button:hover {
			background: #005a87;
		}
        .rutube-video {
            background: #fff;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .video-thumbnail {
            position: relative;
        }

        .rutube-video img {
            width: 100%;
            height: auto;
            border-radius: 6px;
            display: block;
            margin-bottom: 10px;
        }

        .video-info {
            padding: 5px;
        }

        .video-title {
            font-weight: bold;
            color: #333;
        }

        .video-date {
            color: #777;
            font-size: 14px;
        }

		.play-button {
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			background: rgba(0, 0, 0, 0.7);
			color: #fff;
			font-size: 40px;
			padding: 15px 24px 15px 31px;
			border-radius: 50%;
			border: none;
			cursor: pointer;
			transition: background 0.3s ease;
			display: none;
		}

		.play-button:hover {
			background: rgba(0, 0, 0, 0.9);
		}
		
		.preloader {
			width: 100%;
			height: 100%;
			background: url('https://i.gifer.com/ZZ5H.gif') center center no-repeat;
			background-size: 50px 50px;
			position: absolute;
			top: 0;
			left: 0;
			z-index: 2;
		}
		.video-thumbnail {
			position: relative;
			display: inline-block;
			width: 100%;
			height: auto;
			aspect-ratio: 16 / 9;
		}


		.lazy-thumbnail {
			display: none;
			width: 100%;
			height: auto;
		}
		
		@media (max-width: 1200px) {
			.rutube-video-grid {
				grid-template-columns: repeat(2, 1fr);
			}
		}

		@media (max-width: 768px) {
			.rutube-video-grid {
				grid-template-columns: repeat(1, 1fr);
			}

			#rutube-loading-items button {
				width: 100%;
			}
		}
    ";
	}

	public function get_default_template_loading()
	{
		return "<button class=\"next-page\">Показать ещё</button>";
	}
}
