import { toggleDisplay } from "./ts/utils/toggleDisplay";
import { hidePreloader } from "./ts/utils/hidePreloader";
import { handleError } from "./ts/utils/handleError";

document.addEventListener("DOMContentLoaded", function () {
	let page = 1;
	const container = document.getElementById("rutube-video-container");
	const videoBlock = container?.querySelector(".rutube-video-grid");
	const channel = container?.dataset.channel;
	const limit = container?.dataset.limit;
	const loadingItems = document.getElementById("rutube-loading-items");
	let loading = false;


	async function loadVideos(pageNumber, append = false) {
		if (loading) return;
		loading = true;

		const formData = new FormData();
		formData.append("action", "load_admin_videos");
		formData.append("post_id", rutubeAdminParams.post_id);
		formData.append("channel", channel);
		formData.append("limit", limit);
		formData.append("page", pageNumber);

		try {
			const response = await fetch(rutubeAdminParams.ajax_url, {
				method: "POST",
				body: formData
			});
			const data = await response.json();

			if (data.success) {
				videoBlock.innerHTML = append ? videoBlock.innerHTML + data.data.html : data.data.html;

				document.querySelectorAll(".lazy-thumbnail").forEach(img => {
					img.addEventListener("load", function () {
						hidePreloader(this);
					});

					img.addEventListener("error", function () {
						handleError(this);
					});
				});
				showLoadMoreButton(data.data.has_more, data.data.count);
			}
		} catch (error) {
			console.error("Ошибка загрузки видео:", error);
		} finally {
			loading = false;
		}
	}

	function showLoadMoreButton(hasMore, count) {
		loadingItems.innerHTML = "";
		if (hasMore && count) {
			const nextButton = document.createElement("button");
			nextButton.textContent = "Показать ещё";
			nextButton.classList.add("next-page");
			nextButton.addEventListener("click", (e) => {
				e.preventDefault()
				if (!loading) {
					page++;
					loadVideos(page, true);
				}
			});
			loadingItems.appendChild(nextButton);
		}
	}

	function setupModal() {
		const modal = document.getElementById("rutube-modal");
		const iframe = document.getElementById("rutube-iframe");
		const closeBtn = document.querySelector(".modal-close");

		document.addEventListener("click", (event) => {
			if (event.target.classList.contains("play-button")) {
				event.preventDefault();
				const videoSrc = event.target.getAttribute("data-src");
				if (videoSrc) {
					iframe.src = videoSrc;
					toggleDisplay(modal, true, 'flex');
				}
			}
			if (event.target === modal || event.target === closeBtn) {
				toggleDisplay(modal, false);
				iframe.src = "";
			}
		});
	}

	function setupTabs() {
		const tabs = document.querySelectorAll(".rutube-tabs-nav a");
		const contents = document.querySelectorAll(".tab-content");

		tabs.forEach(tab => {
			tab.addEventListener("click", function (e) {
				e.preventDefault();


				tabs.forEach(t => t.classList.remove("active"));
				contents.forEach(c => c.classList.remove("active"));


				this.classList.add("active");
				const activeTabContent = document.querySelector(this.getAttribute("href"));
				activeTabContent?.classList.add("active");


				const textareas = activeTabContent?.querySelectorAll(".editor-template");
				textareas.forEach(textarea => {
					if (textarea && textarea._codeMirrorInstance) {
						setTimeout(() => {
							textarea._codeMirrorInstance.refresh();
						}, 50);
					}
				})
			});
		});
	}


	const clearCacheBtn = document.getElementById("clear-rutube-cache");
	const messageBox = document.getElementById("cache-message-box");


	if (!rutubeAdminParams.cache_enabled) {
		messageBox.style.color = "red";
		messageBox.innerHTML = "Объектный кеш не активирован! Установите и настройте Redis или Memcached.";
		if (clearCacheBtn) {
			clearCacheBtn.style.display = "none";
		}
	} else {
		if (clearCacheBtn) {
			clearCacheBtn.addEventListener("click", async function (e) {
				e.preventDefault()

				const formData = new FormData();
				formData.append("action", "clear_videos_cache");
				formData.append("channel", channel);
				try {
					const response = await fetch(rutubeAdminParams.ajax_url, {
						method: "POST",
						body: formData
					});

					const data = await response.json();

					if (data.success) {
						messageBox.textContent = data.data.message;
						messageBox.style.color = "green";
						await new Promise(resolve => setTimeout(resolve, 1500));
						location.reload();
					} else {
						messageBox.textContent = "Ошибка очистки кеша";
						messageBox.style.color = "red";
					}
				} catch (error) {
					messageBox.textContent = "Ошибка запроса";
					messageBox.style.color = "red";
				}
			});
		}
	}

	function formatEditorContent(editor, beautifyFunction) {
		if (typeof beautifyFunction !== "undefined") {
			const code = editor.getValue();
			const formatted = beautifyFunction(code, {
				indent_size: 4,
				wrap_line_length: 80,
				preserve_newlines: true
			});
			editor.setValue(formatted);
		} else {
			console.error("js-beautify не загружен!");
		}
	}

	const textareas = document.querySelectorAll(".editor-template")
	textareas.forEach(textarea => {
		const editor = CodeMirror.fromTextArea(textarea, {
			mode: textarea.dataset.editorMode ?? "htmlmixed",
			theme: "dracula",
			lineNumbers: true,
			matchBrackets: true,
			autoCloseBrackets: true,
			styleActiveLine: true,
			keyMap: "sublime",
			matchBrackets: true,
			extraKeys: {
				"Ctrl-D": "duplicateLine"
			},
		});
		if (editor.options.mode === 'htmlmixed') {
			editor.setOption("extraKeys", {
				"Ctrl-I": function (cm) {
					formatEditorContent(cm, html_beautify);
				}
			});
		}
	
		if (editor.options.mode === 'css') {
			editor.setOption("extraKeys", {
				"Ctrl-I": function (cm) {
					formatEditorContent(cm, css_beautify);
				}
			});
		}
		textarea._codeMirrorInstance = editor;

		editor.on("change", function () {
			textarea.value = editor.getValue();
		});

		CodeMirror.commands.duplicateLine = function (cm) {
			var cursor = cm.getCursor();
			var line = cm.getLine(cursor.line);
			cm.replaceRange(line + "\n", { line: cursor.line, ch: 0 });
		};

		document.querySelectorAll(".editor-buttons button").forEach(button => {
			button.addEventListener("click", function (e) {
				e.preventDefault()
				const param = this.getAttribute("data-insert");
				if (editor) {
					const doc = editor.getDoc();
					const cursor = doc.getCursor();
					doc.replaceRange(param, cursor);
				}
			});
		});
	})
	loadVideos(page);
	setupModal();
	setupTabs();
});