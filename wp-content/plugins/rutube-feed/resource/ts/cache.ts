import { RutubeAdminParams } from "./interface/RutubeAdminParams.interface";

export function setupCacheClear(
  rutubeAdminParams: RutubeAdminParams,
  channel: string
): void {
  const clearCacheBtn = document.getElementById("clear-rutube-cache");
  const messageBox = document.getElementById("cache-message-box");

  if (!clearCacheBtn || !messageBox) return;

  if (!rutubeAdminParams.cache_enabled) {
    messageBox.style.color = "red";
    messageBox.innerHTML =
      "Объектный кеш не активирован! Установите и настройте Redis или Memcached.";
    if (clearCacheBtn) {
      clearCacheBtn.style.display = "none";
    }
  } else {
    clearCacheBtn.addEventListener("click", async (e) => {
      e.preventDefault();
      const formData = new FormData();
      formData.append("action", "clear_videos_cache");
      formData.append("channel", channel);
      try {
        const response = await fetch(rutubeAdminParams.ajax_url, {
          method: "POST",
          body: formData,
        });
        const data = await response.json();

        if (data.success) {
          messageBox.textContent = data.data.message;
          messageBox.style.color = "green";
          await new Promise((resolve) => setTimeout(resolve, 1500));
          location.reload();
        } else {
          messageBox.textContent = "Ошибка очистки кеша";
          messageBox.style.color = "red";
        }
      } catch {
        messageBox.textContent = "Ошибка запроса";
        messageBox.style.color = "red";
      }
    });
  }
}
