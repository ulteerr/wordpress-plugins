export function setupCacheClear(): void {
  const clearCacheBtn = document.getElementById("clear-rutube-cache");
  const messageBox = document.getElementById("cache-message-box");

  if (!clearCacheBtn || !messageBox) return;

  clearCacheBtn.addEventListener("click", async (e) => {
    e.preventDefault();

    try {
      const response = await fetch("/clear_cache", { method: "POST" });
      const data = await response.json();

      messageBox.textContent = data.success
        ? "Кеш очищен"
        : "Ошибка очистки кеша";
      messageBox.style.color = data.success ? "green" : "red";
    } catch {
      messageBox.textContent = "Ошибка запроса";
      messageBox.style.color = "red";
    }
  });
}
