import { RutubeAdminParams } from "./interface/RutubeAdminParams.interface";
import { RutubeParams } from "./interface/RutubeParams";
import { loadVideos } from "./videoLoader";

export function showLoadMoreButton(
  params: RutubeAdminParams | RutubeParams,
  hasMore: boolean,
  count: number,
  page: number,
  videoBlock: HTMLElement,
  loadingItems: HTMLElement,
  channel?: string,
  limit?: string
): void {
  loadingItems.innerHTML = "";
  if (hasMore && count) {
    const nextButton = document.createElement("button");
    nextButton.textContent = "Показать ещё";
    nextButton.classList.add("next-page");
    nextButton.addEventListener("click", (e) => {
      e.preventDefault();
      loadVideos(
        page + 1,
        params,
        videoBlock,
        loadingItems,
        channel,
        limit,
        true
      );
    });
    loadingItems.appendChild(nextButton);
  }
}
