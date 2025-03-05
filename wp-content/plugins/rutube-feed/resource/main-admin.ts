import { loadVideos } from "./ts/videoLoader";
import { setupModal } from "./ts/modal";
import { setupTabs } from "./ts/tabs";
import { setupEditor } from "./ts/editor";
import { setupCacheClear } from "./ts/cache";
import { RutubeAdminParams } from "./ts/interface/RutubeAdminParams.interface";

declare const rutubeAdminParams: RutubeAdminParams;

document.addEventListener("DOMContentLoaded", () => {
  const container = document.getElementById("rutube-video-container");
  const videoBlock = container?.querySelector(
    ".rutube-video-grid"
  ) as HTMLElement;
  const channel = container?.dataset.channel;
  const limit = container?.dataset.limit;
  const loadingItems = document.getElementById("rutube-loading-items");

  if (videoBlock && loadingItems) {
    loadVideos(1, rutubeAdminParams, videoBlock, loadingItems, channel, limit);
  }

  setupModal();
  setupTabs();
  document.querySelectorAll(".editor-template").forEach((textarea) => {
    if (textarea instanceof HTMLTextAreaElement) {
      setupEditor(textarea);
    }
  });
  if (channel) setupCacheClear(rutubeAdminParams, channel);
});
